<?php
$sub_menu = '600410';
include_once './_common.php';

auth_check_menu($auth, $sub_menu, 'r');

$g5['title'] = '정기결제 결제내역';
include_once G5_ADMIN_PATH.'/admin.head.php';
include_once G5_PLUGIN_PATH.'/jquery-ui/datepicker.php';

$where = array();

$doc = isset($_GET['doc']) ? clean_xss_tags($_GET['doc'], 1, 1) : '';
$sort1 = (isset($_GET['sort1']) && in_array($_GET['sort1'], array('subscription_pg_id', 'py_time', 'pay_id', 'py_cart_price', 'py_receipt_price', 'py_cancel_price', 'py_misu', 'py_cash'))) ? $_GET['sort1'] : '';
$sort2 = (isset($_GET['sort2']) && in_array($_GET['sort2'], array('desc', 'asc'))) ? $_GET['sort2'] : 'desc';
$sel_field = (isset($_GET['sel_field']) && in_array($_GET['sel_field'], array('subscription_pg_id', 'pay_id', 'mb_id', 'py_name', 'py_tel', 'py_hp', 'py_b_name', 'py_b_tel', 'py_b_hp', 'py_deposit_name', 'py_invoice'))) ? $_GET['sel_field'] : '';
$py_status = isset($_GET['py_status']) ? get_search_string($_GET['py_status']) : '';
$search = isset($_GET['search']) ? get_search_string($_GET['search']) : '';

$fr_date = (isset($_GET['fr_date']) && preg_match('/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/', $_GET['fr_date'])) ? $_GET['fr_date'] : '';
$to_date = (isset($_GET['to_date']) && preg_match('/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/', $_GET['to_date'])) ? $_GET['to_date'] : '';

$py_misu = isset($_GET['py_misu']) ? preg_replace('/[^0-9a-z]/i', '', $_GET['py_misu']) : '';
$py_cancel_price = isset($_GET['py_cancel_price']) ? preg_replace('/[^0-9a-z]/i', '', $_GET['py_cancel_price']) : '';
$py_refund_price = isset($_GET['py_refund_price']) ? preg_replace('/[^0-9a-z]/i', '', $_GET['py_refund_price']) : '';
$py_receipt_point = isset($_GET['py_receipt_point']) ? preg_replace('/[^0-9a-z]/i', '', $_GET['py_receipt_point']) : '';
$py_coupon = isset($_GET['py_coupon']) ? preg_replace('/[^0-9a-z]/i', '', $_GET['py_coupon']) : '';
$py_settle_case = isset($_GET['py_settle_case']) ? clean_xss_tags($_GET['py_settle_case'], 1, 1) : '';
$py_escrow = isset($_GET['py_escrow']) ? clean_xss_tags($_GET['py_escrow'], 1, 1) : '';

$tot_itemcount = $tot_orderprice = $tot_receiptprice = $tot_ordercancel = $tot_misu = $tot_couponprice = 0;
$sql_search = '';
if ($search != '') {
    if ($sel_field != '') {
        $where[] = " $sel_field like '%$search%' ";
    }

    if ($save_search != $search) {
        $page = 1;
    }
}

if ($py_status) {
    switch ($py_status) {
        case '전체취소':
            $where[] = " py_status = '취소' ";
            break;
        case '부분취소':
            $where[] = " py_status IN('입금', '준비', '배송', '완료') and py_cancel_price > 0 ";
            break;
        default:
            $where[] = " py_status = '$py_status' ";
            break;
    }

    switch ($py_status) {
        case '주문':
            $sort1 = 'pay_id';
            $sort2 = 'desc';
            break;
        case '입금':   // 결제완료
            $sort1 = 'py_receipt_time';
            $sort2 = 'desc';
            break;
        case '배송':   // 배송중
            $sort1 = 'py_invoice_time';
            $sort2 = 'desc';
            break;
    }
}

