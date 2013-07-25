<?php
include_once('./_common.php');

//print_r2($_POST); exit;

// uq_id 설정
set_unique_id($sw_direct);

if($sw_direct)
    $tmp_uq_id = get_session('ss_uq_direct');
else
    $tmp_uq_id = get_session('ss_uq_id');

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

/*
// notax 필드추가
$sql = " select ct_notax from {$g4['shop_cart_table']} limit 1 ";
$result = sql_query($sql, false);
if(!$result) {
    sql_query(" ALTER TABLE `{$g4['shop_cart_table']}`
                    ADD `ct_notax` TINYINT(4) NOT NULL DEFAULT '0' AFTER `ct_num` ", true);
}
*/

if($act == "buy")
{
    if(!count($_POST['ct_chk']))
        alert("주문하실 상품을 하나이상 선택해 주십시오.");

    $fldcnt = count($_POST['it_id']);
    for($i=0; $i<$fldcnt; $i++) {
        $ct_chk = $_POST['ct_chk'][$i];
        if($ct_chk) {
            $it_id = $_POST['it_id'][$i];
            $sql = " update {$g4['shop_cart_table']}
                        set ct_select = '1'
                        where it_id = '$it_id' and uq_id = '$tmp_uq_id' ";
            sql_query($sql);
        }
    }

    if ($is_member) // 회원인 경우
        goto_url(G4_SHOP_URL.'/orderform.php');
    else
        goto_url(G4_BBS_URL.'/login.php?url='.urlencode(G4_SHOP_URL.'/orderform.php'));
}
else if ($act == "d") // 삭제이면
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
else if ($act == "seldelete") // 선택삭제
{
    if(!count($_POST['ct_chk']))
        alert("삭제하실 상품을 하나이상 선택해 주십시오.");

    $fldcnt = count($_POST['it_id']);
    for($i=0; $i<$fldcnt; $i++) {
        $ct_chk = $_POST['ct_chk'][$i];
        if($ct_chk) {
            $it_id = $_POST['it_id'][$i];
            $sql = " delete from {$g4['shop_cart_table']} where it_id = '$it_id' and uq_id = '$tmp_uq_id' ";
            sql_query($sql);
        }
    }
}
else if ($act == "multi") // 여러개의 상품이 한꺼번에 들어옴.
{
    $fldcnt = count($_POST['it_name']);
    $arr_item = array();

    // 재고등을 검사
    $error = "";
	for ($i=0; $i<$fldcnt; $i++)
    {
        if ($_POST['it_id'][$i] == "" || $_POST['ct_qty'][$i] <= 0) { continue; }

        //옵션있는 상품은 건너뜀
        $arr_item[$i]['opt_skip'] = 0;
        $sql = " select count(*) as cnt from {$g4['shop_item_option_table']} where it_id = '{$_POST['it_id'][$i]}' and io_type = '0' ";
        $tmp = sql_fetch($sql);
        if($tmp['cnt']) {
            $arr_item[$i]['opt_skip'] = 1;
            continue;
        }

        //--------------------------------------------------------
        //  변조 검사
        //--------------------------------------------------------
        $sql = " select * from {$g4['shop_item_table']} where it_id = '{$_POST['it_id'][$i]}' ";
        $it = sql_fetch($sql);
        $arr_item[$i]['notax'] = $it['it_notax'];

        $price = get_price($it);
        // 상품가격이 다름
        if ((int)$price !== (int)$_POST['it_price'][$i])
            die("Error..");

        $point = get_item_point($it);
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

    $ct_count = 0;
    $comma = '';
    $sql = " INSERT INTO {$g4['shop_cart_table']}
                    ( uq_id, mb_id, it_id, it_name, ct_status, ct_price, ct_point, ct_point_use, ct_stock_use, ct_option, ct_qty, ct_num, ct_notax, io_id, io_type, io_price, ct_time, ct_ip, ct_send_cost, ct_direct, ct_select )
                VALUES ";
    $ct_select = 0;
    if($sw_direct)
        $ct_select = 1;

	for ($i=0; $i<$fldcnt; $i++)
    {
        if ($_POST['it_id'][$i] == "" || $_POST['ct_qty'][$i] <= 0) continue;

        // 옵션있는 상품이라면 건너뜀
        if($arr_item[$i]['opt_skip'])
            continue;

        // 포인트 사용하지 않는다면
        if (!$config['cf_use_point']) $_POST['it_point'][$i] = 0;

        // 동일옵션의 상품이 있으면 수량 더함
        $sql2 = " select ct_id
                    from {$g4['shop_cart_table']}
                    where uq_id = '$tmp_uq_id'
                      and it_id = '{$_POST['it_id'][$i]}'
                      and ct_status = '쇼핑' ";
        $row2 = sql_fetch($sql2);
        if($row2['ct_id']) {
            $sql3 = " update {$g4['shop_cart_table']}
                        set ct_qty = ct_qty + {$_POST['ct_qty'][$i]}
                        where ct_id = '{$row2['ct_id']}' ";
            sql_query($sql3);

            continue;
        }

        $sql .= $comma."( '$tmp_uq_id', '{$member['mb_id']}', '{$_POST['it_id'][$i]}', '{$_POST['it_name'][$i]}', '쇼핑', '{$_POST['it_price'][$i]}', '{$_POST['it_point'][$i]}', '0', '0', '{$_POST['it_name'][$i]}', '{$_POST['ct_qty'][$i]}', '0', '{$arr_item[$i]['notax']}', '', '0', '0', '".G4_TIME_YMDHIS."', '$REMOTE_ADDR', '$ct_send_cost', '$sw_direct', '$ct_select' )";
        $comma = ' , ';
        $ct_count++;
    }

    if($ct_count > 0)
        sql_query($sql);
}
else if ($act == "optionmod") // 장바구니에서 옵션변경
{
    if (!$_POST['it_id'])
        alert('장바구니에 담을 상품을 선택하여 주십시오.');

    $option_count = count($_POST['io_id']);

    if($_POST['io_type'][0] != 0)
        alert('상품의 선택옵션을 선택해 주십시오.');

    if($option_count) {
        for($i=0; $i<count($_POST['ct_qty']); $i++) {
            if ($_POST['ct_qty'][$i] < 1)
                alert('수량은 1 이상 입력해 주십시오.');
        }

        //--------------------------------------------------------
        //  변조 검사
        //--------------------------------------------------------
        $total_price = 0;
        $sql = " select * from {$g4['shop_item_table']} where it_id = '{$_POST['it_id']}' ";
        $it = sql_fetch($sql);

        // 옵션정보를 얻어서 배열에 저장
        $opt_list = array();
        $sql = " select * from {$g4['shop_item_option_table']} where it_id = '{$_POST['it_id']}' and io_use = '1' order by io_no asc ";
        $result = sql_query($sql);
        for($i=0; $row=sql_fetch_array($result); $i++) {
            $opt_list[$row['io_type']][$row['io_id']]['price'] = $row['io_price'];
            $opt_list[$row['io_type']][$row['io_id']]['stock'] = $row['io_stock_qty'];
        }

        for($i=0; $i<$option_count; $i++) {
            $opt_id = $_POST['io_id'][$i];
            $opt_type = $_POST['io_type'][$i];
            $opt_price = $_POST['io_price'][$i];
            $opt_qty = $_POST['ct_qty'][$i];

            if((int)$opt_price !== (int)$opt_list[$opt_type][$opt_id]['price'])
                die("Option Price Mismatch");

            if($opt_type == 1)
                $total_price += $opt_price * $opt_qty;
            else
                $total_price += ($it['it_price'] + $opt_price) * $opt_qty;
        }

        // 상품 총금액이 다름
        if ((int)$_POST['total_price'] !== (int)$total_price)
            die("Error..");

        $point = get_item_point($it);
        // 포인트가 다름
        if ((int)$point !== (int)$_POST['it_point'] && $config['cf_use_point'])
            die("Error...");
        //--------------------------------------------------------


        //--------------------------------------------------------
        //  재고 검사
        //--------------------------------------------------------
        // 이미 장바구니에 있는 같은 상품의 수량합계를 구한다.
        for($i=0; $i<$option_count; $i++) {
            $sql = " select SUM(ct_qty) as cnt from {$g4['shop_cart_table']}
                      where it_id = '{$_POST['it_id']}'
                        and uq_id = '$tmp_uq_id'
                        and io_id = '{$_POST['io_id'][$i]}' ";
            $row = sql_fetch($sql);
            $sum_qty = $row['cnt'];

            // 재고 구함
            $ct_qty = $_POST['ct_qty'][$i];
            if(!$_POST['io_id'][$i])
                $it_stock_qty = get_it_stock_qty($_POST['it_id']);
            else
                $it_stock_qty = get_option_stock_qty($_POST['it_id'], $_POST['io_id'][$i], $_POST['io_type'][$i]);

            if ($ct_qty + $sum_qty > $it_stock_qty)
            {
                alert($_POST['io_value'][$i]." 의 재고수량이 부족합니다.\\n\\n현재 재고수량 : " . number_format($it_stock_qty) . " 개");
            }
        }
        //--------------------------------------------------------

        // 기존 장바구니 자료를 먼저 삭제
        sql_query(" delete from {$g4['shop_cart_table']} where uq_id = '$tmp_uq_id' and it_id = '$it_id' ");

        // 포인트 사용하지 않는다면
        if (!$config['cf_use_point']) { $_POST['it_point'] = 0; }

        // 장바구니에 Insert
        $comma = '';
        $sql = " INSERT INTO {$g4['shop_cart_table']}
                        ( uq_id, it_id, it_name, ct_status, ct_price, ct_point, ct_point_use, ct_stock_use, ct_option, ct_qty, ct_num, ct_notax, io_id, io_type, io_price, ct_time, ct_ip, ct_direct, ct_send_cost )
                    VALUES ";

        for($i=0; $i<$option_count; $i++) {
            $sql .= $comma."( '$tmp_uq_id', '{$_POST['it_id']}', '{$_POST['it_name']}', '쇼핑', '{$_POST['it_price']}', '{$_POST['it_point']}', '0', '0', '{$_POST['io_value'][$i]}', '{$_POST['ct_qty'][$i]}', '$i', '{$it['it_notax']}', '{$_POST['io_id'][$i]}', '{$_POST['io_type'][$i]}', '{$_POST['io_price'][$i]}', '".G4_TIME_YMDHIS."', '$REMOTE_ADDR', '$sw_direct', '$ct_send_cost' )";
            $comma = ' , ';
        }

        sql_query($sql);
    } else {
        // 기존 장바구니 자료 삭제
        sql_query(" delete from {$g4['shop_cart_table']} where uq_id = '$tmp_uq_id' and it_id = '$it_id' ");
    }
}
else // 장바구니에 담기
{
    if (!$_POST['it_id'])
        alert('장바구니에 담을 상품을 선택하여 주십시오.');

    $option_count = count($_POST['io_id']);
    if($option_count) {
        if($_POST['io_type'][0] != 0)
            alert('상품의 선택옵션을 선택해 주십시오.');

        for($i=0; $i<count($_POST['ct_qty']); $i++) {
            if ($_POST['ct_qty'][$i] < 1)
                alert('수량은 1 이상 입력해 주십시오.');
        }

        //--------------------------------------------------------
        //  변조 검사
        //--------------------------------------------------------
        $total_price = 0;
        $sql = " select * from {$g4['shop_item_table']} where it_id = '{$_POST['it_id']}' ";
        $it = sql_fetch($sql);

        // 옵션정보를 얻어서 배열에 저장
        $opt_list = array();
        $sql = " select * from {$g4['shop_item_option_table']} where it_id = '{$_POST['it_id']}' and io_use = '1' order by io_no asc ";
        $result = sql_query($sql);
        for($i=0; $row=sql_fetch_array($result); $i++) {
            $opt_list[$row['io_type']][$row['io_id']]['price'] = $row['io_price'];
            $opt_list[$row['io_type']][$row['io_id']]['stock'] = $row['io_stock_qty'];
        }

        for($i=0; $i<$option_count; $i++) {
            $opt_id = $_POST['io_id'][$i];
            $opt_type = $_POST['io_type'][$i];
            $opt_price = $_POST['io_price'][$i];
            $opt_qty = $_POST['ct_qty'][$i];

            if((int)$opt_price !== (int)$opt_list[$opt_type][$opt_id]['price'])
                die("Option Price Mismatch");

            if($opt_type == 1)
                $total_price += $opt_price * $opt_qty;
            else
                $total_price += ($it['it_price'] + $opt_price) * $opt_qty;
        }

        // 상품 총금액이 다름
        if ((int)$_POST['total_price'] !== (int)$total_price)
            die("Error..");

        $point = get_item_point($it);
        // 포인트가 다름
        if ((int)$point !== (int)$_POST['it_point'] && $config['cf_use_point'])
            die("Error...");
        //--------------------------------------------------------


        //--------------------------------------------------------
        //  재고 검사
        //--------------------------------------------------------
        // 이미 장바구니에 있는 같은 상품의 수량합계를 구한다.
        for($i=0; $i<$option_count; $i++) {
            $sql = " select SUM(ct_qty) as cnt from {$g4['shop_cart_table']}
                      where it_id = '{$_POST['it_id']}'
                        and uq_id = '$tmp_uq_id'
                        and io_id = '{$_POST['io_id'][$i]}' ";
            $row = sql_fetch($sql);
            $sum_qty = $row['cnt'];

            // 재고 구함
            $ct_qty = $_POST['ct_qty'][$i];
            if(!$_POST['io_id'][$i])
                $it_stock_qty = get_it_stock_qty($_POST['it_id']);
            else
                $it_stock_qty = get_option_stock_qty($_POST['it_id'], $_POST['io_id'][$i], $_POST['io_type'][$i]);

            if ($ct_qty + $sum_qty > $it_stock_qty)
            {
                alert($_POST['io_value'][$i]." 의 재고수량이 부족합니다.\\n\\n현재 재고수량 : " . number_format($it_stock_qty) . " 개");
            }
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
        $sql = " select ct_num
                    from {$g4['shop_cart_table']}
                    where it_id = '{$_POST['it_id']}'
                      and uq_id = '$tmp_uq_id'
                    order by ct_num desc ";
        $row = sql_fetch($sql);
        if($row['ct_num'] != '')
            $ct_num = (int)$row['ct_num'] + 1;
        else
            $ct_num = 0;

        if($sw_direct)
            $ct_select = 1;
        else
            $ct_select = 0;

        $ct_count = 0;
        $comma = '';
        $sql = " INSERT INTO {$g4['shop_cart_table']}
                        ( uq_id, mb_id, it_id, it_name, ct_status, ct_price, ct_point, ct_point_use, ct_stock_use, ct_option, ct_qty, ct_num, ct_notax, io_id, io_type, io_price, ct_time, ct_ip, ct_send_cost, ct_direct, ct_select )
                    VALUES ";

        for($i=0; $i<$option_count; $i++) {
            // 동일옵션의 상품이 있으면 수량 더함
            $sql2 = " select ct_id
                        from {$g4['shop_cart_table']}
                        where uq_id = '$tmp_uq_id'
                          and it_id = '{$_POST['it_id']}'
                          and io_id = '{$_POST['io_id'][$i]}'
                          and ct_status = '쇼핑' ";
            $row2 = sql_fetch($sql2);
            if($row2['ct_id']) {
                $sql3 = " update {$g4['shop_cart_table']}
                            set ct_qty = ct_qty + {$_POST['ct_qty'][$i]}
                            where ct_id = '{$row2['ct_id']}' ";
                sql_query($sql3);

                continue;
            }

            $sql .= $comma."( '$tmp_uq_id', '{$member['mb_id']}', '{$_POST['it_id']}', '{$_POST['it_name']}', '쇼핑', '{$_POST['it_price']}', '{$_POST['it_point']}', '0', '0', '{$_POST['io_value'][$i]}', '{$_POST['ct_qty'][$i]}', '$ct_num', '{$it['it_notax']}', '{$_POST['io_id'][$i]}', '{$_POST['io_type'][$i]}', '{$_POST['io_price'][$i]}', '".G4_TIME_YMDHIS."', '$REMOTE_ADDR', '$ct_send_cost', '$sw_direct', '$ct_select' )";
            $comma = ' , ';
            $ct_num++;
            $ct_count++;
        }

        if($ct_count > 0)
            sql_query($sql);
    }
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
