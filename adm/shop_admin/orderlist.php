<?
$sub_menu = '400400';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g4['title'] = '주문서관리';
include_once (G4_ADMIN_PATH.'/admin.head.php');

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

$sql_common = " from {$g4['shop_order_table']} a
                left join {$g4['shop_cart_table']} b on (a.uq_id=b.uq_id)
                $sql_search ";

// 김선용 200805 : 조인 사용으로 전체카운트가 일정레코드 이상일 때 지연시간 문제가 심각하므로 변경
/*
$result = sql_query(" select DISTINCT od_id ".$sql_common);
$total_count = mysql_num_rows($result);
*/
$sql = " select count(distinct od_id) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql  = " select a.*, "._MISU_QUERY_."
           $sql_common
           group by a.od_id
           order by $sort1 $sort2
           limit $from_record, $rows ";
$result = sql_query($sql, false);
if (!$result) {
    sql_query(" ALTER TABLE `{$g4['shop_order_table']}` ADD `od_temp_hp` INT NOT NULL AFTER `od_temp_card` ", false);
    sql_query(" ALTER TABLE `{$g4['shop_order_table']}` ADD `od_receipt_hp` INT NOT NULL AFTER `od_receipt_card` ", false);
    sql_query(" ALTER TABLE `{$g4['shop_order_table']}` ADD `od_hp_time` DATETIME NOT NULL AFTER `od_card_time` ", false);
}
//echo $sql;

//$qstr1 = "sel_ca_id=$sel_ca_id&sel_field=$sel_field&search=$search";
// 김선용 200805 : sel_ca_id - 쓰레기 코드
//$qstr1 = "sel_ca_id=$sel_ca_id&sel_field=$sel_field&search=$search&save_search=$search";
$qstr1 = "sel_field=$sel_field&search=$search&save_search=$search";
$qstr = "$qstr1&sort1=$sort1&sort2=$sort2&page=$page";
?>
<style type="text/css">