if ($py_misu) {
    $where[] = ' py_misu != 0 ';
}

if ($py_cancel_price) {
    $where[] = ' py_cancel_price != 0 ';
}

if ($py_refund_price) {
    $where[] = ' py_refund_price != 0 ';
}

if ($py_receipt_point) {
    $where[] = ' py_receipt_point != 0 ';
}

if ($py_coupon) {
    $where[] = ' ( py_cart_coupon > 0 or py_coupon > 0 or py_send_coupon > 0 ) ';
}

if ($py_escrow) {
    $where[] = ' py_escrow = 1 ';
}

if ($fr_date && $to_date) {
    $where[] = " py_time between '$fr_date 00:00:00' and '$to_date 23:59:59' ";
}

if ($where) {
    $sql_search = ' where '.implode(' and ', $where);
}

if ($sel_field == '') {
    $sel_field = 'pay_id';
}
if ($sort1 == '') {
    $sort1 = 'pay_id';
}
if ($sort2 == '') {
    $sort2 = 'desc';
}

$sql_common = " from {$g5['g5_subscription_pay_table']} $sql_search ";

$sql = ' select count(pay_id) as cnt '.$sql_common;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) {
    $page = 1;
} // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

// 지금은 쿠폰제외
$sql = " select *, (py_cart_coupon + py_coupon + py_send_coupon) as couponprice
           $sql_common
           order by $sort1 $sort2
           limit $from_record, $rows ";

$result = sql_query($sql);

$qstr1 = 'py_status='.urlencode($py_status).'&amp;py_settle_case='.urlencode($py_settle_case)."&amp;py_misu=$py_misu&amp;py_cancel_price=$py_cancel_price&amp;py_refund_price=$py_refund_price&amp;py_receipt_point=$py_receipt_point&amp;py_coupon=$py_coupon&amp;fr_date=$fr_date&amp;to_date=$to_date&amp;sel_field=$sel_field&amp;search=$search&amp;save_search=$search";
if ($default['de_escrow_use']) {
    $qstr1 .= "&amp;py_escrow=$py_escrow";
}
$qstr = "$qstr1&amp;sort1=$sort1&amp;sort2=$sort2&amp;page=$page";

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

if (function_exists('pg_setting_check')) {
    pg_setting_check(true);
}
?>
<div class="local_ov01 local_ov">
    <?php echo $listall; ?>
    <span class="btn_ov01"><span class="ov_txt">전체 주문내역</span><span class="ov_num"> <?php echo number_format($total_count); ?>건</span></span>
    <?php if ($py_status == '준비' && $total_count > 0) { ?>
    <a href="./orderdelivery.php" id="order_delivery" class="ov_a">엑셀배송처리</a>
    <?php } ?>
</div>

<form name="frmorderlist" class="local_sch01 local_sch">
<input type="hidden" name="doc" value="<?php echo sanitize_input($doc); ?>">
<input type="hidden" name="sort1" value="<?php echo sanitize_input($sort1); ?>">
<input type="hidden" name="sort2" value="<?php echo sanitize_input($sort2); ?>">
<input type="hidden" name="page" value="<?php echo sanitize_input($page); ?>">
<input type="hidden" name="save_search" value="<?php echo sanitize_input($search); ?>">

