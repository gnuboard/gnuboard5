<?php
$sub_menu = '300700';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

//dbconfig파일에 $g5['faq_table'] , $g5['faq_master_table'] 배열변수가 있는지 체크
if( !isset($g5['faq_table']) || !isset($g5['faq_master_table']) ){
    die('<meta charset="utf-8">/data/dbconfig.php 파일에 <br ><strong>$g5[\'faq_table\'] = G5_TABLE_PREFIX.\'faq\';</strong><br ><strong>$g5[\'faq_master_table\'] = G5_TABLE_PREFIX.\'faq_master\';</strong><br > 를 추가해 주세요.');
}

//자주하시는 질문 마스터 테이블이 있는지 검사한다.
if(!sql_query(" DESCRIBE {$g5['faq_master_table']} ", false)) {
    if(sql_query(" DESCRIBE {$g5['g5_shop_faq_master_table']} ", false)) {
        sql_query(" ALTER TABLE {$g5['g5_shop_faq_master_table']} RENAME TO `{$g5['faq_master_table']}` ;", false);
    } else {
       $query_cp = sql_query(" CREATE TABLE IF NOT EXISTS `{$g5['faq_master_table']}` (
                      `fm_id` int(11) NOT NULL AUTO_INCREMENT,
                      `fm_subject` varchar(255) NOT NULL DEFAULT '',
                      `fm_head_html` text NOT NULL,
                      `fm_tail_html` text NOT NULL,
                      `fm_order` int(11) NOT NULL DEFAULT '0',
                      PRIMARY KEY (`fm_id`)
                    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 ", true);
    }
    // FAQ Master
    sql_query(" insert into `{$g5['faq_master_table']}` set fm_id = '1', fm_subject = '자주하시는 질문' ", false);
}

//자주하시는 질문 테이블이 있는지 검사한다.
if(!sql_query(" DESCRIBE {$g5['faq_table']} ", false)) {
    if(sql_query(" DESCRIBE {$g5['g5_shop_faq_table']} ", false)) {
        sql_query(" ALTER TABLE {$g5['g5_shop_faq_table']} RENAME TO `{$g5['faq_table']}` ;", false);
    } else {
       $query_cp = sql_query(" CREATE TABLE IF NOT EXISTS `{$g5['faq_table']}` (
                      `fa_id` int(11) NOT NULL AUTO_INCREMENT,
                      `fm_id` int(11) NOT NULL DEFAULT '0',
                      `fa_subject` text NOT NULL,
                      `fa_content` text NOT NULL,
                      `fa_order` int(11) NOT NULL DEFAULT '0',
                      PRIMARY KEY (`fa_id`),
                      KEY `fm_id` (`fm_id`)
                    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 ", true);
    }
}

$g5['title'] = 'FAQ관리';
include_once (G5_ADMIN_PATH.'/admin.head.php');

$sql_common = " from {$g5['faq_master_table']} ";

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = "select * $sql_common order by fm_order, fm_id limit $from_record, {$config['cf_page_rows']} ";
$result = sql_query($sql);
?>

<div class="local_ov01 local_ov">
    <?php if ($page > 1) {?><a href="<?php echo $_SERVER['PHP_SELF']; ?>">처음으로</a><?php } ?>
    <span>전체 FAQ <?php echo $total_count; ?>건</span>
</div>

<div class="local_desc01 local_desc">
    <ol>
        <li>FAQ는 무제한으로 등록할 수 있습니다</li>
        <li><strong>FAQ추가</strong>를 눌러 FAQ Master를 생성합니다. (하나의 FAQ 타이틀 생성 : 자주하시는 질문, 이용안내..등 )</li>
        <li>생성한 FAQ Master 의 <strong>제목</strong>을 눌러 세부 내용을 관리할 수 있습니다.</li>
    </ol>
</div>

<div class="btn_add01 btn_add">
    <a href="./faqmasterform.php">FAQ추가</a>
</div>

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?> 목록</caption>
    <thead>
    <tr>
        <th scope="col">ID</th>
        <th scope="col">제목</th>
        <th scope="col">FAQ수</th>
        <th scope="col">순서</th>
        <th scope="col">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php for ($i=0; $row=mysql_fetch_array($result); $i++) {
        $sql1 = " select COUNT(*) as cnt from {$g5['faq_table']} where fm_id = '{$row['fm_id']}' ";
        $row1 = sql_fetch($sql1);
        $cnt = $row1['cnt'];
        $bg = 'bg'.($i%2);
    ?>
    <tr class="<?php echo $bg; ?>">
        <td class="td_num"><?php echo $row['fm_id']; ?></td>
        <td><a href="./faqlist.php?fm_id=<?php echo $row['fm_id']; ?>&amp;fm_subject=<?php echo $row['fm_subject']; ?>"><?php echo stripslashes($row['fm_subject']); ?></a></td>
        <td class="td_num"><?php echo $cnt; ?></td>
        <td class="td_num"><?php echo $row['fm_order']?></td>
        <td class="td_mng">
            <a href="<?php echo G5_BBS_URL; ?>/faq.php?fm_id=<?php echo $row['fm_id']; ?>"><span class="sound_only"><?php echo stripslashes($row['fm_subject']); ?> </span>보기</a>
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
</div>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['PHP_SELF']}?$qstr&amp;page="); ?>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
