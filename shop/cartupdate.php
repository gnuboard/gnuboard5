<?php
include_once('./_common.php');

if ($sw_direct) {
    $tmp_uq_id = get_session('ss_uq_direct');
    if(!$tmp_uq_id) {
        $tmp_uq_id = get_uniqid();
        set_session('ss_uq_direct', $tmp_uq_id);
    }
}
else {
    $tmp_uq_id = get_session('ss_uq_id');
    if(!$tmp_uq_id) {
        $tmp_uq_id = get_uniqid();
        set_session('ss_uq_id', $tmp_uq_id);
    }
}

// 브라우저에서 쿠키를 허용하지 않은 경우라고 볼 수 있음.
if (!$tmp_uq_id)
{
    alert('더 이상 작업을 진행할 수 없습니다.\\n\\n브라우저의 쿠키 허용을 사용하지 않음으로 설정한것 같습니다.\\n\\n브라우저의 인터넷 옵션에서 쿠키 허용을 사용으로 설정해 주십시오.\\n\\n그래도 진행이 되지 않는다면 쇼핑몰 운영자에게 문의 바랍니다.');
}


// 레벨(권한)이 상품구입 권한보다 작다면 상품을 구입할 수 없음.
if ($member['mb_level'] < $default['de_level_sell'])
{
    alert('상품을 구입할 수 있는 권한이 없습니다.');
}


