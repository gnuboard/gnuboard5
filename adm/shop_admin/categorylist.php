<?
$sub_menu = '400200';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g4['title'] = '분류관리';
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

$sql_common = " from {$g4['yc4_category_table']} ";
if ($is_admin != 'super')
    $sql_common .= " $where ca_mb_id = '{$member['mb_id']}' ";
$sql_common .= $sql_search;


// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

if (!$sst)
{
    $sst  = "ca_id";
    $sod = "asc";
}
$sql_order = "order by $sst $sod";

// 출력할 레코드를 얻음
$sql  = " select *
             $sql_common
             $sql_order
             limit $from_record, $rows ";
$result = sql_query($sql);

//$qstr = "page=".$page."&amp;sort1=".$sort1."&amp;sort2=".$sort2;
$qstr = $qstr."&amp;sca=".$sca."&amp;page=".$page."&amp;save_stx=".$stx;
?>

<form name="flist">
<input type="hidden" name="page" value="<?=$page?>">
<input type="hidden" name="save_stx" value="<?=$stx?>">

<fieldset>
    <legend>분류 검색</legend>
    <span>
        <?=$listall?>
        생성된 분류 수 <?=number_format($total_count)?>개
    </span>
    <select name="sfl" title="검색대상">
        <option value="ca_name"<?=get_selected($_GET['sfl'], "ca_name", true);?>>분류명</option>
        <option value="ca_id"<?=get_selected($_GET['sfl'], "ca_id", true);?>>분류코드</option>
        <option value="ca_mb_id"<?=get_selected($_GET['sfl'], "ca_mb_id", true);?>>회원아이디</option>
    </select>
    <input type="text" name="stx" value="<?=$stx?>" title="검색어(필수)" required class="required frm_input">
    <input type="submit" value="검색" class="btn_submit">
</fieldset>

</form>

<section class="cbox">
    <h2>생성된 분류 전체 목록</h2>
    <p>생성된 분류 확인, 추가 및 간략 수정을 할 수 있습니다.</p>

    <?if ($is_admin == 'super') {?>
    <div id="btn_add">
        <a href="./categoryform.php" id="cate_add">분류 추가</a>
    </div>
    <?}?>

    <form name="fcategorylist" method="post" action="./categorylistupdate.php" autocomplete="off">
    <input type="hidden" name="page"  value="<?=$page ?>">
    <input type="hidden" name="sort1" value="<?=$sort1 ?>">
    <input type="hidden" name="sort2" value="<?=$sort2 ?>">

    <table class="frm_basic">
    <thead>
    <tr>
        <th scope="col"><?=subject_sort_link("ca_id");?>분류<br>코드</a></th>
        <th scope="col"><?=subject_sort_link("ca_name");?>분류명</a></th>
        <th scope="col"><?=subject_sort_link("ca_mb_id");?>회원아이디</a></th>
        <th scope="col"><?=subject_sort_link("ca_use");?>판매<br>가능</a></th>
        <th scope="col"><?=subject_sort_link("ca_stock_qty");?>기본재고</a></th>
        <th scope="col">상품수</th>
        <th scope="col">관리</th>
    </tr>
    </thead>
    <tbody>
    <?
    for ($i=0; $row=sql_fetch_array($result); $i++)
    {
        $level = strlen($row['ca_id']) / 2 - 1;
        $p_ca_name = '';

        if ($level > 0) {
            $class = 'class="cate_list_lbl"'; // 2단 이상 분류의 label 에 스타일 부여 - 지운아빠 2013-04-02
            // 상위단계의 분류명
            $p_ca_id = substr($row['ca_id'], 0, $level*2);
            $sql = " select ca_name from {$g4['yc4_category_table']} where ca_id = '$p_ca_id' ";
            $temp = sql_fetch($sql);
            $p_ca_name = $temp['ca_name'].'의';
        } else
            $class = '';

        $s_level = '<label for="ca_name_'.$i.'" '.$class.'>'.$p_ca_name.($level+1).'단 분류</label>';
        $s_level_input_size = 40 - $level *5; // 하위 분류일 수록 입력칸 넓이 작아짐 - 지운아빠 2013-04-02

        if ($level+2 < 6) $s_add = '<a href="./categoryform.php?ca_id='.$row['ca_id'].'&amp;'.$qstr.'">'.($level+2).'단 추가</a><br>'; // 분류는 5단계까지만 가능
        else $s_add = '';
        $s_upd = '<a href="./categoryform.php?w=u&amp;ca_id='.$row['ca_id'].'&amp;'.$qstr.'">수정</a>';
        $s_vie = '<a href="'.G4_SHOP_URL.'/list.php?ca_id='.$row['ca_id'].'">보기</a>';

        if ($is_admin == 'super')
            $s_del = '<a href="javascript:del(\'./categoryformupdate.php?w=d&amp;ca_id='.$row['ca_id'].'&amp;'.$qstr.'\');">삭제</a>';

        // 해당 분류에 속한 상품의 갯수
        $sql1 = " select COUNT(*) as cnt from {$g4['yc4_item_table']}
                      where ca_id = '{$row['ca_id']}'
                      or ca_id2 = '{$row['ca_id']}'
                      or ca_id3 = '{$row['ca_id']}' ";
        $row1 = sql_fetch($sql1);
    ?>
    <tr>
        <td class="td_num">
            <input type="hidden" name="ca_id[<?=$i?>]" value="<?=$row['ca_id']?>">
            <?=$row['ca_id']?>
        </td>
        <td class="td_shop_category"><?=$s_level?> <input type="text" name="ca_name[<?=$i?>]" value="<?=get_text($row['ca_name'])?>" id="ca_name_<?=$i?>" title="<?=$row['ca_id']?>" required class="frm_input required" size="<?=$s_level_input_size?>"></td>

        <td class="td_mbid_input">
            <? if ($is_admin == 'super') {?>
            <input type="text" name="ca_mb_id[<?=$i?>]" size="10" maxlength="20" value="<?=$row['ca_mb_id']?>" class="frm_input" title="회원아이디">
            <? } else { ?>
            <input type="hidden" name="ca_mb_id[<?=$i?>]" value="<?=$row['ca_mb_id']?>">
            <?=$row['ca_mb_id']?>
            <? } ?>
        </td>

        <td class="td_chk"><input type="checkbox" name="ca_use[<?=$i?>]" value="1" <?=($row['ca_use'] ? "checked" : "")?>></td>
        <td class="td_bignum"><input type="text" name="ca_stock_qty[<?=$i?>]" value="<?=$row['ca_stock_qty']?>" class="frm_input" size="6" style="text-align:right"></td>
        <td class="td_num"><a href="./itemlist.php?sca=<?=$row['ca_id']?>"><?=$row1['cnt']?></a></td>
        <td class="td_mng"><?=$s_add?><?=$s_vie?> <?=$s_upd?> <?=$s_del?></td>
    </tr>
    <? }
    if ($i == 0) echo "<tr><td colspan=\"7\" class=\"empty_table\">자료가 한 건도 없습니다.</td></tr>\n";
    ?>
    </tbody>
    </table>

    <div class="btn_list">
        <input type="submit" value="일괄수정">
    </div>

    </form>

    <?=get_paging($config['cf_write_pages'], $page, $total_page, "{$_SERVER['PHP_SELF']}?$qstr&amp;page=");?>

</section>

<?
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>
