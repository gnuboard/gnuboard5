<?php
$sub_menu = '400300';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g4['title'] = '상품관리';
include_once (G4_ADMIN_PATH.'/admin.head.php');

// 분류
$ca_list  = '<option value="">선택</option>'.PHP_EOL;
$sql = " select * from {$g4['shop_category_table']} ";
if ($is_admin != 'super')
    $sql .= " where ca_mb_id = '{$member['mb_id']}' ";
$sql .= " order by ca_id ";
$result = sql_query($sql);
for ($i=0; $row=sql_fetch_array($result); $i++)
{
    $len = strlen($row['ca_id']) / 2 - 1;
    $nbsp = '';
    for ($i=0; $i<$len; $i++) {
        $nbsp .= '&nbsp;&nbsp;&nbsp;';
    }
    $ca_list .= '<option value="'.$row['ca_id'].'">'.$nbsp.$row['ca_name'].'</option>'.PHP_EOL;
}


$where = " and ";
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
    $sql_search .= " $where (a.ca_id like '$sca%' or a.ca_id2 like '$sca%' or a.ca_id3 like '$sca%') ";
}

if ($sfl == "")  $sfl = "it_name";

$sql_common = " from {$g4['shop_item_table']} a ,
                     {$g4['shop_category_table']} b
               where (a.ca_id = b.ca_id";
if ($is_admin != 'super')
    $sql_common .= " and b.ca_mb_id = '{$member['mb_id']}'";
$sql_common .= ") ";
$sql_common .= $sql_search;

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

if (!$sst) {
    $sst  = "it_id";
    $sod = "desc";
}
$sql_order = "order by $sst $sod";


$sql  = " select *
           $sql_common
           $sql_order
           limit $from_record, $rows ";
$result = sql_query($sql);

//$qstr  = $qstr.'&amp;sca='.$sca.'&amp;page='.$page;
$qstr  = $qstr.'&amp;sca='.$sca.'&amp;page='.$page.'&amp;save_stx='.$stx;

$listall = '';
if ($sfl || $stx) // 검색렬일 때만 처음 버튼을 보여줌
    $listall = '<a href="'.$_SERVER['PHP_SELF'].'">전체목록</a>';
?>

<form name="flist">
<input type="hidden" name="page" value="<?php echo $page; ?>">
<input type="hidden" name="save_stx" value="<?php echo $stx; ?>">

<fieldset>
    <legend>상품 검색</legend>

    <span>
        <?php echo $listall; ?>
        등록된 상품 <?php echo $total_count; ?>건
    </span>

    <label for="sca" class="sound_only">분류선택</label>
    <?php // ##### // 웹 접근성 취약 지점 시작 - 지운아빠 2013-04-12 ?>
    <select name="sca" id="sca">
        <option value="">전체분류</option>
        <?php
        $sql1 = " select ca_id, ca_name from {$g4['shop_category_table']} order by ca_id ";
        $result1 = sql_query($sql1);
        for ($i=0; $row1=sql_fetch_array($result1); $i++) {
            $len = strlen($row1['ca_id']) / 2 - 1;
            $nbsp = '';
            for ($i=0; $i<$len; $i++) $nbsp .= '&nbsp;&nbsp;&nbsp;';
            echo '<option value="'.$row1['ca_id'].'" '.get_selected($sca, $row1['ca_id']).'>'.$nbsp.$row1['ca_name'].'</option>'.PHP_EOL;
        }
        ?>
    </select>
    <?php // ##### // 웹 접근성 취약 지점 끝 ?>

    <label for="sfl" class="sound_only">검색대상</label>
    <select name="sfl" id="sfl">
        <option value="it_name" <?php echo get_selected($sfl, 'it_name'); ?>>상품명</option>
        <option value="it_id" <?php echo get_selected($sfl, 'it_id'); ?>>상품코드</option>
        <option value="it_maker" <?php echo get_selected($sfl, 'it_maker'); ?>>제조사</option>
        <option value="it_origin" <?php echo get_selected($sfl, 'it_origin'); ?>>원산지</option>
        <option value="it_sell_email" <?php echo get_selected($sfl, 'it_sell_email'); ?>>판매자 e-mail</option>
    </select>

    <label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
    <input type="text" name="stx" value="<?php echo $stx; ?>" id="stx" required class="frm_input required">
    <input type="submit" value="검색" class="btn_submit">
</fieldset>

</form>

<section class="cbox">
    <h2>상품 목록</h2>

    <div id="btn_add">
        <a href="./itemform.php">상품등록</a>
        <a href="./itemsellrank.php" class="btn_add_optional">상품판매순위</a>
    </div>

    <form name="fitemlistupdate" method="post" action="./itemlistupdate.php" autocomplete="off">
    <input type="hidden" name="sca" value="<?php echo $sca; ?>">
    <input type="hidden" name="sst" value="<?php echo $sst; ?>">
    <input type="hidden" name="sod" value="<?php echo $sod; ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl; ?>">
    <input type="hidden" name="stx" value="<?php echo $stx; ?>">
    <input type="hidden" name="page" value="<?php echo $page; ?>">

    <table>
    <thead>
    <tr>
        <th scope="col" rowspan="2"><?php echo subject_sort_link('it_id', 'sca='.$sca); ?>상품코드 <span class="sound_only">순 정렬</span></a></th>
        <th scope="col" colspan="2" rowspan="2">분류 및 <?php echo subject_sort_link('it_name', 'sca='.$sca); ?>상품명 <span class="sound_only">순 정렬</span></a></th>
        <th scope="col" id="sit_amt"><?php echo subject_sort_link('it_amount', 'sca='.$sca); ?>비회원가격 <span class="sound_only">순 정렬</span></a></th>
        <th scope="col" id="sit_amt2"><?php echo subject_sort_link('it_amount2', 'sca='.$sca); ?>회원가격 <span class="sound_only">순 정렬</span></a></th>
        <th scope="col" id="sit_amt3"><?php echo subject_sort_link('it_amount3', 'sca='.$sca); ?>특별가격 <span class="sound_only">순 정렬</span></a></th>
        <th scope="col" rowspan="2"><?php echo subject_sort_link('it_order', 'sca='.$sca); ?>순서 <span class="sound_only">순 정렬</span></a></th>
        <th scope="col" rowspan="2"><?php echo subject_sort_link('it_use', 'sca='.$sca, 1); ?>판매 <span class="sound_only">순 정렬</span></a></th>
        <th scope="col" rowspan="2"><?php echo subject_sort_link('it_hit', 'sca='.$sca, 1); ?>조회 <span class="sound_only">순 정렬</span></a></th>
        <th scope="col" rowspan="2">관리</th>
    </tr>
    <tr>
        <th scope="col" id="sit_camt"><?php echo subject_sort_link('it_cust_amount', 'sca='.$sca); ?>시중가격 <span class="sound_only">순 정렬</span></a></th>
        <th scope="col" id="sit_pt"><?php echo subject_sort_link('it_point', 'sca='.$sca); ?>포인트 <span class="sound_only">순 정렬</span></a></th>
        <th scope="col" id="sit_qty"><?php echo subject_sort_link('it_stock_qty', 'sca='.$sca); ?>재고 <span class="sound_only">순 정렬</span></a></th>
    </tr>
    </thead>
    <tbody>
    <?php
    for ($i=0; $row=mysql_fetch_array($result); $i++)
    {
        $href = G4_SHOP_URL.'/item.php?it_id='.$row['it_id'];

        $gallery = $row['it_gallery'] ? 'Y' : '';
    ?>
    <tr>
        <td rowspan="2">
            <input type="hidden" name="it_id[<?php echo $i; ?>]" value="<?php echo $row['it_id']; ?>">
            <?php echo $row['it_id']; ?>
        </td>
        <td rowspan="2"><a href="<?php echo $href; ?>"><?php echo get_it_image($row['it_id'].'_s', 50, 50); ?></a></td>
        <td rowspan="2">
            <label for="ca_id_<?php echo $i; ?>" class="sound_only">분류</label>
            <select name="ca_id[<?php echo $i; ?>]" id="ca_id_<?php echo $i; ?>">
                <?php echo conv_selected_option($ca_list, $row['ca_id']); ?>
            </select>
            <?php echo $tmp_ca_list; ?><br>
            <input type="text" name="it_name[<?php echo $i; ?>]" value="<?php echo htmlspecialchars2(cut_str($row['it_name'],250, "")); ?>" required class="frm_input frm_sit_title required" size="30">
        </td>
        <td headers="sit_amt"><input type="text" name="it_amount[<?php echo $i; ?>]" value="<?php echo $row['it_amount']; ?>" class="frm_input sit_amt" size="7"></td>
        <td headers="sit_amt2"><input type="text" name="it_amount2[<?php echo $i; ?>]" value="<?php echo $row['it_amount2']; ?>" class="frm_input sit_amt2" size="7"></td>
        <td headers="sit_amt3"><input type="text" name="it_amount3[<?php echo $i; ?>]" value="<?php echo $row['it_amount3']; ?>" class="frm_input sit_amt3" size="7"></td>
        <td rowspan="2"><input type="text" name="it_order[<?php echo $i; ?>]" value="<?php echo $row['it_order']; ?>" class="frm_input sit_odrby" size="3"></td>
        <td rowspan="2"><input type="checkbox" name="it_use[<?php echo $i; ?>]" <?php echo ($row['it_use'] ? 'checked' : ''); ?> value="1"></td>
        <td rowspan="2"><?php echo $row['it_hit']; ?></td>
        <td rowspan="2" class="td_mng">
            <a href="<?php echo $href; ?>"><img src="./img/icon_view.jpg" alt="<?php echo htmlspecialchars2(cut_str($row['it_name'],250, "")); ?> 보기"></a>
            <a href="javascript:_copy('<?php echo $row['it_id']; ?>', '<?php echo $row['ca_id']; ?>');"><img src="./img/icon_copy.jpg" alt="<?php echo htmlspecialchars2(cut_str($row['it_name'],250, "")); ?> 복사"></a>
            <a href="./itemform.php?w=u&amp;it_id=<?php echo $row['it_id']; ?>&amp;ca_id=<?php echo $row['ca_id']; ?>&amp;<?php echo $qstr; ?>"><img src="./img/icon_mod.jpg" alt="<?php echo htmlspecialchars2(cut_str($row['it_name'],250, "")); ?> 수정"></a>
            <a href="./itemformupdate.php?w=d&amp;it_id=<?php echo $row['it_id']; ?>&amp;ca_id=<?php echo $row['ca_id']; ?>&amp;<?php echo $qstr; ?>" onclick="return delete_confirm();"><img src="./img/icon_del.jpg" alt="<?php echo htmlspecialchars2(cut_str($row['it_name'],250, "")); ?> 삭제"></a>
        </td>
    </tr>
    <tr>
        <td headers="sit_camt"><input type="text" name="it_cust_amount[<?php echo $i; ?>]" value="<?php echo $row['it_cust_amount']; ?>" class="frm_input sit_camt" size="7"></td>
        <td headers="sit_pt"><input type="text" name="it_point[<?php echo $i; ?>]" value="<?php echo $row['it_point']; ?>" class="frm_input sit_pt" size="7"></td>
        <td headers="sit_qty"><input type="text" name="it_stock_qty[<?php echo $i; ?>]" value="<?php echo $row['it_stock_qty']; ?>" class="frm_input sit_qty" size="7"></td>
    </tr>
    <?php
    }
    if ($i == 0)
        echo '<tr><td colspan="20" class="empty_table">자료가 한건도 없습니다.</td></tr>';
    ?>
    </tbody>
    </table>

    <div class="btn_confirm">
        <input type="submit" value="일괄수정" class="btn_submit" accesskey="s">
    </div>
    </form>

</section>

<?php echo get_paging($config['cf_write_pages'], $page, $total_page, "{$_SERVER['PHP_SELF']}?$qstr&amp;page="); ?>

<script>
function _trim(str)
{
    var pattern = /(^\s*)|(\s*$)/g; // \s 공백 문자
    return str.replace(pattern, "");
}

/*
function _copy(it_name, link)
{
    var now = new Date();
    var time = now.getTime() + '';
    var new_it_id = prompt("'"+it_name+"' 상품을 복사하시겠습니까? 상품코드를 입력하세요.", time.substring(3,13));
    if (!new_it_id) {
        alert('상품코드를 입력하세요.');
        return;
    }

    if (g4_charset.toUpperCase() == 'EUC-KR')
        location.href = link+'&amp;new_it_id='+new_it_id;
    else
        location.href = encodeURI(link+'&amp;new_it_id='+new_it_id);
}
*/

function _copy(it_id, ca_id)
{
    window.open('./item_copy.php?it_id='+it_id+'&amp;ca_id='+ca_id, 'copywin', 'left=100, top=100, width=300, height=200, scrollbars=0');
}
</script>

<?php
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>
