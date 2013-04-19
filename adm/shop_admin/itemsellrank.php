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

//$qstr = 'page='.$page.'&amp;sort1='.$sort1.'&amp;sort2='.$sort2;
$qstr1 = $qstr.'&amp;sort1='.$sort1.'&amp;sort2='.$sort2.'&amp;fr_date='.$fr_date.'&amp;to_date='.$to_date.'&amp;sel_ca_id='.$sel_ca_id;

$listall = '';
if ($fr_date || $to_date) // 검색렬일 때만 처음 버튼을 보여줌
    $listall = '<a href="'.$_SERVER['PHP_SELF'].'">전체목록</a>';
?>

<form name="flist">
<input type="hidden" name="doc" value="<?=$doc?>">
<input type="hidden" name="sort1" value="<?=$sort1?>">
<input type="hidden" name="sort2" value="<?=$sort2?>">
<input type="hidden" name="page" value="<?=$page?>">

<fieldset>
    <legend>상품판매순위 검색</legend>

    <span>
        <?=$listall?>
        등록상품 <?=$total_count ?>건
    </span>

    <label for="sel_ca_id" class="sound_only">검색대상</label>
    <? // ##### // 웹 접근성 취약 지점 시작 - 지운아빠 2013-04-17 ?>
    <select name="sel_ca_id" id="sel_ca_id">
        <option value=''>전체분류</option>
        <?
        $sql1 = " select ca_id, ca_name from {$g4['shop_category_table']} order by ca_id ";
        $result1 = sql_query($sql1);
        for ($i=0; $row1=mysql_fetch_array($result1); $i++) {
            $len = strlen($row1['ca_id']) / 2 - 1;
            $nbsp = "";
            for ($i=0; $i<$len; $i++) $nbsp .= "&nbsp;&nbsp;&nbsp;";
            echo '<option value="'.$row1['ca_id'].'" '.get_selected($sel_ca_id, $row1['ca_id']).'>'.$nbsp.$row1['ca_name'].'</option>'.PHP_EOL;
        }
        ?>
    </select>
    <? // ##### // 웹 접근성 취약 지점 끝 ?>

    기간설정
    <input type="text" name="fr_date" value="<?=$fr_date?>" class="frm_input" size="8" maxlength="8"> 에서 <input type="text" name="to_date" value="<?=$to_date?>" class="frm_input" size="8" maxlength="8"> 까지
<input type="submit" value="검색" class="btn_submit">
</fieldset>
</form>

<section class="cbox">
    <h2>상품판매순위</h2>
    <p>판매량을 합산하여 상품판매순위를 집계합니다.</p>

    <div id="btn_add">
        <a href="./itemlist.php" class="btn_add_optional">상품등록</a>
        <a href="./itemstocklist.php" class="btn_add_optional">상품재고관리</a>
    </div>

    <table>
    <thead>
    <tr>
        <th scope="col">순위</th>
        <th scope="col">상품평</th>
        <th scope="col"><a href="<?=title_sort("ct_status_1",1)."&amp;$qstr1"?>">쇼핑<span class="sound_only"> 순 정렬</span></a></th>
        <th scope="col"><a href="<?=title_sort("ct_status_2",1)."&amp;$qstr1"?>">주문<span class="sound_only"> 순 정렬</span></a></th>
        <th scope="col"><a href="<?=title_sort("ct_status_3",1)."&amp;$qstr1"?>">준비<span class="sound_only"> 순 정렬</span></a></th>
        <th scope="col"><a href="<?=title_sort("ct_status_4",1)."&amp;$qstr1"?>">배송<span class="sound_only"> 순 정렬</span></a></th>
        <th scope="col"><a href="<?=title_sort("ct_status_5",1)."&amp;$qstr1"?>">완료<span class="sound_only"> 순 정렬</span></a></th>
        <th scope="col"><a href="<?=title_sort("ct_status_6",1)."&amp;$qstr1"?>">취소<span class="sound_only"> 순 정렬</span></a></th>
        <th scope="col"><a href="<?=title_sort("ct_status_7",1)."&amp;$qstr1"?>">반품<span class="sound_only"> 순 정렬</span></a></th>
        <th scope="col"><a href="<?=title_sort("ct_status_8",1)."&amp;$qstr1"?>">품절<span class="sound_only"> 순 정렬</span></a></th>
        <th scope="col"><a href="<?=title_sort("ct_status_sum",1)."&amp;$qstr1"?>">합계<span class="sound_only"> 순 정렬</span></a></th>
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
            <td class="td_num"><?=$num?></td>
            <td><a href="<?=$href?>"><?=get_it_image($row['it_id'].'_s', 50, 50)?><?=cut_str($row['it_name'],30)?></a></td>
            <td class="td_smallnum"><?=$row['ct_status_1']?></td>
            <td class="td_smallnum"><?=$row['ct_status_2']?></td>
            <td class="td_smallnum"><?=$row['ct_status_3']?></td>
            <td class="td_smallnum"><?=$row['ct_status_4']?></td>
            <td class="td_smallnum"><?=$row['ct_status_5']?></td>
            <td class="td_smallnum"><?=$row['ct_status_6']?></td>
            <td class="td_smallnum"><?=$row['ct_status_7']?></td>
            <td class="td_smallnum"><?=$row['ct_status_8']?></td>
            <td class="td_smallnum"><?=$row['ct_status_sum']?></td>
        </tr>
        <?
    }

    if ($i == 0) {
        echo '<tr><td colspan="20" class="empty_table">자료가 없습니다.</td></tr>';
    }
    ?>
    </tbody>
    </table>
</section>

<?=get_paging($config['cf_write_pages'], $page, $total_page, "{$_SERVER['PHP_SELF']}?$qstr1&amp;page=");?>


<?
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>
