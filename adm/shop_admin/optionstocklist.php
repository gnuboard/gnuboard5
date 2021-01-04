<?php
$sub_menu = '400500';
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, "r");

$doc = isset($_GET['doc']) ? clean_xss_tags($_GET['doc'], 1, 1) : '';
$sort1 = (isset($_GET['sort1']) && in_array($_GET['sort1'], array('b.it_name', 'a.io_stock_qty', 'a.io_use'))) ? $_GET['sort1'] : '';
$sort2 = (isset($_GET['sort2']) && in_array($_GET['sort2'], array('desc', 'asc'))) ? $_GET['sort2'] : 'asc';
$sel_ca_id = isset($_GET['sel_ca_id']) ? get_search_string($_GET['sel_ca_id']) : '';
$sel_field = (isset($_GET['sel_field']) && in_array($_GET['sel_field'], array('b.it_name', 'a.it_id')) ) ? $_GET['sel_field'] : '';
$search = isset($_GET['search']) ? get_search_string($_GET['search']) : '';

$g5['title'] = '상품옵션재고관리';
include_once (G5_ADMIN_PATH.'/admin.head.php');

$sql_search = " where b.it_id is not NULL ";
if ($search != "") {
	if ($sel_field != "") {
    	$sql_search .= " and $sel_field like '%$search%' ";
    }
}

if ($sel_ca_id != "") {
    $sql_search .= " and b.ca_id like '$sel_ca_id%' ";
}

if ($sel_field == "")  $sel_field = "b.it_name";
if ($sort1 == "") $sort1 = "a.io_stock_qty";
if (!in_array($sort1, array('b.it_name', 'a.io_stock_qty', 'a.io_use'))) $sort1 = "a.io_stock_qty";
if ($sort2 == "") $sort2 = "asc";

$sql_common = "  from {$g5['g5_shop_item_option_table']} a left join {$g5['g5_shop_item_table']} b on ( a.it_id = b.it_id ) ";
$sql_common .= $sql_search;

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql  = " select a.it_id,
                 a.io_id,
                 a.io_type,
                 a.io_stock_qty,
                 a.io_noti_qty,
                 a.io_use,
                 b.it_name,
                 b.it_option_subject,
                 b.ca_id
           $sql_common
          order by $sort1 $sort2
          limit $from_record, $rows ";
$result = sql_query($sql);

$qstr1 = 'sel_ca_id='.$sel_ca_id.'&amp;sel_field='.$sel_field.'&amp;search='.$search;
$qstr = $qstr1.'&amp;sort1='.$sort1.'&amp;sort2='.$sort2.'&amp;page='.$page;

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';
?>

<div class="local_ov01 local_ov">
    <?php echo $listall; ?>
    <span class="btn_ov01"><span class="ov_txt">전체 옵션</span><span class="ov_num">  <?php echo $total_count; ?>개</span></span>
</div>

<form name="flist" class="local_sch01 local_sch">
<input type="hidden" name="doc" value="<?php echo $doc; ?>">
<input type="hidden" name="sort1" value="<?php echo $sort1; ?>">
<input type="hidden" name="sort2" value="<?php echo $sort2; ?>">
<input type="hidden" name="page" value="<?php echo $page; ?>">

<label for="sel_ca_id" class="sound_only">분류선택</label>
<select name="sel_ca_id" id="sel_ca_id">
    <option value=''>전체분류</option>
    <?php
    $sql1 = " select ca_id, ca_name from {$g5['g5_shop_category_table']} order by ca_order, ca_id ";
    $result1 = sql_query($sql1);
    for ($i=0; $row1=sql_fetch_array($result1); $i++) {
        $len = strlen($row1['ca_id']) / 2 - 1;
        $nbsp = "";
        for ($i=0; $i<$len; $i++) $nbsp .= "&nbsp;&nbsp;&nbsp;";
        echo '<option value="'.$row1['ca_id'].'" '.get_selected($sel_ca_id, $row1['ca_id']).'>'.$nbsp.$row1['ca_name'].'</option>'.PHP_EOL;
    }
    ?>
</select>

<label for="sel_field" class="sound_only">검색대상</label>
<select name="sel_field" id="sel_field">
    <option value="b.it_name" <?php echo get_selected($sel_field, 'b.it_name'); ?>>상품명</option>
    <option value="a.it_id" <?php echo get_selected($sel_field, 'a.it_id'); ?>>상품코드</option>
</select>

<label for="search" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="search" id="search" value="<?php echo $search; ?>" required class="frm_input required">
<input type="submit" value="검색" class="btn_submit">

</form>

