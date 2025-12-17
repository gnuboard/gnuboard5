<?php
$sub_menu = '600400';
include_once './_common.php';

auth_check_menu($auth, $sub_menu, 'r');

$g5['title'] = '회원 구독 리스트';
include_once G5_ADMIN_PATH.'/admin.head.php';
include_once G5_PLUGIN_PATH.'/jquery-ui/datepicker.php';

$where = array();

$doc = isset($_GET['doc']) ? clean_xss_tags($_GET['doc'], 1, 1) : '';
$sort1 = (isset($_GET['sort1']) && in_array($_GET['sort1'], ['od_id', 'od_cart_price', 'od_receipt_price', 'od_misu', 'od_cash'])) ? $_GET['sort1'] : '';
$sort2 = (isset($_GET['sort2']) && in_array($_GET['sort2'], ['desc', 'asc'])) ? $_GET['sort2'] : 'desc';
$sel_field = (isset($_GET['sel_field']) && in_array($_GET['sel_field'], ['od_id', 'mb_id', 'od_name', 'od_tel', 'od_hp', 'od_b_name', 'od_b_tel', 'od_b_hp', 'od_deposit_name', 'od_invoice'])) ? $_GET['sel_field'] : '';
$od_status = isset($_GET['od_status']) ? get_search_string($_GET['od_status']) : '';
$search = isset($_GET['search']) ? get_search_string($_GET['search']) : '';
$save_search = isset($_GET['save_search']) ? get_search_string($_GET['save_search']) : '';

$fr_date = (isset($_GET['fr_date']) && preg_match('/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/', $_GET['fr_date'])) ? $_GET['fr_date'] : '';
$to_date = (isset($_GET['to_date']) && preg_match('/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/', $_GET['to_date'])) ? $_GET['to_date'] : '';

$od_misu = isset($_GET['od_misu']) ? preg_replace('/[^0-9a-z]/i', '', $_GET['od_misu']) : '';
$od_refund_price = isset($_GET['od_refund_price']) ? preg_replace('/[^0-9a-z]/i', '', $_GET['od_refund_price']) : '';
$od_receipt_point = isset($_GET['od_receipt_point']) ? preg_replace('/[^0-9a-z]/i', '', $_GET['od_receipt_point']) : '';
$od_coupon = isset($_GET['od_coupon']) ? preg_replace('/[^0-9a-z]/i', '', $_GET['od_coupon']) : '';
$od_settle_case = isset($_GET['od_settle_case']) ? clean_xss_tags($_GET['od_settle_case'], 1, 1) : '';
$od_escrow = isset($_GET['od_escrow']) ? clean_xss_tags($_GET['od_escrow'], 1, 1) : '';

$od_enable_status = (isset($_GET['od_enable_status'])) ? (string) preg_replace('/[^0-9a-z]/i', '', $_GET['od_enable_status']) : 'all';

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

if ($od_status) {
    switch ($od_status) {
        case '전체취소':
            $where[] = " od_status = '취소' ";
            break;
        case '부분취소':
            // $where[] = " od_status IN('주문', '입금', '준비', '배송', '완료') and od_cancel_price > 0 ";
            break;
        default:
            $where[] = " od_status = '$od_status' ";
            break;
    }

    switch ($od_status) {
        case '주문':
            $sort1 = 'od_id';
            $sort2 = 'desc';
            break;
        case '입금':   // 결제완료
            $sort1 = 'od_receipt_time';
            $sort2 = 'desc';
            break;
        case '배송':   // 배송중
            $sort1 = 'od_invoice_time';
            $sort2 = 'desc';
            break;
    }
}

if ($od_settle_case) {
    $where[] = " od_settle_case = '$od_settle_case' ";
}

if ($fr_date && $to_date) {
    $where[] = " od_time between '$fr_date 00:00:00' and '$to_date 23:59:59' ";
}

if (($od_enable_status || (int)$od_enable_status === 0) && $od_enable_status !== 'all') {
    $where[] = " od_enable_status = '".(int) $od_enable_status."' ";
}

if ($where) {
    $sql_search = ' where '.implode(' and ', $where);
}

if ($sel_field == '') {
    $sel_field = 'od_id';
}
if ($sort1 == '') {
    $sort1 = 'od_id';
}
if ($sort2 == '') {
    $sort2 = 'desc';
}