<label for="sel_field" class="sound_only">검색대상</label>
<select name="sel_field" id="sel_field">
    <option value="subscription_pg_id" <?php echo get_selected($sel_field, 'subscription_pg_id'); ?>>주문번호</option>
    <option value="mb_id" <?php echo get_selected($sel_field, 'mb_id'); ?>>회원 ID</option>
    <option value="py_name" <?php echo get_selected($sel_field, 'py_name'); ?>>주문자</option>
    <option value="py_tel" <?php echo get_selected($sel_field, 'py_tel'); ?>>주문자전화</option>
    <option value="py_hp" <?php echo get_selected($sel_field, 'py_hp'); ?>>주문자핸드폰</option>
    <option value="py_b_name" <?php echo get_selected($sel_field, 'py_b_name'); ?>>받는분</option>
    <option value="py_b_tel" <?php echo get_selected($sel_field, 'py_b_tel'); ?>>받는분전화</option>
    <option value="py_b_hp" <?php echo get_selected($sel_field, 'py_b_hp'); ?>>받는분핸드폰</option>
    <option value="py_deposit_name" <?php echo get_selected($sel_field, 'py_deposit_name'); ?>>입금자</option>
    <option value="py_invoice" <?php echo get_selected($sel_field, 'py_invoice'); ?>>운송장번호</option>
</select>

<label for="search" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="search" value="<?php echo $search; ?>" id="search" required class="required frm_input" autocomplete="off">
<input type="submit" value="검색" class="btn_submit">

</form>

<form class="local_sch03 local_sch">
<div>
    <strong>주문상태</strong>
    <input type="radio" name="py_status" value="" id="py_status_all"    <?php echo get_checked($py_status, ''); ?>>
    <label for="py_status_all">전체</label>
    <input type="radio" name="py_status" value="주문" id="py_status_odr" <?php echo get_checked($py_status, '주문'); ?>>
    <label for="py_status_odr">주문</label>
    <input type="radio" name="py_status" value="입금" id="py_status_income" <?php echo get_checked($py_status, '입금'); ?>>
    <label for="py_status_income">입금</label>
    <input type="radio" name="py_status" value="준비" id="py_status_rdy" <?php echo get_checked($py_status, '준비'); ?>>
    <label for="py_status_rdy">준비</label>
    <input type="radio" name="py_status" value="배송" id="py_status_dvr" <?php echo get_checked($py_status, '배송'); ?>>
    <label for="py_status_dvr">배송</label>
    <input type="radio" name="py_status" value="완료" id="py_status_done" <?php echo get_checked($py_status, '완료'); ?>>
    <label for="py_status_done">완료</label>
    <input type="radio" name="py_status" value="전체취소" id="py_status_cancel" <?php echo get_checked($py_status, '전체취소'); ?>>
    <label for="py_status_cancel">전체취소</label>
</div>

<div>
    <strong>기타선택</strong>
    <input type="checkbox" name="py_misu" value="Y" id="py_misu01" <?php echo get_checked($py_misu, 'Y'); ?>>
    <label for="py_misu01">미수금</label>
    <input type="checkbox" name="py_cancel_price" value="Y" id="py_misu02" <?php echo get_checked($py_cancel_price, 'Y'); ?>>
    <label for="py_misu02">반품,품절</label>
    <input type="checkbox" name="py_refund_price" value="Y" id="py_misu03" <?php echo get_checked($py_refund_price, 'Y'); ?>>
    <label for="py_misu03">환불</label>
</div>

<div class="sch_last">
    <strong>주문일자</strong>
    <input type="text" id="fr_date"  name="fr_date" value="<?php echo $fr_date; ?>" class="frm_input" size="10" maxlength="10"> ~
    <input type="text" id="to_date"  name="to_date" value="<?php echo $to_date; ?>" class="frm_input" size="10" maxlength="10">
    <button type="button" onclick="javascript:set_date('오늘');">오늘</button>
    <button type="button" onclick="javascript:set_date('어제');">어제</button>
    <button type="button" onclick="javascript:set_date('이번주');">이번주</button>
    <button type="button" onclick="javascript:set_date('이번달');">이번달</button>
    <button type="button" onclick="javascript:set_date('지난주');">지난주</button>
    <button type="button" onclick="javascript:set_date('지난달');">지난달</button>
    <button type="button" onclick="javascript:set_date('전체');">전체</button>
    <input type="submit" value="검색" class="btn_submit">
</div>
</form>

