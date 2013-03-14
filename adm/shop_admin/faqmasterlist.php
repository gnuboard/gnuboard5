<?
$sub_menu = "400710";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

$g4[title] = "FAQ관리";
include_once ("$g4[admin_path]/admin.head.php");

$sql_common = " from $g4[yc4_faq_master_table] ";

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row[cnt];

$rows = $config[cf_page_rows];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = "select * $sql_common order by fm_id desc limit $from_record, $config[cf_page_rows] ";
$result = sql_query($sql);
?>

<table width=100%>
<tr>
    <td width=20%><a href='<?=$_SERVER[PHP_SELF]?>'>처음</a></td>
    <td width=60% align=center>&nbsp;</td>
    <td width=20% align=right>건수 : <? echo $total_count ?>&nbsp;</td>
</tr>
</table>


<table cellpadding=0 cellspacing=0 width=100% border=0>
<colgroup width=80>
<colgroup width=''>
<colgroup width=80>
<colgroup width=80>
<colgroup width=80>
<tr><td colspan=5 height=2 bgcolor=#0E87F9></td></tr>
<tr align=center class=ht>
    <td>ID</td>
    <td>제목</td>
    <td>FAQ 수</td>
    <td>상세보기</td>
    <td><a href='./faqmasterform.php'><img src='<?=$g4[admin_path]?>/img/icon_insert.gif' border=0 title='등록'></a></td>
</tr>
<tr><td colspan=5 height=1 bgcolor=#CCCCCC></td></tr>
<?
for ($i=0; $row=mysql_fetch_array($result); $i++) 
{
    $sql1 = " select COUNT(*) as cnt from $g4[yc4_faq_table] where fm_id = '$row[fm_id]' ";
    $row1 = sql_fetch($sql1);
    $cnt = $row1[cnt];

    $s_detail_vie = icon("보기", "./faqlist.php?fm_id=$row[fm_id]");

    $s_mod = icon("수정", "./faqmasterform.php?w=u&fm_id=$row[fm_id]");
    $s_del = icon("삭제", "javascript:del('./faqmasterformupdate.php?w=d&fm_id=$row[fm_id]');");
    $s_vie = icon("보기", "$g4[shop_path]/faq.php?fm_id=$row[fm_id]");

    $list = $i%2;
    echo "
    <tr class='list$list ht'>
        <td align=center>$row[fm_id]</td>
        <td>" . stripslashes($row[fm_subject]) . "</td>
        <td align=center>$cnt</td>
        <td align=center>$s_detail_vie</td>
        <td align=center>$s_mod $s_del $s_vie</td>
    </tr>";
}

if ($i == 0)
    echo "<tr><td colspan=5 align=center height=100 bgcolor=#ffffff><span class=point>자료가 한건도 없습니다.</span></td></tr>\n";
?>
<tr><td colspan=5 height=1 bgcolor=CCCCCC></td></tr>
</table>

<table width=100%>
<tr bgcolor=#ffffff>
    <td width=50%></td>
    <td width=50% align=right><?=get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr&page=");?></td>
</tr>
</table><br>


<table width=100% cellpadding=5 cellspacing=0 border=0 bgcolor=#F6F6F6>
<tr>
    <td>
        <table width=100% cellpadding=10 cellspacing=0 bgcolor=#FFFFFF>
        <tr>
            <td style="line-height:220%;">
                <B><FONT COLOR="18ABFF">&middot; FAQ 등록하기</FONT></B><BR>

                : FAQ는 무제한으로 등록할 수 있습니다.<BR>
                1. 먼저 <img src='<?=$g4[admin_path]?>/img/icon_insert.gif' align=absmiddle>를 눌러 FAQ Master를 생성합니다. (하나의 FAQ 타이틀 생성 : 자주하시는 질문, 이용안내..등 )<BR>
                2. 상세보기에 있는 <img src='<?=$g4[admin_path]?>/img/icon_viewer.gif' align=absmiddle>을 눌러 세부 내용으로 들어갑니다.
            </td>
        </tr>
        </table>
    </td>
</tr>
</table>

<?
include_once ("$g4[admin_path]/admin.tail.php");
?>