$sql_common = " from {$g5['g5_subscription_order_table']} $sql_search ";

$sql = ' select count(od_id) as cnt '.$sql_common;

$row = sql_fetch($sql);
$total_count = isset($row['cnt']) ? $row['cnt'] : 0;

$rows = $config['cf_page_rows'];
$total_page = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) {
    $page = 1;
} // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " select *,
            (od_cart_coupon + od_coupon + od_send_coupon) as couponprice
           $sql_common
           order by $sort1 $sort2
           limit $from_record, $rows ";
$result = sql_query($sql);

$qstr1 = 'od_status='.urlencode($od_status).'&amp;od_settle_case='.urlencode($od_settle_case)."&amp;od_misu=$od_misu&amp;od_refund_price=$od_refund_price&amp;od_receipt_point=$od_receipt_point&amp;od_coupon=$od_coupon&amp;fr_date=$fr_date&amp;to_date=$to_date&amp;sel_field=$sel_field&amp;search=$search&amp;save_search=$search";
if ($default['de_escrow_use']) {
    $qstr1 .= "&amp;od_escrow=$od_escrow";
}
$qstr = "$qstr1&amp;sort1=$sort1&amp;sort2=$sort2&amp;page=$page";

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

// 정기결제 설정이 테스트로 되어 있는지 체크
subscription_pg_setting_check(true);
?>
<div class="local_ov01 local_ov">
    <?php echo $listall; ?>
    <span class="btn_ov01"><span class="ov_txt">전체 구독내역</span><span class="ov_num"> <?php echo number_format($total_count); ?>건</span></span>
</div>

<form name="frmorderlist" class="local_sch01 local_sch">
<input type="hidden" name="doc" value="<?php echo $doc; ?>">
<input type="hidden" name="sort1" value="<?php echo $sort1; ?>">
<input type="hidden" name="sort2" value="<?php echo $sort2; ?>">
<input type="hidden" name="page" value="<?php echo $page; ?>">
<input type="hidden" name="save_search" value="<?php echo $search; ?>">

<label for="sel_field" class="sound_only">검색대상</label>
<select name="sel_field" id="sel_field">
    <option value="od_id" <?php echo get_selected($sel_field, 'od_id'); ?>>주문번호</option>
    <option value="mb_id" <?php echo get_selected($sel_field, 'mb_id'); ?>>회원 ID</option>
    <option value="od_name" <?php echo get_selected($sel_field, 'od_name'); ?>>주문자</option>
    <option value="od_tel" <?php echo get_selected($sel_field, 'od_tel'); ?>>주문자전화</option>
    <option value="od_hp" <?php echo get_selected($sel_field, 'od_hp'); ?>>주문자핸드폰</option>
    <option value="od_b_name" <?php echo get_selected($sel_field, 'od_b_name'); ?>>받는분</option>
    <option value="od_b_tel" <?php echo get_selected($sel_field, 'od_b_tel'); ?>>받는분전화</option>
    <option value="od_b_hp" <?php echo get_selected($sel_field, 'od_b_hp'); ?>>받는분핸드폰</option>
    <option value="od_deposit_name" <?php echo get_selected($sel_field, 'od_deposit_name'); ?>>입금자</option>
    <option value="od_invoice" <?php echo get_selected($sel_field, 'od_invoice'); ?>>운송장번호</option>
</select>

<label for="search" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="search" value="<?php echo $search; ?>" id="search" required class="required frm_input" autocomplete="off">
<input type="submit" value="검색" class="btn_submit">

</form>

<form class="local_sch03 local_sch">
<div>
    <strong>주문상태</strong>
    <input type="radio" name="od_enable_status" value="all" id="od_enable_status_all"    <?php echo get_checked($od_enable_status, 'all'); ?>>
    <label for="od_enable_status_all">전체</label>
    <input type="radio" name="od_enable_status" value="1" id="od_enable_status_enable" <?php echo get_checked($od_enable_status, '1'); ?>>
    <label for="od_enable_status_enable">활성화</label>
    <input type="radio" name="od_enable_status" value="0" id="od_enable_status_disable" <?php echo get_checked($od_enable_status, '0'); ?>>
    <label for="od_enable_status_disable">비활성화</label>
</div>

