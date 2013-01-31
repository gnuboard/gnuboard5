<?
include_once('./_common.php');

$uq_id = get_session('ss_uniqid');

// 브라우저에서 쿠키를 허용하지 않은 경우라고 볼 수 있음.
if (!$uq_id)
{
    alert("더 이상 작업을 진행할 수 없습니다.\\n\\n브라우저의 쿠키 허용을 사용하지 않음으로 설정한것 같습니다.\\n\\n브라우저의 인터넷 옵션에서 쿠키 허용을 사용으로 설정해 주십시오.\\n\\n그래도 진행이 되지 않는다면 쇼핑몰 운영자에게 문의 바랍니다.");
}

// 레벨(권한)이 상품구입 권한보다 작다면 상품을 구입할 수 없음.
if ($member['mb_level'] < $default['de_level_sell'])
{
    alert('상품을 구입할 수 있는 권한이 없습니다.');
}

// 비회원장바구니
if($default['de_guest_cart_use']) {
    if(!get_cookie('ck_guest_cart_uqid')) {
        set_cookie('ck_guest_cart_uqid', $uq_id, 24*60*60*30); // 30일간 보관
    }
} else {
    if(get_cookie('ck_guest_cart_uqid')) {
        set_cookie('ck_guest_cart_uqid', '', 0);
    }
}

