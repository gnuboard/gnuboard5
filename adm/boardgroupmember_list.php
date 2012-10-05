<?
$sub_menu = "300200";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

$gr = get_group($gr_id);
if (!$gr[gr_id]) {
    alert("존재하지 않는 그룹입니다."); 
}

$sql_common = " from $g4[group_member_table] a 
                left outer join $g4[member_table] b on (a.mb_id = b.mb_id) ";

$sql_search = " where gr_id = '$gr_id' ";
// 회원아이디로 검색되지 않던 오류를 수정
if ($stx) {
    $sql_search .= " and ( ";
    switch ($sfl) {
        default : 
            $sql_search .= " ($sfl like '%$stx%') ";
            break;
    }
    $sql_search .= " ) ";
}

if (!$sst) {
    $sst  = "gm_datetime";
    $sod = "desc";
}
$sql_order = " order by $sst $sod ";

$sql = " select count(*) as cnt
         $sql_common 
         $sql_search 
         $sql_order ";
$row = sql_fetch($sql);
$total_count = $row[cnt];

$rows = $config[cf_page_rows];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " select * 
          $sql_common
          $sql_search
          $sql_order
          limit $from_record, $rows ";
$result = sql_query($sql);

$g4[title] = "접근가능회원";
include_once("./admin.head.php");

$colspan = 7;
?>

<script type="text/javascript" src="<?=$g4[path]?>/js/sideview.js"></script>

<table width=100% cellpadding=3 cellspacing=1>
<form name=fsearch method=get>
<input type=hidden name=gr_id value='<?=$gr_id?>'>
<tr>
    <td width=50% align=left>* <? echo "'<b>[$gr[gr_id]] $gr[gr_subject]</b>' 그룹의 접근가능한 회원 목록"; ?></td>
    <td width=50% align=right>
        <select name=sfl class=cssfl>
            <option value='a.mb_id'>회원아이디</option>
        </select>
        <input type=text name=stx required itemname='검색어' value='<? echo $stx ?>'>
        <input type=image src='<?=$g4[admin_path]?>/img/btn_search.gif' align=absmiddle></td>
</tr>
</form>
</table>

<table width=100% cellpadding=0 cellspacing=0>
<colgroup width=120>
<colgroup width=120>
<colgroup width=120>
<colgroup width=120>
<colgroup width=''>
<colgroup width=100>
<colgroup width=40>
<tr><td colspan='<?=$colspan?>' class='line1'></td></tr>
<tr class='bgcol1 bold col1 ht center'>
    <td><?=subject_sort_link('b.mb_id', "gr_id=$gr_id")?>회원아이디</a></td>
    <td><?=subject_sort_link('b.mb_name', "gr_id=$gr_id")?>이름</a></td>
    <td><?=subject_sort_link('b.mb_nick', "gr_id=$gr_id")?>별명</a></td>
    <td><?=subject_sort_link('b.mb_today_login', "gr_id=$gr_id")?>최종접속</a></td>
    <td><?=subject_sort_link('a.gm_datetime', "gr_id=$gr_id")?>처리일시</a></td>
    <td title='접근가능한 그룹수'>그룹</td>
    <td>삭제</td>
</tr>
<tr><td colspan='<?=$colspan?>' class='line2'></td></tr>

<?
for ($i=0; $row=sql_fetch_array($result); $i++) 
{
    // 접근가능한 그룹수
    $sql2 = " select count(*) as cnt from $g4[group_member_table] where mb_id = '$row[mb_id]' ";
    $row2 = sql_fetch($sql2);
    $group = "";
    if ($row2[cnt])
        $group = "<a href='./boardgroupmember_form.php?mb_id=$row[mb_id]'>$row2[cnt]</a>";

    //$s_del = "<a href=\"javascript:del('./boardgroupmember_update.php?w=listdelete&gm_id=$row[gm_id]');\"><img src='img/icon_delete.gif' border=0 title='삭제'></a>";
    $s_del = "<a href=\"javascript:post_delete('boardgroupmember_update.php', '$row[gm_id]');\"><img src='img/icon_delete.gif' border=0 title='삭제'></a>";

    $mb_nick = get_sideview($row[mb_id], $row[mb_nick], $row[mb_email], $row[mb_homepage]);

    $list = $i%2;
    echo "
    <tr class='list$list col1 ht center'>
        <td>$row[mb_id]</td>
        <td>$row[mb_name]</td>
        <td>$mb_nick</td>
        <td>".substr($row[mb_today_login],2,8)."</td>
        <td>$row[gm_datetime]</td>
        <td>$group</td>
        <td>$s_del</td>
    </tr> ";
} 

if ($i == 0)
{
    echo "<tr><td colspan='$colspan' align=center height=100 class='content contentbg'>자료가 없습니다.</td></tr>";
}

echo "<tr><td colspan='$colspan' class='line2'></td></tr>";
echo "</table>";

$pagelist = get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr&gr_id=$gr_id&page=");
if ($pagelist) 
    echo "<table width=100% cellpadding=3 cellspacing=1><tr><td align=right>$pagelist</td></tr></table>\n";

if ($stx) 
    echo "<script type='text/javascript'>document.fsearch.sfl.value = '$sfl';</script>\n";
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

<form name='fpost' method='post'>
<input type='hidden' name='sst'   value='<?=$sst?>'>
<input type='hidden' name='sod'   value='<?=$sod?>'>
<input type='hidden' name='sfl'   value='<?=$sfl?>'>
<input type='hidden' name='stx'   value='<?=$stx?>'>
<input type='hidden' name='page'  value='<?=$page?>'>
<input type='hidden' name='token' value='<?=$token?>'>
<input type='hidden' name='w'     value='listdelete'>
<input type='hidden' name='gm_id'>
</form>

<?
include_once("./admin.tail.php");
?>
