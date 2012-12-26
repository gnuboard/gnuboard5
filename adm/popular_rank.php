<?
$sub_menu = "300400";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'r');

if (empty($fr_date)) $fr_date = $g4['time_ymd'];
if (empty($to_date)) $to_date = $g4['time_ymd'];

$qstr = "fr_date={$fr_date}{&to_date}={$to_date}";

$sql_common = " from {$g4['popular_table']} a ";
$sql_search = " where trim(pp_word) <> '' and pp_date between '{$fr_date}' and '{$to_date}' ";
$sql_group = " group by pp_word ";
$sql_order = " order by cnt desc ";

$sql = " select pp_word
            {$sql_common}
            {$sql_search}
            {$sql_group} ";
$result = sql_query($sql);
$total_count = mysql_num_rows($result);

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == '') { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " select pp_word, count(*) as cnt
            {$sql_common}
            {$sql_search}
            {$sql_group}
            {$sql_order}
            limit {$from_record}, {$rows} ";
$result = sql_query($sql);

if ($_GET['fr_date'] || $_GET['to_date']) $listall = '<a href="'.$_SERVER['PHP_SELF'].'">전체목록</a>';

$g4['title'] = '인기검색어순위';
include_once('./admin.head.php');

$colspan = 3;
?>

<form id="fsearch" name="fsearch" method="get">
<fieldset>
    <legend>인기검색어 검색</legend>
    <span>
        <?=$listall?>
        건수 <?=number_format($total_count)?>개
    </span>
    <label for="fr_date">기간설정</label>
    <input type="text" id="fr_date" name="fr_date" maxlength="10" value="<?=$fr_date?>" title="구간시작일"> 부터 <input type="text" id="to_date" name="to_date" maxlength="10" value="<?=$to_date?>" title="구간종료일"> 까지
    <input type="submit" class="fieldset_submit" value="검색">
</fieldset>
</form>

<form id="fpopularrank" name="fpopularrank" method="post">
<input type="hidden" name="sst" value="<?=$sst?>">
<input type="hidden" name="sod" value="<?=$sod?>">
<input type="hidden" name="sfl" value="<?=$sfl?>">
<input type="hidden" name="stx" value="<?=$stx?>">
<input type="hidden" name="page" value="<?=$page?>">
<input type="hidden" name="token" value="<?=$token?>">
<table class="tbl_pop_list">
<thead>
<tr>
    <th scope="col">순위</th>
    <th scope="col">검색어</th>
    <th scope="col">검색회수</th>
</tr>
</thead>
<tbody>
<?
for ($i=0; $row=sql_fetch_array($result); $i++) {

    $word = get_text($row['pp_word']);
    $rank = ($i + 1 + ($rows * ($page - 1)));

?>

<tr>
    <td class="td_num"><?=$rank?></td>
    <td><?=$word?></td>
    <td class="td_bignum"><?=$row['cnt']?></td>
</tr>

<?
}

if ($i == 0)
    echo '<tr><td colspan="'.$colspan.'" class="empty_table">자료가 없습니다.</td></tr>';
?>
</tbody>
</table>

<?
$pagelist = get_paging($config['cf_write_pages'], $page, $total_page, "$_SERVER['PHP_SELF']?$qstr&amp;page=");
?>
<div class="pg">
    <?=$pagelist?>
</div>

<?
if ($stx)
    echo "<script>document.fsearch.sfl.value = '$sfl';</script>";
?>
</form>

<?
include_once('./admin.tail.php');
?>
