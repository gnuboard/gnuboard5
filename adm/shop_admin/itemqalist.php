<?php
$sub_menu = '400660';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g4['title'] = '상품문의';
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

if ($sfl == "")  $sfl = "it_name";
if (!$sst) {
    $sst = "iq_id";
    $sod = "desc";
}

$sql_common = "  from {$g4['shop_item_qa_table']} a
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
          order by $sst $sod, iq_id desc
          limit $from_record, $rows ";
$result = sql_query($sql);

//$qstr = 'page='.$page.'&amp;sst='.$sst.'&amp;sod='.$sod.'&amp;stx='.$stx;
$qstr  = $qstr.'&amp;sca='.$sca.'&amp;save_stx='.$stx;

$listall = '';
if ($sfl || $stx) // 검색 결과일 때만 처음 버튼을 보여줌
    $listall = '<a href="'.$_SERVER['PHP_SELF'].'">전체목록</a>';
?>

<form name="flist">
<input type="hidden" name="page" value="<?php echo $page; ?>">
<input type="hidden" name="save_stx" value="<?php echo $stx; ?>">

<fieldset>
    <legend>상품문의 검색</legend>

    <span>
        <?php echo $listall; ?>
        전체 문의내역 <?php echo $total_count; ?>건
    </span>

    <?php // ##### // 웹 접근성 취약 지점 시작 - 지운아빠 2013-04-12 ?>
    <label for="sca" class="sound_only">분류선택</label>
    <select name="sca" id="sca">
        <option value="">전체분류</option>
        <?php
        $sql1 = " select ca_id, ca_name from {$g4['shop_category_table']} order by ca_id ";
        $result1 = sql_query($sql1);
        for ($i=0; $row1=mysql_fetch_array($result1); $i++) {
            $len = strlen($row1['ca_id']) / 2 - 1;
            $nbsp = "";
            for ($i=0; $i<$len; $i++) $nbsp .= "&nbsp;&nbsp;&nbsp;";
            echo '<option value="'.$row1['ca_id'].'" '.get_selected($sca, $row1['ca_id']).'>'.$nbsp.$row1['ca_name'].'</option>'.PHP_EOL;
        }
        ?>
    </select>
    <?php // ##### // 웹 접근성 취약 지점 끝 ?>

    <label for="sfl" class="sound_only">검색대상</label>
    <select name="sfl" id="sfl">
        <option value="it_name" <?php echo get_selected($sfl, 'it_name'); ?>>상품명</option>
        <option value="a.it_id" <?php echo get_selected($sfl, 'a.it_id'); ?>>상품코드</option>
    </select>

    <label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
    <input type="text" name="stx" value="<?php echo $stx; ?>" id="stx" required class="frm_input required">
    <input type="submit" value="검색" class="btn_submit">

</fieldset>

</form>

<section class="cbox">
    <h2>상품문의 목록</h2>

    <ul class="sort_odr">
        <li><?php echo subject_sort_link('it_name'); ?>상품명<span class="sound_only"> 순 정렬</span></a></li>
        <li><?php echo subject_sort_link('mb_name'); ?>이름<span class="sound_only"> 순 정렬</span></a></li>
        <li><?php echo subject_sort_link('iq_subject'); ?>질문<span class="sound_only"> 순 정렬</span></a></li>
        <li><?php echo subject_sort_link('iq_answer'); ?>답변<span class="sound_only"> 순 정렬</span></a></li>
    </ul>

    <table class="frm_basic">
    <thead>
    <tr>
        <th scope="col">상품명</th>
        <th scope="col">이름</th>
        <th scope="col">질문</th>
        <th scope="col">답변</th>
        <th scope="col">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php
    for ($i=0; $row=mysql_fetch_array($result); $i++) {
        $row['iq_subject'] = cut_str($row['iq_subject'], 30, "...");

        $href = G4_SHOP_URL.'/item.php?it_id='.$row['it_id'];

        $name = get_sideview($row['mb_id'], $row['iq_name'], $row['mb_email'], $row['mb_homepage']);

        $answer = $row['iq_answer'] ? 'Y' : '&nbsp;';
     ?>
    <tr>
        <td><a href="<?php echo $href; ?>"><?php echo get_it_image($row['it_id'], 50, 50); ?><?php echo cut_str($row['it_name'],30); ?></a></td>
        <td class="td_name"><?php echo $name; ?></td>
        <td class="sit_qa_subject"><?php echo $row['iq_subject']; ?></td>
        <td class="sit_qa_answer"><?php echo $answer; ?></td>
        <td class="td_smallmng">
            <a href="./itemqaform.php?w=u&amp;iq_id=<?php echo $row['iq_id']; ?>&amp;<?php echo $qstr; ?>" class="sel_a"><span class="sound_only"><?php echo $row['iq_subject']; ?> </span>수정</a>
            <a href="javascript:del('./itemqaformupdate.php?w=d&amp;iq_id=<?php echo $row['iq_id']; ?>&amp;$qstr');" class="sel_a"><span class="sound_only"><?php echo $row['iq_subject']; ?> </span>삭제</a>
        </td>
    </tr>
    <?php
    }
    if ($i == 0) {
        echo '<tr><td colspan="5" class="empty_table"><span>자료가 없습니다.</span></td></tr>';
    }
    ?>
    </tbody>
    </table>

</section>

<?php echo get_paging($config['cf_write_pages'], $page, $total_page, "{$_SERVER['PHP_SELF']}?$qstr&amp;page="); ?>

<?php
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>
