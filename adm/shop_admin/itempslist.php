<?php
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

//$qstr = 'page='.$page.'&amp;sst='.$sst.'&amp;sod='.$sod.'&amp;stx='.$stx;
$qstr  = $qstr.'&amp;sca='.$sca.'&amp;save_stx='.$stx;

$listall = '';
if ($sfl || $stx) // 검색렬일 때만 처음 버튼을 보여줌
    $listall = '<a href="'.$_SERVER['PHP_SELF'].'">전체목록</a>';
?>

<form name="flist">
<input type="hidden" name="page" value="<?php echo $page; ?>">
<input type="hidden" name="save_stx" value="<?php echo $stx; ?>">

<fieldset>
    <legend>사용후기 검색</legend>

    <span>
        <?php echo $listall; ?>
        전체 문의내역 <?php echo $total_count; ?>건
    </span>

    <label for="sca" class="sound_only">분류선택</label>
    <?php // ##### // 웹 접근성 취약 지점 시작 - 지운아빠 2013-04-12 ?>
    <select name="sca" id="sca">
        <option value=''>전체분류</option>
        <?php
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
    <?php // ##### // 웹 접근성 취약 지점 끝 ?>

    <label for="sfl" class="sound_only">검색대상</label>
    <select name="sfl" id="sfl">
        <option value="it_name" <?php echo get_selected($sfl, 'it_name'); ?>>상품명</option>
        <option value="a.it_id" <?php echo get_selected($sfl, 'a.it_id'); ?>>상품코드</option>
        <option value="is_name" <?php echo get_selected($sfl, 'is_name'); ?>>이름</option>
    </select>

    <label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
    <input type="text" name="stx" value="<?php echo $stx; ?>" required class="frm_input required">
    <input type="submit" value="검색" class="btn_submit">

</fieldset>

</form>

<section class="cbox">
    <h2>사용후기 목록</h2>

    <ul class="sort_odr">
        <li><?php echo subject_sort_link("it_name"); ?>상품명</a></li>
        <li><?php echo subject_sort_link("mb_name"); ?>이름</a></li>
        <li><?php echo subject_sort_link("is_subject"); ?>제목</a></li>
        <li><?php echo subject_sort_link("is_score"); ?>점수</a></li>
        <li><?php echo subject_sort_link("is_confirm"); ?>확인</a></li>
    </ul>

    <table class="frm_basic">
    <thead>
    <tr>
        <th scope="col">상품명</th>
        <th scope="col">이름</th>
        <th scope="col">제목</th>
        <th scope="col">점수</th>
        <th scope="col">확인</th>
        <th scope="col">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++)
    {
        $row['is_subject'] = cut_str($row['is_subject'], 30, "...");

        $href = G4_SHOP_URL.'/item.php?it_id='.$row['it_id'];

        $name = get_sideview($row['mb_id'], get_text($row['is_name']), $row['mb_email'], $row['mb_homepage']);

        $confirm = $row['is_confirm'] ? 'Y' : '&nbsp;';
    ?>

    <tr>
        <td><a href="<?php echo $href; ?>"><?php echo get_it_image($row['it_id'], 50, 50); ?><?php echo cut_str($row['it_name'],30); ?></a></td>
        <td class="td_name"><?php echo $name; ?></td>
        <td class="sit_ps_subject"><?php echo $row['is_subject']; ?></td>
        <td class="td_num"><?php echo $row['is_score']; ?></td>
        <td class="sit_ps_confirm"><?php echo $confirm; ?></td>
        <td class="td_smallmng">
            <a href="./itempsform.php?w=u&amp;is_id=<?php echo $row['is_id']; ?>&amp;$qstr"><span class="sound_only"><?php echo $row['is_subject']; ?> </span>수정</a>
            <a href="./itempsformupdate.php?w=d&amp;is_id=<?php echo $row['is_id']; ?>&amp;<?php echo $qstr; ?>" onclick="return delete_confirm();"><span class="sound_only"><?php echo $row['is_subject']; ?> </span>삭제</a>
        </td>
    </tr>

    <?php
    }

    if ($i == 0) {
        echo '<tr><td colspan="6" class="empty_table">자료가 없습니다.</td></tr>';
    }
    ?>
    </tbody>
    </table>

</section>


<?php echo get_paging($config['cf_write_pages'], $page, $total_page, "{$_SERVER['PHP_SELF']}?$qstr&amp;page="); ?>

<?php
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>
