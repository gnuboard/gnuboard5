<?php
include_once('./_common.php');

// print_r2($_POST); exit;

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
else // 장바구니에 담기
{
    $count = count($_POST['it_id']);
    if ($count < 1)
        alert('장바구니에 담을 상품을 선택하여 주십시오.');

    for($i=0; $i<$count; $i++) {
        // 보관함의 상품을 담을 때 체크되지 않은 상품 건너뜀
        if($act == 'multi' && !$_POST['chk_it_id'][$i])
            continue;

        $it_id = $_POST['it_id'][$i];
        $opt_count = count($_POST['io_id'][$it_id]);

        if($opt_count && $_POST['io_type'][$it_id][0] != 0)
            alert('상품의 선택옵션을 선택해 주십시오.');

        for($k=0; $k<$opt_count; $k++) {
            if ($_POST['ct_qty'][$it_id][$k] < 1)
                alert('수량은 1 이상 입력해 주십시오.');
        }

        // 상품정보
        $sql = " select * from {$g4['shop_item_table']} where it_id = '$it_id' ";
        $it = sql_fetch($sql);
        if(!$it['it_id'])
            alert('상품정보가 존재하지 않습니다.');

        // 옵션정보를 얻어서 배열에 저장
        $opt_list = array();
        $sql = " select * from {$g4['shop_item_option_table']} where it_id = '$it_id' order by io_no asc ";
        $result = sql_query($sql);
        $lst_count = 0;
        for($k=0; $row=sql_fetch_array($result); $k++) {
            $opt_list[$row['io_type']][$row['io_id']]['id'] = $row['io_id'];
            $opt_list[$row['io_type']][$row['io_id']]['use'] = $row['io_use'];
            $opt_list[$row['io_type']][$row['io_id']]['price'] = $row['io_price'];
            $opt_list[$row['io_type']][$row['io_id']]['stock'] = $row['io_stock_qty'];
            $lst_count++;
        }

        // 포인트
        $point = 0;
        if($config['cf_use_point'])
            $point = get_item_point($it);

        //--------------------------------------------------------
        //  재고 검사
        //--------------------------------------------------------
        // 이미 장바구니에 있는 같은 상품의 수량합계를 구한다.
        for($k=0; $k<$opt_count; $k++) {
            $io_id = $_POST['io_id'][$it_id][$k];
            $io_type = $_POST['io_type'][$it_id][$k];
            $io_value = $_POST['io_value'][$it_id][$k];

            $sql = " select SUM(ct_qty) as cnt from {$g4['shop_cart_table']}
                      where it_id = '$it_id'
                        and uq_id = '$tmp_uq_id'
                        and io_id = '$io_id' ";
            $row = sql_fetch($sql);
            $sum_qty = $row['cnt'];

            // 재고 구함
            $ct_qty = $_POST['ct_qty'][$it_id][$k];
            if(!$io_id)
                $it_stock_qty = get_it_stock_qty($it_id);
            else
                $it_stock_qty = get_option_stock_qty($it_id, $io_id, $io_type);

            if ($ct_qty + $sum_qty > $it_stock_qty)
            {
                alert($io_value." 의 재고수량이 부족합니다.\\n\\n현재 재고수량 : " . number_format($it_stock_qty) . " 개");
            }
        }
        //--------------------------------------------------------

        // 바로구매에 있던 장바구니 자료를 지운다.
        if($i == 0)
            sql_query(" delete from {$g4['shop_cart_table']} where uq_id = '$tmp_uq_id' and ct_direct = 1 ", false);

        // 옵션수정일 때 기존 장바구니 자료를 먼저 삭제
        if($act == 'optionmod')
            sql_query(" delete from {$g4['shop_cart_table']} where uq_id = '$tmp_uq_id' and it_id = '$it_id' ");

        // 장바구니에 Insert
        $sql = " select ct_num
                    from {$g4['shop_cart_table']}
                    where it_id = '$it_id'
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

        for($k=0; $k<$opt_count; $k++) {
            $io_id = $_POST['io_id'][$it_id][$k];
            $io_type = $_POST['io_type'][$it_id][$k];
            $io_value = $_POST['io_value'][$it_id][$k];

            // 옵션정보가 존재하는데 선택된 옵션이 없으면 건너뜀
            if($lst_count && $io_id == '')
                continue;

            // 구매할 수 없는 옵션은 건너뜀
            if($io_id && !$opt_list[$io_type][$io_id]['use'])
                continue;

            $io_price = $opt_list[$io_type][$io_id]['price'];
            $ct_qty = $_POST['ct_qty'][$it_id][$k];

            // 동일옵션의 상품이 있으면 수량 더함
            $sql2 = " select ct_id
                        from {$g4['shop_cart_table']}
                        where uq_id = '$tmp_uq_id'
                          and it_id = '$it_id'
                          and io_id = '$io_id'
                          and ct_status = '쇼핑' ";
            $row2 = sql_fetch($sql2);
            if($row2['ct_id']) {
                $sql3 = " update {$g4['shop_cart_table']}
                            set ct_qty = ct_qty + '$ct_qty'
                            where ct_id = '{$row2['ct_id']}' ";
                sql_query($sql3);
                continue;
            }

            $sql .= $comma."( '$tmp_uq_id', '{$member['mb_id']}', '{$it['it_id']}', '{$it['it_name']}', '쇼핑', '{$it['it_price']}', '$point', '0', '0', '$io_value', '$ct_qty', '$ct_num', '{$it['it_notax']}', '$io_id', '$io_type', '$io_price', '".G4_TIME_YMDHIS."', '$REMOTE_ADDR', '$ct_send_cost', '$sw_direct', '$ct_select' )";
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
