<?
$sub_menu = "400730";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

$g4[title] = "배너관리";
include_once ("$g4[admin_path]/admin.head.php");

$sql_common = " from $g4[yc4_banner_table] ";

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row[cnt];

$rows = $config[cf_page_rows];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함
?>

<table width=100% height=30>
<tr>
    <td width=60%><?=subtitle($g4[title])?></td>
    <td width=20% align=right>건수 : <? echo $total_count ?>&nbsp;</td>
</tr>
</table>


<table cellpadding=0 cellspacing=0 width=100%>
<colgroup width=25>
<colgroup width=''>
<colgroup width=50>
<colgroup width=90>
<colgroup width=90>
<colgroup width=50>
<colgroup width=40>
<colgroup width=80>
<tr><td colspan=8 height=2 bgcolor=#0E87F9></td></tr>
<tr align=center class=ht>
    <td>ID</td>
    <td>이미지</td>
    <td>위치</td>
    <td>시작일시</td>
    <td>종료일시</td>
    <td>출력순서</td>
    <td>조회</td>
    <td><?=icon("입력", "./bannerform.php");?></td>
</tr>
<tr><td colspan=8 height=1 bgcolor=#CCCCCC></td></tr>
<?
$sql = " select * from $g4[yc4_banner_table] 
          order by bn_order, bn_id desc 
          limit $from_record, $rows  ";
$result = sql_query($sql);
for ($i=0; $row=mysql_fetch_array($result); $i++)
{
    // 테두리 있는지
    $bn_border  = $row[bn_border];
    // 새창 띄우기인지
    $bn_new_win = ($row[bn_new_win]) ? "target='_new'" : "";

    $bn_img = "";
    if ($row[bn_url] && $row[bn_url] != "http://")
        $bn_img .= "<a href='$row[bn_url]' $bn_new_win>";
    $bn_img .= "<img src='$g4[path]/data/banner/$row[bn_id]' border='$bn_border' alt='$row[bn_alt]'></a>";

    $bn_begin_time = substr($row[bn_begin_time], 2, 14);
    $bn_end_time   = substr($row[bn_end_time], 2, 14);

    $s_mod = icon("수정", "./bannerform.php?w=u&bn_id=$row[bn_id]");
    $s_del = icon("삭제", "javascript:del('./bannerformupdate.php?w=d&bn_id=$row[bn_id]');");

    $list = $i%2;
    echo "
    <tr class='list$list center'>
        <td>$row[bn_id]</td>
        <td align=left style='padding-top:5px; padding-bottom:5px;'>$bn_img</td>
        <td>$row[bn_position]</td>
        <td>$bn_begin_time</td>
        <td>$bn_end_time</td>
        <td>$row[bn_order]</td>
        <td>$row[bn_hit]</td>
        <td>$s_mod  $s_del</td>
    </tr><tr><td colspan=8 height=1 bgcolor=F5F5F5></td></tr>";
}

if ($i == 0) {
    echo "<tr><td colspan=8 align=center height=100 bgcolor=#ffffff><span class=point>자료가 한건도 없습니다.</span></td></tr>\n";
}
?>
<tr><td colspan=8 height=1 bgcolor=#CCCCCC></td></tr>
</table>

<table width=100%>
<tr>
    <td width=50%></td>
    <td width=50% align=right><?=get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr&page=");?></td>
</tr>
</table>

<?
include_once ("$g4[admin_path]/admin.tail.php");
?>
