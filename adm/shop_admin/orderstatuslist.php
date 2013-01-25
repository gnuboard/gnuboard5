<?
$sub_menu = "400410";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

$g4[title] = "주문개별관리";
include_once ("$g4[admin_path]/admin.head.php");

$where = " where ";
$sql_search = "";
if ($search != "") {
    if ($sel_field == "c.ca_id") {
    	$sql_search .= " $where $sel_field like '$search%' ";
        $where = " and ";
    } else if ($sel_field != "") {
    	$sql_search .= " $where $sel_field like '%$search%' ";
        $where = " and ";
    }

    if ($save_search != $search)
        $page = 1;
}

if ($sel_field == "")  $sel_field = "od_id";
if ($sort1 == "") $sort1 = "od_id";
if ($sort2 == "") $sort2 = "desc";

$sql_common = " from $g4[yc4_order_table] a
                left join $g4[yc4_cart_table] b on (a.on_uid = b.on_uid)
                left join $g4[yc4_item_table] c on (b.it_id = c.it_id)
                $sql_search ";

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row[cnt];

$rows = $config[cf_page_rows];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql  = " select a.od_id,
                 a.mb_id,
                 a.od_name,
                 a.od_deposit_name,
                 a.od_time,
                 b.it_opt1,
                 b.it_opt2,
                 b.it_opt3,
                 b.it_opt4,
                 b.it_opt5,
                 b.it_opt6,
                 b.ct_status,
                 b.ct_qty,
                 b.ct_amount,
                 b.ct_point,
                 (b.ct_qty * b.ct_amount) as ct_sub_amount,
                 (b.ct_qty * b.ct_point)  as ct_sub_point,
                 c.it_id,
                 c.it_name,
                 c.it_opt1_subject,
                 c.it_opt2_subject,
                 c.it_opt3_subject,
                 c.it_opt4_subject,
                 c.it_opt5_subject,
                 c.it_opt6_subject
           $sql_common
           order by $sort1 $sort2
           limit $from_record, $rows ";
$result = sql_query($sql);

$qstr1 = "sel_ca_id=$sel_ca_id&sel_field=$sel_field&search=$search&save_search=$search";
$qstr  = "$qstr1&sort1=$sort1&sort2=$sort2&page=$page";
?>

