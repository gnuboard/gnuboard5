<?
$sub_menu = '500130';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g4['title'] = '전자결제내역';
include_once (G4_ADMIN_PATH.'/admin.head.php');

sql_query(" ALTER TABLE `{$g4['shop_card_history_table']}` ADD INDEX `od_id` ( `od_id` ) ", false);

$where = " where ";
$sql_search = "";
if ($search != "")
{
	if ($sel_field != "")
    {
    	$sql_search .= " $where $sel_field like '%$search%' ";
        $where = " and ";
    }
}

if ($sel_field == "")  $sel_field = "a.od_id";
if ($sort1 == "") $sort1 = "od_id";
if ($sort2 == "") $sort2 = "desc";

$sql_common = " from {$g4['shop_card_history_table']} a
                left join {$g4['shop_order_table']} b on (a.od_id = b.od_id)
                $sql_search ";

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql  = " select a.*,
                 concat(a.cd_trade_ymd, ' ', a.cd_trade_hms) as cd_app_time
           $sql_common
           order by $sort1 $sort2
           limit $from_record, $rows ";
$result = sql_query($sql);

$qstr1 = 'sel_ca_id='.$sel_ca_id.'&amp;sel_field='.$sel_field.'&amp;search='.$search;
$qstr  = $qstr1.'&amp;sort1='.$sort1.'&amp;sort2='.$sort2.'&amp;page='.$page;

$listall = '';
if ($search) // 검색 결과일 때만 처음 버튼을 보여줌
    $listall = '<a href="'.$_SERVER['PHP_SELF'].'">전체목록</a>';
?>

<form name="flist">
<input type="hidden" name="sort1" value="<?=$sort1?>">
<input type="hidden" name="page" value="<?=$page?>">

<fieldset>
    <legend>전자결제내역 검색</legend>

    <span>
        <?=$listall?>
        전체 전자결제내역 <?=$total_count ?>건
    </span>

    <label for="sel_field" class="sound_only">검색대상</label>
    <select name="sel_field" id="sel_field">
        <option value="a.od_id" <?=get_selected($_GET['sel_field'], 'a.od_id')?>>주문번호</option>
        <option value="cd_app_no" <?=get_selected($_GET['sel_field'], 'cd_app_no')?>>승인번호</option>
        <option value="cd_opt01" <?=get_selected($_GET['sel_field'], 'cd_opt01')?>>결제자</option>
    </select>
    <label for="search" class="sound_only">검색어</label>
    <input type="text" name="search" value="<?=$search?>" id="search" required class="frm_input required" autocomplete="off">
    <input type="submit" value="검색" class="btn_submit">
</fieldset>

</form>

<section class="cbox">
    <h2>전자결제내역</h2>
    <p>신용카드 혹은 실시간 계좌이체로 결제(승인) 된 내역이며, 주문번호를 클릭하시면 주문상세 페이지로 이동합니다.</p>
    <table class="frm_basic">
    <thead>
    <tr>
        <th scope="col"><a href="<?=title_sort("od_id") . "&amp;$qstr1"; ?>">주문번호<span class="sound_only"> 순 정렬</span></a></th>
        <th scope="col"><a href="<?=title_sort("cd_amount") . "&amp;$qstr1"; ?>">승인금액<span class="sound_only"> 순 정렬</span></a></th>
        <th scope="col"><a href="<?=title_sort("cd_app_no") . "&amp;$qstr1"; ?>">승인번호<span class="sound_only"> 순 정렬</span></a></th>
        <th scope="col"><a href="<?=title_sort("cd_app_rt") . "&amp;$qstr1"; ?>">승인결과<span class="sound_only"> 순 정렬</span></a></th>
        <th scope="col"><a href="<?=title_sort("cd_app_time") . "&amp;$qstr1"; ?>">승인일시<span class="sound_only"> 순 정렬</span></a></th>
        <th scope="col"><a href="<?=title_sort("cd_opt01") . "&amp;$qstr1"; ?>">결제자<span class="sound_only"> 순 정렬</span></a></th>
    </tr>
    </thead>
    <tbody>
    <? for ($i=0; $row=sql_fetch_array($result); $i++) { ?>
    <tr>
        <td class="td_odrnum2"><a href="./orderform.php?od_id=<?=$row['od_id']?>"><?=$row['od_id']?></a></td>
        <td><?=display_amount($row['cd_amount'])?></td>
        <td class="td_num"><?=$row['cd_app_no']?></td>
        <td class="td_smallmng"><?=$row['cd_app_rt']?></td>
        <td class="td_time"><?=$row['cd_app_time']?></td>
        <td class="td_smallmng"><?=$row['cd_opt01']?></td>
    </tr>
    <?
    }
    if ($i == 0)
        echo '<tr><td colspan="6" class="empty_table">자료가 없습니다.</td></tr>'
    ?>
    </tbody>
    </table>

</section>

<?=get_paging($config['cf_write_pages'], $page, $total_page, "{$_SERVER['PHP_SELF']}?$qstr&amp;page=");?>

<?
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>
