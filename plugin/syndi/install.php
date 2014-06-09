<?php
include_once('./_common.php');

if ($is_admin != 'super') {
    alert('최고관리자만 설치가 가능합니다.');
}

$sql = " CREATE TABLE IF NOT EXISTS `{$g5['syndi_log_table']}` ( `content_id` int(11) NOT NULL, `bbs_id` varchar(50) NOT NULL, `title` text NOT NULL, `link_alternative` varchar(250) NOT NULL, `delete_date` varchar(14) NOT NULL, PRIMARY KEY  (`content_id`,`bbs_id`) ) ENGINE=MyISAM DEFAULT CHARSET=utf8 ";
sql_query($sql, false);

alert('신디케이션 로그 테이블을 생성했습니다.', G5_URL);
?>