<form name="forderlist" id="forderlist" onsubmit="return forderlist_submit(this);" method="post" autocomplete="off">
<input type="hidden" name="search_py_status" value="<?php echo $py_status; ?>">

<div class="tbl_head01 tbl_wrap">
    <table id="sodr_list">
    <caption>주문 내역 목록</caption>
    <thead>
    <tr>
        <th scope="col" rowspan="3">
            <label for="chkall" class="sound_only">주문 전체</label>
            <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
        </th>
        <th scope="col"rowspan="3" ><a href="#">주문상품</a></th>
        <th scope="col" rowspan="2"><a href="<?php echo title_sort('py_time', 1)."&amp;$qstr1"; ?>">주문날짜</a></th>
        <th scope="col" id="th_ordnum" rowspan="2"><a href="<?php echo title_sort('pay_id', 1)."&amp;$qstr1"; ?>">주문번호</a></th>
        <th scope="col" id="th_odrer">주문자</th>
        <th scope="col" id="th_odrertel">주문자전화</th>
        <th scope="col" id="th_recvr">받는분</th>
        <th scope="col" rowspan="3">주문합계<br>선불배송비포함</th>
        <th scope="col" rowspan="2">입금합계</th>
        <th scope="col" rowspan="3">주문취소</th>
        <th scope="col" rowspan="3">미수금</th>
        <th scope="col" rowspan="3">보기</th>
    </tr>
    <tr>
        <th scope="col" id="th_odrid">회원ID</th>
        <th scope="col" id="th_odrcnt">주문상품수</th>
        <th scope="col" id="th_odrall">결제수단(PG사)</th>
    </tr>
    <tr>
        <th scope="col" id="odrstat">정기결제회차</th>
        <th scope="col" id="odrpay">주문상태</th>
        <th scope="col" id="delino">운송장번호</th>
        <th scope="col" id="delicom">배송회사</th>
        <th scope="col" id="delidate">배송일시</th>
        <th scope="col" >쿠폰</th>
    </tr>
    </thead>
    <tbody>
    <?php
    for ($i = 0; $row = sql_fetch_array($result); ++$i) {
        
        $od = get_subscription_order($row['od_id']);
        
        // 결제 수단
        $s_receipt_way = $s_br = '';
        if ($row['py_settle_case']) {
            $s_receipt_way = check_pay_name_replace($row['py_settle_case'], $row);
            $s_br = '<br />';
        } else {
            $s_receipt_way = '결제수단없음';
            $s_br = '<br />';
        }

        if ($row['py_receipt_point'] > 0) {
            $s_receipt_way .= $s_br.'포인트';
        }

        $mb_nick = get_sideview($row['mb_id'], get_text($od['od_name']), $od['od_email'], '');

        $py_cnt = 0;
        if ($row['mb_id']) {
            $sql2 = " select count(*) as cnt from {$g5['g5_subscription_pay_table']} where mb_id = '{$row['mb_id']}' ";
            $row2 = sql_fetch($sql2);
            $py_cnt = $row2['cnt'];
        }
        
        $subscription_pg_id = $row['subscription_pg_id'];
        $disp_pay_id = substr($subscription_pg_id, 0, 10).'-'.substr($subscription_pg_id, 10);

        // 주문 번호에 에스크로 표시
        $py_paytype = '';
        if ($row['py_test']) {
            $py_paytype .= '<span class="list_test">테스트</span>';
        }

        if ($default['de_escrow_use'] && $row['py_escrow']) {
            $py_paytype .= '<span class="list_escrow">에스크로</span>';
        }

        $uid = md5($row['pay_id'].$row['py_time']);

        $invoice_time = is_null_time($row['py_invoice_time']) ? G5_TIME_YMDHIS : $row['py_invoice_time'];
        $delivery_company = $row['py_delivery_company'] ? $row['py_delivery_company'] : $default['de_delivery_company'];

        $bg = 'bg'.($i % 2);
        $td_color = 0;
        if ($row['py_cancel_price'] > 0) {
            $bg .= 'cancel';
            $td_color = 1;
        }
        
        $goods = get_subscription_pay_full_goods($row['pay_id'], 1);
        ?>
    <tr class="orderlist<?php echo ' '.$bg; ?>">
        <td rowspan="3" class="td_chk">
            <input type="hidden" name="pay_id[<?php echo $i; ?>]" value="<?php echo $row['pay_id']; ?>" id="pay_id_<?php echo $i; ?>">
            <label for="chk_<?php echo $i; ?>" class="sound_only">주문번호 <?php echo $row['pay_id']; ?></label>
            <input type="checkbox" name="chk[]" value="<?php echo $i; ?>" id="chk_<?php echo $i; ?>">
        </td>
        <td rowspan="3">
            <div>
                <?php echo $goods['thumb']; ?>
                <br>
                <?php echo $goods['full_name']; ?>
            </div>
        </td>
        <td headers="th_ordnum" class="td_odrnum2" rowspan="2">
            <?php echo $row['py_receipt_time']; ?>
        </td>
        <td headers="th_ordnum" class="td_odrnum2" rowspan="2">
            <a href="#" class="orderitem"><?php echo $disp_pay_id; ?></a>
            <?php echo $py_paytype; ?>
        </td>
        <td headers="th_odrer" class="td_name"><?php echo $mb_nick; ?></td>
        <td headers="th_odrertel" class="td_tel"><?php echo get_text($row['py_hp']); ?></td>
        <td headers="th_recvr" class="td_name"><a href="<?php echo $_SERVER['SCRIPT_NAME']; ?>?sort1=<?php echo $sort1; ?>&amp;sort2=<?php echo $sort2; ?>&amp;sel_field=py_b_name&amp;search=<?php echo get_text($row['py_b_name']); ?>"><?php echo get_text($row['py_b_name']); ?></a></td>
        <td rowspan="3" class="td_num td_numsum"><?php echo number_format($row['py_cart_price'] + $row['py_send_cost'] + $row['py_send_cost2']); ?></td>
        <td rowspan="2" class="td_num_right"><?php echo number_format($row['py_receipt_price']);    // 입금합계 금액 ?></td>
        <td rowspan="3" class="td_numcancel<?php echo $td_color; ?> td_num"><?php echo number_format($row['py_cancel_price']); ?></td>
        <td rowspan="3" class="td_num_right"><?php echo number_format($row['py_misu']); ?></td>
        <td rowspan="3" class="td_mng td_mng_s">
            <a href="./payform.php?pay_id=<?php echo $row['pay_id']; ?>&amp;<?php echo $qstr; ?>" class="mng_mod btn btn_02"><span class="sound_only"><?php echo $row['pay_id']; ?> </span>보기</a>
        </td>
    </tr>
    <tr class="<?php echo $bg; ?>">
        <td headers="th_odrid">
            <?php if ($row['mb_id']) { ?>
            <a href="<?php echo $_SERVER['SCRIPT_NAME']; ?>?sort1=<?php echo $sort1; ?>&amp;sort2=<?php echo $sort2; ?>&amp;sel_field=mb_id&amp;search=<?php echo $row['mb_id']; ?>"><?php echo $row['mb_id']; ?></a>
            <?php } else { ?>
            비회원
            <?php } ?>
        </td>
        <td headers="th_odrcnt">
            <?php echo $row['py_cart_count']; ?>건<br>
            <?php echo $py_cnt; ?>건
        </td>
        <td headers="th_odrall">
            <input type="hidden" name="current_settle_case[<?php echo $i; ?>]" value="<?php echo $row['py_settle_case']; ?>">
            <?php echo $s_receipt_way.' ('.$row['py_pg'].')'; ?>
        </td>
    </tr>
    <tr class="<?php echo $bg; ?>">
        <td headers="odrstat" class="odrstat">
            <?php echo get_text($row['py_round_no']); ?> 회
        </td>
        <td headers="odrpay" class="odrpay">
            <?php echo $row['py_status']; ?>
            <input type="hidden" name="current_status[<?php echo $i; ?>]" value="<?php echo $row['py_status']; ?>">
        </td>
        <td headers="delino" class="delino">
            <?php if ($py_status == '준비') { ?>
                <input type="text" name="py_invoice[<?php echo $i; ?>]" value="<?php echo $row['py_invoice']; ?>" class="frm_input" size="10">
            <?php } else {
                echo $row['py_invoice'] ? $row['py_invoice'] : '-';
            } ?>
        </td>
        <td headers="delicom">
            <?php if ($py_status == '준비') { ?>
                <select name="py_delivery_company[<?php echo $i; ?>]">
                    <?php echo get_delivery_company($delivery_company); ?>
                </select>
            <?php } else {
                echo $row['py_delivery_company'] ? $row['py_delivery_company'] : '-';
            } ?>
        </td>
        <td headers="delidate">
            <?php if ($py_status == '준비') { ?>
                <input type="text" name="py_invoice_time[<?php echo $i; ?>]" value="<?php echo $invoice_time; ?>" class="frm_input" size="10" maxlength="19">
            <?php } else {
                echo is_null_time($row['py_invoice_time']) ? '-' : substr($row['py_invoice_time'], 2, 14);
            } ?>
        </td>
        <td class="td_num_right">
            <?php echo $row['py_coupon'];    // 사용된 쿠폰 금액 ?>
        </td>
    </tr>
    <?php
        $tot_itemcount += $row['py_cart_count'];
        $tot_orderprice += ($row['py_cart_price'] + $row['py_send_cost'] + $row['py_send_cost2']);
        $tot_ordercancel += $row['py_cancel_price'];
        $tot_receiptprice += $row['py_receipt_price'];
        $tot_couponprice += $row['couponprice'];
        $tot_misu += $row['py_misu'];
    }
