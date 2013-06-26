<?php
$sub_menu = '400710';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g4['title'] = 'FAQ관리';
include_once (G4_ADMIN_PATH.'/admin.head.php');

$sql_common = " from {$g4['shop_faq_master_table']} ";

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = "select * $sql_common order by fm_id desc limit $from_record, {$config['cf_page_rows']} ";
$result = sql_query($sql);
?>

<section class="cbox">
    <h2>FAQ관리</h2>

    <p>
        <?php if ($page > 1) {?><a href="<?php echo $_SERVER['PHP_SELF']; ?>">처음으로</a><?php } ?>
        <span>전체 FAQ <?php echo $total_count; ?>건</span>
    </p>

    <ol>
        <li>FAQ는 무제한으로 등록할 수 있습니다</li>
        <li><strong>FAQ추가</strong>를 눌러 FAQ Master를 생성합니다. (하나의 FAQ 타이틀 생성 : 자주하시는 질문, 이용안내..등 )</li>
        <li>생성한 FAQ Master 의 <strong>제목</strong>을 눌러 세부 내용을 관리할 수 있습니다.</li>
    </ol>

    <div class="btn_add">
        <a href="./faqmasterform.php">FAQ추가</a>
    </div>

    <table>
    <thead>
    <tr>
        <th scope="col">ID</th>
        <th scope="col">제목</th>
        <th scope="col">FAQ수</th>
        <th scope="col">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php for ($i=0; $row=mysql_fetch_array($result); $i++) {
        $sql1 = " select COUNT(*) as cnt from {$g4['shop_faq_table']} where fm_id = '{$row['fm_id']}' ";
        $row1 = sql_fetch($sql1);
        $cnt = $row1['cnt'];
    ?>
    <tr>
        <td class="td_num"><?php echo $row['fm_id']; ?></td>
        <td><a href="./faqlist.php?fm_id=<?php echo $row['fm_id']; ?>&amp;fm_subject=<?php echo $row['fm_subject']; ?>"><?php echo stripslashes($row['fm_subject']); ?></a></td>
        <td class="td_num"><?php echo $cnt; ?></td>
        <td class="td_mng">
            <a href="<?php echo G4_SHOP_URL; ?>/faq.php?fm_id=<?php echo $row['fm_id']; ?>"><span class="sound_only"><?php echo stripslashes($row['fm_subject']); ?> </span>보기</a>
            <a href="./faqmasterform.php?w=u&amp;fm_id=<?php echo $row['fm_id']; ?>"><span class="sound_only"><?php echo stripslashes($row['fm_subject']); ?> </span>수정</a>
            <a href="./faqmasterformupdate.php?w=d&amp;fm_id=<?php echo $row['fm_id']; ?>" onclick="return delete_confirm();"><span class="sound_only"><?php echo stripslashes($row['fm_subject']); ?> </span>삭제</a>
        </td>
    </tr>
    <?php
    }

    if ($i == 0){
        echo '<tr><td colspan="5" class="empty_table"><span>자료가 한건도 없습니다.</span></td></tr>';
    }
    ?>
    </tbody>
    </table>
</section>

<?php echo get_paging(G4_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['PHP_SELF']}?$qstr&amp;page="); ?>

<?php
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>
