<?php
$sub_menu = '400440';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$sql_common = " from {$g5['g5_shop_personalpay_table']} ";

$sql_search = " where (1) ";
if ($stx) {
    $sql_search .= " and ( ";
    switch ($sfl) {
        case 'pp_id' :
            $sql_search .= " ({$sfl} = '{$stx}') ";
            break;
        case 'od_id' :
            $sql_search .= " ({$sfl} = '{$stx}') ";
            break;
        default :
            $sql_search .= " ({$sfl} like '%{$stx}%') ";
            break;
    }
    $sql_search .= " ) ";
}

if (!$sst) {
    $sst  = "pp_id";
    $sod = "desc";
}
$sql_order = " order by {$sst} {$sod} ";

$sql = " select count(*) as cnt
            {$sql_common}
            {$sql_search}
            {$sql_order} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " select *
            {$sql_common}
            {$sql_search}
            {$sql_order}
            limit {$from_record}, {$rows} ";
$result = sql_query($sql);

$g5['title'] = '개인결제 관리';
include_once (G5_ADMIN_PATH.'/admin.head.php');

$colspan = 10;
?>

<div class="local_ov01 local_ov">
    전체 <?php echo number_format($total_count) ?> 건
</div>

<form name="fsearch" id="fsearch" class="local_sch01 local_sch" method="get">
    <select name="sfl" title="검색대상">
        <option value="pp_id"<?php echo get_selected($_GET['sfl'], "pp_id"); ?>>개인결제번호</option>
        <option value="pp_name"<?php echo get_selected($_GET['sfl'], "pp_name"); ?>>이름</option>
        <option value="od_id"<?php echo get_selected($_GET['sfl'], "od_id"); ?>>주문번호</option>
    </select>
    <label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
    <input type="text" name="stx" value="<?php echo $stx ?>" id="stx" required class="required frm_input">
    <input type="submit" class="btn_submit" value="검색">
</form>

<div class="btn_add01 btn_add">
    <a href="./personalpayform.php" id="personalpay_add">개인결제 추가</a>
</div>

<form name="fpersonalpaylist" id="fpersonalpaylist" method="post" action="./personalpaylistdelete.php" onsubmit="return fpersonalpaylist_submit(this);">
<input type="hidden" name="sst" value="<?php echo $sst; ?>">
<input type="hidden" name="sod" value="<?php echo $sod; ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl; ?>">
<input type="hidden" name="stx" value="<?php echo $stx; ?>">
<input type="hidden" name="page" value="<?php echo $page; ?>">
<input type="hidden" name="token" value="">

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?> 목록</caption>
    <thead>
    <tr>
        <th scope="col">
            <label for="chkall" class="sound_only">개인결제 전체</label>
            <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
        </th>
        <th scope="col">제목</th>
        <th scope="col"><?php echo subject_sort_link('od_id') ?>주문번호</a></th>
        <th scope="col">주문금액</th>
        <th scope="col">입금금액</th>
        <th scope="col">미수금액</th>
        <th scope="col">입금방법</a></th>
        <th scope="col"><?php echo subject_sort_link('pp_receipt_time') ?>입금일</a></a></th>
        <th scope="col">사용</th>
        <th scope="col">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        if($row['od_id'])
            $od_id = '<a href="./orderform.php?od_id='.$row['od_id'].'" target="_blank">'.$row['od_id'].'</a>';
        else
            $od_id = '&nbsp;';

        $bg = 'bg'.($i%2);
    ?>

    <tr class="<?php echo $bg; ?>">
        <td class="td_chk">
            <input type="hidden" id="pp_id_<?php echo $i; ?>" name="pp_id[<?php echo $i; ?>]" value="<?php echo $row['pp_id']; ?>">
            <input type="checkbox" id="chk_<?php echo $i; ?>" name="chk[]" value="<?php echo $i; ?>" title="내역선택">
        </td>
        <td><?php echo $row['pp_name']; ?></td>
        <td class="td_odrnum3"><?php echo $od_id; ?></td>
        <td class="td_numsum"><?php echo number_format($row['pp_price']); ?></td>
        <td class="td_numincome"><?php echo number_format($row['pp_receipt_price']); ?></td>
        <td class="td_numrdy"><?php echo number_format($row['pp_price'] - $row['pp_receipt_price']); ?></td>
        <td class="td_payby"><?php echo $row['pp_settle_case']; ?></td>
        <td class="td_date"><?php echo is_null_time($row['pp_receipt_time']) ? '' : substr($row['pp_receipt_time'], 2, 8); ?></td>
        <td class="td_boolean"><?php echo $row['pp_use'] ? '예' : '아니오'; ?></td>
        <td class="td_mngsmall">
            <a href="./personalpayform.php?w=u&amp;pp_id=<?php echo $row['pp_id']; ?>&amp;<?php echo $qstr; ?>"><span class="sound_only"><?php echo $row['pp_id']; ?> </span>수정</a>
            <a href="./personalpaycopy.php?pp_id=<?php echo $row['pp_id']; ?>" class="personalpaycopy"><span class="sound_only"><?php echo $row['pp_id']; ?> </span>복사</a>
        </td>
    </tr>

    <?php
    }

    if ($i == 0)
        echo '<tr><td colspan="'.$colspan.'" class="empty_table">자료가 없습니다.</td></tr>';
    ?>
    </tbody>
    </table>
</div>

<div class="btn_list01 btn_list">
    <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value">
</div>

</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}?$qstr&amp;page="); ?>

<script>
$(function() {
    $(".personalpaycopy").on("click", function() {
        var href = this.href;
        window.open(href, "copywin", "left=100, top=100, width=600, height=300, scrollbars=0");
        return false;
    });
});

function fpersonalpaylist_submit(f)
{
    if (!is_checked("chk[]")) {
        alert(document.pressed+" 하실 항목을 하나 이상 선택하세요.");
        return false;
    }

    if(document.pressed == "선택삭제") {
        if(!confirm("선택한 자료를 정말 삭제하시겠습니까?")) {
            return false;
        }
    }

    return true;
}
</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>