sql_free_result($result);
if ($i == 0) {
    echo '<tr><td colspan="12" class="empty_table">자료가 없습니다.</td></tr>';
}
?>
    </tbody>
    <tfoot>
    <tr class="orderlist">
        <th scope="row" colspan="3">&nbsp;</th>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td><?php echo number_format($tot_itemcount); ?>건</td>
        <th scope="row">합 계</th>
        <td><?php echo number_format($tot_orderprice); ?></td>
        <td><?php echo number_format($tot_receiptprice); ?></td>
        <td><?php echo number_format($tot_ordercancel); ?></td>
        <td><?php echo number_format($tot_misu); ?></td>
        <td></td>
    </tr>
    </tfoot>
    </table>
</div>

<div class="local_cmd01 local_cmd">
<?php if (($py_status == '' || $py_status == '완료' || $py_status == '전체취소') == false) {
    // 검색된 주문상태가 '전체', '완료', '전체취소', '부분취소' 가 아니라면
    ?>
    <label for="py_status" class="cmd_tit">주문상태 변경</label>
    <?php
        $change_status = '';
    if ($py_status == '주문') {
        $change_status = '입금';
    }
    if ($py_status == '입금') {
        $change_status = '준비';
    }
    if ($py_status == '준비') {
        $change_status = '배송';
    }
    if ($py_status == '배송') {
        $change_status = '완료';
    }
    ?>
    <label><input type="checkbox" name="py_status" value="<?php echo $change_status; ?>"> '<?php echo $py_status; ?>'상태에서 '<strong><?php echo $change_status; ?></strong>'상태로 변경합니다.</label>
    <?php if ($py_status == '주문' || $py_status == '준비') { ?>
    <input type="checkbox" name="py_send_mail" value="1" id="py_send_mail" checked="checked">
    <label for="py_send_mail"><?php echo $change_status; ?>안내 메일</label>
    <input type="checkbox" name="send_sms" value="1" id="py_send_sms" checked="checked">
    <label for="py_send_sms"><?php echo $change_status; ?>안내 SMS</label>
    <?php } ?>
    <?php if ($py_status == '준비') { ?>
    <input type="checkbox" name="send_escrow" value="1" id="py_send_escrow">
    <label for="py_send_escrow">에스크로배송등록</label>
    <?php } ?>
    <input type="submit" value="선택수정" class="btn_submit" onclick="document.pressed=this.value">
<?php } ?>
    <?php if ($py_status == '주문') { ?> <span>주문상태에서만 삭제가 가능합니다.</span> <input type="submit" value="선택삭제" class="btn_submit" onclick="document.pressed=this.value"><?php } ?>
