<?php
$sub_menu = '300700';
require_once './_common.php';

auth_check_menu($auth, $sub_menu, "r");

$g5['title'] = 'FAQ 상세관리';
if (isset($_REQUEST['fm_subject'])) {
    $fm_subject = clean_xss_tags($_REQUEST['fm_subject'], 1, 1, 255);
    $g5['title'] .= ' : ' . $fm_subject;
}

$fm_id = isset($fm_id) ? (int) $fm_id : 0;

require_once G5_ADMIN_PATH . '/admin.head.php';

$sql = " select * from {$g5['faq_master_table']} where fm_id = '$fm_id' ";
$fm = sql_fetch($sql);

$sql_common = " from {$g5['faq_table']} where fm_id = '$fm_id' ";

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$sql = "select * $sql_common order by fa_order , fa_id ";
$result = sql_query($sql);
?>

<div class="local_ov01 local_ov">
    <span class="btn_ov01"><span class="ov_txt"> 등록된 FAQ 상세내용</span><span class="ov_num"> <?php echo $total_count; ?>건</span></span>
</div>

<div class="local_desc01 local_desc">
    <ol>
        <li>FAQ는 무제한으로 등록할 수 있습니다</li>
        <li><strong>FAQ 상세내용 추가</strong>를 눌러 자주하는 질문과 답변을 입력합니다.</li>
    </ol>
</div>

<div class="btn_fixed_top">
    <a href="./faqmasterlist.php" class="btn btn_02">FAQ 관리</a>
    <a href="./faqform.php?fm_id=<?php echo $fm['fm_id']; ?>" class="btn btn_01">FAQ 상세내용 추가</a>
</div>

<div class="tbl_head01 tbl_wrap">
    <table>
        <caption><?php echo $g5['title']; ?> 목록</caption>
        <thead>
            <tr>
                <th scope="col">번호</th>
                <th scope="col">제목</th>
                <th scope="col">순서</th>
                <th scope="col">관리</th>
            </tr>
        </thead>
        <tbody>
            <?php
            for ($i = 0; $row = sql_fetch_array($result); $i++) {
                $row1 = sql_fetch(" select COUNT(*) as cnt from {$g5['faq_table']} where fm_id = '{$row['fm_id']}' ");
                $cnt = $row1['cnt'];

                $s_mod = icon("수정", "");
                $s_del = icon("삭제", "");

                $num = $i + 1;

                $bg = 'bg' . ($i % 2);

                $fa_subject = conv_content($row['fa_subject'], 1);
                ?>

                <tr class="<?php echo $bg; ?>">
                    <td class="td_num"><?php echo $num; ?></td>
                    <td class="td_left"><?php echo $fa_subject; ?></td>
                    <td class="td_num"><?php echo $row['fa_order']; ?></td>
                    <td class="td_mng td_mng_m">
                        <a href="./faqform.php?w=u&amp;fm_id=<?php echo $row['fm_id']; ?>&amp;fa_id=<?php echo $row['fa_id']; ?>" class="btn btn_03"><span class="sound_only"><?php echo $fa_subject; ?> </span>수정</a>
                        <a href="./faqformupdate.php?w=d&amp;fm_id=<?php echo $row['fm_id']; ?>&amp;fa_id=<?php echo $row['fa_id']; ?>" onclick="return delete_confirm(this);" class="btn btn_02"><span class="sound_only"><?php echo $fa_subject; ?> </span>삭제</a>
                    </td>
                </tr>
                <?php
            }

            if ($i == 0) {
                echo '<tr><td colspan="4" class="empty_table">자료가 없습니다.</td></tr>';
            }
            ?>
        </tbody>
    </table>

</div>


<?php
require_once G5_ADMIN_PATH . '/admin.tail.php';
