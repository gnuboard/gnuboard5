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

    <div class="btn_add sort_with">
        <a href="./itemform.php">상품등록</a>
        <a href="./itemexcel.php" onclick="return excelform(this.href);" target="_blank">상품일괄등록</a>
    </div>

    <ul class="sort_odr">
        <li><?php echo subject_sort_link('it_id', 'sca='.$sca); ?>상품코드<span class="sound_only"> 순 정렬</span></a></li>
        <li><?php echo subject_sort_link('it_name', 'sca='.$sca); ?>상품명<span class="sound_only"> 순 정렬</span></a></li>
        <li><?php echo subject_sort_link('it_price', 'sca='.$sca); ?>판매가격<span class="sound_only"> 순 정렬</span></a></li>
        <li><?php echo subject_sort_link('it_cust_price', 'sca='.$sca); ?>시중가격<span class="sound_only"> 순 정렬</span></a></li>
        <li><?php echo subject_sort_link('it_order', 'sca='.$sca); ?>순서<span class="sound_only"> 순 정렬</span></a></li>
        <li><?php echo subject_sort_link('it_use', 'sca='.$sca, 1); ?>판매<span class="sound_only"> 순 정렬</span></a></li>
        <li><?php echo subject_sort_link('it_hit', 'sca='.$sca, 1); ?>조회<span class="sound_only"> 순 정렬</span></a></li>
        <li><?php echo subject_sort_link('it_point', 'sca='.$sca); ?>포인트<span class="sound_only"> 순 정렬</span></a></li>
        <li><?php echo subject_sort_link('it_stock_qty', 'sca='.$sca); ?>재고<span class="sound_only"> 순 정렬</span></a></li>
    </ul>

    <form name="fitemlistupdate" method="post" action="./itemlistupdate.php" onsubmit="return fitemlist_submit(this);" autocomplete="off">
    <input type="hidden" name="sca" value="<?php echo $sca; ?>">
    <input type="hidden" name="sst" value="<?php echo $sst; ?>">
    <input type="hidden" name="sod" value="<?php echo $sod; ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl; ?>">
    <input type="hidden" name="stx" value="<?php echo $stx; ?>">
    <input type="hidden" name="page" value="<?php echo $page; ?>">

    <table>
    <thead>
    <tr>
        <th scope="col" rowspan="2">
            <label for="chkall" class="sound_only">게시판 전체</label>
            <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
        </th>
        <th scope="col" rowspan="2">상품코드</th>
        <th scope="col" colspan="2" rowspan="2">분류 및 상품명</th>
        <th scope="col" id="sit_amt">판매가격</th>
        <th scope="col" id="sit_camt">시중가격</th>
        <th scope="col" rowspan="2">순서</th>
        <th scope="col" rowspan="2">판매</th>
        <th scope="col" rowspan="2">조회</th>
        <th scope="col" rowspan="2">관리</th>
    </tr>
    <tr>
        <th scope="col" id="sit_pt">포인트</th>
        <th scope="col" id="sit_qty">재고</th>
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
            <label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['bo_subject']) ?> 게시판</label>
            <input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i; ?>">
        </td>
        <td rowspan="2">
            <input type="hidden" name="it_id[<?php echo $i; ?>]" value="<?php echo $row['it_id']; ?>">
            <?php echo $row['it_id']; ?>
        </td>
        <td rowspan="2"><a href="<?php echo $href; ?>"><?php echo get_it_image($row['it_id'], 50, 50); ?></a></td>
        <td rowspan="2">
            <label for="ca_id_<?php echo $i; ?>" class="sound_only">분류</label>
            <select name="ca_id[<?php echo $i; ?>]" id="ca_id_<?php echo $i; ?>">
                <?php echo conv_selected_option($ca_list, $row['ca_id']); ?>
            </select>
            <?php echo $tmp_ca_list; ?><br>
            <input type="text" name="it_name[<?php echo $i; ?>]" value="<?php echo htmlspecialchars2(cut_str($row['it_name'],250, "")); ?>" required class="frm_input frm_sit_title required" size="30">
        </td>
        <td headers="sit_amt"><input type="text" name="it_price[<?php echo $i; ?>]" value="<?php echo $row['it_price']; ?>" class="frm_input sit_amt" size="7"></td>
        <td headers="sit_camt"><input type="text" name="it_cust_price[<?php echo $i; ?>]" value="<?php echo $row['it_cust_price']; ?>" class="frm_input sit_camt" size="7"></td>
        <td rowspan="2"><input type="text" name="it_order[<?php echo $i; ?>]" value="<?php echo $row['it_order']; ?>" class="frm_input sit_odrby" size="3"></td>
        <td rowspan="2"><input type="checkbox" name="it_use[<?php echo $i; ?>]" <?php echo ($row['it_use'] ? 'checked' : ''); ?> value="1"></td>
        <td rowspan="2"><?php echo $row['it_hit']; ?></td>
        <td rowspan="2" class="td_mng sv_use">
            <div class="sel_wrap">
                <button type="button" class="sel_btn">관리하기</button>
                <ul class="sel_ul">
                    <li class="sel_li"><a href="<?php echo $href; ?>" class="sel_a"><span class="sound_only"><?php echo htmlspecialchars2(cut_str($row['it_name'],250, "")); ?> </span>보기</a></li>
                    <li class="sel_li"><a href="./item_copy.php?it_id=<?php echo $row['it_id']; ?>&amp;ca_id=<?php echo $row['ca_id']; ?>" class="item_copy sel_a" target="_blank"><span class="sound_only"><?php echo htmlspecialchars2(cut_str($row['it_name'],250, "")); ?> </span>복사</a></li>
                    <li class="sel_li"><a href="./itemform.php?w=u&amp;it_id=<?php echo $row['it_id']; ?>&amp;ca_id=<?php echo $row['ca_id']; ?>&amp;<?php echo $qstr; ?>" class="sel_a"><span class="sound_only"><?php echo htmlspecialchars2(cut_str($row['it_name'],250, "")); ?> </span>수정</a></li>
                    <!-- <li class="sel_li"><a href="./itemformupdate.php?w=d&amp;it_id=<?php echo $row['it_id']; ?>&amp;ca_id=<?php echo $row['ca_id']; ?>&amp;<?php echo $qstr; ?>" class="sel_a" onclick="return delete_confirm();"><span class="sound_only"><?php echo htmlspecialchars2(cut_str($row['it_name'],250, "")); ?> </span>삭제</a></li> -->
                </ul>
            </div>
        </td>
    </tr>
    <tr>
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

    <div class="btn_list">
        <input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value">
        <?php if ($is_admin == 'super') { ?>
        <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value">
        <?php } ?>
    </div>
    <!-- <div class="btn_confirm">
        <input type="submit" value="일괄수정" class="btn_submit" accesskey="s">
    </div> -->
    </form>

</section>

<?php echo get_paging($config['cf_write_pages'], $page, $total_page, "{$_SERVER['PHP_SELF']}?$qstr&amp;page="); ?>

<script>
function fitemlist_submit(f)
{
    if (!is_checked("chk[]")) {
        alert(document.pressed+" 하실 항목을 하나 이상 선택하세요.");
        return false;
    }

    if(document.pressed == "선택삭제") {
        if(!confirm("선택한 자료를 정말 삭제하시겠습니까?")) {
            return false;
        }
    }

    return true;
}

$(function() {
    $(".item_copy").click(function() {
        var href = $(this).attr("href");
        window.open(href, "copywin", "left=100, top=100, width=300, height=200, scrollbars=0");
        return false;
    });
});

function excelform(url)
{
    var opt = "width=600,height=450,left=10,top=10";
    window.open(url, "win_excel", opt);
    return false;
}
</script>

<?php
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>