</div>

<div class="local_desc02 local_desc">
<p>
    &lt;무통장&gt;인 경우에만 &lt;주문&gt;에서 &lt;입금&gt;으로 변경됩니다. 가상계좌는 입금시 자동으로 &lt;입금&gt;처리됩니다.<br>
    &lt;준비&gt;에서 &lt;배송&gt;으로 변경시 &lt;에스크로배송등록&gt;을 체크하시면 에스크로 주문에 한해 PG사에 배송정보가 자동 등록됩니다.<br>
    <strong>주의!</strong> 주문번호를 클릭하여 나오는 주문상세내역의 주소를 외부에서 조회가 가능한곳에 올리지 마십시오.
</p>
</div>

</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}?$qstr&amp;page="); ?>

<script>
$(function(){
    $("#fr_date, #to_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+0d" });

    // 주문상품보기
    $(".orderitem").on("click", function() {
        var $this = $(this);
        var pay_id = $this.text().replace(/[^0-9]/g, "");

        if($this.next("#orderitemlist").length)
            return false;

        $("#orderitemlist").remove();

        $.post(
            "./ajax.orderitem.php",
            { pay_id: pay_id },
            function(data) {
                $this.after("<div id=\"orderitemlist\"><div class=\"itemlist\"></div></div>");
                $("#orderitemlist .itemlist")
                    .html(data)
                    .append("<div id=\"orderitemlist_close\"><button type=\"button\" id=\"orderitemlist-x\" class=\"btn_frmline\">닫기</button></div>");
            }
        );

        return false;
    });

    // 상품리스트 닫기
    $("#sodr_list").on("click", "#orderitemlist-x", function(e) {
        $("#orderitemlist").remove();
    });

    $("body").on("click", function(e) {
        if ($(e.target).closest("#orderitemlist").length === 0){
            $("#orderitemlist").remove();
        }
    });

    // 엑셀배송처리창
    $("#order_delivery").on("click", function() {
        var opt = "width=600,height=450,left=10,top=10";
        window.open(this.href, "win_excel", opt);
        return false;
    });
});

