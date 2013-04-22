<?
$sub_menu = '400410';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g4['title'] = '주문개별내역';
if ($sel_field == 'ct_status') $g4['title'] .= ' ('.$search.')';
include_once (G4_ADMIN_PATH.'/admin.head.php');

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

$sql_common = " from {$g4['shop_order_table']} a
                          left join {$g4['shop_cart_table']} b on (a.uq_id = b.uq_id)
                          left join {$g4['shop_item_table']} c on (b.it_id = c.it_id)
                          $sql_search ";

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
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

$lines = array();
for ($i=0; $row=sql_fetch_array($result); $i++)
{
    $lines[$i] = $row;

    $tot_amount += $row['ct_amount'];
    $tot_qty    += $row['ct_qty'];
    $tot_sub_amount += $row['ct_sub_amount'];
    $tot_sub_point  += $row['ct_sub_point'];
}

$qstr1 = "sel_ca_id=$sel_ca_id&amp;sel_field=$sel_field&amp;search=$search&amp;save_search=$search";
$qstr  = "$qstr1&amp;sort1=$sort1&amp;sort2=$sort2&amp;page=$page";

$listall = '';
if ($search) // 검색렬일 때만 처음 버튼을 보여줌
    $listall = '<a href="'.$_SERVER['PHP_SELF'].'">전체목록</a>';
?>

<form name="frmorderlist">
<input type="hidden" name="doc" value="<?=$doc ?>">
<input type="hidden" name="sort1" value="<?=$sort1 ?>">
<input type="hidden" name="page" value="<?=$page ?>">
<input type="hidden" name="save_search" value="<?=$search?>">
<fieldset>
    <legend>주문상태별 검색</legend>
    <span>
        <?=$listall?>
        전체 주문내역 <?=$total_count ?>건
    </span>

    <ul class="anchor">
        <li><a href="<?=$_SERVER['PHP_SELF'].'?sort1='.$sort1.'&amp;sort2='.$sort2.'&amp;sel_field=ct_status&amp;search='.urlencode("준비")?>">준비</a></li>
        <li><a href="<?=$_SERVER['PHP_SELF'].'?sort1='.$sort1.'&amp;sort2='.$sort2.'&amp;sel_field=ct_status&amp;search='.urlencode("주문")?>">주문</a></li>
        <li><a href="<?=$_SERVER['PHP_SELF'].'?sort1='.$sort1.'&amp;sort2='.$sort2.'&amp;sel_field=ct_status&amp;search='.urlencode("배송")?>">배송</a></li>
        <li><a href="<?=$_SERVER['PHP_SELF'].'?sort1='.$sort1.'&amp;sort2='.$sort2.'&amp;sel_field=ct_status&amp;search='.urlencode("완료")?>">완료</a></li>
        <li><a href="<?=$_SERVER['PHP_SELF'].'?sort1='.$sort1.'&amp;sort2='.$sort2.'&amp;sel_field=ct_status&amp;search='.urlencode("취소")?>">취소</a></li>
        <li><a href="<?=$_SERVER['PHP_SELF'].'?sort1='.$sort1.'&amp;sort2='.$sort2.'&amp;sel_field=ct_status&amp;search='.urlencode("반품")?>">반품</a></li>
        <li><a href="<?=$_SERVER['PHP_SELF'].'?sort1='.$sort1.'&amp;sort2='.$sort2.'&amp;sel_field=ct_status&amp;search='.urlencode("품절")?>">품절</a></li>
    </ul>

    <label for="sel_field" class="sound_only">검색대상</label>
    <select name="sel_field" id="sel_field">
        <option value="od_id" <?=get_selected($sel_field, 'od_id')?>>주문번호</option>
        <option value="od_name" <?=get_selected($sel_field, 'od_name')?>>주문자</option>
        <option value="mb_id" <?=get_selected($sel_field, 'mb_id')?>>회원 ID</option>
        <option value="od_deposit_name" <?=get_selected($sel_field, 'od_deposit_name')?>>입금자</option>
        <option value="c.it_id" <?=get_selected($sel_field, 'c,it_id')?>>상품코드</option>
        <option value="c.ca_id" <?=get_selected($sel_field, 'c.ca_id')?>>분류코드</option>
        <option value="ct_status" <?=get_selected($sel_field, 'ct_status')?>>상태</option>
    </select>
    <label for="search" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
    <input type="text" name="search" value="<?=$search ?>" id="search" required class="required frm_input" autocomplete="off">
    <input type="submit" value="검색" class="btn_submit">
</fieldset>
</form>

