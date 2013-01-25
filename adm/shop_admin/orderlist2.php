<?
$sub_menu = "400420";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

$g4[title] = "주문서관리";
include_once ("$g4[admin_path]/admin.head.php");

$where = " where ";
$sql_search = "";
if ($search != "")
{
	if ($sel_field != "") 
    {
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
                left join $g4[yc4_cart_table] b on (a.on_uid=b.on_uid)
                $sql_search ";

// 테이블의 전체 레코드수만 얻음
$row = sql_fetch("select count(od_id) as cnt from {$g4['yc4_order_table']} $sql_search ");
$total_count = $row[cnt];

$rows = $config[cf_page_rows];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql  = " select a.od_id, 
                 a.*, "._MISU_QUERY_."
           $sql_common
           group by a.od_id 
           order by $sort1 $sort2
           limit $from_record, $rows ";
$result = sql_query($sql);

//$qstr1 = "sel_ca_id=$sel_ca_id&sel_field=$sel_field&search=$search";
$qstr1 = "sel_ca_id=$sel_ca_id&sel_field=$sel_field&search=$search&save_search=$search";
$qstr = "$qstr1&sort1=$sort1&sort2=$sort2&page=$page";
?>

<table width=100% cellpadding=4 cellspacing=0>
<form name=frmorderlist>
<input type=hidden name=doc   value="<? echo $doc   ?>">
<input type=hidden name=sort1 value="<? echo $sort1 ?>">
<input type=hidden name=sort2 value="<? echo $sort2 ?>">
<input type=hidden name=page  value="<? echo $page ?>">
<tr>
    <td width=20%><a href='<?=$_SERVER[PHP_SELF]?>'>처음</a></td>
    <td width=60% align=center>
        <select name=sel_field>
            <option value='od_id'>주문번호
            <option value='mb_id'>회원 ID
            <option value='od_name'>주문자
            <option value='od_b_name'>받는분
            <option value='od_deposit_name'>입금자
            <option value='od_invoice'>운송장번호
        </select>
        <input type=hidden name=save_search value='<?=$search?>'>
        <input type=text name=search value='<? echo $search ?>' autocomplete="off">
        <input type=image src='<?=$g4[admin_path]?>/img/btn_search.gif' align=absmiddle>
    </td>
    <td width=20% align=right>건수 : <? echo $total_count ?>&nbsp;</td>
</tr>
</table>


<table width=100% cellpadding=0 cellspacing=0>
<colgroup width=60>
<colgroup width=''>
<colgroup width=70>
<colgroup width=30>
<colgroup width=70>
<colgroup width=60>
<colgroup width=60>
<colgroup width=70>
<colgroup width=60>
<colgroup width=70>
<colgroup width=60>
<colgroup width=55>
<tr><td colspan=12 height=2 bgcolor=#0E87F9></td></tr>
<tr align=center class=ht>
    <td><a href='<?=title_sort("od_id", 1)."&$qstr1";?>'>주문번호</a></td>
    <td><a href='<?=title_sort("od_name")."&$qstr1";?>'>주문자</a></td>
    <td><a href='<? echo title_sort("mb_id")."&$qstr1"; ?>'>회원ID</a></td>
    <td><a href='<?=title_sort("itemcount", 1)."&$qstr1";?>'>건수</a></td>
    <td><a href='<?=title_sort("orderamount", 1)."&$qstr1";?>'><FONT COLOR="1275D3">주문합계</a></FONT></td>
    <td><a href='<?=title_sort("ordercancel", 1)."&$qstr1";?>'>주문취소</a></td>
    <td><a href='<?=title_sort("od_dc_amount", 1)."&$qstr1";?>'>DC</a></td>
    <td><a href='<?=title_sort("receiptamount")."&$qstr1";?>'><FONT COLOR="1275D3">입금합계</font></a></td>
    <td><a href='<?=title_sort("receiptcancel", 1)."&$qstr1";?>'>입금취소</a></td>
    <td><a href='<?=title_sort("misu", 1)."&$qstr1";?>'><font color='#FF6600'>미수금</font></a></td>
    <td>결제수단</td>
    <td>수정 삭제</td>
</tr>
<tr align=center>
    <td></td>
    <td colspan=3>상품명</td>
    <td>판매가</td>
    <td>수량</td>
    <td>포인트</td>
    <td colspan=2>상태</td>
    <td>소계</td>
    <td></td>
    <td></td>
</tr>
<tr><td colspan=12 height=1 bgcolor=#CCCCCC></td></tr>

<?
$tot_itemcnt       = 0;
$tot_orderamount   = 0;
$tot_ordercancel   = 0;
$tot_dc_amount     = 0;
$tot_receiptamount = 0;
$tot_receiptcancel = 0;
$tot_misuamount    = 0;
for ($i=0; $row=mysql_fetch_array($result); $i++) 
{
    // 결제 수단
    $s_receipt_way = $s_br = "";
    if ($row[od_settle_case])
    {
        $s_receipt_way = $row[od_settle_case];
        $s_br = '<br/>';
    }
    else
    {
        if ($row[od_temp_bank] > 0 || $row[od_receipt_bank] > 0) 
        {
            //$s_receipt_way = "무통장입금";
            $s_receipt_way = cut_str($row[od_bank_account],8,"");
            $s_br = "<br>";
        }

        if ($row[od_temp_card] > 0 || $row[od_receipt_card] > 0) 
        {
            // 미수금이 없고 카드결제를 하지 않았다면 카드결제를 선택후 무통장 입금한 경우임
            if ($row[misuamount] <= 0 && $row[od_receipt_card] == 0)
                ; // 화면 출력하지 않음
            else 
            {
                $s_receipt_way .= $s_br."카드";
                if ($row[od_receipt_card] == 0)
                    $s_receipt_way .= "<span class=small><span class=point style='font-size:8pt;'>(미승인)</span></span>";
                $s_br = "<br>";
            }
        }
    }

    if ($row[od_receipt_point] > 0)
        $s_receipt_way .= $s_br."포인트";             

    $s_mod = icon("수정", "./orderform.php?od_id=$row[od_id]&$qstr");
    $s_del = icon("삭제", "javascript:del('./orderdelete.php?od_id=$row[od_id]&on_uid=$row[on_uid]&mb_id=$row[mb_id]&$qstr&list=2');");

    if ($i>0)
        echo "<tr><td colspan=12 height=1 bgcolor='#CCCCCC'></td></tr>";

    $list = $i%2;
    echo "
    <tr class='list$list ht'>
        <td align=center title='주문일시 : $row[od_time]'><a href='$g4[shop_path]/orderinquiryview.php?od_id=$row[od_id]&on_uid=$row[on_uid]'>$row[od_id]</a></td>
        <td align=center><a href='$_SERVER[PHP_SELF]?sort1=$sort1&sort2=$sort2&sel_field=od_name&search=$row[od_name]'><span title='$od_deposit_name'>".cut_str($row[od_name],30,"")."</span></a></td>
        <td align=center><a href='$_SERVER[PHP_SELF]?sort1=$sort1&sort2=$sort2&sel_field=mb_id&search=$row[mb_id]'>$row[mb_id]</a></td>
        <td align=center>{$row[itemcount]}건</td>
        <td align=right><FONT COLOR='#1275D3'>".number_format($row[orderamount])."</font></td>
        <td align=right>".number_format($row[ordercancel])."</td>
        <td align=right>".number_format($row[od_dc_amount])."</td>
        <td align=right><FONT COLOR='#1275D3'>".number_format($row[receiptamount])."</font></td>
        <td align=right>".number_format($row[receiptcancel])."</td>
        <td align=right><FONT COLOR='#FF6600'>".number_format($row[misu])."</FONT></td>
        <td align=center>$s_receipt_way</td>
        <td align=center>$s_mod $s_del</a></td>
    </tr>";

    $tot_itemcount     += $row[itemcount];
    $tot_orderamount   += $row[orderamount];
    $tot_ordercancel   += $row[ordercancel];
    $tot_dc_amount     += $row[od_dc_amount];
    $tot_receiptamount += $row[receiptamount];
    $tot_receiptcancel += $row[receiptcancel];
    $tot_misu          += $row[misu];

    // 상품개별출력
    $sql2 = " select c.it_name, 
                     b.* 
                from $g4[yc4_order_table] a
                left join $g4[yc4_cart_table] b on (a.on_uid = b.on_uid)
                left join $g4[yc4_item_table] c on (b.it_id = c.it_id) 
               where od_id = '$row[od_id]' ";
    $result2 = sql_query($sql2);
    for ($k=0; $row2=sql_fetch_array($result2); $k++) 
    {
        $href = "$g4[shop_path]/item.php?it_id=$row2[it_id]";
        $it_name = "<a href='$href'>".cut_str($row2[it_name],35)."</a><br>";
        $it_name .= print_item_options($row2[it_id], $row2[it_opt1], $row2[it_opt2], $row2[it_opt3], $row2[it_opt4], $row2[it_opt5], $row2[it_opt6]);

        $sub_amount = $row2[ct_qty] * $row2[ct_amount];
        $sub_point  = $row2[ct_qty] * $row2[ct_point];

        echo "
        <tr class='list$list ht'>
            <td></td>
            <td colspan=3>
                <table width=100% cellpadding=0 cellspacing=0>
                <tr>
                	<td style='padding-top:5px; padding-bottom:5px;'><a href='$href'>".get_it_image("{$row2[it_id]}_s", 50, 50)."</a></td>
                	<td>$it_name</td>
                </tr>
                </table></td>
            <td align=right>".number_format($row2[ct_amount])."&nbsp;</td>
            <td align=center>$row2[ct_qty]</td>
            <td align=right>".number_format($sub_point)."&nbsp;</td>
            <td align=center colspan=2>$row2[ct_status]</td>
            <td align=right>".number_format($sub_amount)."&nbsp;</td>
            <td></td>
            <td></td>
        </tr>";
    }
}

if ($i == 0)
    echo "<tr><td colspan=12 align=center height=100 bgcolor='#FFFFFF'><span class=point>자료가 한건도 없습니다.</span></td></tr>\n";
?>
</form>
<tr><td colspan=12 bgcolor='#CCCCCC'></td></tr>
<tr class=ht>
    <td colspan=3 align=center>합 계</td>
    <td align=center><?=(int)$tot_itemcount?>건</td>
    <td align=right><FONT COLOR='#1275D3'><?=number_format($tot_orderamount)?></FONT></td>
    <td align=right><?=number_format($tot_ordercancel)?></td>
    <td align=right><?=number_format($tot_dc_amount)?></td>
    <td align=right><FONT COLOR='#1275D3'><?=number_format($tot_receiptamount)?></FONT></td>
    <td align=right><?=number_format($tot_receiptcancel)?></td>
    <td align=right><FONT COLOR='#FF6600'><?=number_format($tot_misu)?></FONT></td>
    <td colspan=2></td>
</tr>
<tr><td colspan=12 bgcolor='#CCCCCC'></td></tr>
</table>

<table width=100%>
<tr>
    <td width=50%>&nbsp;</td>
    <td width=50% align=right><?=get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr&page=");?></td>
</tr>
</table>

<font color=crimson>주의)</font> 주문번호를 클릭하여 나오는 주문상세내역의 주소를 외부에서 조회가 가능한곳에 올리지 마십시오.

<script language="JavaScript">
var f = document.frmorderlist;
f.sel_field.value  = '<? echo $sel_field ?>';
</script>

<?
include_once ("$g4[admin_path]/admin.tail.php");
?>
