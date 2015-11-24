<?php
$sub_menu = '300600';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

if( !isset($g5['content_table']) ){
    die('<meta charset="utf-8">/data/dbconfig.php 파일에 <strong>$g5[\'content_table\'] = G5_TABLE_PREFIX.\'content\';</strong> 를 추가해 주세요.');
}
//내용(컨텐츠)정보 테이블이 있는지 검사한다.
if(!sql_query(" DESCRIBE {$g5['content_table']} ", false)) {
    if(sql_query(" DESCRIBE {$g5['g5_shop_content_table']} ", false)) {
        sql_query(" ALTER TABLE {$g5['g5_shop_content_table']} RENAME TO `{$g5['content_table']}` ;", false);
    } else {
       $query_cp = sql_query(" CREATE TABLE IF NOT EXISTS `{$g5['content_table']}` (
                      `co_id` varchar(20) NOT NULL DEFAULT '',
                      `co_html` tinyint(4) NOT NULL DEFAULT '0',
                      `co_subject` varchar(255) NOT NULL DEFAULT '',
                      `co_content` longtext NOT NULL,
                      `co_hit` int(11) NOT NULL DEFAULT '0',
                      `co_include_head` varchar(255) NOT NULL,
                      `co_include_tail` varchar(255) NOT NULL,
                      PRIMARY KEY (`co_id`)
                    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 ", true);

        // 내용관리 생성
        sql_query(" insert into `{$g5['content_table']}` set co_id = 'company', co_html = '1', co_subject = '회사소개', co_content= '<p align=center><b>회사소개에 대한 내용을 입력하십시오.</b></p>' ", false );
        sql_query(" insert into `{$g5['content_table']}` set co_id = 'privacy', co_html = '1', co_subject = '개인정보 처리방침', co_content= '<p align=center><b>개인정보 처리방침에 대한 내용을 입력하십시오.</b></p>' ", false );
        sql_query(" insert into `{$g5['content_table']}` set co_id = 'provision', co_html = '1', co_subject = '서비스 이용약관', co_content= '<p align=center><b>서비스 이용약관에 대한 내용을 입력하십시오.</b></p>' ", false );
    }
}

$g5['title'] = '내용관리';
include_once (G5_ADMIN_PATH.'/admin.head.php');

$sql_common = " from {$g5['content_table']} ";

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = "select * $sql_common order by co_id limit $from_record, {$config['cf_page_rows']} ";
$result = sql_query($sql);
?>

<div class="local_ov01 local_ov">
    <?php if ($page > 1) {?><a href="<?php echo $_SERVER['SCRIPT_NAME']; ?>">처음으로</a><?php } ?>
    <span>전체 내용 <?php echo $total_count; ?>건</span>
</div>

<div class="btn_add01 btn_add">
    <a href="./contentform.php">내용 추가</a>
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
    <?php for ($i=0; $row=sql_fetch_array($result); $i++) {
        $bg = 'bg'.($i%2);
    ?>
    <tr class="<?php echo $bg; ?>">
        <td class="td_id"><?php echo $row['co_id']; ?></td>
        <td><?php echo htmlspecialchars2($row['co_subject']); ?></td>
        <td class="td_mng">
            <a href="./contentform.php?w=u&amp;co_id=<?php echo $row['co_id']; ?>"><span class="sound_only"><?php echo htmlspecialchars2($row['co_subject']); ?> </span>수정</a>
            <a href="<?php echo G5_BBS_URL; ?>/content.php?co_id=<?php echo $row['co_id']; ?>"><span class="sound_only"><?php echo htmlspecialchars2($row['co_subject']); ?> </span> 보기</a>
            <a href="./contentformupdate.php?w=d&amp;co_id=<?php echo $row['co_id']; ?>" onclick="return delete_confirm(this);"><span class="sound_only"><?php echo htmlspecialchars2($row['co_subject']); ?> </span>삭제</a>
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
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
