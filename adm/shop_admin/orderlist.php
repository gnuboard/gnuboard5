<?php
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
        if($sel_field == 'mb_id')
            $sel_field = 'a.'.$sel_field;

        $sql_search .= " $where $sel_field like '%$search%' ";
        $where = " and ";
    }

    if ($save_search != $search)
        $page = 1;
}

if ($sel_field == "")  $sel_field = "a.od_id";
if ($sort1 == "") $sort1 = "a.od_id";
if ($sort2 == "") $sort2 = "desc";

$sql_common = " from {$g4['shop_order_table']} a
                left join {$g4['shop_cart_table']} b on (a.od_id=b.od_id)
                $sql_search ";

// 김선용 200805 : 조인 사용으로 전체카운트가 일정레코드 이상일 때 지연시간 문제가 심각하므로 변경
/*
$result = sql_query(" select DISTINCT od_id ".$sql_common);
$total_count = mysql_num_rows($result);
*/
$sql = " select count(distinct a.od_id) as cnt " . $sql_common;
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
<input type="hidden" name="doc" value="<?php echo $doc; ?>">
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
        <option value="a.od_id" <?php echo get_selected($sel_field, 'a.od_id'); ?>>주문번호</option>
        <option value="mb_id" <?php echo get_selected($sel_field, 'mb_id'); ?>>회원 ID</option>
        <option value="od_name" <?php echo get_selected($sel_field, 'od_name'); ?>>주문자</option>
        <option value="od_tel" <?php echo get_selected($sel_field, 'od_tel'); ?>>주문자전화</option>
        <option value="od_hp" <?php echo get_selected($sel_field, 'od_hp'); ?>>주문자핸드폰</option>
        <option value="od_b_name" <?php echo get_selected($sel_field, 'od_b_name'); ?>>받는분</option>
        <option value="od_b_tel" <?php echo get_selected($sel_field, 'od_b_tel'); ?>>받는분전화</option>
        <option value="od_b_hp" <?php echo get_selected($sel_field, 'od_b_hp'); ?>>받는분핸드폰</option>
        <option value="od_deposit_name" <?php echo get_selected($sel_field, 'od_deposit_name'); ?>>입금자</option>
        <option value="od_invoice" <?php echo get_selected($sel_field, 'od_invoice'); ?>>운송장번호</option>
    </select>

    <label for="search" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
    <input type="text" name="search" value="<?php echo $search; ?>" id="search" required class="required frm_input" autocomplete="off">
    <input type="submit" value="검색" class="btn_submit">
</fieldset>
</form>