if ($act == 'alldelete') // 모두 삭제이면
{
    $sql = " delete from {$g4['yc4_cart_table']}
              where uq_id = '$uq_id' ";
    sql_query($sql);
}
else if ($act == 'seldelete') // 선택 삭제이면
{
    $sel_count = count($_POST['ct_chk']);
    if(!$sel_count) {
        alert('삭제할 상품을 1개이상 선택해 주세요.');
    }

    for($k=0; $k<$sel_count; $k++) {
        $ct_id = $_POST['ct_chk'][$k];
        $sql = " delete from {$g4['yc4_cart_table']} where uq_id = '$uq_id' and ( ct_id = '$ct_id' or ct_parent = '$ct_id' ) ";
        sql_query($sql);
    }
}
else if($act == 'selectedbuy') // 선택주문이면
{
    $chk_count = count($_POST['ct_chk']);
    if(!$chk_count)
        alert("주문하실 상품을 1개 이상 선택해 주세요.");

    // 이전 선택주문 상품 초기화
    $sql = " update {$g4['yc4_cart_table']}
                set ct_selected = '0'
                where uq_id = '$uq_id' ";
    sql_query($sql);

    $count = count($_POST['ct_id']);
    for($i=0; $i<$count; $i++) {
        $chk_id = $_POST['ct_chk'][$i];
        if(!$chk_id)
            continue;

        $sql = " update {$g4['yc4_cart_table']}
                    set ct_selected = '1'
                    where ct_id = '$chk_id'
                      or ct_parent = '$chk_id' ";
        sql_query($sql);
    }

    if ($is_member)
    {
        goto_url(G4_SHOP_URL."/orderform.php?act=$act");
    }
    else
    {
        goto_url(G4_BBS_URL."/login.php?url=".urlencode(G4_SHOP_URL."/orderform.php?act=$act"));
    }
}
else if ($act == 'allupdate') // 수량 변경이면 : 모두 수정이면
{
    $fldcnt = count($_POST['ct_id']);

    // 수량 변경, 재고등을 검사
    $error = "";
	for ($i=0; $i<$fldcnt; $i++)
    {
        $ct_id = $_POST['ct_id'][$i];
        if($ct_id) {
            // 상품1개일 때만 수량변경
            $sql = " select count(ct_id) as cnt from {$g4['yc4_cart_table']} where uq_id = '$uq_id' and it_id = '{$_POST['it_id'][$i]}' ";
            $row = sql_fetch($sql);
            if($row['cnt'] > 1) {
                continue;
            }

            // 재고 구함
            $stock_qty = get_it_stock_qty($_POST['it_id'][$i]);

            // 변경된 수량이 재고수량보다 크면 오류
            if ($_POST['ct_qty'][$i] > $stock_qty) {
                $error .= "{$_POST['it_name'][$i]} 의 재고수량이 부족합니다. 현재 재고수량 : $stock_qty 개\\n\\n";
                continue;
            }

            // 수량수정
            $sql = " update {$g4['yc4_cart_table']}
                        set ct_qty = '{$_POST['ct_qty'][$i]}'
                      where ct_id  = '$ct_id'
                        and uq_id = '$uq_id' ";
            sql_query($sql);
        }
    }

    // 오류가 있다면 오류메세지 출력
    if ($error != "") { alert($error); }
}
else // 장바구니에 담기
{
    if (!$_POST['it_id'])
        alert('장바구니에 담을 상품을 선택하여 주십시오.');

    $ct_count = count($_POST['ct_qty']);

    // 상품정보
    $sql = " select it_id, it_use, it_gallery, it_tel_inq, it_option_use, it_supplement_use
                from {$g4['yc4_item_table']} where it_id = '{$_POST['it_id']}' ";
    $it = sql_fetch($sql);

    // 주문가능한 상품인지
    if(!$it['it_use'] || $it['it_gallery'] || $it['it_tel_inq']) {
        alert($_POST['it_name'].'은(는) 주문할 수 없습니다.');
    }

    // 비회원가격과 회원가격이 다르다면
    if (!$is_member && $default['de_different_msg'])
    {
        $sql = " select it_amount, it_amount2 from {$g4['yc4_item_table']} where it_id = '{$_POST['it_id']}' ";
        $row = sql_fetch($sql);
        if ($row['it_amount2'] && $row['it_amount'] != $row['it_amount2']) {
            echo "<meta http-equiv=\"content-type\" content=\"text/html; charset=".$g4['charset']."\">";
            echo "<script>alert('비회원가격과 회원가격이 다릅니다. 로그인 후 구입하여 주십시오.');</script>";
        }
    }

    //--------------------------------------------------------
    //  변조 검사
    //--------------------------------------------------------
    //$is_option : 1-> 선택옵션, 2->추가옵션, 0->옵션없는 상품
    $total_amount = (int)$_POST['total_amount'];
    $total_price = 0;
    $item_price = (int)$_POST['it_amount'];

    for($i=0; $i<$ct_count; $i++) {
        $is_option = (int)$_POST['is_option'][$i];
        $ct_price = (int)$_POST['ct_amount'][$i];
        $it_qty = $_POST['ct_qty'][$i];

        if($is_option == 2) {
            $total_price += ($ct_price * $it_qty);
        } else {
            $total_price += ($item_price + $ct_price) * $it_qty;
        }
    }

    // 총 금액 다름
    if($total_amount != $total_price) {
        die('Error.');
    }
    //--------------------------------------------------------


    //--------------------------------------------------------
    //  재고 및 사용여부 검사
    //--------------------------------------------------------
    // $is_option : 1-> 선택옵션, 2->추가옵션, 0->옵션없는 상품
    for($i=0; $i<$ct_count; $i++) {
        $is_option = $_POST['is_option'][$i];
        $it_name = get_text($_POST['it_name']);
        $opt_id = $_POST['opt_id'][$i];
        $ct_qty = $_POST['ct_qty'][$i];

        if($is_option) {
            // 주문가능한 옵션인지
            if($is_option == 1) {
                $sql1 = " select opt_use as option_use from {$g4['yc4_option_table']}
                            where it_id = '{$_POST['it_id']}' and opt_id = '$opt_id' ";
            } else {
                $sql1 = " select sp_use as option_use from {$g4['yc4_supplement_table']}
                            where it_id = '{$_POST['it_id']}' and sp_id = '$opt_id' ";
            }
            $row1 = sql_fetch($sql1);

            if(!$row1['option_use']) {
                $ct_option = $_POST['ct_option'][$i];
                $msg = '선택하신 상품 : '.$it_name.'('.$ct_option.')은(는) 구매할 수 없습니다.';

                alert($msg);
            }

            // 이미 장바구니에 있는 같은 옵션의 수량합계를 구한다.
            $sql = " select SUM(ct_qty) as cnt from {$g4['yc4_cart_table']}
                        where it_id = '{$_POST['it_id']}' and opt_id = '$opt_id' and uq_id <> '$uq_id' and is_option = '$is_option' and ct_status = '쇼핑' ";
            $row = sql_fetch($sql);
            $cart_qty = $row['cnt'];
            $stock_qty = get_option_stock_qty($_POST['it_id'], $opt_id, $is_option);
        } else {
            // 이미 장바구니에 있는 같은 상품의 수량합계를 구한다.
            $sql = " select SUM(ct_qty) as cnt from {$g4['yc4_cart_table']}
                        where it_id = '{$_POST['it_id']}' and uq_id <> '$uq_id' and is_option = '$is_option' and ct_status = '쇼핑' ";
            $row = sql_fetch($sql);
            $cart_qty = $row['cnt'];
            $stock_qty = get_it_stock_qty($_POST['it_id']);
        }

        if($stock_qty < $ct_qty + $cart_qty) {
            if($is_option) {
                $ct_option = $_POST['ct_option'][$i];
                $msg = '선택하신 상품 : '.$it_name.'('.$ct_option.')은(는) 재고가 부족하여 구매할 수 없습니다.';
            } else {
                $msg = '선택하신 상품 : '.$it_name.'은(는) 재고가 부족하여 구매할 수 없습니다.';
            }

            alert($msg);
        }
    }
    //--------------------------------------------------------

    // 바로구매에 있던 장바구니 자료를 지운다.
    $result = sql_query(" delete from {$g4['yc4_cart_table']} where uq_id = '$uq_id' and ct_direct = 1 ", false);
    if (!$result) {
        // 삭제중 에러가 발생했다면 필드가 없다는 것이므로 바로구매 필드를 생성한다.
        sql_query(" ALTER TABLE `{$g4['yc4_cart_table']}` ADD `ct_direct` TINYINT NOT NULL ");
    }

    // 포인트 사용하지 않는다면
    if (!$config['cf_use_point']) { $_POST['it_point'] = 0; }

    // 장바구니에 Insert
    $it_name = get_text($_POST['it_name']);
    $ct_parent = 0;

    for($i=0; $i<$ct_count; $i++) {
        $is_option = $_POST['is_option'][$i];
        if($is_option == 1 || $is_option == 0) {
            $it_amount = $_POST['it_amount'];
        } else {
            $it_amount = 0;
        }
        $opt_id = $_POST['opt_id'][$i];
        $ct_amount = $_POST['ct_amount'][$i];
        $ct_qty = $_POST['ct_qty'][$i];
        $ct_point = 0;
        $ct_option = get_text($_POST['ct_option'][$i]);
        $opt_space = '';
        $ct_send_cost_pay = trim($_POST['ct_send_cost_pay']);

        // No옵션 상품이 장바구니에 있는치 체크, 있으면 수량변경
        $nopt_count = 0;
        if($is_option == 0 && !$sw_direct) {
            $sql2 = " select count(*) as cnt from {$g4['yc4_cart_table']}
                        where uq_id = '$uq_id' and it_id = '{$_POST['it_id']}' and is_option = '0' and ct_direct = '0' ";
            $row2 = sql_fetch($sql2);
            $nopt_count = (int)$row2['cnt'];
        }

        if($nopt_count) {
            $sql = " update {$g4['yc4_cart_table']} set ct_qty = ct_qty + $ct_qty
                        where uq_id = '$uq_id' and it_id = '{$_POST['it_id']}' and is_option = '0' and ct_direct = '0' ";
        } else {
            $sql = " insert into {$g4['yc4_cart_table']}
                        set uq_id               = '$uq_id',
                            ct_parent           = '$ct_parent',
                            mb_id               = '{$member['mb_id']}',
                            is_option           = '$is_option',
                            it_id               = '{$_POST['it_id']}',
                            it_name             = '$it_name',
                            opt_id              = '$opt_id',
                            ct_option           = '$ct_option',
                            ct_status           = '쇼핑',
                            it_amount           = '$it_amount',
                            ct_amount           = '$ct_amount',
                            ct_qty              = '$ct_qty',
                            ct_point            = '$ct_point',
                            ct_send_cost_pay    = '$ct_send_cost_pay',
                            ct_stock_use        = '0',
                            ct_point_use        = '0',
                            ct_time             = '{$g4['time_ymdhis']}',
                            ct_ip               = '$REMOTE_ADDR',
                            ct_direct           = '$sw_direct' ";
        }

        sql_query($sql);

        if($ct_parent == 0)
            $ct_parent = mysql_insert_id();
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
    goto_url(G4_SHOP_URL."/cart.php");
}
?>
