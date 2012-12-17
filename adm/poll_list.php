<?
$sub_menu = "200900";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'r');

$token = get_token();

$sql_common = " from {$g4['poll_table']} ";

$sql_search = " where (1) ";
if ($stx) {
    $sql_search .= " and ( ";
    switch ($sfl) {
        default :
            $sql_search .= " ({$sfl} like '%{$stx}%') ";
            break;
    }
    $sql_search .= " ) ";
}

if (!$sst) {
    $sst  = "po_id";
    $sod = "desc";
}
$sql_order = " order by {$sst} {$sod} ";

$sql = " select count(*) as cnt
            {$sql_common}
            {$sql_search}
            {$sql_order} ";
$row = sql_fetch($sql);
$total_count = $row[cnt];

$rows = $config[cf_page_rows];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == '') $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " select *
            {$sql_common}
            {$sql_search}
            {$sql_order}
            limit {$from_record}, {$rows} ";
$result = sql_query($sql);

if ($sfl || $stx) // 검색렬일 때만 처음 버튼을 보여줌
    $listall = '<a href="'.$_SERVER['PHP_SELF'].'">전체목록</a>';

$g4['title'] = '투표관리';
include_once('./admin.head.php');

$colspan = 6;
?>

<form id="fsearch" name="fsearch" method="get">
<fieldset>
    <legend>투표검색</legend>
    <span>
        <?=$listall?>
        투표수 : <?=number_format($total_count)?>개
    </span>
    <label for="sfl">검색대상</label>
    <select id="sfl" name="sfl">
        <option value='po_subject'>제목</option>
    </select>
    <input type="text" name="stx" required value="<?=$stx?>" title="검색어">
    <input type="submit" class="fieldset_submit" value="검색">
</fieldset>
</form>

<div id="btn_add">
    <a href="./poll_form.php" id="poll_add">투표 추가</a>
</div>

<table>
<caption>투표목록</caption>
<thead>
<tr>
    <th scope="col">번호</th>
    <th scope="col">제목</th>
    <th scope="col">투표권한</th>
    <th scope="col">투표수</th>
    <th scope="col">기타의견</th>
    <th scope="col">관리</th>
</tr>
</thead>
<tbody>
<?
for ($i=0; $row=sql_fetch_array($result); $i++) {
    $sql2 = " select sum(po_cnt1+po_cnt2+po_cnt3+po_cnt4+po_cnt5+po_cnt6+po_cnt7+po_cnt8+po_cnt9) as sum_po_cnt from {$g4['poll_table']} where po_id = '{$row[po_id]}' ";
    $row2 = sql_fetch($sql2);
    $po_etc = ($row['po_etc']) ? "사용" : "미사용";

    $s_mod = '<a href="./poll_form.php?'.$qstr.'&amp;w=u&amp;po_id='.$row[po_id].'">수정</a>';
    $s_del = '<a href="javascript:post_delete(\'poll_form_update.php\', \''.$row[po_id].'\');">삭제</a>';
?>

<tr>
    <td class="td_num"><?=$row[po_id]?></td>
    <td><?=cut_str(get_text($row['po_subject']),70)?></td>
    <td class="td_num"><?=$row[po_level]?></td>
    <td class="td_num"><?=$row2[sum_po_cnt]?></td>
    <td class="td_etc"><?=$po_etc?></td>
    <td class="td_mng"><?=$s_mod?> <?=$s_del?></td>
</tr>

<?
}

if ($i==0)
    echo '<tr><td colspan="'.$colspan.'" class="empty_table">자료가 없습니다.</td></tr>';
?>
</tbody>
</table>

<?
$pagelist = get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr&page=");
if ($pagelist) {?>
<div class="pg">
    <?=$pagelist?>
</div>
<?}?>

<?
if ($stx)
    echo '<script>document.fsearch.sfl.value = \''.$sfl.'\';</script>'.PHP_EOL;
?>

<script>
// POST 방식으로 삭제
function post_delete(action_url, val)
{
	var f = document.fpost;

	if(confirm("한번 삭제한 자료는 복구할 방법이 없습니다.\n\n정말 삭제하시겠습니까?")) {
        f.po_id.value = val;
		f.action      = action_url;
		f.submit();
	}
}
</script>

<form id="fpost" name="fpost" method="post">
<input type="hidden" name="sst" value="<?=$sst?>">
<input type="hidden" name="sod" value="<?=$sod?>">
<input type="hidden" name="sfl" value="<?=$sfl?>">
<input type="hidden" name="stx" value="<?=$stx?>">
<input type="hidden" name="page" value="<?=$page?>">
<input type="hidden" name="token" value="<?=$token?>">
<input type="hidden" name="w" value='d'>
<input type="hidden" name="po_id">
</form>

<?
include_once ('./admin.tail.php');
?>