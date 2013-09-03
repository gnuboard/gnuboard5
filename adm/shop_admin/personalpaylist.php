<?php
$sub_menu = '400440';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$token = get_token();

$sql_common = " from {$g4['shop_personalpay_table']} ";

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
if ($page == '') $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " select *
            {$sql_common}
            {$sql_search}
            {$sql_order}
            limit {$from_record}, {$rows} ";
$result = sql_query($sql);

$g4['title'] = '개인결제 관리';
include_once (G4_ADMIN_PATH.'/admin.head.php');

$colspan = 8;
?>

<form name="fsearch" id="fsearch" method="get">
<fieldset>
    <legend>개인결제 검색</legend>
    <span>
        <?php echo $listall ?>
        전체 <?php echo number_format($total_count) ?> 건
    </span>
    <select name="sfl" title="검색대상">
        <option value="pp_id"<?php echo get_selected($_GET['sfl'], "pp_id"); ?>>개인결제번호</option>
        <option value="pp_name"<?php echo get_selected($_GET['sfl'], "pp_name"); ?>>이름</option>
        <option value="od_id"<?php echo get_selected($_GET['sfl'], "od_id"); ?>>주문번호</option>
    </select>
    <label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
    <input type="text" name="stx" value="<?php echo $stx ?>" id="stx" required class="required frm_input">
    <input type="submit" class="btn_submit" value="검색">
</fieldset>
</form>

<section id="spp_list" class="cbox">
    <h2>개인결제 내역</h2>

    <div class="btn_add sort_with">
        <a href="./personalpayform.php" id="personalpay_add">개인결제 추가</a>
    </div>

    <ul class="sort_odr">
        <li><?php echo subject_sort_link('pp_id') ?>개인결제번호<span class="sound_only"> 순 정렬</span></a></li>
        <li><?php echo subject_sort_link('od_id') ?>주문번호<span class="sound_only"> 순 정렬</span></a></li>
        <li><?php echo subject_sort_link('pp_receipt_time') ?>입금일<span class="sound_only"> 순 정렬</span></a></li>
    </ul>

    <form name="fpersonalpaylist" id="fpersonalpaylist" method="post" action="./personalpaylistdelete.php" onsubmit="return fpersonalpaylist_submit(this);">
    <input type="hidden" name="sst" value="<?php echo $sst; ?>">
    <input type="hidden" name="sod" value="<?php echo $sod; ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl; ?>">
    <input type="hidden" name="stx" value="<?php echo $stx; ?>">
    <input type="hidden" name="page" value="<?php echo $page; ?>">
    <input type="hidden" name="token" value="<?php echo $token; ?>">

    <table>
    <thead>
    <tr>
        <th scope="col">
            <label for="chkall" class="sound_only">개인결제 전체</label>
            <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
        </th>
        <th scope="col">이름</th>
        <th scope="col">주문번호</th>
        <th scope="col">주문금액</th>
        <th scope="col">입금금액</th>
        <th scope="col">미수금액</th>
        <th scope="col">입금방법</a></th>
        <th scope="col">입금일</a></th>
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
    ?>

    <tr>
        <td class="td_chk">
            <input type="hidden" id="pp_id_<?php echo $i; ?>" name="pp_id[<?php echo $i; ?>]" value="<?php echo $row['pp_id']; ?>">
            <input type="checkbox" id="chk_<?php echo $i; ?>" name="chk[]" value="<?php echo $i; ?>" title="내역선택">
        </td>
        <td class="spp_list_name"><?php echo $row['pp_name']; ?></td>
        <td class="td_odrnum3 spp_list_id"><?php echo $od_id; ?></td>
        <td class="td_bignum"><?php echo number_format($row['pp_amount']); ?></td>
        <td class="td_bignum"><?php echo number_format($row['pp_receipt_amount']); ?></td>
        <td class="td_bignum"><?php echo number_format($row['pp_amount'] - $row['pp_receipt_amount']); ?></td>
        <td class="td_payby"><?php echo $row['pp_settle_case']; ?></td>
        <td class="td_date"><?php echo is_null_time($row['pp_receipt_time']) ? '' : substr($row['pp_receipt_time'], 2, 8); ?></td>
        <td class="td_boolean"><?php echo $row['pp_use'] ? '예' : '아니오'; ?></td>
        <td class="td_smallmng">
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

    <div class="btn_list">
        <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value">
    </div>

    </form>
</section>

<?php echo get_paging(G4_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['PHP_SELF']}?$qstr&amp;page="); ?>

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
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>