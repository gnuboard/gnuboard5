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

$qstr1 = "sel_ca_id=$sel_ca_id&sel_field=$sel_field&search=$search";
$qstr  = "$qstr1&sort1=$sort1&sort2=$sort2&page=$page";
?>

<style type="text/css">
.ordercardhistory{text-align:center}
</style>

<form name="flist">
<input type="hidden" name="sort1" value="<? echo $sort1 ?>">
<input type="hidden" name="page" value="<? echo $page ?>">
<p><a href="<?=$_SERVER['PHP_SELF']?>">처음</a></p>
<fieldset>
<legend>전자결제내역 검색</legend>
<select name="sel_field" title="검색대상">
    <option value="a.od_id">주문번호</option>
    <option value="cd_app_no">승인번호</option>
    <option value="cd_opt01">결제자</option>
</select>
<? if ($sel_field) echo "<script> document.flist.sel_field.value = '$sel_field';</script>"; ?>
<input type="text" name="search" value="<? echo $search ?>" class="frm_input" autocomplete="off">
<input type="submit" value="검색" class="btn_submit">
</fieldset>
<p>건수 : <? echo $total_count ?></p>

<section class="cbox">
    <h2>전자결제내역</h2>
    <p>*신용카드, 실시간 계좌이체로 승인한 내역이며, 주문번호를 클릭하시면 주문상세 페이지로 이동합니다.</p>
    <table class="frm_basic">
    <colgroup>
        <col class="grid_2">
        <col class="grid_8">
        <col class="grid_2">
        <col class="grid_2">
        <col class="grid_2">
        <col class="grid_2">
    </colgroup>
    <thead>
    <tr>
        <th scope="col"><a href="<? echo title_sort("od_id") . "&$qstr1"; ?>">주문번호</a></th>
        <th scope="col"><a href="<? echo title_sort("cd_amount") . "&$qstr1"; ?>">승인금액</a></th>
        <th scope="col"><a href="<? echo title_sort("cd_app_no") . "&$qstr1"; ?>">승인번호</a></th>
        <th scope="col"><a href="<? echo title_sort("cd_app_rt") . "&$qstr1"; ?>">승인결과</a></th>
        <th scope="col"><a href="<? echo title_sort("cd_app_time") . "&$qstr1"; ?>">승인일시</a></th>
        <th scope="col"><a href="<? echo title_sort("cd_opt01") . "&$qstr1"; ?>">결제자</a></th>
    </tr>
    </thead>
    <tbody>
    <?
    for ($i=0; $row=sql_fetch_array($result); $i++)
    {
        $list = $i%2;
    ?>
    <tr>
        <td class="ordercardhistory"><a href="./orderform.php?od_id=<?=$row['od_id']?>"><?=$row['od_id']?></a></td>
        <td><?=display_amount($row['cd_amount'])?></td>
        <td class="ordercardhistory"><?=$row['cd_app_no']?></td>
        <td class="ordercardhistory"><?=$row['cd_app_rt']?></td>
        <td class="ordercardhistory"><?=$row['cd_app_time']?></td>
        <td class="ordercardhistory"><?=$row['cd_opt01']?></td>
    </tr>
    <?
    }

    if ($i == 0)
        echo '<tr><td colspan="6" class="empty_table"><span>자료가 한건도 없습니다.</span></td></tr>'
    ?>
    </tbody>
    </table>
    <?=get_paging($config['cf_write_pages'], $page, $total_page, "{$_SERVER['PHP_SELF']}?$qstr&page=");?>
</section>
</form>


<?
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>