if ($act == "d") // 삭제이면
{
    $sql = " delete from {$g4['shop_cart_table']}
              where ct_id = '$ct_id'
                and uq_id = '$tmp_uq_id' ";
    sql_query($sql);
}
else if ($act == "alldelete") // 모두 삭제이면
{
    $sql = " delete from {$g4['shop_cart_table']}
              where uq_id = '$tmp_uq_id' ";
    sql_query($sql);
}
else if ($act == "allupdate") // 수량 변경이면 : 모두 수정이면
{
    $fldcnt = count($_POST['ct_id']);

    // 수량 변경, 재고등을 검사
    $error = "";
	for ($i=0; $i<$fldcnt; $i++)
    {
        // 재고 구함
        $stock_qty = get_it_stock_qty($_POST['it_id'][$i]);

        // 변경된 수량이 재고수량보다 크면 오류
        if ($_POST['ct_qty'][$i] > $stock_qty)
            $error .= "{$_POST['it_name'][$i]} 의 재고수량이 부족합니다. 현재 재고수량 : $stock_qty 개\\n\\n";
    }

    // 오류가 있다면 오류메세지 출력
    if ($error != "") { alert($error); }

	for ($i=0; $i<$fldcnt; $i++)
    {
        $sql = " update {$g4['shop_cart_table']}
                    set ct_qty = '{$_POST['ct_qty'][$i]}'
                  where ct_id  = '{$_POST['ct_id'][$i]}'
                    and uq_id = '$tmp_uq_id' ";
        sql_query($sql);
    }
}
else if ($act == "multi") // 온라인견적(등)에서 여러개의 상품이 한꺼번에 들어옴.
{
    // 보관함에서 금액이 제대로 반영되지 않던 오류를 수정
    $fldcnt = count($_POST['it_name']);

    // 재고등을 검사
    $error = "";
	for ($i=0; $i<$fldcnt; $i++)
    {
        if ($_POST['it_id'][$i] == "" || $_POST['ct_qty'][$i] <= 0) { continue; }

        //--------------------------------------------------------
        //  변조 검사
        //--------------------------------------------------------
        $sql = " select * from {$g4['shop_item_table']} where it_id = '{$_POST['it_id'][$i]}' ";
        $it = sql_fetch($sql);

        $price = get_price($it);
        // 상품가격이 다름
        if ((int)$price !== (int)$_POST['it_price'][$i])
            die("Error..");

        $point = $it['it_point'];
        // 포인트가 다름
        if ((int)$point !== (int)$_POST['it_point'][$i] && $config['cf_use_point'])
            die("Error...");
        //--------------------------------------------------------

        // 이미 장바구니에 있는 같은 상품의 수량합계를 구한다.
        $sql = " select SUM(ct_qty) as cnt from {$g4['shop_cart_table']} where it_id = '{$_POST['it_id'][$i]}' and uq_id = '$tmp_uq_id' ";
        $row = sql_fetch($sql);
        $sum_qty = $row['cnt'];

        // 재고 구함
        $it_stock_qty = get_it_stock_qty($_POST['it_id'][$i]);
        if ($_POST['ct_qty'][$i] + $sum_qty > $it_stock_qty) {
            $error .= "{$_POST['it_name'][$i]} 의 재고수량이 부족합니다. 현재 재고수량 : $it_stock_qty\\n\\n";
        }
    }

    // 오류가 있다면 오류메세지 출력
    if ($error != "") { alert($error); }

	for ($i=0; $i<$fldcnt; $i++)
    {
        if ($_POST['it_id'][$i] == "" || $_POST['ct_qty'][$i] <= 0) continue;

        // 포인트 사용하지 않는다면
        if (!$config['cf_use_point']) $_POST['it_point'][$i] = 0;

        // 장바구니에 Insert
        $sql = " insert {$g4['shop_cart_table']}
                    set uq_id       = '$tmp_uq_id',
                        it_id        = '{$_POST['it_id'][$i]}',
                        it_name      = '{$_POST['it_name'][$i]}',
                        ct_status    = '쇼핑',
                        ct_price     = '{$_POST['it_price'][$i]}',
                        ct_point     = '{$_POST['it_point'][$i]}',
                        ct_point_use = '0',
                        ct_stock_use = '0',
                        ct_qty       = '{$_POST['ct_qty'][$i]}',
                        ct_time      = '".G4_TIME_YMDHIS."',
                        ct_ip        = '$REMOTE_ADDR' ";
        sql_query($sql);
    }
}
else // 장바구니에 담기
{
    if (!$_POST['it_id'])
        alert('장바구니에 담을 상품을 선택하여 주십시오.');

    if ($_POST['ct_qty'] < 1)
        alert('수량은 1 이상 입력해 주십시오.');

    //--------------------------------------------------------
    //  변조 검사
    //--------------------------------------------------------
    $opt_amount = 0;
    $sql = " select * from {$g4['shop_item_table']} where it_id = '{$_POST['it_id']}' ";
    $it = sql_fetch($sql);
    for ($i=1; $i<=6; $i++) {
        //$dst_opt = $_POST["it_opt".$i];
        $dst_opt = trim($_POST["it_opt".$i]);
        if ($dst_opt) {
            $org_opt = $it["it_opt".$i];
            $exp_opt = explode("\n", trim($org_opt));
            $exists = false;
            for ($k=0; $k<count($exp_opt); $k++) {
                $opt = trim($exp_opt[$k]);
                if ($dst_opt == $opt) {
                    $exists = true;
                    $exp_option = explode(";", $opt);
                    $opt_amount += (int)$exp_option[1];
                    break;
                }
            }
            if ($exists == false) {
                // 옵션이 다름
                die("Error.");
            }
        }
    }

    $price = get_price($it) + $opt_amount;
    // 상품가격이 다름
    if ((int)$price !== (int)$_POST['it_price'])
        die("Error..");

    $point = $it['it_point'];
    // 포인트가 다름
    if ((int)$point !== (int)$_POST['it_point'] && $config['cf_use_point'])
        die("Error...");
    //--------------------------------------------------------


    //--------------------------------------------------------
    //  재고 검사
    //--------------------------------------------------------
    // 이미 장바구니에 있는 같은 상품의 수량합계를 구한다.
    $sql = " select SUM(ct_qty) as cnt from {$g4['shop_cart_table']}
              where it_id = '{$_POST['it_id']}'
                and uq_id = '$tmp_uq_id' ";
    $row = sql_fetch($sql);
    $sum_qty = $row['cnt'];

    // 재고 구함
    $it_stock_qty = get_it_stock_qty($_POST['it_id']);
    if ($ct_qty + $sum_qty > $it_stock_qty)
    {
        alert("$it_name 의 재고수량이 부족합니다.\\n\\n현재 재고수량 : " . number_format($it_stock_qty) . " 개");
    }
    //--------------------------------------------------------

    // 바로구매에 있던 장바구니 자료를 지운다.
    $result = sql_query(" delete from {$g4['shop_cart_table']} where uq_id = '$tmp_uq_id' and ct_direct = 1 ", false);
    if (!$result) {
        // 삭제중 에러가 발생했다면 필드가 없다는 것이므로 바로구매 필드를 생성한다.
        sql_query(" ALTER TABLE `{$g4['shop_cart_table']}` ADD `ct_direct` TINYINT NOT NULL ");
    }

    // 포인트 사용하지 않는다면
    if (!$config['cf_use_point']) { $_POST['it_point'] = 0; }

    // 장바구니에 Insert
    $sql = " insert {$g4['shop_cart_table']}
                set uq_id       = '$tmp_uq_id',
                    it_id        = '{$_POST['it_id']}',
                    it_name      = '{$_POST['it_name']}',
                    it_opt1      = '{$_POST['it_opt1']}',
                    it_opt2      = '{$_POST['it_opt2']}',
                    it_opt3      = '{$_POST['it_opt3']}',
                    it_opt4      = '{$_POST['it_opt4']}',
                    it_opt5      = '{$_POST['it_opt5']}',
                    it_opt6      = '{$_POST['it_opt6']}',
                    ct_status    = '쇼핑',
                    ct_price     = '{$_POST['it_price']}',
                    ct_point     = '{$_POST['it_point']}',
                    ct_point_use = '0',
                    ct_stock_use = '0',
                    ct_qty       = '{$_POST['ct_qty']}',
                    ct_time      = '".G4_TIME_YMDHIS."',
                    ct_ip        = '$REMOTE_ADDR',
                    ct_direct    = '$sw_direct' ";
    sql_query($sql);
}

// 바로 구매일 경우
if ($sw_direct)
{
    if ($is_member)
    {
    	goto_url(G4_SHOP_URL."/orderform.php?sw_direct=$sw_direct");
    }
    else
    {
    	goto_url(G4_BBS_URL."/login.php?url=".urlencode(G4_SHOP_URL."/orderform.php?sw_direct=$sw_direct"));
    }
}
else
{
    goto_url(G4_SHOP_URL.'/cart.php');
}
?>
