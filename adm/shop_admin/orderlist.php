<?
$sub_menu = '400400';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g4['title'] = '주문내역';
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

$lines = array();
$tot_itemcnt       = 0;
$tot_orderamount   = 0;
$tot_ordercancel   = 0;
$tot_dc_amount     = 0;
$tot_receiptamount = 0;
$tot_receiptcancel = 0;
$tot_misuamount    = 0;
for ($i=0; $row=mysql_fetch_array($result); $i++)
{
    $lines[$i] = $row;

    $tot_itemcount     += $row['itemcount'];
    $tot_orderamount   += $row['orderamount'];
    $tot_ordercancel   += $row['ordercancel'];
    $tot_dc_amount     += $row['od_dc_amount'];
    $tot_receiptamount += $row['receiptamount'];
    $tot_receiptcancel += $row['receiptcancel'];
    $tot_misu          += $row['misu'];
}

//$qstr1 = "sel_ca_id=$sel_ca_id&amp;sel_field=$sel_field&amp;search=$search";
// 김선용 200805 : sel_ca_id - 쓰레기 코드
//$qstr1 = "sel_ca_id=$sel_ca_id&amp;sel_field=$sel_field&amp;search=$search&amp;save_search=$search";
$qstr1 = "sel_field=$sel_field&amp;search=$search&amp;save_search=$search";
$qstr = "$qstr1&amp;sort1=$sort1&amp;sort2=$sort2&amp;page=$page";

$listall = '';
if ($search) // 검색렬일 때만 처음 버튼을 보여줌
    $listall = '<a href="'.$_SERVER['PHP_SELF'].'">전체목록</a>';
?>
<style>

</style>

<form name="frmorderlist">
<input type="hidden" name="doc" value="<? echo $doc   ?>">
<input type="hidden" name="sort1" value="<? echo $sort1 ?>">
<input type="hidden" name="sort2" value="<? echo $sort2 ?>">
<input type="hidden" name="page" value="<? echo $page ?>">
<input type="hidden" name="save_search" value="<?=$search?>">
<fieldset>
    <legend>주문내역 검색</legend>
    <span>
        <?=$listall?>
        전체 주문내역 <?=$total_count ?>건
    </span>
    <select name="sel_field" title="검색대상">
        <option value="od_id" <?=get_selected($sel_field, 'od_id')?>>주문번호</option>
        <option value="mb_id" <?=get_selected($sel_field, 'mb_id')?>>회원 ID</option>
        <option value="od_name" <?=get_selected($sel_field, 'od_name')?>>주문자</option>
        <option value="od_tel" <?=get_selected($sel_field, 'od_tel')?>>주문자전화</option>
        <option value="od_hp" <?=get_selected($sel_field, 'od_hp')?>>주문자핸드폰</option>
        <option value="od_b_name" <?=get_selected($sel_field, 'od_b_name')?>>받는분</option>
        <option value="od_b_tel" <?=get_selected($sel_field, 'od_b_tel')?>>받는분전화</option>
        <option value="od_b_hp" <?=get_selected($sel_field, 'od_b_hp')?>>받는분핸드폰</option>
        <option value="od_deposit_name" <?=get_selected($sel_field, 'od_deposit_name')?>>입금자</option>
        <option value="od_invoice" <?=get_selected($sel_field, 'od_invoice')?>>운송장번호</option>
    </select>
    <input type="text" name="search" value="<? echo $search ?>" required class="required frm_input" autocomplete="off">
    <input type="submit" value="검색" class="btn_submit">
</fieldset>
</form>

