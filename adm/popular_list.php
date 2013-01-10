<?
$sub_menu = "300300";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'r');

// 체크된 자료 삭제
if (isset($_POST['chk']) && is_array($_POST['chk'])) {
    for ($i=0; $i<count($chk); $i++) {
        // 실제 번호를 넘김
        $k = $chk[$i];

        sql_query(" delete from {$g4['popular_table']} where pp_id = '{$_POST['pp_id'][$k]}' ", true);
    }
}

$sql_common = " from {$g4['popular_table']} a ";
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
if ($page == '') { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " select *
            {$sql_common}
            {$sql_search}
            {$sql_order}
            limit {$from_record}, {$rows} ";
$result = sql_query($sql);

if (isset($stx))
    $listall = '<a href="'.$_SERVER['PHP_SELF'].'">전체목록</a>';

$g4['title'] = '인기검색어관리';
include_once('./admin.head.php');

$colspan = 4;
?>

<script>
var list_update_php = '';
var list_delete_php = 'popular_list.php';
</script>

<form id="fsearch" name="fsearch" method="get">
<fieldset>
    <legend>인기검색어 검색</legend>
    <span>
        <?=$listall?>
        건수 : <?=number_format($total_count)?>개
    </span>
    <label for="sfl">검색대상</label>
    <select id="sfl" name="sfl">
        <option value="pp_word">검색어</option>
        <option value="pp_date">등록일</option>
    </select>
    <input type="text" name="stx" required value="<?=$stx?>" title="검색어">
    <input type="submit" class="fieldset_submit" value="검색">
</fieldset>
</form>

<form id="fpopularlist" name="fpopularlist" method="post">
<input type="hidden" name="sst" value="<?=$sst?>">
<input type="hidden" name="sod" value="<?=$sod?>">
<input type="hidden" name="sfl" value="<?=$sfl?>">
<input type="hidden" name="stx" value="<?=$stx?>">
<input type="hidden" name="page" value="<?=$page?>">
<input type="hidden" name="token" value="<?=$token?>">
<table class="tbl_pop_list">
<thead>
<tr>
    <th scope="col"><input type="checkbox" id="chkall" name="chkall" value="1" onclick="check_all(this.form)" title="현재 페이지 인기검색어 전체선택"></th>
    <th scope="col"><?=subject_sort_link('pp_word')?>검색어</a></th>
    <th scope="col">등록일</th>
    <th scope="col">등록IP</th>
</tr>
</thead>
<tbody>
<?
for ($i=0; $row=sql_fetch_array($result); $i++) {

    $word = get_text($row['pp_word']);
?>

<tr>
    <td class="td_chk">
        <input type="hidden" name="pp_id[<?=$i?>]" value="<?=$row['pp_id']?>">
        <input type="checkbox" id="chk_<?=$i?>" name="chk[]" value="<?=$i?>" title="<?=$word?> 선택">
    </td>
    <td>&nbsp; <a href="<?=$_SERVER['PHP_SELF']?>?sfl=pp_word&amp;stx=<?=$word?>"><?=$word?></a></td>
    <td><?=$row['pp_date']?></td>
    <td><?=$row['pp_ip']?></td>
</tr>

<?
}

if ($i == 0)
    echo '<tr><td colspan="'.$colspan.'" class="empty_table">자료가 없습니다.</td></tr>';
?>
</tbody>
</table>

<?if ($is_admin == 'super'){ ?>
<div class="btn_list">
    <button onclick="btn_check(this.form, 'delete')">선택삭제</button>
</div>
<?}?>

<?
$pagelist = get_paging($config['cf_write_pages'], $page, $total_page, "{$_SERVER['PHP_SELF']}?$qstr&amp;page=");
?>
<div class="pg">
    <?=$pagelist?>
</div>
</form>

<?
if (isset($stx))
    echo '<script>document.fsearch.sfl.value = \''.$sfl.'\';</script>';
?>

<?
include_once('./admin.tail.php');
?>
