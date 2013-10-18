<?php
$sub_menu = '400500';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g5['title'] = '배송일괄처리';
include_once (G5_ADMIN_PATH.'/admin.head.php');

//sql_query(" update {$g5['g5_shop_cart_table']} set ct_status = '완료' where ct_status = '배송' ");

// 배송회사리스트 ---------------------------------------------
$delivery_options = '<option value="">선택하세요</option>'.PHP_EOL;
$sql = " select * from {$g5['g5_shop_delivery_table']} order by dl_order ";
$result = sql_query($sql);
for($i=0; $row=sql_fetch_array($result); $i++) {
    $delivery_options .= '<option value="'.$row['dl_id'].'">'.$row['dl_company'].'</option>'.PHP_EOL;
}
// 배송회사리스트 end ---------------------------------------------

$where = " where ";
$sql_search = "";
if ($search != "") {
    if ($sel_field != "") {
        $sql_search .= " $where $sel_field like '%$search%' ";
        $where = " and ";
    }
}

$sql_search .= " $where od_status in ('준비', '배송') ";

if ($sel_ca_id != "") {
    $sql_search .= " $where ca_id like '$sel_ca_id%' ";
}

if ($sel_field == "")  $sel_field = "od_id";

$sql_common = " from {$g5['g5_shop_order_table']} $sql_search ";

// 테이블의 전체 레코드수만 얻음
if ($chk_misu) {
    $sql  = " select * $sql_common where od_misu <= 0 ";
    $result = sql_query($sql);
    $total_count = mysql_num_rows($result);
}
else {
    $row = sql_fetch("select count(od_id) as cnt from {$g5['g5_shop_order_table']} $sql_search ");
    $total_count = $row['cnt'];
}

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

if (!$sort1) {
    $sort1 = "od_id";
}

if (!$sort2) {
    $sort2 = "desc";
}

if ($sort2 == "desc") {
    $unsort2 == "asc";
} else {
    $unsort2 == "desc";
}

$qstr1 = 'sel_ca_id='.$sel_ca_id.'&amp;sel_field='.$sel_field.'&amp;search='.$search.'&amp;chk_misu='.$chk_misu;
$qstr  = $qstr1.'&amp;sort1='.$sort1.'&amp;sort2='.$sort2.'&amp;page='.$page;

$listall = '<a href="'.$_SERVER['PHP_SELF'].'" class="ov_listall">전체목록</a>';
?>

<div class="local_ov01 local_ov">
    <?php echo $listall; ?>
    전체 주문내역 <?php echo $total_count; ?>건
</div>

<form name="flist" autocomplete="off" class="local_sch01 local_sch">
<input type="hidden" name="doc"  value="<?php echo $doc; ?>">
<input type="hidden" name="page" value="<?php echo $page; ?>">

<input type="checkbox" name="chk_misu" value="1" id="chk_misu" <?php echo $chk_misu?'checked="checked"':''; ?> />
<label for="chk_misu">미수금없음</label>

<label for="sel_field" class="sound_only">검색대상</label>
<select name="sel_field">
    <option value="od_id" <?php echo get_selected($sel_field, 'od_id'); ?>>주문번호</option>
    <option value="od_name" <?php echo get_selected($sel_field, 'od_name'); ?>>주문자</option>
    <option value="od_invoice" <?php echo get_selected($sel_field, 'od_invoice'); ?>>운송장번호</option>
</select>

<label for="search" class="sound_only">검색어</label>
<input type="text" name="search" value="<?php echo $search; ?>" id="search" class="frm_input">
<input type="submit" value="검색" class="btn_submit">
</form>

<div class="local_desc01 local_desc">
    <ul>
        <li>주문액은 취소, 반품, 품절이 포함된 금액이 아닙니다.</li>
        <li>입금액은 환불/취소가 포함된 금액이 아닙니다.</li>
        <li>배송일시, 배송회사는 입력의 편의성을 위하여 기본값으로 설정되어 있습니다. 운송장번호만 없는것이 미배송 주문자료입니다.</li>
    </ul>
</div>