<section class="cbox">
    <h2>주문내역 목록</h2>
    <p><?=help('<strong>주의!</strong> 주문번호를 클릭하여 나오는 주문상세내역의 주소를 외부에서 조회가 가능한곳에 올리지 마십시오.')?></p>

    <table id="sodr_list">
    <thead>
    <tr>
        <th scope="col"><a href="<?=title_sort("od_id", 1)."&amp;$qstr1";?>">주문번호<span class="sound_only"> 순 정렬</span><br>주문일시</a></th>
        <th scope="col">
            <a href="<?=title_sort("od_name")."&amp;$qstr1";?>">주문자<span class="sound_only"> 순 정렬</span></a><br>
            <a href="<?=title_sort("mb_id")."&amp;$qstr1"; ?>">회원ID<span class="sound_only"> 순 정렬</span>
        </th>
        <th scope="col"><a href="<?=title_sort("itemcount", 1)."&amp;$qstr1";?>">건수<span class="sound_only"> 순 정렬</span></a></th>
        <th scope="col"><a href="<?=title_sort("orderamount", 1)."&amp;$qstr1";?>" class="order_sum">주문합계<span class="sound_only"> 순 정렬</span></a></th>
        <th scope="col"><a href="<?=title_sort("ordercancel", 1)."&amp;$qstr1";?>">주문취소<span class="sound_only"> 순 정렬</span></a></th>
        <th scope="col"><a href="<?=title_sort("od_dc_amount", 1)."&amp;$qstr1";?>">DC<span class="sound_only"> 순 정렬</span></a></th>
        <th scope="col"><a href="<?=title_sort("receiptamount")."&amp;$qstr1";?>" class="order_sum">입금합계<span class="sound_only"> 순 정렬</span></a></th>
        <th scope="col"><a href="<?=title_sort("receiptcancel", 1)."&amp;$qstr1";?>">입금취소<span class="sound_only"> 순 정렬</span></a></th>
        <th scope="col"><a href="<?=title_sort("misu", 1)."&amp;$qstr1";?>" class="order_outstanding">미수금<span class="sound_only"> 순 정렬</span></a></th>
        <th scope="col">결제수단</th>
        <th scope="col">관리</th>
    </tr>
    </thead>
    <tfoot>
    <tr class="orderlist">
        <th scope="row" colspan="2">합 계</td>
        <td><?=(int)$tot_itemcount?>건</td>
        <td><?=number_format($tot_orderamount)?></td>
        <td><?=number_format($tot_ordercancel)?></td>
        <td><?=number_format($tot_dc_amount)?></td>
        <td><?=number_format($tot_receiptamount)?></td>
        <td><?=number_format($tot_receiptcancel)?></td>
        <td><?=number_format($tot_misu)?></td>
        <td colspan="2"></td>
    </tr>
    </tfoot>
    <tbody>
    <?
    for ($i=0; $i<count($lines[$i]); $i++)
    {
        // 결제 수단
        $s_receipt_way = $s_br = "";
        if ($lines[$i]['od_settle_case'])
        {
            $s_receipt_way = $lines[$i]['od_settle_case'];
            $s_br = '<br />';
        }
        else
        {
            if ($lines[$i]['od_temp_bank'] > 0 || $lines[$i]['od_receipt_bank'] > 0)
            {
                //$s_receipt_way = "무통장입금";
                $s_receipt_way = cut_str($lines[$i]['od_bank_account'],8,"");
                $s_br = "<br />";
            }

            if ($lines[$i]['od_temp_card'] > 0 || $lines[$i]['od_receipt_card'] > 0)
            {
                // 미수금이 없고 카드결제를 하지 않았다면 카드결제를 선택후 무통장 입금한 경우임
                if ($lines[$i]['misuamount'] <= 0 && $lines[$i]['od_receipt_card'] == 0)
                    ; // 화면 출력하지 않음
                else
                {
                    $s_receipt_way .= $s_br."카드";
                    if ($lines[$i]['od_receipt_card'] == 0)
                        $s_receipt_way .= "<span><span>(미승인)</span></span>";
                    $s_br = "<br />";
                }
            }
        }

        if ($lines[$i]['od_receipt_point'] > 0)
            $s_receipt_way .= $s_br."포인트";

        $mb_nick = get_sideview($lines[$i]['mb_id'], $lines[$i]['od_name'], $lines[$i]['od_email'], '');

        if ($lines[$i]['mb_id'])
        {
            $sql2 = " select count(*) as cnt from {$g4['shop_order_table']} where mb_id = '{$lines[$i]['mb_id']}' ";
            $row2 = sql_fetch($sql2);
        }
        ?>
        <tr class="orderlist">
            <td class="td_odrnum2">
                <a href="<?=G4_SHOP_URL?>/orderinquiryview.php?od_id=<?=$lines[$i]['od_id']?>&amp;uq_id=<?=$lines[$i]['uq_id']?>">
                    <?=$lines[$i]['od_id']?><br>
                    <span class="sound_only">주문일시 </span><?=$lines[$i]['od_time']?>
                </a>
            </td>
            <!-- <td align=center><a href="<?=$_SERVER['PHP_SELF']?>?sort1=$sort1&amp;sort2=$sort2&amp;sel_field=od_name&amp;search=<?=$lines[$i]['od_name']?>'><span title="<?=$od_deposit_name?>"><?=cut_str($lines[$i]['od_name'],8,"")?></span></a></td> -->
            <td class="td_name">
                <?=$mb_nick?><br>
                <a href="<?=$_SERVER['PHP_SELF']?>?sort1=<?=$sort1?>&sort2=<?=$sort2?>&sel_field=mb_id&search=<?=$lines[$i]['mb_id']?>">
                    <?=$lines[$i]['mb_id']?>
                </a>
            </td>
            <td class="td_sodr_cnt"><b><?=$lines[$i]['itemcount']?></b>건<br>누적 <?=$row2['cnt']?>건</td>
            <td class="td_sodr_sum"><?=number_format($lines[$i]['orderamount'])?></td>
            <td><?=number_format($lines[$i]['ordercancel'])?></td>
            <td><?=number_format($lines[$i]['od_dc_amount'])?></td>
            <td class="td_sodr_sum"><?=number_format($lines[$i]['receiptamount'])?></td>
            <td><?=number_format($lines[$i]['receiptcancel'])?></td>
            <td class="td_sodr_nonpay"><?=number_format($lines[$i]['misu'])?></td>
            <td><?=$s_receipt_way?></td>
            <td>
                <a href="./orderform.php?od_id=<?=$lines[$i]['od_id']?>&amp;<?=$qstr?>">수정</a>
                <a href="javascript:del('./orderdelete.php?od_id=<?=$lines[$i]['od_id']?>&amp;uq_id=<?=$lines[$i]['uq_id']?>&amp;mb_id=<?=$lines[$i]['mb_id']?>&amp;<?=$qstr?>)">삭제</a>
            </td>
        </tr>
    <?
    }
    mysql_free_result($result);
    if ($i == 0)
        echo '<tr><td colspan="11" class="empty_table"><span>자료가 한건도 없습니다.</span></td></tr>';
    ?>
    </tbody>
    </table>
</section>

<?=get_paging($config['cf_write_pages'], $page, $total_page, "{$_SERVER['PHP_SELF']}?$qstr&amp;page=");?>

<?
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>
