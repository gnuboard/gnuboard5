<?
$sub_menu = '500140';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g4['title'] = '보관함현황';
include_once (G4_ADMIN_PATH.'/admin.head.php');

if (!$to_date) $to_date = date("Ymd", time());

if ($sort1 == "") $sort1 = "it_id_cnt";
if ($sort2 == "") $sort2 = "desc";

$sql  = " select a.it_id,
                 b.it_name,
                 COUNT(a.it_id) as it_id_cnt
            from {$g4['shop_wish_table']} a, {$g4['shop_item_table']} b ";
$sql .= " where a.it_id = b.it_id ";
if ($fr_date && $to_date)
{
    $fr = preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})/", "\\1-\\2-\\3", $fr_date);
    $to = preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})/", "\\1-\\2-\\3", $to_date);
    $sql .= " and a.wi_time between '$fr 00:00:00' and '$to 23:59:59' ";
}
if ($sel_ca_id)
{
    $sql .= " and b.ca_id like '$sel_ca_id%' ";
}
$sql .= " group by a.it_id, b.it_name
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

$qstr = "page=$page&sort1=$sort1&sort2=$sort2";
$qstr1 = "fr_date=$fr_date&to_date=$to_date&sel_ca_id=$sel_ca_id";
?>
<style type="text/css">
    .wishlist{text-align:center}
</style>


<form name="flist">
<input type="hidden" name="doc" value="<? echo $doc ?>">
<input type="hidden" name="sort1" value="<? echo $sort1 ?>">
<input type="hidden" name="sort2" value="<? echo $sort2 ?>">
<input type="hidden" name="page" value="<? echo $page ?>">

<p><a href="<?=$_SERVER['PHP_SELF']?>">처음</a></p>
<fieldset>
<legend>보관함현황 검색</legend>
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
기간 : 
<input type="text" name="fr_date" value="<?=$fr_date?>" class="frm_input" size="8" maxlength="8">
~ 
<input type="text" name="to_date" value="<?=$to_date?>" class="frm_input" size="8" maxlength="8">
<input type="submit" value="검색" class="btn_submit">
</fieldset>
<p>건수 : <? echo $total_count ?></p>
</form>

<section class="cbox">
    <h2>보관함현황</h2>
    <p> *수량을 합산하여 순위를 출력합니다.</p>
    <table class="frm_basic">
    <colgroup>
        <col class="grid_2">
        <col class="gird_14">
        <col class="grid_2">
    </colgroup>
    <thead>
    <tr>
        <th scope="col">순위</th>
        <th scope="col">상품평</th>
        <th scope="col">건수</th>
    </tr>
    </thead>
    <tbody>
    <?
    for ($i=0; $row=mysql_fetch_array($result); $i++)
    {
        $s_mod = icon("수정", "./itemqaform.php?w=u&iq_id={$row['iq_id']}&$qstr");
        $s_del = icon("삭제", "javascript:del('./itemqaupdate.php?w=d&iq_id={$row['iq_id']}&$qstr');");

        $href = G4_SHOP_URL."/item.php?it_id={$row['it_id']}";

        $num = $rank + $i + 1;

        $list = $i%2;
        ?>
        <tr>
            <td class="wishlist"><?=$num?></td>
            <td><a href="<?=$href?>"><?=get_it_image($row['it_id'].'_s', 50, 50)?><?=cut_str($row['it_name'],30)?></a></td>
            <td class="wishlist"><?=$row[it_id_cnt]?></td>
        </tr>
        <?
    }

    if ($i == 0) {
        echo '<tr><td colspan="20" class="empty_table"><span>자료가 한건도 없습니다.</span></td></tr>';
    }
    ?>
    </tbody>
    </table>
    <?=get_paging($config['cf_write_pages'], $page, $total_page, "{$_SERVER['PHP_SELF']}?$qstr&page=");?>
</section>

<?
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>
