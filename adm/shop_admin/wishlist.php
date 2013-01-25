<?
$sub_menu = "500140";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

$g4[title] = "보관함현황";
include_once ("$g4[admin_path]/admin.head.php");

if (!$to_date) $to_date = date("Ymd", time());

if ($sort1 == "") $sort1 = "it_id_cnt";
if ($sort2 == "") $sort2 = "desc";

$sql  = " select a.it_id, 
                 b.it_name,
                 COUNT(a.it_id) as it_id_cnt
            from $g4[yc4_wish_table] a, $g4[yc4_item_table] b ";
$sql .= " where a.it_id = b.it_id ";
if ($fr_date && $to_date) 
{
    $fr = preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})/", "\\1-\\2-\\3", $fr_date);
    $to = preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})/", "\\1-\\2-\\3", $to_date);
    $sql .= " and a.wi_time between '$fr 00:00:00' and '$to 23:59:59' ";
}
if ($sel_ca_id)
{
    $sql .= " and b.ca_id like '$sel_ca_id%' ";
}
$sql .= " group by a.it_id, b.it_name
          order by $sort1 $sort2 ";
$result = sql_query($sql);
$total_count = mysql_num_rows($result);

$rows = $config[cf_page_rows];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$rank = ($page - 1) * $rows;

$sql = $sql . " limit $from_record, $rows ";
$result = sql_query($sql);

$qstr = "page=$page&sort1=$sort1&sort2=$sort2";
$qstr1 = "fr_date=$fr_date&to_date=$to_date&sel_ca_id=$sel_ca_id";
?>

<table width=100% cellpadding=4 cellspacing=0>
<form name=flist>
<input type=hidden name=doc   value="<? echo $doc ?>">
<input type=hidden name=sort1 value="<? echo $sort1 ?>">
<input type=hidden name=sort2 value="<? echo $sort2 ?>">
<input type=hidden name=page  value="<? echo $page ?>">
<tr>
    <td width=10%><a href='<?=$_SERVER[PHP_SELF]?>'>처음</a></td>
    <td width=80% align=center>
        <select name="sel_ca_id">
            <option value=''>전체분류
            <?
            $sql1 = " select ca_id, ca_name from $g4[yc4_category_table] order by ca_id ";
            $result1 = sql_query($sql1);
            for ($i=0; $row1=mysql_fetch_array($result1); $i++) {
                $len = strlen($row1[ca_id]) / 2 - 1;
                $nbsp = "";
                for ($i=0; $i<$len; $i++) $nbsp .= "&nbsp;&nbsp;&nbsp;";
                echo "<option value='$row1[ca_id]'>$nbsp$row1[ca_name]\n";
            }
            ?>
        </select>
        <script> document.flist.sel_ca_id.value = '<?=$sel_ca_id?>';</script>

        기간 : <input type=text name=fr_date size=8 maxlength=8 itemname='기간' value='<?=$fr_date?>'> ~ <input type=text name=to_date size=8 maxlength=8 itemname='기간' value='<?=$to_date?>'>
        <input type=image src='<?=$g4[admin_path]?>/img/btn_search.gif' align=absmiddle>
    </td>
    <td width=10% align=right>건수 : <? echo $total_count ?>&nbsp;</td>
</tr>
</table>

<table cellpadding=0 cellspacing=0 width=100%>
<tr><td colspan=20 height=3 bgcolor=#0E87F9></td></tr>
<tr align=center class=ht>
    <td width=50>순위</td>
    <td width=80></td>
    <td width=''>상품명</td>
    <td width=50>건수</td>
</tr>
<tr><td colspan=20 height=1 bgcolor=#CCCCCC></td></tr>
<?
for ($i=0; $row=mysql_fetch_array($result); $i++) 
{
    $s_mod = icon("수정", "./itemqaform.php?w=u&iq_id=$row[iq_id]&$qstr");
    $s_del = icon("삭제", "javascript:del('./itemqaupdate.php?w=d&iq_id=$row[iq_id]&$qstr');");

    $href = "$g4[shop_path]/item.php?it_id=$row[it_id]";

    $num = $rank + $i + 1;

    $list = $i%2;
    echo "
    <tr class='list$list center'>
        <td>$num</td>
        <td style='padding-top:5px; padding-bottom:5px;'><a href='$href'>".get_it_image("{$row[it_id]}_s", 50, 50)."</a></td>
        <td align=left><a href='$href'>".cut_str($row[it_name],30)."</a></td>
        <td>$row[it_id_cnt]</td>
    </tr><tr><td colspan=20 height=1 bgcolor=F5F5F5></td></tr>";
}                         

if ($i == 0) {
    echo "<tr><td colspan=20 align=center height=100 bgcolor=#ffffff><span class=point>자료가 한건도 없습니다.</span></td></tr>\n";
}
?>
<tr><td colspan=20 height=1 bgcolor=CCCCCC></td></tr>
</table>


<table width=100%>
<tr>
    <td width=50%>&nbsp;</td>
    <td width=50% align=right><?=get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr&page=");?></td>
</tr>
</table>

* 수량을 합산하여 순위를 출력합니다.

<?
include_once ("$g4[admin_path]/admin.tail.php");
?>
