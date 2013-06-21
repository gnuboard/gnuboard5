<?php
$sub_menu = '400500';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g4['title'] = '배송일괄처리';
include_once (G4_ADMIN_PATH.'/admin.head.php');

//sql_query(" update $g4[shop_cart_table] set ct_status = '완료' where ct_status = '배송' ");

// 배송회사리스트 ---------------------------------------------
$delivery_options = '<option value="">선택하세요</option>'.PHP_EOL;
$sql = " select * from {$g4['shop_delivery_table']} order by dl_order ";
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

if ($sel_ca_id != "") {
    $sql_search .= " $where ca_id like '$sel_ca_id%' ";
}

if ($sel_field == "")  $sel_field = "od_id";

$sql_common = " from {$g4['shop_order_table']} a
                left join {$g4['shop_cart_table']} b on (a.uq_id=b.uq_id)
                $sql_search ";

// 테이블의 전체 레코드수만 얻음
if ($chk_misu) {
    $sql  = " select od_id, a.*, "._MISU_QUERY_." $sql_common group by od_id having  misu <= 0 ";
    $result = sql_query($sql);
    $total_count = mysql_num_rows($result);
}
else {
    $row = sql_fetch("select count(od_id) as cnt from {$g4['shop_order_table']} $sql_search ");
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

$listall = '';
if ($search) // 검색렬일 때만 처음 버튼을 보여줌
    $listall = '<a href="'.$_SERVER['PHP_SELF'].'">전체목록</a>';
?>

<form name="flist" autocomplete="off">
<input type="hidden" name="doc"  value="<?php echo $doc; ?>">
<input type="hidden" name="page" value="<?php echo $page; ?>">
<fieldset>
    <legend>배송조건 검색</legend>
    <span>
        <?php echo $listall; ?>
        전체 주문내역 <?php echo $total_count; ?>건
    </span>

    <label for="chk_misu">미수금없음</label>
    <input type="checkbox" name="chk_misu" value="1" id="chk_misu" <?php echo $chk_misu?"checked='checked'":""; ?> />

    <label for="sel_field" class="sound_only">검색대상</label>
    <select name="sel_field">
        <option value="od_id" <?php echo get_selected($sel_field, 'od_id'); ?>>주문번호</option>
        <option value="od_name" <?php echo get_selected($sel_field, 'od_name'); ?>>주문자</option>
        <option value="od_invoice" <?php echo get_selected($sel_field, 'od_invoice'); ?>>운송장번호</option>
    </select>

    <label for="search" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
    <input type="text" name="search" value="<?php echo $search; ?>" id="search" required class="frm_input required">
    <input type="submit" value="검색" class="btn_submit">
</fieldset>
</form>

<section class="cbox">
    <h2>배송내역</h2>
    <ul>
        <li>주문액은 취소, 반품, 품절, DC가 포함된 금액이 아닙니다.</li>
        <li>입금액은 환불, 승인취소가 포함된 금액이 아닙니다.</li>
        <li>배송일시, 배송회사는 입력의 편의성을 위하여 기본값으로 설정되어 있습니다. 운송장번호만 없는것이 미배송 주문자료입니다.</li>
    </ul>

    <ul class="sort_odr">
        <li><a href="<?php echo title_sort("od_id",1) . "&amp;$qstr1"; ?>">주문번호<span class="sound_only"> 순 정렬</span></a></li>
        <li><a href="<?php echo title_sort("od_name") . "&amp;$qstr1"; ?>">주문자<span class="sound_only"> 순 정렬</span></a></li>
        <li><a href="<?php echo title_sort("orderamount",1) . "&amp;$qstr1"; ?>">주문액<span class="sound_only"> 순 정렬</span></a></li>
        <li><a href="<?php echo title_sort("receiptamount",1) . "&amp;$qstr1"; ?>">입금액<span class="sound_only"> 순 정렬</span></a></li>
        <li><a href="<?php echo title_sort("misu",1) . "&amp;$qstr1"; ?>">미수금<span class="sound_only"> 순 정렬</span></a></li>
        <li><a href="<?php echo title_sort("od_hope_date",1) . "&amp;$qstr1"; ?>">희망배송일<span class="sound_only"> 순 정렬</span></a></li>
        <li><a href="<?php echo title_sort("od_invoice_time") . "&amp;$qstr1"; ?>">배송일시<span class="sound_only"> 순 정렬</span></a></li>
        <li><a href="<?php echo title_sort("od_invoice", 1) . "&amp;$qstr1"; ?>">운송장번호<span class="sound_only"> 순 정렬</span></a></li>
    </ul>

    <form name="fdeliverylistupate" method="post" action="./deliverylistupdate.php" autocomplete="off">
    <input type="hidden" name="sel_ca_id" value="<?php echo $sel_ca_id; ?>">
    <input type="hidden" name="sel_field" value="<?php echo $sel_field; ?>">
    <input type="hidden" name="search" value="<?php echo $search; ?>">
    <input type="hidden" name="page" value="<?php echo $page; ?>">
    <input type="hidden" name="sort1" value="<?php echo $sort1; ?>">
    <input type="hidden" name="sort2" value="<?php echo $sort2; ?>">

    <table id="sdeli_proc">
    <thead>
    <tr>
        <th scope="col">주문번호</th>
        <th scope="col">주문자</th>
        <th scope="col">주문액</th>
        <th scope="col">입금액</th>
        <th scope="col">미수금</th>
        <th scope="col">희망배송일</th>
        <th scope="col">배송일시</th>
        <th scope="col">배송업체</th>
        <th scope="col">운송장번호</th>
    </tr>
    </thead>
    <tbody>
    <?php
    $sql  = " select a.od_id,
                     a.*, "._MISU_QUERY_."
              $sql_common
              group by a.od_id ";
    if ($chk_misu)
        $sql .= " having  misu <= 0 ";
    $sql .= "  order by $sort1 $sort2/* 김선용 심각한 트래픽으로 미사용, a.od_invoice asc*/
              limit $from_record, $config[cf_page_rows] ";
    $result = sql_query($sql);
    for ($i=0; $row=mysql_fetch_array($result); $i++)
    {
        $invoice_time = G4_TIME_YMDHIS;
        if (!is_null_time($row['od_invoice_time']))
            $invoice_time = $row['od_invoice_time'];

        $sql1 = " select * from {$g4['member_table']} where mb_id = '{$row['mb_id']}' ";
        $row1 = sql_fetch($sql1);
        $name = get_sideview($row['mb_id'], $row['mb_name'], $row['mb_email'], $row['mb_homepage']);

        if ($default['de_hope_date_use'])
            $hope_date = substr($row['od_hope_date'],2,8).' ('.get_yoil($row['od_hope_date']).')';
        else
            $hope_date = "사용안함";
    ?>
    <tr>
        <td>
            <input type="hidden" name="od_id[<?php echo $i; ?>]" value="<?php echo $row['od_id']; ?>">
            <input type="hidden" name="uq_id[<?php echo $i; ?>]" value="<?php echo $row['uq_id']; ?>">
            <input type="hidden" name="od_tno[<?php echo $i; ?>]" value="<?php echo $row['od_tno']; ?>">
            <input type="hidden" name="od_escrow[<?php echo $i; ?>]" value="<?php echo $row['od_escrow']; ?>">
            <a href="./orderform.php?od_id=<?php echo $row['od_id']; ?>"><?php echo $row['od_id']; ?></a>
        </td>
        <td class="td_name"><?php echo $row['od_name']; ?></td>
        <td><?php echo display_price($row['orderamount']); ?></td>
        <td><?php echo display_price($row['receiptamount']); ?></td>
        <td><?php echo display_price($row['misu']); ?></td>
        <td><?php echo $hope_date; ?></td>
        <td><input type="text" name="od_invoice_time[<?php echo $i; ?>]" value="<?php echo $invoice_time; ?>" class="frm_input" size="20" maxlength="19"></td>
        <td>
            <label for="dl_id_<?php echo $i; ?>">배송업체</label>
            <select name="dl_id[<?php echo $i; ?>]" id="dl_id_<?php echo $i; ?>">
                <?php echo conv_selected_option($delivery_options, $row['dl_id']); ?>
            </select>
        </td>
        <td>
            <!-- 값이 바뀌었는지 비교하기 위하여 저장 -->
            <input type="hidden" name="save_dl_id[<?php echo $i; ?>]" value="<?php echo $row['dl_id']; ?>">
            <input type="hidden" name="save_od_invoice[<?php echo $i; ?>]" value="<?php echo $row['od_invoice']; ?>">
            <input type="text" name="od_invoice[<?php echo $i; ?>]" value="<?php echo $row['od_invoice']; ?>" class="frm_input" size="10">
        </td>
    </tr>
    <?php
        if ($row['dl_id']) {
            //echo "<script> document.fdeliverylistupate.elements('dl_id[$i]').value = '$row[dl_id]'; </script>";
            // FF 3.0 에서 위의 코드는 에러를 발생함 (080626 수정)
            echo "<script> document.fdeliverylistupate.elements['dl_id[$i]'].value = '{$row['dl_id']}'; </script>";
        }
    }
    if ($i == 0)
        echo '<tr><td colspan="20" class="empty_table">자료가 한건도 없습니다.</td></tr>';
    ?>
    </table>

    <fieldset id="sdeli_proc_fs">
        <legend>배송 처리 후 안내 발송 선택</legend>
        <label for="od_send_mail">메일발송</label>
        <input type="checkbox" name="od_send_mail" value="1" id="od_send_mail" checked>
        <label for="od_send_sms">SMS</label>
        <input type="checkbox" name="send_sms" value="1" id="od_send_sms" checked>
        <label for="od_send_escrow">에스크로배송시작</label>
        <input type="checkbox" name="send_escrow" value="1" id="od_send_escrow">
    </fieldset>

    <div class="btn_confirm">
        <input type="submit" value="일괄수정" class="btn_submit" accesskey="s">
    </div>

    </form>

</section>

<?php echo get_paging(G4_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['PHP_SELF']}?$qstr&amp;page="); ?>

<?php
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>