function set_date(today)
{
    <?php
    $date_term = date('w', G5_SERVER_TIME);
$week_term = $date_term + 7;
$last_term = strtotime(date('Y-m-01', G5_SERVER_TIME));
?>
    if (today == "오늘") {
        document.getElementById("fr_date").value = "<?php echo G5_TIME_YMD; ?>";
        document.getElementById("to_date").value = "<?php echo G5_TIME_YMD; ?>";
    } else if (today == "어제") {
        document.getElementById("fr_date").value = "<?php echo date('Y-m-d', G5_SERVER_TIME - 86400); ?>";
        document.getElementById("to_date").value = "<?php echo date('Y-m-d', G5_SERVER_TIME - 86400); ?>";
    } else if (today == "이번주") {
        document.getElementById("fr_date").value = "<?php echo date('Y-m-d', strtotime('-'.$date_term.' days', G5_SERVER_TIME)); ?>";
        document.getElementById("to_date").value = "<?php echo date('Y-m-d', G5_SERVER_TIME); ?>";
    } else if (today == "이번달") {
        document.getElementById("fr_date").value = "<?php echo date('Y-m-01', G5_SERVER_TIME); ?>";
        document.getElementById("to_date").value = "<?php echo date('Y-m-d', G5_SERVER_TIME); ?>";
    } else if (today == "지난주") {
        document.getElementById("fr_date").value = "<?php echo date('Y-m-d', strtotime('-'.$week_term.' days', G5_SERVER_TIME)); ?>";
        document.getElementById("to_date").value = "<?php echo date('Y-m-d', strtotime('-'.($week_term - 6).' days', G5_SERVER_TIME)); ?>";
    } else if (today == "지난달") {
        document.getElementById("fr_date").value = "<?php echo date('Y-m-01', strtotime('-1 Month', $last_term)); ?>";
        document.getElementById("to_date").value = "<?php echo date('Y-m-t', strtotime('-1 Month', $last_term)); ?>";
    } else if (today == "전체") {
        document.getElementById("fr_date").value = "";
        document.getElementById("to_date").value = "";
    }
}
</script>

