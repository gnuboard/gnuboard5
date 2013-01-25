<?
$sub_menu = "400650";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

$g4[title] = "사용후기";
include_once ("$g4[admin_path]/admin.head.php");

$where = " where ";
$sql_search = "";
if ($stx != "") {
    if ($sfl != "") {
        $sql_search .= " $where $sfl like '%$stx%' ";
        $where = " and ";
    }
    if ($save_stx != $stx)
        $page = 1;
}

if ($sca != "") {
    $sql_search .= " and ca_id like '$sca%' ";
}

if ($sfl == "")  $sfl = "a.it_name";
if (!$sst) {
    $sst = "is_id";
    $sod = "desc";
}

$sql_common = "  from $g4[yc4_item_ps_table] a
                 left join $g4[yc4_item_table] b on (a.it_id = b.it_id)
                 left join $g4[member_table] c on (a.mb_id = c.mb_id) ";
$sql_common .= $sql_search;

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row[cnt];

$rows = $config[cf_page_rows];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql  = " select *
          $sql_common
          order by $sst $sod, is_id desc
          limit $from_record, $rows ";
$result = sql_query($sql);

//$qstr = "page=$page&sst=$sst&sod=$sod&stx=$stx";
$qstr  = "$qstr&sca=$sca&save_stx=$stx";
?>

<form name=flist style="margin:0px;">
<table width=100% cellpadding=4 cellspacing=0>
<input type=hidden name=page value="<?=$page?>">
<tr>
    <td width=10%><a href='<?=$_SERVER[PHP_SELF]?>'>처음</a></td>
    <td width=80% align=center>
        <select name="sca">
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
        <script> document.flist.sca.value = '<?=$sca?>';</script>

        <select name=sfl>
            <option value='it_name'>상품명
            <option value='a.it_id'>상품코드
            <option value='is_name'>이름
        </select>
        <? if ($sfl) echo "<script> document.flist.sfl.value = '$sfl';</script>"; ?>

        <input type=hidden name=save_stx value='<?=$stx?>'>
        <input type=text name=stx value='<?=$stx?>'>
        <input type=image src='<?=$g4[admin_path]?>/img/btn_search.gif' align=absmiddle>
    </td>
    <td width=10% align=right>건수 : <? echo $total_count ?>&nbsp;</td>
</tr>
</table>
</form>

<table cellpadding=0 cellspacing=0 width=100% border=0>
<colgroup width=80>
<colgroup width=''>
<colgroup width=80>
<colgroup width=200>
<colgroup width=40>
<colgroup width=40>
<colgroup width=80>
<tr><td colspan=7 height=2 bgcolor=#0E87F9></td></tr>
<tr align=center class=ht>
    <td></td>
    <td><?=subject_sort_link("it_name"); ?>상품명</a></td>
    <td><?=subject_sort_link("mb_name"); ?>이름</a></td>
    <td><?=subject_sort_link("is_subject"); ?>제목</a></td>
    <td><?=subject_sort_link("is_score"); ?>점수</a></td>
    <td><?=subject_sort_link("is_confirm"); ?>확인</a></td>
    <td>수정 삭제</td>
</tr>
<tr><td colspan=7 height=1 bgcolor=#CCCCCC></td></tr>
<?
for ($i=0; $row=sql_fetch_array($result); $i++)
{
    $row[is_subject] = cut_str($row[is_subject], 30, "...");

    $href = "$g4[shop_path]/item.php?it_id=$row[it_id]";

    $name = get_sideview($row[mb_id], get_text($row[is_name]), $row[mb_email], $row[mb_homepage]);

    $s_mod = icon("수정", "./itempsform.php?w=u&is_id=$row[is_id]&$qstr");
    $s_del = icon("삭제", "javascript:del('./itempsformupdate.php?w=d&is_id=$row[is_id]&$qstr');");

    $confirm = $row[is_confirm] ? "Y" : "&nbsp;";

    $list = $i%2;
    echo "
    <tr class='list$list'>
        <td style='padding-top:5px; padding-bottom:5px;'><a href='$href'>".get_it_image("{$row[it_id]}_s", 50, 50)."</a></td>
        <td><a href='$href'>".cut_str($row[it_name],30)."</a></td>
        <td align=center>$name</td>
        <td>$row[is_subject]</td>
        <td align=center>$row[is_score]</td>
        <td align=center>$confirm</td>
        <td align=center>$s_mod $s_del</td>
    </tr>";
}

if ($i == 0) {
    echo "<tr><td colspan=7 align=center height=100 bgcolor=#ffffff><span class=point>자료가 한건도 없습니다.</span></td></tr>\n";
}
?>
<tr><td colspan=7 height=1 bgcolor=CCCCCC></td></tr>
</table>


<table width=100%>
<tr>
    <td width=50%>&nbsp;</td>
    <td width=50% align=right><?=get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr&page=");?></td>
</tr>
</table>

<?
include_once ("$g4[admin_path]/admin.tail.php");
?>
