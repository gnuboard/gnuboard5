<?php
$sub_menu = '400420';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g4['title'] = '주문통합내역';
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

// 테이블의 전체 레코드수만 얻음
$row = sql_fetch("select count(od_id) as cnt from {$g4['shop_order_table']} $sql_search ");
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
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
$qstr1 = "sel_ca_id=$sel_ca_id&amp;sel_field=$sel_field&amp;search=$search&amp;save_search=$search";
$qstr = "$qstr1&amp;sort1=$sort1&amp;sort2=$sort2&amp;page=$page";

$listall = '';
if ($search) // 검색렬일 때만 처음 버튼을 보여줌
    $listall = '<a href="'.$_SERVER['PHP_SELF'].'">전체목록</a>';
?>

<form name="frmorderlist">
<input type="hidden" name="doc" value="<?php cho $doc; ?>">
<input type="hidden" name="sort1" value="<?php echo $sort1; ?>">
<input type="hidden" name="sort2" value="<?php echo $sort2; ?>">
<input type="hidden" name="page" value="<?php echo $page; ?>">
<input type="hidden" name="save_search" value="<?php echo $search; ?>">
<fieldset>
    <legend>주문내역 검색</legend>
    <span>
        <?php echo $listall; ?>
        전체 주문내역 <?php echo $total_count; ?>건
    </span>

    <label for="sel_field" class="sound_only">검색대상</label>
    <select name="sel_field" id="sel_field">
        <option value="od_id" <?php echo get_selected($sel_field, 'od_id'); ?>>주문번호</option>
        <option value="mb_id" <?php echo get_selected($sel_field, 'mb_id'); ?>>회원 ID</option>
        <option value="od_name" <?php echo get_selected($sel_field, 'od_name'); ?>>주문자</option>
        <option value="od_b_name" <?php echo get_selected($sel_field, 'od_b_name'); ?>>받는분</option>
        <option value="od_deposit_name" <?php echo get_selected($sel_field, 'od_deposit_name'); ?>>입금자</option>
        <option value="od_invoice" <?php echo get_selected($sel_field, 'od_invoice'); ?>>운송장번호</option>
    </select>

    <label for="search" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
    <input type="text" name="search" value="<?php echo $search; ?>" id="search" autocomplete="off">
    <input type="submit" value="검색" class="btn_submit">
</fieldset>
</form>