<form name=frmorderlist style="margin:0px;">
<input type=hidden name=doc   value="<? echo $doc ?>">
<input type=hidden name=sort1 value="<? echo $sort1 ?>">
<input type=hidden name=page  value="<? echo $page ?>">
<table width=100% cellpadding=4 cellspacing=0>
<tr>
    <td width=10%><a href='<?=$_SERVER[PHP_SELF]?>'>처음</a></td>
    <td width=80% align=center>
        <!-- <input type=button value='주문' class=btn1 onclick="location.href='<?="$_SERVER[PHP_SELF]?sort1=$sort1&sort2=$sort2&sel_field=ct_status&search=주문"?>'">
        <input type=button value='준비' class=btn1 onclick="location.href='<?="$_SERVER[PHP_SELF]?sort1=$sort1&sort2=$sort2&sel_field=ct_status&search=준비"?>'">
        <input type=button value='배송' class=btn1 onclick="location.href='<?="$_SERVER[PHP_SELF]?sort1=$sort1&sort2=$sort2&sel_field=ct_status&search=배송"?>'">
        <input type=button value='완료' class=btn1 onclick="location.href='<?="$_SERVER[PHP_SELF]?sort1=$sort1&sort2=$sort2&sel_field=ct_status&search=완료"?>'">
        <input type=button value='취소' class=btn1 onclick="location.href='<?="$_SERVER[PHP_SELF]?sort1=$sort1&sort2=$sort2&sel_field=ct_status&search=취소"?>'">
        <input type=button value='반품' class=btn1 onclick="location.href='<?="$_SERVER[PHP_SELF]?sort1=$sort1&sort2=$sort2&sel_field=ct_status&search=반품"?>'">
        <input type=button value='품절' class=btn1 onclick="location.href='<?="$_SERVER[PHP_SELF]?sort1=$sort1&sort2=$sort2&sel_field=ct_status&search=품절"?>'"> -->
        <!-- utf-8 에서 처리되도록 변경 -->
        <input type=button value='주문' class=btn1 onclick="location.href='<?="$_SERVER[PHP_SELF]?sort1=$sort1&sort2=$sort2&sel_field=ct_status&search=".urlencode('주문')?>'">
        <input type=button value='준비' class=btn1 onclick="location.href='<?="$_SERVER[PHP_SELF]?sort1=$sort1&sort2=$sort2&sel_field=ct_status&search=".urlencode('준비')?>'">
        <input type=button value='배송' class=btn1 onclick="location.href='<?="$_SERVER[PHP_SELF]?sort1=$sort1&sort2=$sort2&sel_field=ct_status&search=".urlencode('배송')?>'">
        <input type=button value='완료' class=btn1 onclick="location.href='<?="$_SERVER[PHP_SELF]?sort1=$sort1&sort2=$sort2&sel_field=ct_status&search=".urlencode('완료')?>'">
        <input type=button value='취소' class=btn1 onclick="location.href='<?="$_SERVER[PHP_SELF]?sort1=$sort1&sort2=$sort2&sel_field=ct_status&search=".urlencode('취소')?>'">
        <input type=button value='반품' class=btn1 onclick="location.href='<?="$_SERVER[PHP_SELF]?sort1=$sort1&sort2=$sort2&sel_field=ct_status&search=".urlencode('반품')?>'">
        <input type=button value='품절' class=btn1 onclick="location.href='<?="$_SERVER[PHP_SELF]?sort1=$sort1&sort2=$sort2&sel_field=ct_status&search=".urlencode('품절')?>'">
        &nbsp;
        <select name=sel_field>
            <option value='od_id'>주문번호
            <option value='od_name'>주문자
            <option value='mb_id'>회원 ID
            <option value='od_deposit_name'>입금자
            <option value='c.it_id'>상품코드
            <option value='c.ca_id'>분류코드
            <option value='ct_status'>상태
        </select>
        <input type=hidden name=save_search value='<?=$search?>'>
        <input type=text name=search value='<? echo $search ?>' autocomplete="off">
        <input type=image src='<?=$g4[admin_path]?>/img/btn_search.gif' align=absmiddle>
    </td>
    <td width=10% align=right>건수 : <? echo $total_count ?>&nbsp;</td>
</tr>
</table>


<table width=100% cellpadding=0 cellspacing=0>
<colgroup width=80>
<colgroup width=70>
<colgroup width=70>
<colgroup width=60>
<colgroup width=''>
<colgroup width=60>
<colgroup width=30>
<colgroup width=70>
<colgroup width=50>
<colgroup width=30>
<colgroup width=30>
<tr><td colspan=11 height=3 bgcolor=#0E87F9></td></tr>
<tr align=center class=ht>
    <td><a href="<?=title_sort("od_id")."&$qstr1";?>">주문번호</a></td>
    <td><a href="<?=title_sort("od_name")."&$qstr1";?>">주문자</a></td>
    <td><a href="<?=title_sort("mb_id")."&$qstr1";?>">회원ID</a></td>
    <td></td>
    <td><a href="<?=title_sort("it_name")."&$qstr1";?>">상품명</a></td>
    <td><a href="<?=title_sort("ct_amount")."&$qstr1";?>">판매가</a></td>
    <td><a href="<?=title_sort("ct_qty")."&$qstr1";?>">수량</a></td>
    <td><a href="<?=title_sort("ct_sub_amount")."&$qstr1";?>">소계</a></td>
    <td><a href="<?=title_sort("ct_sub_point")."&$qstr1";?>">포인트</a></td>
    <td><a href="<?=title_sort("ct_status")."&$qstr1";?>">상태</a></td>
    <td>수정</td>