.orderlist{text-align:center}
.order_sum{color:#1275d3}
.order_outstanding{color:#ff6600}
.order_care{color:red}

</style>

<form name="frmorderlist">
<input type="hidden" name="doc" value="<? echo $doc   ?>">
<input type="hidden" name="sort1" value="<? echo $sort1 ?>">
<input type="hidden" name="sort2" value="<? echo $sort2 ?>">
<input type="hidden" name="page" value="<? echo $page ?>">

<section class="cbox">
    <h2>주문서 목록</h2>
    <div>
        <p><a href="<?=$_SERVER['PHP_SELF']?>">처음</a></p>
        <select name="sel_field">
            <option value="od_id">주문번호</option>
            <option value="mb_id">회원 ID</option>
            <option value="od_name">주문자</option>
            <option value="od_tel">주문자전화</option>
            <option value="od_hp">주문자핸드폰</option>
            <option value="od_b_name">받는분</option>
            <option value="od_b_tel">받는분전화</option>
            <option value="od_b_hp">받는분핸드폰</option>
            <option value="od_deposit_name">입금자</option>
            <option value="od_invoice">운송장번호</option>
        </select>
        <input type="hidden" name="save_search" value="<?=$search?>">
        <input type="text" name="search" value="<? echo $search ?>" autocomplete="off">
        <input type="image" src="<?=G4_ADMIN_URL?>/img/btn_search.gif">
        <p>건수 <? echo $total_count ?></p>
    </div>
    <table>
    <colgroup>
        <col class="grid_3">
        <col>
        <col>
        <col>
        <col>
        <col>
        <col>
        <col>
        <col>
        <col>
        <col>
        <col class="grid_3">
    </colgroup>
    <thead>
    <tr>
        <th><a href="<?=title_sort("od_id", 1)."&$qstr1";?>">주문번호</a></th>
        <th><a href="<?=title_sort("od_name")."&$qstr1";?>">주문자</a></th>
        <th><a href="<? echo title_sort("mb_id")."&$qstr1"; ?>">회원ID</a></th>
        <th><a href="<?=title_sort("itemcount", 1)."&$qstr1";?>">건수</a> <span title="회원별 누적 건수">(누적)</span></th>
        <th><a href="<?=title_sort("orderamount", 1)."&$qstr1";?>" class="order_sum">주문합계</a></th>
        <th><a href="<?=title_sort("ordercancel", 1)."&$qstr1";?>">주문취소</a></th>
        <th><a href="<?=title_sort("od_dc_amount", 1)."&$qstr1";?>">DC</a></th>
        <th><a href="<?=title_sort("receiptamount")."&$qstr1";?>" class="order_sum">입금합계</a></th>
        <th><a href="<?=title_sort("receiptcancel", 1)."&$qstr1";?>">입금취소</a></th>
        <th><a href="<?=title_sort("misu", 1)."&$qstr1";?>" class="order_outstanding">미수금</a></th>
        <th>결제수단</th>
        <th>관리</th>
    </tr>
    </thead>
    <tbody>
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
            $s_receipt_way = $row['od_settle_case'];
            $s_br = '<br />';
        }
        else
        {
            if ($row['od_temp_bank'] > 0 || $row['od_receipt_bank'] > 0)
            {
                //$s_receipt_way = "무통장입금";
                $s_receipt_way = cut_str($row['od_bank_account'],8,"");
                $s_br = "<br />";
            }

            if ($row['od_temp_card'] > 0 || $row['od_receipt_card'] > 0)
            {
                // 미수금이 없고 카드결제를 하지 않았다면 카드결제를 선택후 무통장 입금한 경우임
                if ($row['misuamount'] <= 0 && $row['od_receipt_card'] == 0)
                    ; // 화면 출력하지 않음
                else
                {
                    $s_receipt_way .= $s_br."카드";
                    if ($row['od_receipt_card'] == 0)
                        $s_receipt_way .= "<span><span>(미승인)</span></span>";
                    $s_br = "<br />";
                }
            }
        }

        if ($row['od_receipt_point'] > 0)
            $s_receipt_way .= $s_br."포인트";

        $s_mod = icon("수정", "./orderform.php?od_id=$row[od_id]&$qstr");
        $s_del = icon("삭제", "javascript:del('./orderdelete.php?od_id={$row['od_id']}&uq_id={$row['uq_id']}&mb_id={$row['mb_id']}&$qstr');");

        $mb_nick = get_sideview($row['mb_id'], $row['od_name'], $row['od_email'], '');

        $tot_cnt = "";
        if ($row['mb_id'])
        {
            $sql2 = " select count(*) as cnt from {$g4['shop_order_table']} where mb_id = '{$row['mb_id']}' ";
            $row2 = sql_fetch($sql2);
            $tot_cnt = '('.$row2['cnt'].')';
        }

        $list = $i%2;
        ?>
        <tr class="orderlist">
            <td title="주문일시 : <?=$row['od_time']?>"><a href="<?=G4_SHOP_URL?>/orderinquiryview.php?od_id=<?=$row['od_id']?>&uq_id=<?=$row['uq_id']?>"><?=$row['od_id']?></a></td>
            <!-- <td align=center><a href='{$_SERVER['PHP_SELF']}?sort1=$sort1&sort2=$sort2&sel_field=od_name&search={$row['od_name']}'><span title='$od_deposit_name'>".cut_str($row['od_name'],8,"")."</span></a></td> -->
            <td><?=$mb_nick?></td>
            <td><a href="<?=$_SERVER['PHP_SELF']?>?sort1=<?=$sort1?>&sort2=<?=$sort2?>&sel_field=mb_id&search=<?=$row['mb_id']?>"><?=$row['mb_id']?></a></td>
            <td><?=$row['itemcount']?>건 <?=$tot_cnt?></td>
            <td class="order_sum"><?=number_format($row['orderamount'])?></td>
            <td><?=number_format($row['ordercancel'])?></td>
            <td><?=number_format($row['od_dc_amount'])?></td>
            <td class="order_sum"><?=number_format($row['receiptamount'])?></td>
            <td><?=number_format($row['receiptcancel'])?></td>
            <td class="order_outstanding"><?=number_format($row['misu'])?></td>
            <td><?=$s_receipt_way?></td>
            <td><a href="./orderform.php?od_id=<?=$row[od_id]?>&<?=$qstr?>">수정</a> <a href="javascript:del('./orderdelete.php?od_id=<?=$row['od_id']?>&uq_id=<?=$row['uq_id']?>&mb_id=<?=$row['mb_id']?>&<?=$qstr?>)">삭제</a></td>
        </tr>
        <?

        $tot_itemcount     += $row['itemcount'];
        $tot_orderamount   += $row['orderamount'];
        $tot_ordercancel   += $row['ordercancel'];
        $tot_dc_amount     += $row['od_dc_amount'];
        $tot_receiptamount += $row['receiptamount'];
        $tot_receiptcancel += $row['receiptcancel'];
        $tot_misu          += $row['misu'];
    }
    mysql_free_result($result);
    if ($i == 0)
        echo '<tr><td colspan="12" class="orderlist"><span>자료가 한건도 없습니다.</span></td></tr>';
    ?>
    </tbody>
    <tfoot>
    <tr class="orderlist">
        <td colspan="3">합 계</td>
        <td><?=(int)$tot_itemcount?>건</td>
        <td class="order_sum"><?=number_format($tot_orderamount)?></td>
        <td><?=number_format($tot_ordercancel)?></td>
        <td><?=number_format($tot_dc_amount)?></td>
        <td class="order_sum"><?=number_format($tot_receiptamount)?></td>
        <td><?=number_format($tot_receiptcancel)?></td>
        <td class="order_outstanding"><?=number_format($tot_misu)?></td>
        <td colspan="2"></td>
    </tr>
    </tfoot>
    </table>
</section>
</form>
<p><?=get_paging($config['cf_write_pages'], $page, $total_page, "{$_SERVER['PHP_SELF']}?$qstr&page=");?></p>

<p><span class="order_care">주의)</span> 주문번호를 클릭하여 나오는 주문상세내역의 주소를 외부에서 조회가 가능한곳에 올리지 마십시오.</p>

<script>
var f = document.frmorderlist;
f.sel_field.value  = '<? echo $sel_field ?>';
</script>

<?
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>
