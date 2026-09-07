<?php
$sub_menu = '300600';
require_once './_common.php';

auth_check_menu($auth, $sub_menu, "r");

if (!isset($g5['content_table'])) {
    die('<meta charset="utf-8">/data/dbconfig.php 파일에 <strong>$g5[\'content_table\'] = G5_TABLE_PREFIX.\'content\';</strong> 를 추가해 주세요.');
}
//내용(컨텐츠)정보 테이블이 있는지 검사한다.

$g5['title'] = '내용관리';
require_once G5_ADMIN_PATH . '/admin.head.php';

$sql_common = " from {$g5['content_table']} ";

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) {
    $page = 1;
} // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = "select * $sql_common order by co_id limit $from_record, {$config['cf_page_rows']} ";
$result = sql_query($sql);
?>

<div class="local_ov01 local_ov">
    <?php if ($page > 1) { ?><a href="<?php echo $_SERVER['SCRIPT_NAME']; ?>">처음으로</a><?php } ?>
    <span class="btn_ov01"><span class="ov_txt">전체 내용</span><span class="ov_num"> <?php echo $total_count; ?>건</span></span>
</div>

<div class="btn_fixed_top">
    <a href="./contentform.php" class="btn btn_01">내용 추가</a>
</div>

<div class="tbl_head01 tbl_wrap">
    <table>
        <caption><?php echo $g5['title']; ?> 목록</caption>
        <thead>
            <tr>
                <th scope="col">ID</th>
                <th scope="col">제목</th>
                <th scope="col">관리</th>
            </tr>
        </thead>
        <tbody>
            <?php for ($i = 0; $row = sql_fetch_array($result); $i++) {
                $bg = 'bg' . ($i % 2);
            ?>
                <tr class="<?php echo $bg; ?>">
                    <td class="td_id"><?php echo $row['co_id']; ?></td>
                    <td class="td_left"><?php echo htmlspecialchars2($row['co_subject']); ?></td>
                    <td class="td_mng td_mng_l">
                        <a href="./contentform.php?w=u&amp;co_id=<?php echo $row['co_id']; ?>" class="btn btn_03"><span class="sound_only"><?php echo htmlspecialchars2($row['co_subject']); ?> </span>수정</a>
                        <a href="<?php echo get_pretty_url('content', $row['co_id']); ?>" class="btn btn_02"><span class="sound_only"><?php echo htmlspecialchars2($row['co_subject']); ?> </span> 보기</a>
                        <a href="./contentformupdate.php?w=d&amp;co_id=<?php echo $row['co_id']; ?>" onclick="return delete_confirm(this);" class="btn btn_02"><span class="sound_only"><?php echo htmlspecialchars2($row['co_subject']); ?> </span>삭제</a>
                    </td>
                </tr>
            <?php
            }
            if ($i == 0) {
                echo '<tr><td colspan="3" class="empty_table">자료가 한건도 없습니다.</td></tr>';
            }
            ?>
        </tbody>
    </table>
</div>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}?$qstr&amp;page="); ?>

<?php
require_once G5_ADMIN_PATH . '/admin.tail.php';
