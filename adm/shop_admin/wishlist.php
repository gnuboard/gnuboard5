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

$qstr = 'page='.$page.'&amp;sort1='.$sort1.'&amp;sort2='.$sort2;
$qstr1 = 'fr_date='.$fr_date.'&amp;to_date='.$to_date.'&amp;sel_ca_id='.$sel_ca_id;

$listall = '';
if ($search) // 검색렬일 때만 처음 버튼을 보여줌
    $listall = '<a href="'.$_SERVER['PHP_SELF'].'">전체목록</a>';
?>

<form name="flist">
<input type="hidden" name="doc" value="<?=$doc ?>">
<input type="hidden" name="sort1" value="<?=$sort1 ?>">
<input type="hidden" name="sort2" value="<?=$sort2 ?>">
<input type="hidden" name="page" value="<?=$page ?>">

<fieldset>
    <legend>보관함현황 검색</legend>

    <span>
        <?=$listall?>
        전체 보관함 내역 <?=$total_count ?>건
    </span>

    <label for="sel_ca_id" class="sound_only">검색대상</label>
    <? // ##### // 웹 접근성 취약 지점 시작 - 지운아빠 2013-04-18 ?>
    <select name="sel_ca_id" id="sel_ca_id">
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
    <? // ##### // 웹 접근성 취약 지점 끝 ?>
    기간설정
    <label for="fr_date" class="sound_only">기간 시작일</label>
    <input type="text" name="fr_date" value="<?=$fr_date?>" id="fr_date" class="frm_input" size="8" maxlength="8"> 부터
    <label for="to_date" class="sound_only">기간 종료일</label>
    <input type="text" name="to_date" value="<?=$to_date?>" id="to_date" class="frm_input" size="8" maxlength="8"> 까지
    <input type="submit" value="검색" class="btn_submit">
</fieldset>

</form>

<section class="cbox">
    <h2>보관함 현황</h2>
    <p>고객님들이 보관함에 가장 많이 넣은 순으로 순위를 출력합니다.</p>

    <table>
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
        // $s_mod = icon("수정", "./itemqaform.php?w=u&iq_id={$row['iq_id']}&$qstr");
        // $s_del = icon("삭제", "javascript:del('./itemqaupdate.php?w=d&iq_id={$row['iq_id']}&$qstr');");

        $href = G4_SHOP_URL.'/item.php?it_id='.$row['it_id'];
        $num = $rank + $i + 1;
    ?>
    <tr>
        <td class="td_num"><?=$num?></td>
        <td><a href="<?=$href?>"><?=get_it_image($row['it_id'].'_s', 50, 50)?><?=cut_str($row['it_name'],30)?></a></td>
        <td class="td_num"><?=$row['it_id_cnt']?></td>
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
