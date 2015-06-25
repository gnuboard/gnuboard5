<?php
$sub_menu = "300300";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'r');

// 체크된 자료 삭제
if (isset($_POST['chk']) && is_array($_POST['chk'])) {
    for ($i=0; $i<count($_POST['chk']); $i++) {
        $pp_id = $_POST['chk'][$i];

        sql_query(" delete from {$g5['popular_table']} where pp_id = '$pp_id' ", true);
    }
}

$sql_common = " from {$g5['popular_table']} a ";
$sql_search = " where (1) ";

if ($stx) {
    $sql_search .= " and ( ";
    switch ($sfl) {
        case "pp_word" :
            $sql_search .= " ({$sfl} like '{$stx}%') ";
            break;
        case "pp_date" :
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
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " select *
            {$sql_common}
            {$sql_search}
            {$sql_order}
            limit {$from_record}, {$rows} ";
$result = sql_query($sql);

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

$g5['title'] = '인기검색어관리';
include_once('./admin.head.php');

$colspan = 4;
?>

<script>
var list_update_php = '';
var list_delete_php = 'popular_list.php';
</script>

<div class="local_ov01 local_ov">
        <?php echo $listall ?>
        건수 : <?php echo number_format($total_count) ?>개
</div>

<form name="fsearch" id="fsearch" class="local_sch01 local_sch" method="get">
<div class="sch_last">
    <label for="sfl" class="sound_only">검색대상</label>
    <select name="sfl" id="sfl">
        <option value="pp_word"<?php echo get_selected($_GET['sfl'], "pp_word"); ?>>검색어</option>
        <option value="pp_date"<?php echo get_selected($_GET['sfl'], "pp_date"); ?>>등록일</option>
    </select>
    <label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
    <input type="text" name="stx" value="<?php echo $stx ?>" id="stx" required class="required frm_input">
    <input type="submit" value="검색" class="btn_submit">
</div>
</form>

<form name="fpopularlist" id="fpopularlist" method="post">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="<?php echo $token ?>">

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?> 목록</caption>
    <thead>
    <tr>
        <th scope="col">
            <label for="chkall" class="sound_only">현재 페이지 인기검색어 전체</label>
            <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
        </th>
        <th scope="col"><?php echo subject_sort_link('pp_word') ?>검색어</a></th>
        <th scope="col">등록일</th>
        <th scope="col">등록IP</th>
    </tr>
    </thead>
    <tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++) {

        $word = get_text($row['pp_word']);
        $bg = 'bg'.($i%2);
    ?>

    <tr class="<?php echo $bg; ?>">
        <td class="td_chk">
            <label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo $word ?></label>
            <input type="checkbox" name="chk[]" value="<?php echo $row['pp_id'] ?>" id="chk_<?php echo $i ?>">
        </td>
        <td><a href="<?php echo $_SERVER['SCRIPT_NAME'] ?>?sfl=pp_word&amp;stx=<?php echo $word ?>"><?php echo $word ?></a></td>
        <td><?php echo $row['pp_date'] ?></td>
        <td><?php echo $row['pp_ip'] ?></td>
    </tr>

    <?php
    }

    if ($i == 0)
        echo '<tr><td colspan="'.$colspan.'" class="empty_table">자료가 없습니다.</td></tr>';
    ?>
    </tbody>
    </table>

</div>

<?php if ($is_admin == 'super'){ ?>
<div class="btn_list01 btn_list">
    <button type="submit">선택삭제</button>
</div>
<?php } ?>

</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}?$qstr&amp;page="); ?>

<script>
$(function() {
    $('#fpopularlist').submit(function() {
        if(confirm("한번 삭제한 자료는 복구할 방법이 없습니다.\n\n정말 삭제하시겠습니까?")) {
            if (!is_checked("chk[]")) {
                alert("선택삭제 하실 항목을 하나 이상 선택하세요.");
                return false;
            }

            return true;
        } else {
            return false;
        }
    });
});
</script>

<?php
include_once('./admin.tail.php');
?>