<!--
<div>
    <strong>결제수단</strong>
    <input type="radio" name="od_settle_case" value="신용카드" id="od_settle_case06" <?php echo get_checked($od_settle_case, '신용카드'); ?>>
    <label for="od_settle_case06">신용카드</label>
</div>
-->

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
<input type="hidden" name="search_od_status" value="<?php echo $od_status; ?>">

<div class="tbl_head01 tbl_wrap">
    <table id="sodr_list">
    <caption>주문 내역 목록</caption>
    <thead>
    <tr>
        <th scope="col" rowspan="3">
            <label for="chkall" class="sound_only">주문 전체</label>
            <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
        </th>
        <th scope="col" id="th_ordnum" colspan="2"><a href="<?php echo title_sort('od_id', 1)."&amp;$qstr1"; ?>">구독번호</a></th>
        <th scope="col" id="th_odrer">주문자</th>
        <th scope="col" id="th_odrertel">주문자전화</th>
        <th scope="col" id="th_recvr">받는분</th>
        <th scope="col" rowspan="2">주문합계<br>선불배송비포함</th>
        <th scope="col" >입금합계</th>
        <th scope="col" rowspan="2">배송주기</th>
        <th scope="col" rowspan="2">주문회차</th>
        <th scope="col" rowspan="2">카드정보</th>
        <th scope="col" rowspan="2">보기</th>
    </tr>
    <tr>
        <th scope="col" id="odrstat">주문상태</th>
        <th scope="col" id="odrpay">결제수단 (PG사)</th>
        <th scope="col" id="th_odrid">회원ID</th>
        <th scope="col" id="th_odrcnt">주문상품수</th>
        <th scope="col" id="th_odrall">누적구독수</th>
        <th scope="col" id="th_odrall">쿠폰</th>
    </tr>
    </thead>
    <tbody>
    <?php
    for ($i = 0; $row = sql_fetch_array($result); ++$i) {
        // 결제 수단
        $s_receipt_way = $s_br = '';
        if ($row['od_settle_case']) {
            $s_receipt_way = check_pay_name_replace($row['od_settle_case'], $row);
            $s_br = '<br />';
        } else {
            $s_receipt_way = '결제수단없음';
            $s_br = '<br />';
        }

        if ($row['od_receipt_point'] > 0) {
            $s_receipt_way .= $s_br.'포인트';
        }

        $mb_nick = get_sideview($row['mb_id'], get_text($row['od_name']), $row['od_email'], '');

        $od_cnt = 0;
        if ($row['mb_id']) {
            $sql2 = " select count(*) as cnt from {$g5['g5_subscription_order_table']} where mb_id = '{$row['mb_id']}' ";
            $row2 = sql_fetch($sql2);
            $od_cnt = $row2['cnt'];
        }

        // 주문 번호에 device 표시
        $od_mobile = '';
        if ($row['od_mobile']) {
            $od_mobile = '(M)';
        }

        // 주문번호에 - 추가
        switch (strlen($row['od_id'])) {
            case 16:
                $disp_od_id = substr($row['od_id'], 0, 8).'-'.substr($row['od_id'], 8);
                break;
            default:
                $disp_od_id = substr($row['od_id'], 0, 6).'-'.substr($row['od_id'], 6);
                break;
        }

        // 주문 번호에 에스크로 표시
        $od_paytype = '';
        if ($row['od_test']) {
            $od_paytype .= '<span class="list_test">테스트</span>';
        }

        $uid = md5($row['od_id'].$row['od_time'].$row['od_ip']);

        // $invoice_time = is_null_time($row['od_invoice_time']) ? G5_TIME_YMDHIS : $row['od_invoice_time'];
        $invoice_time = G5_TIME_YMDHIS;
        $delivery_company = '';

        $bg = 'bg'.($i % 2);
        $td_color = 0;
        if (! $row['od_enable_status']) {
            $bg .= 'cancel';
            $td_color = 1;
        }
        
        // 정기구독정보의 배송주기와 이용횟수 등을 가져옴
        $crp = calculateRecurringPaymentDetails($row);
        
        // 배송주기
        $od_deliverys = $crp['deliverys'];
        // 신청한 총 이용횟수 (숫자 + 글자)
        $od_usages = $crp['usages'];
        // 신청한 총 이용횟수 (숫자만)
        $od_usage_count = $crp['usage_count'];
        // 정기구독이 진행중이명 0, 끝났으면 1
        $is_end_subscription = $crp['is_end_subscription'];
        // 현재횟차
        $current_cycle = $crp['current_cycle'];
        // 현재횟차 (숫자 + 글자)
        $current_cycle_str = $crp['current_cycle_str'];
        
        $cards = get_customer_card_info($row);
        
        $card_txt = $cards ? $cards['od_card_name'] . ' ('.$cards['card_mask_number'].')' : '카드정보없음';
        ?>
    <tr class="orderlist<?php echo ' '.$bg; ?>">
        <td rowspan="3" class="td_chk">
            <input type="hidden" name="od_id[<?php echo $i; ?>]" value="<?php echo $row['od_id']; ?>" id="od_id_<?php echo $i; ?>">
            <label for="chk_<?php echo $i; ?>" class="sound_only">주문번호 <?php echo $row['od_id']; ?></label>
            <input type="checkbox" name="chk[]" value="<?php echo $i; ?>" id="chk_<?php echo $i; ?>">
        </td>
        <td headers="th_ordnum" class="td_odrnum2" rowspan="2" colspan="2">
            <a href="<?php echo G5_SUBSCRIPTION_URL; ?>/orderinquiryview.php?od_id=<?php echo $row['od_id']; ?>&amp;uid=<?php echo $uid; ?>" class="orderitem"><?php echo $disp_od_id; ?></a>
            <?php echo $od_mobile; ?>
            <?php echo $od_paytype; ?>
        </td>
        <td headers="th_odrer" class="td_name"><?php echo $mb_nick; ?></td>
        <td headers="th_odrertel" class="td_tel"><?php echo get_text($row['od_tel']); ?></td>
        <td headers="th_recvr" class="td_name"><a href="<?php echo $_SERVER['SCRIPT_NAME']; ?>?sort1=<?php echo $sort1; ?>&amp;sort2=<?php echo $sort2; ?>&amp;sel_field=od_b_name&amp;search=<?php echo get_text($row['od_b_name']); ?>"><?php echo get_text($row['od_b_name']); ?></a></td>
        <td rowspan="3" class="td_num td_numsum"><?php echo number_format($row['od_cart_price'] + $row['od_send_cost'] + $row['od_send_cost2']); ?></td>
        <td rowspan="1" class="td_num_right"><?php echo number_format($row['od_receipt_price']); ?></td>
        <td rowspan="3">
            <?php echo $od_deliverys; ?>
        </td>
        <td rowspan="3">
            <?php echo $current_cycle; ?> / <?php echo $od_usage_count; ?>
        </td>
        <td rowspan="3"><?php echo $card_txt; ?></td>
        <td rowspan="3" class="td_mng td_mng_s">
            <a href="./orderform.php?od_id=<?php echo $row['od_id']; ?>&amp;<?php echo $qstr; ?>" class="mng_mod btn btn_02"><span class="sound_only"><?php echo $row['od_id']; ?> </span>보기</a>
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
        <td headers="th_odrcnt"><?php echo $row['od_cart_count']; ?>건</td>
        <td headers="th_odrall"><?php echo $od_cnt; ?>건</td>
        <td rowspan="2" class="td_num_right"><?php echo number_format($row['couponprice']); ?></td>
    </tr>
    <tr class="<?php echo $bg; ?>">
        <td headers="odrstat" class="odrstat">
            <input type="hidden" name="current_enable_status[<?php echo $i; ?>]" value="<?php echo $row['od_enable_status']; ?>">
            <?php
            // 주문상태
            echo $row['od_enable_status'] ? '활성화' : '비활성화'; ?>
        </td>
        <td headers="odrpay" class="odrpay">
            <input type="hidden" name="current_settle_case[<?php echo $i; ?>]" value="<?php echo $row['od_settle_case']; ?>">
            <?php echo $s_receipt_way.' ('.$row['od_pg'].')'; ?>
        </td>
        <td headers="delino" class="delino">
            <?php if ($od_status == '준비') { ?>
                <input type="text" name="od_invoice[<?php echo $i; ?>]" value="<?php echo $row['od_invoice']; ?>" class="frm_input" size="10">
            <?php } else {
                // echo $row['od_invoice'] ? $row['od_invoice'] : '-';
            } ?>
        </td>
        <td headers="delicom">
            <?php if ($od_status == '준비') { ?>
                <select name="od_delivery_company[<?php echo $i; ?>]">
                    <?php echo get_delivery_company($delivery_company); ?>
                </select>
            <?php } else {
                // echo $row['od_delivery_company'] ? $row['od_delivery_company'] : '-';
            } ?>
        </td>
        <td headers="delidate">
            <?php if ($od_status == '준비') { ?>
                <input type="text" name="od_invoice_time[<?php echo $i; ?>]" value="<?php echo $invoice_time; ?>" class="frm_input" size="10" maxlength="19">
            <?php } else {
                // echo is_null_time($row['od_invoice_time']) ? '-' : substr($row['od_invoice_time'], 2, 14);
            } ?>
        </td>
    </tr>
    <?php
        $tot_itemcount += $row['od_cart_count'];
        $tot_orderprice += ($row['od_cart_price'] + $row['od_send_cost'] + $row['od_send_cost2']);
        //$tot_ordercancel += 0;
        //$tot_receiptprice += $row['od_receipt_price'];
        $tot_couponprice += $row['couponprice'];
        
        //$tot_misu += 0;
    }