<section class="cbox">
    <h2>주문내역 목록</h2>
    <p><strong>주의!</strong> 주문번호를 클릭하여 나오는 주문상세내역의 주소를 외부에서 조회가 가능한곳에 올리지 마십시오.</p>

    <div class="btn_add sort_with">
        <a href="./orderprint.php" class="btn_add_optional">주문내역출력</a>
        <a href="./ordercardhistory.php" class="btn_add_optional">전자결제내역</a>
    </div>

    <ul id="sort_sodr" class="sort_odr">
        <li><a href="<?php echo title_sort("od_id", 1)."&amp;$qstr1"; ?>">주문번호<span class="sound_only"> 순 정렬</span></a></li>
        <li><a href="<?php echo title_sort("od_name")."&amp;$qstr1"; ?>">주문자<span class="sound_only"> 순 정렬</span></a></li>
        <li><a href="<?php echo title_sort("mb_id")."&amp;$qstr1"; ?>">회원ID<span class="sound_only"> 순 정렬</span></a></li>
        <li><a href="<?php echo title_sort("itemcount", 1)."&amp;$qstr1"; ?>">건수<span class="sound_only"> 순 정렬</span></a></li>
        <li><a href="<?php echo title_sort("orderamount", 1)."&amp;$qstr1"; ?>">주문합계<span class="sound_only"> 순 정렬</span></a></li>
        <li><a href="<?php echo title_sort("ordercancel", 1)."&amp;$qstr1"; ?>">주문취소<span class="sound_only"> 순 정렬</span></a></li>
        <li><a href="<?php echo title_sort("od_dc_amount", 1)."&amp;$qstr1"; ?>">DC<span class="sound_only"> 순 정렬</span></a></li>
        <li><a href="<?php echo title_sort("receiptamount")."&amp;$qstr1"; ?>">입금합계<span class="sound_only"> 순 정렬</span></a></li>
        <li><a href="<?php echo title_sort("receiptcancel", 1)."&amp;$qstr1"; ?>">입금취소<span class="sound_only"> 순 정렬</span></a></li>
        <li><a href="<?php echo title_sort("misu", 1)."&amp;$qstr1"; ?>">미수금<span class="sound_only"> 순 정렬</span></a></li>
    </ul>

    <table id="sodr_list">
    <thead>
    <tr>
        <th scope="col">주문번호<br>주문일시</th>
        <th scope="col">주문자<br>회원ID</th>
        <th scope="col">건수</th>
        <th scope="col">주문합계</th>
        <th scope="col">주문취소</th>
        <th scope="col">DC</th>
        <th scope="col">입금합계</th>
        <th scope="col">입금취소</th>
        <th scope="col">미수금</th>
        <th scope="col">결제수단</th>
        <th scope="col">관리</th>
    </tr>
    </thead>
    <tfoot>
    <tr class="orderlist">
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
            $s_receipt_way = '결제수단없음';
            $s_br = '<br />';
        }

        if ($lines[$i]['od_receipt_point'] > 0)
            $s_receipt_way .= $s_br."포인트";

        $mb_nick = get_sideview($lines[$i]['mb_id'], $lines[$i]['od_name'], $lines[$i]['od_email'], '');

        $od_cnt = 0;
        if ($lines[$i]['mb_id'])
        {
            $sql2 = " select count(*) as cnt from {$g4['shop_order_table']} where mb_id = '{$lines[$i]['mb_id']}' ";
            $row2 = sql_fetch($sql2);
            $od_cnt = $row2['cnt'];
        }

        // 주문device
        $od_mobile = '';
        if($lines[$i]['od_mobile'])
            $od_mobile = '(M)';

        $uid = md5($lines[$i]['od_id'].$lines[$i]['od_time'].$lines[$i]['od_ip']);
    ?>
    <tr class="orderlist">
        <td class="td_odrnum2">
            <?php echo $od_mobile; ?>
            <a href="<?php echo G4_SHOP_URL; ?>/orderinquiryview.php?od_id=<?php echo $lines[$i]['od_id']; ?>&amp;uid=<?php echo $uid; ?>">
                <?php echo $lines[$i]['od_id']; ?><br>
                <span class="sound_only">주문일시 </span><?php echo $lines[$i]['od_time']; ?>
            </a>
        </td>
        <!-- <td align=center><a href="<?php echo $_SERVER['PHP_SELF']; ?>?sort1=$sort1&amp;sort2=$sort2&amp;sel_field=od_name&amp;search=<?php echo $lines[$i]['od_name']; ?>'><span title="<?php echo $od_deposit_name; ?>"><?php echo cut_str($lines[$i]['od_name'],8,""); ?></span></a></td> -->
        <td class="td_name">
            <?php echo $mb_nick; ?><br>
            <a href="<?php echo $_SERVER['PHP_SELF']; ?>?sort1=<?php echo $sort1; ?>&amp;sort2=<?php echo $sort2; ?>&amp;sel_field=mb_id&amp;search=<?php echo $lines[$i]['mb_id']; ?>">
                <?php echo $lines[$i]['mb_id']; ?>
            </a>
        </td>
        <td class="td_sodr_cnt"><b><?php echo $lines[$i]['itemcount']; ?></b>건<?php if($od_cnt) { ?><br>누적 <?php echo $od_cnt; ?>건<?php } ?></td>
        <td class="td_sodr_sum"><?php echo number_format($lines[$i]['orderamount']); ?></td>
        <td><?php echo number_format($lines[$i]['ordercancel']); ?></td>
        <td><?php echo number_format($lines[$i]['od_dc_amount']); ?></td>
        <td class="td_sodr_sum"><?php echo number_format($lines[$i]['receiptamount']); ?></td>
        <td><?php echo number_format($lines[$i]['receiptcancel']); ?></td>
        <td class="td_sodr_nonpay"><?php echo number_format($lines[$i]['misu']); ?></td>
        <td><?php echo $s_receipt_way; ?></td>
        <td class="td_mng">
            <a href="./orderform.php?od_id=<?php echo $lines[$i]['od_id']; ?>&amp;<?php echo $qstr; ?>"><span class="sound_only"><?php echo $lines[$i]['od_id']; ?> </span>수정</a>
            <a href="./orderdelete.php?od_id=<?php echo $lines[$i]['od_id']; ?>&amp;mb_id=<?php echo $lines[$i]['mb_id']; ?>&amp;<?php echo $qstr; ?>" onclick="return delete_confirm();"><span class="sound_only"><?php echo $lines[$i]['od_id']; ?> </span>삭제</a>
        </td>
    </tr>
    <?php
    }
    mysql_free_result($result);
    if ($i == 0)
        echo '<tr><td colspan="11" class="empty_table">자료가 없습니다.</td></tr>';
    ?>
    </tbody>
    </table>
</section>

<?php echo get_paging(G4_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['PHP_SELF']}?$qstr&amp;page="); ?>

<?php
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>