<section class="cbox">
    <h2>주문통합내역 목록</h2>
    <p><strong>주의!</strong> 주문번호를 클릭하여 나오는 주문상세내역의 주소를 외부에서 조회가 가능한곳에 올리지 마십시오.</p>

    <div id="btn_add">
        <a href="./orderprint.php" class="btn_add_optional">주문내역출력</a>
        <a href="./ordercardhistory.php" class="btn_add_optional">전자결제내역</a>
    </div>

    <table id="sodr_all">
    <thead>
    <tr>
        <th id="sodr_all_num"><a href="<?php echo title_sort("od_id", 1)."&amp;$qstr1"; ?>">주문번호<span class="sound_only"> 순 정렬</span></a><br>주문일시</th>
        <th id="sodr_all_id">
            <a href="<?php echo title_sort("od_name")."&amp;$qstr1"; ?>">주문자<span class="sound_only"> 순 정렬</span>/입금자</a><br>
            <a href="<?php echo title_sort("mb_id")."&amp;$qstr1"; ?>">회원ID</a>
        </th>
        <th id="sodr_all_cnt"><a href="<?php echo title_sort("itemcount", 1)."&amp;$qstr1"; ?>">건수<span class="sound_only"> 순 정렬</span></a></th>
        <th id="sodr_all_calc"><a href="<?php echo title_sort("orderamount", 1)."&amp;$qstr1"; ?>">주문합계<span class="sound_only"> 순 정렬</span></a></th>
        <th id="sodr_all_cancel"><a href="<?php echo title_sort("ordercancel", 1)."&amp;$qstr1"; ?>">주문취소<span class="sound_only"> 순 정렬</span></a></th>
        <th id="sodr_all_dc"><a href="<?php echo title_sort("od_dc_amount", 1)."&amp;$qstr1"; ?>">DC<span class="sound_only"> 순 정렬</span></a></th>
        <th id="sodr_all_inc"><a href="<?php echo title_sort("receiptamount")."&amp;$qstr1"; ?>">입금합계<span class="sound_only"> 순 정렬</span></a></th>
        <th id="sodr_all_inc_cancel"><a href="<?php echo title_sort("receiptcancel", 1)."&amp;$qstr1"; ?>">입금취소<span class="sound_only"> 순 정렬</span></a></th>
        <th id="sodr_all_nonpay"><a href="<?php echo title_sort("misu", 1)."&amp;$qstr1"; ?>">미수금<span class="sound_only"> 순 정렬</span></a></th>
        <th id="sodr_all_payby">결제수단</th>
        <th id="sodr_all_mng">관리</th>
    </tr>
    <tr>
        <th colspan="5" id="sodr_all_item">상품명</th>
        <th id="sodr_all_cost">판매가</th>
        <th id="sodr_all_qty">수량</th>
        <th id="sodr_all_pt">포인트</th>
        <th id="sodr_all_tot">소계</th>
        <th colspan="2" id="sodr_all_stats">상태</th>
    </tr>
    </thead>
    <tfoot>
    <tr>
        <th scope="row" colspan="2">합 계</td>
        <td><?php echo (int)$tot_itemcount; ?>건</td>
        <td><?php echo number_format($tot_orderamount); ?></td>
        <td><?php echo number_format($tot_ordercancel); ?></td>
        <td><?php echo number_format($tot_dc_amount); ?></td>
        <td><?php echo number_format($tot_receiptamount); ?></td>
        <td><?php echo number_format($tot_receiptcancel); ?></td>
        <td><?php echo number_format($tot_misu); ?></td>
        <td colspan="2"></td>
    </tr>
    </tfoot>
    <tbody>
    <?php
    for ($i=0; $i<count($lines); $i++)
    {
        // 결제 수단
        $s_receipt_way = $s_br = "";
        if ($lines[$i]['od_settle_case'])
        {
            $s_receipt_way = $lines[$i]['od_settle_case'];
            $s_br = '<br/>';
        }
        else
        {
            if ($lines[$i]['od_temp_bank'] > 0 || $lines[$i]['od_receipt_bank'] > 0)
            {
                //$s_receipt_way = "무통장입금";
                $s_receipt_way = cut_str($lines[$i]['od_bank_account'],8,"");
                $s_br = "<br>";
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
                        $s_receipt_way .= '<span class="small"><span class="point" style="font-size:8pt;">(미승인)</span></span>';
                    $s_br = '<br>';
                }
            }
        }

        if ($lines[$i]['od_receipt_point'] > 0)
            $s_receipt_way .= $s_br.'포인트';
        ?>

        <tr>
            <td headers="sodr_all_num"><a href="<?php echo G4_SHOP_URL; ?>/orderinquiryview.php?od_id=<?php echo $lines[$i]['od_id']; ?>&amp;uq_id=<?php echo $lines[$i]['uq_id']; ?>"><?php echo $lines[$i]['od_id']; ?><br><?php echo $lines[$i]['od_time']; ?></a></td>
            <td headers="sodr_all_id" class="td_name">
                <a href="<?php echo $_SERVER['PHP_SELF']; ?>?sort1=<?php echo $sort1;?>&amp;sort2=<?php echo $sort2; ?>&amp;sel_field=od_name&amp;search=<?php echo $lines[$i]['od_name']; ?>">
                    <?php echo cut_str($lines[$i]['od_name'],30,""); ?>/<?php echo $od_deposit_name; ?>
                </a><br>
                <a href="<?php echo $_SERVER['PHP_SELF']; ?>?sort1=<?php echo $sort1; ?>&amp;sort2=<?php echo $sort2; ?>&amp;sel_field=mb_id&amp;search=<?php echo $lines[$i]['mb_id']; ?>"><?php echo $lines[$i]['mb_id']; ?></a>
            </td>
            <td headers="sodr_all_cnt"><?php echo $lines[$i]['itemcount']; ?>건</td>
            <td headers="sodr_all_calc" class="td_sodr_sum"><?php echo number_format($lines[$i]['orderamount']); ?></td>
            <td headers="sodr_all_cancel"><?php echo number_format($lines[$i]['ordercancel']); ?></td>
            <td headers="sodr_all_dc"><?php echo number_format($lines[$i]['od_dc_amount']); ?></td>
            <td headers="sodr_all_inc" class="td_sodr_sum"><?php echo number_format($lines[$i]['receiptamount']); ?></td>
            <td headers="sodr_all_inc_cancel"><?php echo number_format($lines[$i]['receiptcancel']); ?></td>
            <td headers="sodr_all_nonpay" class="td_sodr_nonpay"><?php echo number_format($lines[$i]['misu']); ?></td>
            <td headers="sodr_all_payby"><?php echo $s_receipt_way; ?></td>
            <td headers="sodr_all_mng">
                <a href="./orderform.php?od_id=<?php echo $lines[$i]['od_id']; ?>&amp;$qstr?>"><img src="./img/icon_mod.jpg" alt="주문 수정"></a>
                <a href="./orderdelete.php?od_id=<?php echo $lines[$i]['od_id']; ?>&amp;uq_id=<?php echo $lines[$i]['uq_id']; ?>&amp;mb_id=<?php echo $lines[$i]['mb_id']; ?>&amp;<?php echo $qstr; ?>&amp;list=2" onclick="return delete_confirm();"><img src="./img/icon_del.jpg" alt="주문 삭제"></a>
            </td>
        </tr>

        <?php
        // 상품개별출력
        $sql2 = " select c.it_name,
                         b.*
                    from {$g4['shop_order_table']} a
                    left join {$g4['shop_cart_table']} b on (a.uq_id = b.uq_id)
                    left join {$g4['shop_item_table']} c on (b.it_id = c.it_id)
                   where od_id = '{$lines[$i]['od_id']}' ";
        $result2 = sql_query($sql2);
        for ($k=0;$row2=sql_fetch_array($result2);$k++) {
            $href = G4_SHOP_URL."/item.php?it_id={$row2['it_id']}";
            $it_name = '<a href="'.$href.'">'.cut_str($row2['it_name'],35).'</a><br>';
            $it_name .= print_item_options($row2['it_id'], $row2['it_opt1'], $row2['it_opt2'], $row2['it_opt3'], $row2['it_opt4'], $row2['it_opt5'], $row2['it_opt6']);

            $sub_amount = $row2['ct_qty'] * $row2['ct_amount'];
            $sub_point  = $row2['ct_qty'] * $row2['ct_point'];
        ?>

        <tr class="tr_sodr_item">
            <td headers="sodr_all_item" colspan="5">
                <ul>
                    <li><a href="<?php echo $href; ?>"><?php echo get_it_image($row2['it_id'].'_s', 50, 50); ?><?php echo $it_name; ?></a></li>
                </ul>
            </td>
            <td headers="sodr_all_cost"><?php echo number_format($row2['ct_amount']); ?></td>
            <td headers="sodr_all_qty"><?php echo $row2['ct_qty']; ?></td>
            <td headers="sodr_all_pt"><?php echo number_format($sub_point); ?></td>
            <td headers="sodr_all_tot"><?php echo number_format($sub_amount); ?></td>
            <td headers="sodr_all_stats" colspan="2"><?php echo $row2['ct_status']; ?></td>
        </tr>

        <?php
        }
    }

    if ($i == 0)
        echo '<tr><td colspan="12" class="empty_table">자료가 한건도 없습니다.</td></tr>';
    ?>
    </tbody>
    </table>
</section>

<?php echo get_paging($config['cf_write_pages'], $page, $total_page, "{$_SERVER['PHP_SELF']}?$qstr&amp;page="); ?>

<?php
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>