<section class="cbox">
    <h2><?=$g4['title']?> 목록</h2>

    <div id="btn_add">
        <a href="./orderprint.php" class="btn_add_optional">주문내역출력</a>
        <a href="./ordercardhistory.php" class="btn_add_optional">전자결제내역</a>
    </div>

    <table id="sodr_status">
    <thead>
    <tr>
        <th scope="col"><a href="<?=title_sort("od_id")."&amp;$qstr1";?>">주문번호<span class="sound_only"> 순 정렬</span></a><br>주문일시</th>
        <th scope="col"><a href="<?=title_sort("it_name")."&amp;$qstr1";?>">상품명<span class="sound_only"> 순 정렬</span></a></th>
        <th scope="col"><a href="<?=title_sort("od_name")."&amp;$qstr1";?>">주문자<span class="sound_only"> 순 정렬</span></a><br>입금자</th>
        <th scope="col"><a href="<?=title_sort("mb_id")."&amp;$qstr1";?>">회원ID<span class="sound_only"> 순 정렬</span></a></th>
        <th scope="col"><a href="<?=title_sort("ct_amount")."&amp;$qstr1";?>">판매가<span class="sound_only"> 순 정렬</span></a></th>
        <th scope="col"><a href="<?=title_sort("ct_qty")."&amp;$qstr1";?>">수량<span class="sound_only"> 순 정렬</span></a></th>
        <th scope="col"><a href="<?=title_sort("ct_sub_amount")."&amp;$qstr1";?>">소계<span class="sound_only"> 순 정렬</span></a></th>
        <th scope="col"><a href="<?=title_sort("ct_sub_point")."&amp;$qstr1";?>">포인트<span class="sound_only"> 순 정렬</span></a></th>
        <th scope="col"><a href="<?=title_sort("ct_status")."&amp;$qstr1";?>">상태<span class="sound_only"> 순 정렬</span></a></th>
        <th scope="col">수정</th>
    </tr>
    </thead>
    <tfoot>
    <tr>
        <th scope="row" colspan="4">합 계</td>
        <td><?=number_format($tot_amount)?></td>
        <td><?=number_format($tot_qty)?></td>
        <td><?=number_format($tot_sub_amount)?></td>
        <td><?=number_format($tot_sub_point)?></td>
        <td colspan="2"></td>
    </tr>
    </tfoot>
    <tbody>
    <?
    for ($i=0; $i<count($lines); $i++) {

        $href = $_SERVER['PHP_SELF'].'?sort1='.$sort1.'&amp;sort2='.$sort2.'&amp;sel_field=c.it_id&amp;search='.$lines[$i]['it_id'];
        $it_name = '<a href="'.$href.'">'.cut_str($lines[$i]['it_name'],35).'</a><br>';
        $it_name .= print_item_options($lines[$i]['it_id'], $lines[$i]['it_opt1'], $lines[$i]['it_opt2'], $lines[$i]['it_opt3'], $lines[$i]['it_opt4'], $lines[$i]['it_opt5'], $lines[$i]['it_opt6']);

        $s_mod = icon("수정", "");
    ?>
    <tr>
        <td class="td_odrnum2">
            <a href="<?=$_SERVER['PHP_SELF']?>?sort1=<?=$sort1?>&amp;sort2=<?=$sort2?>&amp;sel_field=od_id&amp;search=<?=$lines[$i]['od_id']?>"><?=$lines[$i]['od_id']?></a><br>
            <?=$lines[$i]['od_time']?>
        </td>
        <td class="td_it_img"><a href="<?=$href?>"><?=get_it_image($lines[$i]['it_id'].'_s', 50, 50)?><?=$it_name?></a></td>
        <td class="td_name">
            <a href="<?=$_SERVER['PHP_SELF']?>?sort1=<?=$sort1?>&amp;sort2=<?=$sort2?>&amp;sel_field=od_name&amp;search=<?=$lines[$i]['od_name']?>"><?=cut_str($lines[$i]['od_name'],10,"")?></a>
            <? if ($lines[$i]['od_deposit_name'] != "") echo '<br>'.$lines[$i]['od_deposit_name']?>
        </td>
        <td class="td_name"><a href="<?=$_SERVER['PHP_SELF']?>?sort1=<?=$sort1?>&amp;sort2=<?=$sort2?>&amp;sel_field=mb_id&amp;search=<?=$lines[$i]['mb_id']?>"><?=$lines[$i]['mb_id']?></a></td>
        <td><?=number_format($lines[$i]['ct_amount'])?></td>
        <td><?=$lines[$i]['ct_qty']?></td>
        <td><?=number_format($lines[$i]['ct_sub_amount'])?></td>
        <td><?=number_format($lines[$i]['ct_sub_point'])?></td>
        <td><a href="<?=$_SERVER['PHP_SELF']?>?sort1=<?=$sort1?>&amp;sort2=<?=$sort2?>&amp;sel_field=ct_status&amp;search=<?=$lines[$i]['ct_status']?>"><?=$lines[$i]['ct_status']?></a></td>
        <td><a href="./orderform.php?od_id=<?=$lines[$i]['od_id']?>">수정</a></td>
    </tr>
    <?
    }

    if ($i == 0) echo '<tr><td colspan="11" class="empty_table">자료가 한건도 없습니다.</td></tr>';
    ?>
    </tbody>
    </table>

</section>

<?=get_paging($config['cf_write_pages'], $page, $total_page, "{$_SERVER['PHP_SELF']}?$qstr&amp;page=");?>

<?
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>