sql_free_result($result);
if ($i == 0) {
    echo '<tr><td colspan="11" class="empty_table">자료가 없습니다.</td></tr>';
}
?>
    </tbody>
    <tfoot>
    <tr class="orderlist">
        <th scope="row" colspan="3">&nbsp;</th>
        <td>&nbsp;</td>
        <td><?php echo number_format($tot_itemcount); ?>건</td>
        <th scope="row">합 계</th>
        <td><?php echo number_format($tot_orderprice); ?></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>
    </tfoot>
    </table>
</div>

<div class="local_cmd01 local_cmd">
<?php if (($od_status == '' || $od_status == '완료' || $od_status == '전체취소') == false) {
    // 검색된 주문상태가 '전체', '완료', '전체취소', '부분취소' 가 아니라면
    ?>
    <label for="od_status" class="cmd_tit">주문상태 변경</label>
    <?php
        $change_status = '';
    if ($od_status == '주문') {
        $change_status = '입금';
    }
    if ($od_status == '입금') {
        $change_status = '준비';
    }
    if ($od_status == '준비') {
        $change_status = '배송';
    }
    if ($od_status == '배송') {
        $change_status = '완료';
    }
    ?>
    <label><input type="checkbox" name="od_status" value="<?php echo $change_status; ?>"> '<?php echo $od_status; ?>'상태에서 '<strong><?php echo $change_status; ?></strong>'상태로 변경합니다.</label>
    <?php if ($od_status == '주문' || $od_status == '준비') { ?>
    <input type="checkbox" name="od_send_mail" value="1" id="od_send_mail" checked="checked">
    <label for="od_send_mail"><?php echo $change_status; ?>안내 메일</label>
    <input type="checkbox" name="send_sms" value="1" id="od_send_sms" checked="checked">
    <label for="od_send_sms"><?php echo $change_status; ?>안내 SMS</label>
    <?php } ?>
    <?php if ($od_status == '준비') { ?>
    <input type="checkbox" name="send_escrow" value="1" id="od_send_escrow">
    <label for="od_send_escrow">에스크로배송등록</label>
    <?php } ?>
    <input type="submit" value="선택수정" class="btn_submit" onclick="document.pressed=this.value">
<?php } ?>
    <?php if ($od_status == '주문') { ?> <span>주문상태에서만 삭제가 가능합니다.</span> <input type="submit" value="선택삭제" class="btn_submit" onclick="document.pressed=this.value"><?php } ?>
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
        var od_id = $this.text().replace(/[^0-9]/g, "");

        if($this.next("#orderitemlist").length)
            return false;

        $("#orderitemlist").remove();

        $.post(
            "./ajax.orderitem.php",
            { od_id: od_id },
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
    switch (f.od_status.value) {
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

    var change_status = f.od_status.value;

    if (f.od_status.checked == false) {
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

                    var invoice      = f.elements['od_invoice['+k+']'];
                    var invoice_time = f.elements['od_invoice_time['+k+']'];
                    var delivery_company = f.elements['od_delivery_company['+k+']'];

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
