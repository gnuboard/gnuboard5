<?
$sub_menu = '500100';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g4['title'] = '상품판매순위';
include_once (G4_ADMIN_PATH.'/admin.head.php');

if (!$to_date) $to_date = date("Ymd", time());

if ($sort1 == "") $sort1 = "ct_status_sum";
if ($sort2 == "") $sort2 = "desc";

$sql  = " select a.it_id,
                 b.*,
                 SUM(IF(ct_status = '쇼핑',ct_qty, 0)) as ct_status_1,
                 SUM(IF(ct_status = '주문',ct_qty, 0)) as ct_status_2,
                 SUM(IF(ct_status = '준비',ct_qty, 0)) as ct_status_3,
                 SUM(IF(ct_status = '배송',ct_qty, 0)) as ct_status_4,
                 SUM(IF(ct_status = '완료',ct_qty, 0)) as ct_status_5,
                 SUM(IF(ct_status = '취소',ct_qty, 0)) as ct_status_6,
                 SUM(IF(ct_status = '반품',ct_qty, 0)) as ct_status_7,
                 SUM(IF(ct_status = '품절',ct_qty, 0)) as ct_status_8,
                 SUM(ct_qty) as ct_status_sum
            from {$g4['shop_cart_table']} a, {$g4['shop_item_table']} b ";
$sql .= " where a.it_id = b.it_id ";
if ($fr_date && $to_date)
{
    $fr = preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})/", "\\1-\\2-\\3", $fr_date);
    $to = preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})/", "\\1-\\2-\\3", $to_date);
    $sql .= " and ct_time between '$fr 00:00:00' and '$to 23:59:59' ";
}
if ($sel_ca_id)
{
    $sql .= " and b.ca_id like '$sel_ca_id%' ";
}
$sql .= " group by a.it_id
          order by $sort1 $sort2 ";
$result = sql_query($sql);
$total_count = mysql_num_rows($result);

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$rank = ($page - 1) * $rows;

$sql = $sql . " limit $from_record, $rows ";
$result = sql_query($sql);

//$qstr = "page=$page&sort1=$sort1&sort2=$sort2";
$qstr1 = "$qstr&sort1=$sort1&sort2=$sort2&fr_date=$fr_date&to_date=$to_date&sel_ca_id=$sel_ca_id";
?>

<style type="text/css">
    .itemsellrank{text-align:center}
</style>

<form name="flist">
<input type="hidden" name="doc" value="<? echo $doc ?>">
<input type="hidden" name="sort1" value="<? echo $sort1 ?>">
<input type="hidden" name="sort2" value="<? echo $sort2 ?>">
<input type="hidden" name="page" value="<? echo $page ?>">
<p><a href='<?=$_SERVER['PHP_SELF']?>'>처음</a></p>
<fieldset>
<legend>상품판매순위 검색</legend>
<select name="sel_ca_id" title="검색분류">
    <option value=''>전체분류</option>
    <?
    $sql1 = " select ca_id, ca_name from {$g4['shop_category_table']} order by ca_id ";
    $result1 = sql_query($sql1);
    for ($i=0; $row1=mysql_fetch_array($result1); $i++) {
        $len = strlen($row1['ca_id']) / 2 - 1;
        $nbsp = "";
        for ($i=0; $i<$len; $i++) $nbsp .= "&nbsp;&nbsp;&nbsp;";
        echo "<option value='{$row1['ca_id']}'>$nbsp{$row1['ca_name']}\n";
    }
    ?>
</select>
<script> document.flist.sel_ca_id.value = '<?=$sel_ca_id?>';</script>

기간 : <input type="text" name="fr_date" value="<?=$fr_date?>" class="frm_input" size="8" maxlength="8"> ~ <input type="text" name="to_date" value="<?=$to_date?>" class="frm_input" size="8" maxlength="8">
<input type="submit" value="검색" class="btn_submit">
</fieldset>
</form>
<p>건수 : <? echo $total_count ?></p>
<section class="cbox">
    <h2>상품판매순위</h2>
    <p>*수량을 합산하여 순위를 출력합니다.</p>
    <table class="frm_basic">
    <colgroup>
        <col class="grid_1">
        <col class="grid_8">
        <col class="grid_1">
        <col class="grid_1">
        <col class="grid_1">
        <col class="grid_1">
        <col class="grid_1">
        <col class="grid_1">
        <col class="grid_1">
        <col class="grid_1">
        <col class="grid_1">
    </colgroup>
    <thead>
    <tr>
        <th scope="col">순위</th>
        <th scope="col">상품평</th>
        <th scope="col"><a href="<?=title_sort("ct_status_1",1)."&$qstr1"?>">쇼핑</a></th>
        <th scope="col"><a href="<?=title_sort("ct_status_2",1)."&$qstr1"?>">주문</a></th>
        <th scope="col"><a href="<?=title_sort("ct_status_3",1)."&$qstr1"?>">준비</a></th>
        <th scope="col"><a href="<?=title_sort("ct_status_4",1)."&$qstr1"?>">배송</a></th>
        <th scope="col"><a href="<?=title_sort("ct_status_5",1)."&$qstr1"?>">완료</a></th>
        <th scope="col"><a href="<?=title_sort("ct_status_6",1)."&$qstr1"?>">취소</a></th>
        <th scope="col"><a href="<?=title_sort("ct_status_7",1)."&$qstr1"?>">반품</a></th>
        <th scope="col"><a href="<?=title_sort("ct_status_8",1)."&$qstr1"?>">품절</a></th>
        <th scope="col"><a href="<?=title_sort("ct_status_sum",1)."&$qstr1"?>">합계</a></th>
    </tr>
    </thead>
    <tbody>
    <?
    for ($i=0; $row=mysql_fetch_array($result); $i++)
    {
        $href = G4_SHOP_URL."/item.php?it_id={$row['it_id']}";

        $num = $rank + $i + 1;

        $list = $i%2;
        ?>
        <tr>
            <td class="itemsellrank"><?=$num?></td>
            <td><a href="<?=$href?>"><?=get_it_image($row['it_id'].'_s', 50, 50)?><?=cut_str($row['it_name'],30)?></a></td>
            <td class="itemsellrank"><?=$row['ct_status_1']?></td>
            <td class="itemsellrank"><?=$row['ct_status_2']?></td>
            <td class="itemsellrank"><?=$row['ct_status_3']?></td>
            <td class="itemsellrank"><?=$row['ct_status_4']?></td>
            <td class="itemsellrank"><?=$row['ct_status_5']?></td>
            <td class="itemsellrank"><?=$row['ct_status_6']?></td>
            <td class="itemsellrank"><?=$row['ct_status_7']?></td>
            <td class="itemsellrank"><?=$row['ct_status_8']?></td>
            <td class="itemsellrank"><?=$row['ct_status_sum']?></td>
        </tr>
        <?
    }

    if ($i == 0) {
        echo '<tr><td colspan="20" class="empty_table"><span>자료가 한건도 없습니다.</span></td></tr>';
    }
    ?>
    </tbody>
    </table>
    <?=get_paging($config['cf_write_pages'], $page, $total_page, "{$_SERVER['PHP_SELF']}?$qstr1&page=");?>
</section>


<?
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>