<script>
function forderlist_submit(f)
{
    if (!is_checked("chk[]")) {
        alert(document.pressed+" 하실 항목을 하나 이상 선택하세요.");
        return false;
    }

    /*
    switch (f.py_status.value) {
        case "" :
            alert("변경하실 주문상태를 선택하세요.");
            return false;
        case '주문' :

        default :

    }
    */

    if(document.pressed == "선택삭제") {
        if(confirm("선택한 자료를 정말 삭제하시겠습니까?")) {
            f.action = "./orderlistdelete.php";
            return true;
        }
        return false;
    }

    var change_status = f.py_status.value;

    if (f.py_status.checked == false) {
        alert("주문상태 변경에 체크하세요.");
        return false;
    }

    var chk = document.getElementsByName("chk[]");

    for (var i=0; i<chk.length; i++)
    {
        if (chk[i].checked)
        {
            var k = chk[i].value;
            var current_settle_case = f.elements['current_settle_case['+k+']'].value;
            var current_status = f.elements['current_status['+k+']'].value;

            switch (change_status)
            {
                case "입금" :
                    if (!(current_status == "주문" && current_settle_case == "무통장")) {
                        alert("'주문' 상태의 '무통장'(결제수단)인 경우에만 '입금' 처리 가능합니다.");
                        return false;
                    }
                    break;

                case "준비" :
                    if (current_status != "입금") {
                        alert("'입금' 상태의 주문만 '준비'로 변경이 가능합니다.");
                        return false;
                    }
                    break;

                case "배송" :
                    if (current_status != "준비") {
                        alert("'준비' 상태의 주문만 '배송'으로 변경이 가능합니다.");
                        return false;
                    }

                    var invoice      = f.elements['py_invoice['+k+']'];
                    var invoice_time = f.elements['py_invoice_time['+k+']'];
                    var delivery_company = f.elements['py_delivery_company['+k+']'];

                    if ($.trim(invoice_time.value) == '') {
                        alert("배송일시를 입력하시기 바랍니다.");
                        invoice_time.focus();
                        return false;
                    }

                    if ($.trim(delivery_company.value) == '') {
                        alert("배송업체를 입력하시기 바랍니다.");
                        delivery_company.focus();
                        return false;
                    }

                    if ($.trim(invoice.value) == '') {
                        alert("운송장번호를 입력하시기 바랍니다.");
                        invoice.focus();
                        return false;
                    }

                    break;
            }
        }
    }

    if (!confirm("선택하신 주문서의 주문상태를 '"+change_status+"'상태로 변경하시겠습니까?"))
        return false;

    f.action = "./orderlistupdate.php";
    return true;
}
</script>

<?php
include_once G5_ADMIN_PATH.'/admin.tail.php';
