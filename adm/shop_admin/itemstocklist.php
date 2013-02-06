<?
$sub_menu = "400620";
include_once("./_common.php");
include_once(G4_LIB_PATH.'/thumbnail.lib.php');

auth_check($auth[$sub_menu], "r");

$g4[title] = "상품재고관리";
include_once(G4_ADMIN_PATH."/admin.head.php");

$sql_search = " where 1 ";
if ($search != "") {
	if ($sel_field != "") {
    	$sql_search .= " and $sel_field like '%$search%' ";
    }
}

if ($sel_ca_id != "") {
    $sql_search .= " and ca_id like '$sel_ca_id%' ";
}

if ($sel_field == "")  $sel_field = "it_name";
if ($sort1 == "") $sort1 = "it_id";
if ($sort2 == "") $sort2 = "desc";

$sql_common = "  from $g4[yc4_item_table] ";
$sql_common .= $sql_search;

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row[cnt];

$rows = $config[cf_page_rows];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql  = " select it_id,
                 it_name,
                 it_use,
                 it_stock_qty,
                 it_img1,
                 it_img2,
                 it_img3,
                 it_img4,
                 it_img5,
                 it_img6,
                 it_img7,
                 it_img8,
                 it_img9,
                 it_img10
           $sql_common
          order by $sort1 $sort2
          limit $from_record, $rows ";
$result = sql_query($sql);

$qstr1 = "sel_ca_id=$sel_ca_id&sel_field=$sel_field&search=$search";
$qstr  = "$qstr1&sort1=$sort1&sort2=$sort2&page=$page";
?>

<form id="flist" name="flist">
<table>
<input type="hidden" id="doc" name="doc"   value="<? echo $doc ?>">
<input type="hidden" id="sort1" name="sort1" value="<? echo $sort1 ?>">
<input type="hidden" id="sort2" name="sort2" value="<? echo $sort2 ?>">
<input type="hidden" id="page" name="page"  value="<? echo $page ?>">
<tr>
    <td width=10%><a href='<?=$_SERVER[PHP_SELF]?>'>처음</a></td>
    <td%>
        <select id="sel_ca_id" name="sel_ca_id">
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

        <select id="sel_field" name="sel_field">
            <option value='it_name'>상품명
            <option value='it_id'>상품코드
        </select>
        <? if ($sel_field) echo "<script> document.flist.sel_field.value = '$sel_field';</script>"; ?>

        <input type="text" id="search" name="search" value='<? echo $search ?>'>
        <input type="image" src='<?=$g4[admin_path]?>/img/btn_search.gif' align=absmiddle>
    </td>
    <td width=10%>건수 : <? echo $total_count ?>&nbsp;</td>
</tr>
</table>
</form>


<form id="fitemstocklist" name="fitemstocklist" method=post action="./itemstocklistupdate.php">
<input type="hidden" id="sort1" name="sort1"      value="<? echo $sort1 ?>">
<input type="hidden" id="sort2" name="sort2"      value="<? echo $sort2 ?>">
<input type="hidden" id="sel_ca_id" name="sel_ca_id"  value="<? echo $sel_ca_id ?>">
<input type="hidden" id="sel_field" name="sel_field"  value="<? echo $sel_field ?>">
<input type="hidden" id="search" name="search"     value="<? echo $search ?>">
<input type="hidden" id="page" name="page"       value="<? echo $page ?>">
<table>
<colgroup>
<colgroup>
<colgroup>
<colgroup>
<colgroup>
<colgroup>
<colgroup>
<colgroup>
<colgroup width=40>
<tr><td colspan=9 height=2 bgcolor=#0E87F9></td></tr>
<tr>
    <td><a href='<? echo title_sort("it_id") . "&$qstr1"; ?>'>상품코드</a></td>
    <td colspan=2><a href='<? echo title_sort("it_name") . "&$qstr1"; ?>'>상품명</a></td>
    <td><a href='<? echo title_sort("it_stock_qty") . "&$qstr1"; ?>'>창고재고</a></td>
    <td>주문대기</td>
    <td>가재고</td>
    <td>재고수정</td>
    <td><a href='<? echo title_sort("it_use") . "&$qstr1"; ?>'>판매</a></td>
    <td>수정</td>
</tr>
<tr><td colspan=9 height=1 bgcolor=#CCCCCC></td></tr>
<?
for ($i=0; $row=mysql_fetch_array($result); $i++)
{
    $href = "{$g4[shop_path]}/item.php?it_id=$row[it_id]";

    $sql1 = " select SUM(ct_qty) as sum_qty
                from $g4[yc4_cart_table]
               where it_id = '$row[it_id]'
                 and ct_stock_use = '0'
                 and ct_status in ('주문', '준비') ";
    $row1 = sql_fetch($sql1);
    $wait_qty = $row1['sum_qty'];

    // 가재고 (미래재고)
    $temporary_qty = $row['it_stock_qty'] - $wait_qty;

    $s_mod = icon("수정", "./itemform.php?w=u&it_id=$row[it_id]&ca_id=$row[ca_id]&$qstr");

    $list = $i%2;

    // 리스트 썸네일 이미지
    $filepath = G4_DATA_PATH.'/item/'.$row['it_id'];
    for($k=1; $k<=10; $k++) {
        $idx = 'it_img'.$k;
        if(file_exists($filepath.'/'.$row[$idx]) && is_file($filepath.'/'.$row[$idx])) {
            $filename = $row[$idx];
            break;
        }
    }

    echo "
    <input type=\"hidden\" name='it_id[$i]' value='$row[it_id]'>
    <tr class='list$list center'>
        <td>$row[it_id]</td>
        <td style='padding-top:5px; padding-bottom:5px;'><a href='$href'>".get_it_image($row['it_id'], $filename, 50, 50)."</a></td>
        <td align=left><a href='$href'>".cut_str(stripslashes($row[it_name]), 60, "&#133")."</a></td>
        <td>".number_format($row[it_stock_qty])."</td>
        <td>".number_format($wait_qty)."</td>
        <td>".number_format($temporary_qty)."</td>
        <td><input type=\"text\" name='it_stock_qty[$i]' value='$row[it_stock_qty]' size=10 style='text-align:right;' autocomplete='off'></td>
        <td><input type=\"checkbox\" name='it_use[$i]' value='1' ".($row[it_use] ? "checked" : "")."></td>
        <td>$s_mod</td>
    </tr><tr>";
}

if (!$i)
    echo "<tr><td colspan=9 height=100 bgcolor=#ffffff><span class=point>자료가 한건도 없습니다.</span></td></tr>";
?>
<tr><td colspan=9 height=1 bgcolor=#CCCCCC></td></tr>
</table>

<table>
<tr>
    <td colspan=50%><input type="submit" value='일괄수정' accesskey='s'></td>
    <td><?=get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr&page=");?></td>
</tr>
</form>
</table><br>

* 상품의 재고와 판매를 일괄 처리합니다.<br>
* 가재고는 창고재고 - 주문대기 수량입니다.<br>
* 재고수정의 수량은 창고재고를 수정하는것입니다.

<?
include_once(G4_ADMIN_PATH."/admin.tail.php");
?>