<form name="fdeliverylist" method="post" onsubmit="return fdeliverylist_submit(this);" autocomplete="off">
<input type="hidden" name="sel_ca_id" value="<?php echo $sel_ca_id; ?>">
<input type="hidden" name="sel_field" value="<?php echo $sel_field; ?>">
<input type="hidden" name="search" value="<?php echo $search; ?>">
<input type="hidden" name="page" value="<?php echo $page; ?>">
<input type="hidden" name="sort1" value="<?php echo $sort1; ?>">
<input type="hidden" name="sort2" value="<?php echo $sort2; ?>">

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?> 목록</caption>
    <thead>
    <tr>
        <th scope="col">
            <label for="chkall" class="sound_only">주문 전체</label>
            <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
        </th>
        <th scope="col"><a href="<?php echo title_sort("od_id",1) . "&amp;$qstr1"; ?>">주문번호</a></th>
        <th scope="col"><a href="<?php echo title_sort("od_name") . "&amp;$qstr1"; ?>">주문자</a></th>
        <th scope="col"><a href="<?php echo title_sort("od_cart_price",1) . "&amp;$qstr1"; ?>">주문액</a></th>
        <th scope="col"><a href="<?php echo title_sort("od_receipt_price",1) . "&amp;$qstr1"; ?>">입금액</a></th>
        <th scope="col"><a href="<?php echo title_sort("od_misu",1) . "&amp;$qstr1"; ?>">미수금</a></th>
        <th scope="col"><a href="<?php echo title_sort("od_hope_date",1) . "&amp;$qstr1"; ?>">희망배송일</a></th>
        <th scope="col"><a href="<?php echo title_sort("od_invoice_time") . "&amp;$qstr1"; ?>">배송일시</a></th>
        <th scope="col">배송업체</th>
        <th scope="col"><a href="<?php echo title_sort("od_invoice", 1) . "&amp;$qstr1"; ?>">운송장번호</a></th>
        <th scope="col">완료상태</th>
    </tr>
    </thead>
    <tbody>
    <?php
    $sql  = " select * $sql_common ";
    if ($chk_misu)
        $sql .= " where  od_misu <= 0 ";
    $sql .= "  order by $sort1 $sort2
              limit $from_record, {$config['cf_page_rows']} ";
    $result = sql_query($sql);
    for ($i=0; $row=mysql_fetch_array($result); $i++)
    {
        $invoice_time = G5_TIME_YMDHIS;
        if (!is_null_time($row['od_invoice_time']))
            $invoice_time = $row['od_invoice_time'];

        $sql1 = " select * from {$g5['member_table']} where mb_id = '{$row['mb_id']}' ";
        $row1 = sql_fetch($sql1);
        $name = get_sideview($row['mb_id'], $row['mb_name'], $row['mb_email'], $row['mb_homepage']);

        if ($default['de_hope_date_use'])
            $hope_date = substr($row['od_hope_date'],2,8).' ('.get_yoil($row['od_hope_date']).')';
        else
            $hope_date = "사용안함";

        $tr_bg = $i%2 ? 'class="tr_bg1"' : 'class="tr_bg0"';
    ?>
    <tr<?php echo ' '.$tr_bg; ?>>
        <td>
            <input type="hidden" name="od_id[<?php echo $i ?>]" value="<?php echo $row['od_id'] ?>" id="od_id_<?php echo $i ?>">
            <label for="chk_<?php echo $i; ?>" class="sound_only">주문번호 <?php echo $row['od_id']; ?></label>
            <input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i ?>">
        </td>
        <td>
            <!-- <input type="hidden" name="od_id[<?php echo $i; ?>]" value="<?php echo $row['od_id']; ?>"> -->
            <input type="hidden" name="od_tno[<?php echo $i; ?>]" value="<?php echo $row['od_tno']; ?>">
            <input type="hidden" name="od_escrow[<?php echo $i; ?>]" value="<?php echo $row['od_escrow']; ?>">
            <a href="./orderform.php?od_id=<?php echo $row['od_id']; ?>"><?php echo $row['od_id']; ?></a>
        </td>
        <td class="td_name"><?php echo $row['od_name']; ?></td>
        <td class="td_numsum"><?php echo display_price($row['od_cart_price']); ?></td>
        <td class="td_numincome"><?php echo display_price($row['od_receipt_price']); ?></td>
        <td class="td_numrdy"><?php echo display_price($row['od_misu']); ?></td>
        <td class="td_mngsmall"><?php echo $hope_date; ?></td>
        <td class="td_datetime td_input"><input type="text" name="od_invoice_time[<?php echo $i; ?>]" value="<?php echo $invoice_time; ?>" class="frm_input" size="20" maxlength="19"></td>
        <td class="td_delicom">
            <label for="dl_id_<?php echo $i; ?>" class="sound_only">배송업체</label>
            <select name="dl_id[<?php echo $i; ?>]" id="dl_id_<?php echo $i; ?>">
                <?php echo conv_selected_option($delivery_options, $row['dl_id']); ?>
            </select>
        </td>
        <td class="td_input">
            <!-- 값이 바뀌었는지 비교하기 위하여 저장 -->
            <input type="hidden" name="save_dl_id[<?php echo $i; ?>]" value="<?php echo $row['dl_id']; ?>">
            <input type="hidden" name="save_od_invoice[<?php echo $i; ?>]" value="<?php echo $row['od_invoice']; ?>">
            <input type="text" name="od_invoice[<?php echo $i; ?>]" value="<?php echo $row['od_invoice']; ?>" class="frm_input" size="10">
        </td>
        <td><input type="checkbox" name="od_status" value="1"></td>
    </tr>
    <?php
    }
    if ($i == 0)
        echo '<tr><td colspan="10" class="empty_table">자료가 한건도 없습니다.</td></tr>';
    ?>
    </table>
</div>

<div class="local_cmd01 local_cmd">
    <input type="checkbox" name="od_send_mail" value="1" id="od_send_mail" checked>
    <label for="od_send_mail">메일발송</label>
    <input type="checkbox" name="send_sms" value="1" id="od_send_sms" checked>
    <label for="od_send_sms">SMS</label>
    <input type="checkbox" name="send_escrow" value="1" id="od_send_escrow">
    <label for="od_send_escrow">에스크로배송시작</label>
</div>

<div class="btn_confirm01 btn_confirm">
    <input type="submit" value="선택수정" class="btn_submit" onclick="document.pressed=this.value">
</div>

</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['PHP_SELF']}?$qstr&amp;page="); ?>

<script>
function fdeliverylist_submit(f)
{
    if (!is_checked("chk[]")) {
        alert(document.pressed+" 하실 항목을 하나 이상 선택하세요.");
        return false;
    }

    f.action = "./deliverylistupdate.php";

    return true;
}
</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
