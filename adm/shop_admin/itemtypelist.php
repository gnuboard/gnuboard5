<?
$sub_menu = '400610';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g4['title'] = '상품유형관리';
include_once (G4_ADMIN_PATH.'/admin.head.php');

/*
$sql_search = " where 1 ";
if ($search != "") {
	if ($sel_field != "") {
    	$sql_search .= " and $sel_field like '%$search%' ";
    }
}

if ($sel_ca_id != "") {
    $sql_search .= " and (ca_id like '$sel_ca_id%' or ca_id2 like '$sel_ca_id%' or ca_id3 like '$sel_ca_id%') ";
}

if ($sel_field == "")  $sel_field = "it_name";
*/

$where = " where ";
$sql_search = "";
if ($stx != "") {
    if ($sfl != "") {
        $sql_search .= " $where $sfl like '%$stx%' ";
        $where = " and ";
    }
    if ($save_stx != $stx)
        $page = 1;
}

if ($sca != "") {
    $sql_search .= " $where (ca_id like '$sca%' or ca_id2 like '$sca%' or ca_id3 like '$sca%') ";
}

if ($sfl == "")  $sfl = "it_name";

if (!$sst)  {
    $sst  = "it_id";
    $sod = "desc";
}
$sql_order = "order by $sst $sod";

$sql_common = "  from {$g4['shop_item_table']} ";
$sql_common .= $sql_search;

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql  = " select it_id,
                 it_name,
                 it_type1,
                 it_type2,
                 it_type3,
                 it_type4,
                 it_type5
          $sql_common
          $sql_order
          limit $from_record, $rows ";
$result = sql_query($sql);

//$qstr1 = "sel_ca_id=$sel_ca_id&sel_field=$sel_field&search=$search";
//$qstr  = "$qstr1&sort1=$sort1&sort2=$sort2&page=$page";
$qstr  = "$qstr&sca=$sca&page=$page&save_stx=$stx";
?>
<style type="text/css">
    .itemtypelist{text-align:center}
</style>

    <form name="flist">
    <fieldset>
        <legend>게시판 검색</legend>
        <input type="hidden" name="doc" value="<? echo $doc ?>">
        <input type="hidden" name="sort1" value="<? echo $sort1 ?>">
        <input type="hidden" name="sort2" value="<? echo $sort2 ?>">
        <input type="hidden" name="page" value="<? echo $page ?>">
        <p><a href="<?=$_SERVER['PHP_SELF']?>">처음</a></p>
        <select name="sca" title="검색분류">
            <option value="">전체분류</option>
            <?
            $sql1 = " select ca_id, ca_name from {$g4['shop_category_table']} order by ca_id ";
            $result1 = sql_query($sql1);
            for ($i=0; $row1=sql_fetch_array($result1); $i++) {
                $len = strlen($row1['ca_id']) / 2 - 1;
                $nbsp = "";
                for ($i=0; $i<$len; $i++) $nbsp .= "&nbsp;&nbsp;&nbsp;";
                echo "<option value='{$row1['ca_id']}'>$nbsp{$row1['ca_name']}\n";
            }
            ?>
        </select>
        <script> document.flist.sca.value = '<?=$sca?>';</script>
        <select name="sfl" title="검색대상">
            <option value='it_name'>상품명</option>
            <option value='it_id'>상품코드</option>
        </select>
        <? if ($slf) echo "<script> document.flist.slf.value = '$sfl';</script>"; ?>
        <input type="text" name="stx" value="<? echo $stx ?>" title="검색어">
        <input type="submit" value="검색" class="btn_submit">
    </fieldset>
    <p>건수 : <? echo $total_count ?></p>
    </form>

<section class="cbox">
    <h2>상품유형관리</h2>
    <p>*상품의 유형을 일괄처리합니다.</p>
    <form name="fitemtypelist" method="post" action="./itemtypelistupdate.php">
    <input type="hidden" name="sca" value="<?=$sca?>">
    <input type="hidden" name="sst" value="<?=$sst?>">
    <input type="hidden" name="sod" value="<?=$sod?>">
    <input type="hidden" name="sfl" value="<?=$sfl?>">
    <input type="hidden" name="stx" value="<?=$stx?>">
    <input type="hidden" name="page" value="<?=$page?>">
    <table class="frm_basic">
    <colgroup>
        <col class="grid_2">
        <col class="grid_9">
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
        <th scope="col"><?=subject_sort_link("it_id", $qstr, 1)?>상품<br>코드</a></th>
        <th scope="col"><?=subject_sort_link("it_name")?>상품명</a></th>
        <th scope="col"><?=subject_sort_link("it_type1", $qstr, 1)?>히트<br>상품</a></th>
        <th scope="col"><?=subject_sort_link("it_type2", $qstr, 1)?>추천<br>상품</a></th>
        <th scope="col"><?=subject_sort_link("it_type3", $qstr, 1)?>신규<br>상품</a></th>
        <th scope="col"><?=subject_sort_link("it_type4", $qstr, 1)?>인기<br>상품</a></th>
        <th scope="col"><?=subject_sort_link("it_type5", $qstr, 1)?>할인<br>상품</a></th>
        <th scope="col">관리</th>
    </tr>
    </thead>
    <tbody>
        <?
        for ($i=0; $row=sql_fetch_array($result); $i++)
        {
            $href = G4_SHOP_URL."/item.php?it_id={$row['it_id']}";

            $s_mod = icon("수정", "./itemform.php?w=u&it_id={$row['it_id']}&ca_id={$row['ca_id']}&$qstr");

            $list = $i%2;
            ?>
            <input type="hidden" name="it_id[$i]" value="<?=$row['it_id']?>">
            <tr>
                <td class="itemtypelist"><?=$row['it_id']?></td>
                <td><a href="<?=$href?>"><?=get_it_image($row['it_id'].'_s', 50, 50)?><?=cut_str(stripslashes($row['it_name']), 60, "&#133")?></a></td>
                <td class="itemtypelist"><input type="checkbox" name="it_type1[$i]" value="1" <?=($row['it_type1'] ? 'checked' : '')?> title="히트상품"></td>
                <td class="itemtypelist"><input type="checkbox" name="it_type2[$i]" value="1" <?=($row['it_type2'] ? 'checked' : '')?> title="추천상품"></td>
                <td class="itemtypelist"><input type="checkbox" name="it_type3[$i]" value="1" <?=($row['it_type3'] ? 'checked' : '')?> title="신규상품"></td>
                <td class="itemtypelist"><input type="checkbox" name="it_type4[$i]" value="1" <?=($row['it_type4'] ? 'checked' : '')?> title="인기상품"></td>
                <td class="itemtypelist"><input type="checkbox" name="it_type5[$i]" value="1" <?=($row['it_type5'] ? 'checked' : '')?> title="할인상품"></td>
                <td class="itemtypelist"><a href="./itemform.php?w=u&it_id=<?=$row['it_id']?>&ca_id=<?=$row['ca_id']?>&<?=$qstr?>">수정</a></td>
            </tr>
            <?
        }

        if (!$i)
            echo '<tr><td colspan="9" class="itemtypelist empty_table"><span>자료가 한건도 없습니다.</span></td></tr>';
        ?>
    </tbody>
    <tfoot>

    </tfoot>
    </table>
    </form>
    <div class="btn_list">
        <input type="submit" value="일괄수정">
    </div>
    <?=get_paging($config['cf_write_pages'], $page, $total_page, "{$_SERVER['PHP_SELF']}?$qstr&page=");?>
</section>



<?
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>
