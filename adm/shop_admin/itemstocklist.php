<?php
$sub_menu = '400620';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g4['title'] = '상품재고관리';
include_once (G4_ADMIN_PATH.'/admin.head.php');

$sql_search = " where 1 ";
if ($search != "") {
	if ($sel_field != "") {
    	$sql_search .= " and $sel_field like '%$search%' ";
    }
}

if ($sel_ca_id != "") {
    $sql_search .= " and ca_id like '$sel_ca_id%' ";
}

if ($sel_field == "")  $sel_field = "it_name";
if ($sort1 == "") $sort1 = "it_id";
if ($sort2 == "") $sort2 = "desc";

$sql_common = "  from $g4[shop_item_table] ";
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
                 it_use,
                 it_stock_qty
           $sql_common
          order by $sort1 $sort2
          limit $from_record, $rows ";
$result = sql_query($sql);

$qstr1 = 'sel_ca_id='.$sel_ca_id.'&amp;sel_field='.$sel_field.'&amp;search='.$search;
$qstr = $qstr1.'&amp;sort1='.$sort1.'&amp;sort2='.$sort2.'&amp;page='.$page;

$listall = '';
if ($search) // 검색 결과일 때만 처음 버튼을 보여줌
    $listall = '<a href="'.$_SERVER['PHP_SELF'].'">전체목록</a>';
?>

<form name="flist">
<input type="hidden" name="doc" value="<?php echo $doc; ?>">
<input type="hidden" name="sort1" value="<?php echo $sort1; ?>">
<input type="hidden" name="sort2" value="<?php echo $sort2; ?>">
<input type="hidden" name="page" value="<?php echo $page; ?>">

<fieldset>
    <legend>상품재고 검색</legend>

    <span>
        <?php echo $listall; ?>
        전체 상품 <?php echo $total_count; ?>개
    </span>

    <?php // ##### // 웹 접근성 취약 지점 시작 - 지운아빠 2013-04-15 ?>
    <label for="sel_ca_id" class="sound_only">분류선택</label>
    <select name="sel_ca_id" id="sel_ca_id">
        <option value=''>전체분류</option>
        <?php
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
    <?php // ##### // 웹 접근성 취약 지점 끝 ?>

    <label for="sel_field" class="sound_only">검색대상</label>
    <select name="sel_field" id="sel_field">
        <option value="it_name" <?php echo get_selected($sel_field, 'it_name'); ?>>상품명</option>
        <option value="it_id" <?php echo get_selected($sel_field, 'it_id'); ?>>상품코드</option>
    </select>

    <label for="search" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
    <input type="text" name="search" value="<?php echo $search; ?>" required class="frm_input required">
    <input type="submit" value="검색" class="btn_submit">
</fieldset>

</form>

<section class="cbox">
    <h2>상품재고 목록</h2>
    <p>재고수정의 수치를 수정하시면 창고재고의 수치가 변경됩니다.</p>

    <div id="btn_add">
        <a href="./itemsellrank.php" class="btn_add_optional">상품판매순위</a>
    </div>

    <form name="fitemstocklist" action="./itemstocklistupdate.php" method="post">
    <input type="hidden" name="sort1" value="<?php echo $sort1; ?>">
    <input type="hidden" name="sort2" value="<?php echo $sort2; ?>">
    <input type="hidden" name="sel_ca_id" value="<?php echo $sel_ca_id; ?>">
    <input type="hidden" name="sel_field" value="<?php echo $sel_field; ?>">
    <input type="hidden" name="search" value="<?php echo $search; ?>">
    <input type="hidden" name="page" value="<?php echo $page; ?>">

    <table class="frm_basic">
    <thead>
    <tr>
        <th scope="col"><a href="<?php echo title_sort("it_id") . "&amp;$qstr1"; ?>">상품코드<span class="sound_only"> 순 정렬</span></a></th>
        <th scope="col"><a href="<?php echo title_sort("it_name") . "&amp;$qstr1"; ?>">상품명<span class="sound_only"> 순 정렬</span></a></th>
        <th scope="col"><a href="<?php echo title_sort("it_stock_qty") . "&amp;$qstr1"; ?>">창고재고<span class="sound_only"> 순 정렬</span></a></th>
        <th scope="col">주문대기</th>
        <th scope="col">가재고</th>
        <th scope="col">재고수정</th>
        <th scope="col"><a href="<?php echo title_sort("it_use") . "&amp;$qstr1"; ?>">판매<span class="sound_only"> 순 정렬</span></a></th>
        <th scope="col">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php
    for ($i=0; $row=mysql_fetch_array($result); $i++)
    {
        $href = G4_SHOP_URL."/item.php?it_id={$row['it_id']}";

        $sql1 = " select SUM(ct_qty) as sum_qty
                    from {$g4['shop_cart_table']}
                   where it_id = '{$row['it_id']}'
                     and ct_stock_use = '0'
                     and ct_status in ('주문', '준비') ";
        $row1 = sql_fetch($sql1);
        $wait_qty = $row1['sum_qty'];

        // 가재고 (미래재고)
        $temporary_qty = $row['it_stock_qty'] - $wait_qty;

    ?>
    <tr>
        <td class="td_bignum">
            <input type="hidden" name="it_id[<?php echo $i; ?>]" value="<?php echo $row['it_id']; ?>">
            <?php echo $row['it_id']; ?>
        </td>
        <td><a href="<?php echo $href; ?>"><?php echo get_it_image($row['it_id'].'_s', 50, 50); ?><?php echo cut_str(stripslashes($row['it_name']), 60, "&#133"); ?></a></td>
        <td class="td_num"><?php echo number_format($row['it_stock_qty']); ?></td>
        <td class="td_num"><?php echo number_format($wait_qty); ?></td>
        <td class="td_num"><?php echo number_format($temporary_qty); ?></td>
        <td class="td_num"><input type="text" name="it_stock_qty[<?php echo $i; ?>]" value="<?php echo $row['it_stock_qty']; ?>" class="frm_input" size="10" autocomplete="off"></td>
        <td class="td_chk"><input type="checkbox" name="it_use[<?php echo $i; ?>]" value="1" <?php echo ($row['it_use'] ? "checked" : ""); ?>></td>
        <td class="td_smallmng"><a href="./itemform.php?w=u&amp;it_id=<?php echo $row['it_id']; ?>&amp;ca_id=<?php echo $row['ca_id']; ?>&amp;$qstr">수정</a></td>
    </tr><tr>
    <?php
    }
    if (!$i)
        echo '<tr><td colspan="8" class="empty_table"><span>자료가 없습니다.</span></td></tr>';
    ?>
    </tbody>
    </table>
    <div class="btn_confirm">
        <input type="submit" value="일괄수정" class="btn_submit">
    </div>
    </form>

</section>

<?php echo get_paging($config['cf_write_pages'], $page, $total_page, "{$_SERVER['PHP_SELF']}?$qstr&amp;page="); ?>

<?php
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>
