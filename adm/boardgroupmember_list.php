<?
$sub_menu = "300200";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'r');

$gr = get_group($gr_id);
if (!$gr['gr_id']) {
    alert('존재하지 않는 그룹입니다.');
}

$sql_common = " from {$g4['group_member_table']} a
                         left outer join {$g4['member_table']} b on (a.mb_id = b.mb_id) ";

$sql_search = " where gr_id = '{$gr_id}' ";
// 회원아이디로 검색되지 않던 오류를 수정
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
    $sst  = "gm_datetime";
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
if ($page == "") $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " select *
            {$sql_common}
            {$sql_search}
            {$sql_order}
            limit {$from_record}, {$rows} ";
$result = sql_query($sql);

$g4['title'] = $gr['gr_subject'].' 그룹 접근가능회원';
include_once('./admin.head.php');

$colspan = 7;
?>

<script src="<?=$g4['path']?>/js/sideview.js"></script>

<form id="fsearch" name="fsearch" method="get">
<input type="hidden" id="gr_id" name="gr_id" value="<?=$gr_id?>">
<fieldset>
    <legend><?=$gr['gr_subject']?> 그룹에서 검색 (그룹아이디:<?=$gr['gr_id']?>)</legend>
    <select id="sfl" name="sfl">
        <option value='a.mb_id'>회원아이디</option>
    </select>
    <input type="text" id="stx" name="stx" required value="<? echo $stx ?>">
    <input type="submit" value="검색">
</fieldset>
</form>

<table>
<caption><?=$gr['gr_subject']?> 그룹에 접근가능한 회원 목록 (그룹아이디:<?=$gr['gr_id']?>)</caption>
<thead>
<tr>
    <th scope="col" id="th_mb_id"><?=subject_sort_link('b.mb_id', 'gr_id='.$gr_id)?>회원아이디</a></th>
    <th scope="col" id="th_mb_name"><?=subject_sort_link('b.mb_name', 'gr_id='.$gr_id)?>이름</a></th>
    <th scope="col" id="th_mb_nick"><?=subject_sort_link('b.mb_nick', 'gr_id='.$gr_id)?>별명</a></th>
    <th scope="col" id="th_mb_last"><?=subject_sort_link('b.mb_today_login', 'gr_id='.$gr_id)?>최종접속</a></th>
    <th scope="col" id="th_datetime"><?=subject_sort_link('a.gm_datetime', 'gr_id='.$gr_id)?>처리일시</a></th>
    <th scope="col" id="th_group">그룹</th>
    <th scope="col" id="th_del">삭제</th>
</tr>
</thead>
<tbody>
<?
for ($i=0; $row=sql_fetch_array($result); $i++)
{
    // 접근가능한 그룹수
    $sql2 = " select count(*) as cnt from {$g4['group_member_table']} where mb_id = '{$row['mb_id']}' ";
    $row2 = sql_fetch($sql2);
    $group = "";
    if ($row2['cnt'])
        $group = '<a href="./boardgroupmember_form.php?mb_id='.$row['mb_id'].'">'.$row2['cnt'].'</a>';

    $s_del = '<a href="javascript:post_delete(\'boardgroupmember_update.php\', \''.$row['gm_id'].'\');">삭제</a>';

    $mb_nick = get_sideview($row['mb_id'], $row['mb_nick'], $row['mb_email'], $row['mb_homepage']);
?>
<tr>
    <td headers="th_mb_id"><?=$row['mb_id']?></td>
    <td headers="th_mb_name"><?=$row['mb_name']?></td>
    <td headers="th_mb_nick"><?=$mb_nick?></td>
    <td headers="th_mb_last"><?=substr($row['mb_today_login'],2,8)?></td>
    <td headers="th_datetime"><?=$row['gm_datetime']?></td>
    <td headers="th_group"><?=$group?></td>
    <td headers="th_del"><?=$s_del?></td>
</tr>
<?
}

if ($i == 0)
{
    echo '<tr><td colspan="'.$colspan.'" class="empty_table">자료가 없습니다.</td></tr>';
}
?>
</tbody>
</table>

<?
$pagelist = get_paging($config['cf_write_pages'], $page, $total_page, "{$_SERVER['PHP_SELF']}?$qstr&amp;gr_id=$gr_id&page=");
if ($pagelist) {?>
<div class="paginate">
    <?=$pagelist?>
</div>
<?}?>

<?
if ($stx)
    echo '<script>document.fsearch.sfl.value = "'.$sfl.'";</script>';
?>

<script>
// POST 방식으로 삭제
function post_delete(action_url, val)
{
    var f = document.fpost;

    if(confirm("한번 삭제한 자료는 복구할 방법이 없습니다.\n\n정말 삭제하시겠습니까?")) {
        f.gm_id.value = val;
        f.action      = action_url;
        f.submit();
    }
}
</script>

<form id="fpost" name="fpost" method="post">
<input type="hidden" id="sst" name="sst" value="<?=$sst?>">
<input type="hidden" id="sod" name="sod" value="<?=$sod?>">
<input type="hidden" id="sfl" name="sfl" value="<?=$sfl?>">
<input type="hidden" id="stx" name="stx" value="<?=$stx?>">
<input type="hidden" id="page" name="page" value="<?=$page?>">
<input type="hidden" id="token" name="token" value="<?=$token?>">
<input type="hidden" id="w" name="w" value="listdelete">
<input type="hidden" id="gm_id" name="gm_id">
</form>

<?
include_once('./admin.tail.php');
?>
