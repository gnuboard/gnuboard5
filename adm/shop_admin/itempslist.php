<?
$sub_menu = '400650';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g4['title'] = '사용후기';
include_once (G4_ADMIN_PATH.'/admin.head.php');

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
    $sql_search .= " and ca_id like '$sca%' ";
}

if ($sfl == "")  $sfl = "a.it_name";
if (!$sst) {
    $sst = "is_id";
    $sod = "desc";
}

$sql_common = "  from {$g4['shop_item_ps_table']} a
                 left join {$g4['shop_item_table']} b on (a.it_id = b.it_id)
                 left join {$g4['member_table']} c on (a.mb_id = c.mb_id) ";
$sql_common .= $sql_search;

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql  = " select *
          $sql_common
          order by $sst $sod, is_id desc
          limit $from_record, $rows ";
$result = sql_query($sql);

//$qstr = "page=$page&sst=$sst&sod=$sod&stx=$stx";
$qstr  = "$qstr&sca=$sca&save_stx=$stx";
?>
<style type="text/css">
    .itempslist{text-align:center}
</style>

<form name="flist">
<input type="hidden" name="page" value="<?=$page?>">
<p><a href='<?=$_SERVER['PHP_SELF']?>'>처음</a></p>
<fieldset>
    <legend>상품후기 검색</legend>
    <select name="sca" title="검색분류">
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
        <script> document.flist.sca.value = '<?=$sca?>';</script>

        <select name="sfl" title="검색대상">
        <option value="it_name">상품명</option>
        <option value="a.it_id">상품코드</option>
        <option value="is_name">이름</option>
        </select>
        <? if ($sfl) echo "<script> document.flist.sfl.value = '$sfl';</script>"; ?>

        <input type="hidden" name="save_stx" value="<?=$stx?>">
        <input type="text" name="stx" value="<?=$stx?>" class="frm_input" title="검색어">
        <input type="submit" value="검색" class="btn_submit">
</fieldset>
<p>건수 : <? echo $total_count ?></p>
</form>
<section class="cbox">
<table class="frm_basic">
<colgroup>
    <col class="grid_8">
    <col class="grid_2">
    <col class="grid_4">
    <col class="grid_1">
    <col class="grid_1">
    <col class="grid_2">
</colgroup>
<thead>
<tr>
    <th scope="col" class="itempslist"><?=subject_sort_link("it_name"); ?>상품명</a></th>
    <th scope="col"><?=subject_sort_link("mb_name"); ?>이름</a></th>
    <th scope="col"><?=subject_sort_link("is_subject"); ?>제목</a></th>
    <th scope="col"><?=subject_sort_link("is_score"); ?>점수</a></th>
    <th scope="col"><?=subject_sort_link("is_confirm"); ?>확인</a></th>
    <th scope="col">관리</th>
</tr>
</thead>
<tbody>
<?
for ($i=0; $row=sql_fetch_array($result); $i++)
{
    $row['is_subject'] = cut_str($row['is_subject'], 30, "...");

    $href = G4_SHOP_URL."/item.php?it_id={$row['it_id']}";

    $name = get_sideview($row['mb_id'], get_text($row['is_name']), $row['mb_email'], $row['mb_homepage']);

    $s_mod = icon("수정", "./itempsform.php?w=u&is_id={$row['is_id']}&$qstr");
    $s_del = icon("삭제", "javascript:del('./itempsformupdate.php?w=d&is_id={$row['is_id']}&$qstr');");

    $confirm = $row['is_confirm'] ? "Y" : "&nbsp;";

    $list = $i%2;
    ?>
    <tr>
        <td><a href="<?=$href?>"><?=get_it_image($row['it_id'].'_s', 50, 50)?><?=cut_str($row['it_name'],30)?></a></td>
        <td class="itempslist"><?=$name?></td>
        <td><?=$row['is_subject']?></td>
        <td class="itempslist"><?=$row['is_score']?></td>
        <td class="itempslist"><?=$confirm?></td>
        <td class="itempslist"><a href="./itempsform.php?w=u&is_id=<?=$row['is_id']?>&$qstr">수정</a> <a href="./itempsformupdate.php?w=d&is_id=<?=$row['is_id']?>&$qstr')">삭제</a></td>
    </tr>
    <?
}

if ($i == 0) {
    echo '<tr><td colspan="7" class="empty_table"><span>자료가 한건도 없습니다.</span></td></tr>';
}
?>
</tbody>
</table>
</section>


<?=get_paging($config['cf_write_pages'], $page, $total_page, "{$_SERVER['PHP_SELF']}?$qstr&page=");?>

<?
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>