<form name="fitemstocklist" action="./optionstocklistupdate.php" method="post">
<input type="hidden" name="sort1" value="<?php echo $sort1; ?>">
<input type="hidden" name="sort2" value="<?php echo $sort2; ?>">
<input type="hidden" name="sel_ca_id" value="<?php echo $sel_ca_id; ?>">
<input type="hidden" name="sel_field" value="<?php echo $sel_field; ?>">
<input type="hidden" name="search" value="<?php echo $search; ?>">
<input type="hidden" name="page" value="<?php echo $page; ?>">

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?> 목록</caption>
    <thead>
    <tr>
        <th scope="col"><a href="<?php echo title_sort("b.it_name") . "&amp;$qstr1"; ?>">상품명</a></th>
        <th scope="col">옵션항목</th>
        <th scope="col">옵션타입</th>
        <th scope="col"><a href="<?php echo title_sort("a.io_stock_qty") . "&amp;$qstr1"; ?>">창고재고</a></th>
        <th scope="col">주문대기</th>
        <th scope="col">가재고</th>
        <th scope="col">재고수정</th>
        <th scope="col">통보수량</th>
        <th scope="col"><a href="<?php echo title_sort("a.io_use") . "&amp;$qstr1"; ?>">판매</a></th>
        <th scope="col">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++)
    {
        $href = shop_item_url($row['it_id']);

        $sql1 = " select SUM(ct_qty) as sum_qty
                    from {$g5['g5_shop_cart_table']}
                   where it_id = '{$row['it_id']}'
                     and io_id = '{$row['io_id']}'
                     and ct_stock_use = '0'
                     and ct_status in ('쇼핑', '주문', '입금', '준비') ";
        $row1 = sql_fetch($sql1);
        $wait_qty = $row1['sum_qty'];

        // 가재고 (미래재고)
        $temporary_qty = $row['io_stock_qty'] - $wait_qty;

        $option = '';
        $option_br = '';
        if($row['io_type']) {
            $opt = explode(chr(30), $row['io_id']);
            if($opt[0] && $opt[1])
                $option .= $opt[0].' : '.$opt[1];
        } else {
            $subj = explode(',', $row['it_option_subject']);
            $opt = explode(chr(30), $row['io_id']);
            for($k=0; $k<count($subj); $k++) {
                if($subj[$k] && $opt[$k]) {
                    $option .= $option_br.$subj[$k].' : '.$opt[$k];
                    $option_br = '<br>';
                }
            }
        }

        $type = '선택옵션';
        if($row['io_type'])
            $type = '추가옵션';

        // 통보수량보다 재고수량이 작을 때
        $io_stock_qty = number_format($row['io_stock_qty']);
        $io_stock_qty_st = ''; // 스타일 정의
        if($row['io_stock_qty'] <= $row['io_noti_qty']) {
            $io_stock_qty_st = ' sit_stock_qty_alert';
            $io_stock_qty = ''.$io_stock_qty.' !<span class="sound_only"> 재고부족 </span>';
        }

        $bg = 'bg'.($i%2);
    ?>
    <tr class="<?php echo $bg; ?>">
        <td class="td_left">
            <input type="hidden" name="it_id[<?php echo $i; ?>]" value="<?php echo $row['it_id']; ?>">
            <input type="hidden" name="io_id[<?php echo $i; ?>]" value="<?php echo $row['io_id']; ?>">
            <input type="hidden" name="io_type[<?php echo $i; ?>]" value="<?php echo $row['io_type']; ?>">
            <a href="<?php echo $href; ?>"><?php echo get_it_image($row['it_id'], 50, 50); ?> <?php echo cut_str(stripslashes($row['it_name']), 60, "&#133"); ?></a>
        </td>
        <td class="td_left"><?php echo $option; ?></td>
        <td class="td_mng"><?php echo $type; ?></td>
        <td class="td_num<?php echo $io_stock_qty_st; ?>"><?php echo $io_stock_qty; ?></td>
        <td class="td_num"><?php echo number_format($wait_qty); ?></td>
        <td class="td_num"><?php echo number_format($temporary_qty); ?></td>
        <td class="td_num">
            <label for="stock_qty_<?php echo $i; ?>" class="sound_only">재고수정</label>
            <input type="text" name="io_stock_qty[<?php echo $i; ?>]" value="<?php echo $row['io_stock_qty']; ?>" id="stock_qty_<?php echo $i; ?>" class="frm_input" size="8" autocomplete="off">
        </td>
        <td class="td_num">
            <label for="noti_qty_<?php echo $i; ?>" class="sound_only">통보수량</label>
            <input type="text" name="io_noti_qty[<?php echo $i; ?>]" value="<?php echo $row['io_noti_qty']; ?>" id="noti_qty_<?php echo $i; ?>" class="frm_input" size="8" autocomplete="off">
        </td>
        <td class="td_chk2">
            <label for="use_<?php echo $i; ?>" class="sound_only">판매</label>
            <input type="checkbox" name="io_use[<?php echo $i; ?>]" value="1" id="use_<?php echo $i; ?>" <?php echo ($row['io_use'] ? "checked" : ""); ?>>
        </td>
        <td class="td_mng td_mng_s"><a href="./itemform.php?w=u&amp;it_id=<?php echo $row['it_id']; ?>&amp;ca_id=<?php echo $row['ca_id']; ?>&amp;<?php echo $qstr; ?>" class="btn btn_03">수정</a></td>
    </tr>
    <?php
    }
    if (!$i)
        echo '<tr><td colspan="10" class="empty_table"><span>자료가 없습니다.</span></td></tr>';
    ?>
    </tbody>
    </table>
</div>

<div class="btn_fixed_top">
    <a href="./itemstocklist.php" class="btn btn_02">상품재고관리</a>
    <a href="./itemsellrank.php" class="btn btn_02">상품판매순위</a>
    <input type="submit" value="일괄수정" class="btn_submit btn">
</div>

</form>

<div class="local_desc01 local_desc">
    <p>
        재고수정의 수치를 수정하시면 창고재고의 수치가 변경됩니다.<br>
        창고재고가 부족한 경우 재고수량 뒤에 <span class="sit_stock_qty_alert">!</span><span class="sound_only"> 혹은 재고부족</span>으로 표시됩니다.
    </p>
</div>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}?$qstr&amp;page="); ?>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');