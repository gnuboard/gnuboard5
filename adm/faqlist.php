<?php
$sub_menu = '300700';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

//dbconfig파일에 $g5['faq_table'] , $g5['faq_master_table'] 배열변수가 있는지 체크
if( !isset($g5['faq_table']) || !isset($g5['faq_master_table']) ){
    die('/data/dbconfig.php 파일에 <br ><strong>$g5[\'faq_table\'] = G5_TABLE_PREFIX.\'faq\';</strong><br ><strong>$g5[\'faq_master_table\'] = G5_TABLE_PREFIX.\'faq_master\';</strong><br > 를 추가해 주세요.');
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

$g5['title'] = 'FAQ 상세관리';
if ($fm_subject) $g5['title'] .= ' : '.$fm_subject;
include_once (G5_ADMIN_PATH.'/admin.head.php');

$sql = " select * from {$g5['faq_master_table']} where fm_id = '$fm_id' ";
$fm = sql_fetch($sql);

$sql_common = " from {$g5['faq_table']} where fm_id = '$fm_id' ";

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row[cnt];

$sql = "select * $sql_common order by fa_order , fa_id ";
$result = sql_query($sql);
?>

<div class="local_ov01 local_ov">
    등록된 FAQ 상세내용 <?php echo $total_count; ?>건
</div>

<div class="local_desc01 local_desc">
    <ol>
        <li>FAQ는 무제한으로 등록할 수 있습니다</li>
        <li><strong>FAQ 상세내용 추가</strong>를 눌러 자주하는 질문과 답변을 입력합니다.</li>
    </ol>
</div>

<div class="btn_add01 btn_add">
    <a href="./faqform.php?fm_id=<?php echo $fm['fm_id']; ?>">FAQ 상세내용 추가</a>
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
    for ($i=0; $row=sql_fetch_array($result); $i++)
    {
        $row1 = sql_fetch(" select COUNT(*) as cnt from {$g5['faq_table']} where fm_id = '{$row['fm_id']}' ");
        $cnt = $row1[cnt];

        $s_mod = icon("수정", "");
        $s_del = icon("삭제", "");

        $num = $i + 1;

        $bg = 'bg'.($i%2);
    ?>

    <tr class="<?php echo $bg; ?>">
        <td class="td_num"><?php echo $num; ?></td>
        <td><?php echo stripslashes($row['fa_subject']); ?></td>
        <td class="td_num"><?php echo $row['fa_order']; ?></td>
        <td class="td_mngsmall">
            <a href="./faqform.php?w=u&amp;fm_id=<?php echo $row['fm_id']; ?>&amp;fa_id=<?php echo $row['fa_id']; ?>"><span class="sound_only"><?php echo stripslashes($row['fa_subject']); ?> </span>수정</a>
            <a href="javascript:del('./faqformupdate.php?w=d&amp;fm_id=<?php echo $row['fm_id']; ?>&amp;fa_id=<?php echo $row['fa_id']; ?>');"><span class="sound_only"><?php echo stripslashes($row['fa_subject']); ?> </span>삭제</a>
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

<div class="btn_confirm01 btn_confirm">
    <a href="./faqmasterlist.php">FAQ 관리</a>
</div>


<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