</tr>
<tr><td colspan=11 height=1 bgcolor=#CCCCCC></td></tr>
<tr><td colspan=11 height=3 bgcolor=#F8F8F8></td></tr>

<?
for ($i=0; $row=sql_fetch_array($result); $i++) {

    $od_deposit_name = "";
    if ($row[od_deposit_name] != "")
        $od_deposit_name = "title='입금자 : $row[od_deposit_name]'";

    $href = "$_SERVER[PHP_SELF]?sort1=$sort1&sort2=$sort2&sel_field=c.it_id&search=$row[it_id]";
    $it_name = "<a href='$href'>".cut_str($row[it_name],35)."</a><br>";
    $it_name .= print_item_options($row[it_id], $row[it_opt1], $row[it_opt2], $row[it_opt3], $row[it_opt4], $row[it_opt5], $row[it_opt6]);

    $s_mod = icon("수정", "./orderform.php?od_id=$row[od_id]");

    $list = $i%2;
    echo "
    <tr class='list$list center'>
        <td align=center title='주문일시 : $row[od_time]'><a href='$_SERVER[PHP_SELF]?sort1=$sort1&sort2=$sort2&sel_field=od_id&search=$row[od_id]'>$row[od_id]</a></td>
        <td align=center $od_deposit_name><a href='$_SERVER[PHP_SELF]?sort1=$sort1&sort2=$sort2&sel_field=od_name&search=$row[od_name]'>".cut_str($row[od_name],10,"")."</a></td>
        <td align=center><a href='$_SERVER[PHP_SELF]?sort1=$sort1&sort2=$sort2&sel_field=mb_id&search=$row[mb_id]'>$row[mb_id]</a></td>
        <td style='padding-top:5px; padding-bottom:5px;'><a href='$href'>".get_it_image("{$row[it_id]}_s", 50, 50)."</a></td>
        <td align=left>$it_name</td>
        <td align=right>".number_format($row[ct_amount])."&nbsp;</td>
        <td align=center>$row[ct_qty]</td>
        <td align=right>".number_format($row[ct_sub_amount])."&nbsp;</td>
        <td align=right>".number_format($row[ct_sub_point])."&nbsp;</td>
        <td align=center><a href='$_SERVER[PHP_SELF]?sort1=$sort1&sort2=$sort2&sel_field=ct_status&search=$row[ct_status]'>$row[ct_status]</a></td>
        <td align=center>$s_mod</td>
    </tr>";

    $tot_amount += $row[ct_amount];
    $tot_qty    += $row[ct_qty];
    $tot_sub_amount += $row[ct_sub_amount];
    $tot_sub_point  += $row[ct_sub_point];
}

if ($i == 0)
    echo "<tr><td colspan=11 align=center height=100 bgcolor=#ffffff><span class=point>자료가 한건도 없습니다.</span></td></tr>\n";
?>
<tr><td colspan=11 height=1 bgcolor=#CCCCCC></td></tr>
<tr class=ht>
    <td colspan=5 align=right>합 계&nbsp;</td>
    <td align=right><?=number_format($tot_amount)?>&nbsp;</td>
    <td align=right><?=number_format($tot_qty)?>&nbsp;</td>
    <td align=right><?=number_format($tot_sub_amount)?>&nbsp;</td>
    <td align=right><?=number_format($tot_sub_point)?>&nbsp;</td>
    <td colspan=2></td>
</tr>
<tr><td colspan=11 height=1 bgcolor=#CCCCCC></td></tr>
</table>

<table width=100%>
<tr>
    <td width=50%>&nbsp;</td>
    <td width=50% align=right><?=get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr&page=");?></td>
</tr>
</table>
</form>


<script language="JavaScript">
var f = document.frmorderlist;
f.sel_field.value  = '<? echo $sel_field ?>';
</script>

<?
include_once ("$g4[admin_path]/admin.tail.php");
?>
