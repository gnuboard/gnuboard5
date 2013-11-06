<?php
include_once('./_common.php');

if (G5_IS_MOBILE) {
    include_once(G5_MSHOP_PATH.'/search.php');
    return;
}

$g5['title'] = "상품 검색 결과";
include_once('./_head.php');

$q       = utf8_strcut(escape_trim($_GET['q']), 30, "");
$qname   = escape_trim($_GET['qname']);
$qexplan = escape_trim($_GET['qexplan']);
$qid     = escape_trim($_GET['qid']);
$qcaid   = escape_trim($_GET['qcaid']);
$qfrom   = escape_trim($_GET['qfrom']);
$qto     = escape_trim($_GET['qto']);

// QUERY 문에 공통적으로 들어가는 내용
// 상품명에 검색어가 포한된것과 상품판매가능인것만
$sql_common = " from {$g5['g5_shop_item_table']} a, {$g5['g5_shop_category_table']} b ";

$where = array();
$where[] = " (a.ca_id = b.ca_id and a.it_use = 1 and b.ca_use = 1) ";

$search_all = true;
// 상세검색 이라면
if (isset($qname) || isset($qexplan) || isset($qid))
    $search_all = false;

if ($q) {
    $arr = explode(" ", $q);
    $detail_where = array();
    for ($i=0; $i<count($arr); $i++) {
        $word = trim($arr[$i]);
        if (!$word) continue;

        $or = array();
        if ($search_all || $qname) 
            $or[] = " a.it_name like '%$word%' ";
        if ($search_all || $qexplan)
            $or[] = " a.it_explan2 like '%$word%' "; // tag 를 제거한 상품설명을 검색한다.
        if ($search_all || $qid)
            $or[] = " a.it_id like '%$word%' ";

        $detail_where[] = "(" . implode(" or ", $or) . ")";

        // 인기검색어
        $sql = " insert into {$g5['popular_table']} set pp_word = '$word', pp_date = '".G5_TIME_YMD."', pp_ip = '{$_SERVER['REMOTE_ADDR']}' "; 
        sql_query($sql, FALSE);
    }

    $where[] = "(".implode(" or ", $detail_where).")";
}

if ($qcaid)
    $where[] = " a.ca_id like '$qcaid%' ";

if ($qfrom || $qto) 
    $where[] = " a.it_price between '$qfrom' and '$qto' ";

$sql_where = " where " . implode(" and ", $where);

// 상품 출력순서가 있다면
if ($sort != "")
    $order_by = $sort.' '.$sortodr.' , it_order, it_id desc';

// 검색된 내용이 몇행인지를 얻는다
$sql = " select COUNT(*) as cnt $sql_common $sql_where ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];
?>

<form name="frmdetailsearch" onsubmit="return detail_search_submit(this);">
상세검색 : 
<input type="checkbox" name="qname"   <?php echo isset($qname)?'checked="checked"':'';?>> 상품명
<input type="checkbox" name="qexplan" <?php echo isset($qexplan)?'checked="checked"':'';?>> 상품설명
<input type="checkbox" name="qid"     <?php echo isset($qid)?'checked="checked"':'';?>> 상품코드<br>
상품가격 : 
<input type="text" name="qfrom" value="<?php echo $qfrom; ?>" size="10">원 부터
<input type="text" name="qto" value="<?php echo $qto; ?>" size="10">원 까지<br>
검색어 : <input type="text" name="q" value="<?php echo $q; ?>" size="40" maxlength="30">
<input type="submit" value="검색">
<p>상세검색을 선택하지 않거나, 상품가격을 입력하지 않으면 전체에서 검색합니다.</p>
</form>

<!-- 검색결과 시작 { -->
<div id="ssch">

    <div id="ssch_ov">검색어 <strong><?php echo ($q ? stripslashes(get_text($q)) : '없음'); ?></strong> | 검색 결과 <strong><?php echo $total_count; ?></strong>건</div>

    <div>
        <?php
        // 리스트 유형별로 출력
        $list_file = G5_SHOP_SKIN_PATH.'/'.G5_SHOP_SEARCH_SKIN;
        if (file_exists($list_file)) {

            // 총몇개 = 한줄에 몇개 * 몇줄
            $items = 4 * 5;
            // 페이지가 없으면 첫 페이지 (1 페이지)
            if ($page == "") $page = 1;
            // 시작 레코드 구함
            $from_record = ($page - 1) * $items;

            $list = new item_list(G5_SHOP_SEARCH_SKIN, G5_SHOP_SEARCH_MOD, G5_SHOP_SEARCH_ROW, $default['de_simg_width'], $default['de_simg_height']);
            $list->set_query(" select * $sql_common $sql_where ");
            $list->set_is_page(true);
            $list->set_order_by($order_by);
            $list->set_from_record($from_record);
            $list->set_view('it_img', true);
            $list->set_view('it_id', true);
            $list->set_view('it_name', true);
            $list->set_view('it_basic', true);
            $list->set_view('it_cust_price', false);
            $list->set_view('it_price', true);
            $list->set_view('it_icon', true);
            $list->set_view('sns', true);
            echo $list->run();

            // where 된 전체 상품수
            $total_count = $list->total_count;
            // 전체 페이지 계산
            $total_page  = ceil($total_count / $items);
        }
        else
        {
            $i = 0;
            $error = '<p class="sct_nofile">'.$ca['ca_skin'].' 파일을 찾을 수 없습니다.<br>관리자에게 알려주시면 감사하겠습니다.</p>';
        }

        if ($i==0)
        {
            echo '<div>'.$error.'</div>';
        }

        $qstr1 .= 'ca_id='.$ca_id;
        if($skin)
            $qstr1 .= '&amp;skin='.$skin;
        $qstr1 .='&amp;sort='.$sort.'&amp;sortodr='.$sortodr;
        echo get_paging($config['cf_write_pages'], $page, $total_page, $_SERVER['PHP_SELF'].'?'.$qstr1.'&amp;page=');
        ?>
    </div>

</div>
<!-- } 검색결과 끝 -->

<?php
include_once('./_tail.php');
?>
