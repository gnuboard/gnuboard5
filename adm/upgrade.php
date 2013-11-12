<?
$sub_menu = "100600";
include_once("./_common.php");

check_demo();

if ($is_admin != "super")
    alert("최고관리자만 접근 가능합니다.", $g5[path]);

$g5[title] = "업그레이드";
include_once("./admin.head.php");

/*
// 4.20.00
// 1:1 게시판 테이블 생성
$sql = " CREATE TABLE `$g5[oneboard_table]` (
  `ob_table` varchar(20) NOT NULL,
  `ob_subject` varchar(255) NOT NULL,
  `ob_admin` varchar(255) NOT NULL,
  `ob_skin` varchar(255) NOT NULL,
  `ob_write_level` tinyint(4) NOT NULL,
  `ob_upload_level` tinyint(4) NOT NULL,
  `ob_use_dhtml_editor` tinyint(4) NOT NULL,
  `ob_use_email` tinyint(4) NOT NULL,
  `ob_table_width` smallint(6) NOT NULL,
  `ob_subject_len` smallint(6) NOT NULL,
  `ob_page_rows` smallint(6) NOT NULL,
  `ob_image_width` smallint(6) NOT NULL,
  `ob_image_head` varchar(255) NOT NULL,
  `ob_image_tail` varchar(255) NOT NULL,
  `ob_include_head` varchar(255) NOT NULL,
  `ob_include_tail` varchar(255) NOT NULL,
  `ob_content_head` text NOT NULL,
  `ob_content_tail` text NOT NULL,
  `ob_insert_content` text NOT NULL,
  `ob_1_subj` varchar(255) NOT NULL,
  `ob_2_subj` varchar(255) NOT NULL,
  `ob_3_subj` varchar(255) NOT NULL,
  `ob_4_subj` varchar(255) NOT NULL,
  `ob_5_subj` varchar(255) NOT NULL,
  `ob_6_subj` varchar(255) NOT NULL,
  `ob_7_subj` varchar(255) NOT NULL,
  `ob_8_subj` varchar(255) NOT NULL,
  `ob_9_subj` varchar(255) NOT NULL,
  `ob_10_subj` varchar(255) NOT NULL,
  `ob_1` varchar(255) NOT NULL,
  `ob_2` varchar(255) NOT NULL,
  `ob_3` varchar(255) NOT NULL,
  `ob_4` varchar(255) NOT NULL,
  `ob_5` varchar(255) NOT NULL,
  `ob_6` varchar(255) NOT NULL,
  `ob_7` varchar(255) NOT NULL,
  `ob_8` varchar(255) NOT NULL,
  `ob_9` varchar(255) NOT NULL,
  `ob_10` varchar(255) NOT NULL,
  PRIMARY KEY  (`ob_table`)
) ";
sql_query($sql, false);
*/

// 회원테이블의 주키를 mb_no 로 교체
sql_query(" ALTER TABLE `$g5[member_table]` DROP PRIMARY KEY ", false);
sql_query(" ALTER TABLE `$g5[member_table]` ADD `mb_no` INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST ", false);
sql_query(" ALTER TABLE `$g5[member_table]` ADD UNIQUE `mb_id` ( `mb_id` ) ", false);


// 4.11.00
// 트랙백 토큰
sql_query("CREATE TABLE `$g5[token_table]` (
  `to_token` varchar(32) NOT NULL default '',
  `to_datetime` datetime NOT NULL default '0000-00-00 00:00:00',
  `to_ip` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`to_token`),
  KEY `to_datetime` (`to_datetime`),
  KEY `to_ip` (`to_ip`)
) TYPE=MyISAM", FALSE);

// 4.09.00
// 기본환경설정 테이블 필드 추가
sql_query(" ALTER TABLE `{$g5['config_table']}` ADD `cf_1_subj` VARCHAR( 255 ) NOT NULL AFTER `cf_open_modify` ", FALSE);
sql_query(" ALTER TABLE `{$g5['config_table']}` ADD `cf_2_subj` VARCHAR( 255 ) NOT NULL AFTER `cf_1_subj` ", FALSE);
sql_query(" ALTER TABLE `{$g5['config_table']}` ADD `cf_3_subj` VARCHAR( 255 ) NOT NULL AFTER `cf_2_subj` ", FALSE);
sql_query(" ALTER TABLE `{$g5['config_table']}` ADD `cf_4_subj` VARCHAR( 255 ) NOT NULL AFTER `cf_3_subj` ", FALSE);
sql_query(" ALTER TABLE `{$g5['config_table']}` ADD `cf_5_subj` VARCHAR( 255 ) NOT NULL AFTER `cf_4_subj` ", FALSE);
sql_query(" ALTER TABLE `{$g5['config_table']}` ADD `cf_6_subj` VARCHAR( 255 ) NOT NULL AFTER `cf_5_subj` ", FALSE);
sql_query(" ALTER TABLE `{$g5['config_table']}` ADD `cf_7_subj` VARCHAR( 255 ) NOT NULL AFTER `cf_6_subj` ", FALSE);
sql_query(" ALTER TABLE `{$g5['config_table']}` ADD `cf_8_subj` VARCHAR( 255 ) NOT NULL AFTER `cf_7_subj` ", FALSE);
sql_query(" ALTER TABLE `{$g5['config_table']}` ADD `cf_9_subj` VARCHAR( 255 ) NOT NULL AFTER `cf_8_subj` ", FALSE);
sql_query(" ALTER TABLE `{$g5['config_table']}` ADD `cf_10_subj` VARCHAR( 255 ) NOT NULL AFTER `cf_9_subj` ", FALSE);

// 게시판 그룹 테이블 필드 추가
sql_query(" ALTER TABLE `{$g5['group_table']}` ADD `gr_1_subj` VARCHAR( 255 ) NOT NULL AFTER `gr_use_access` ", FALSE);
sql_query(" ALTER TABLE `{$g5['group_table']}` ADD `gr_2_subj` VARCHAR( 255 ) NOT NULL AFTER `gr_1_subj` ", FALSE);
sql_query(" ALTER TABLE `{$g5['group_table']}` ADD `gr_3_subj` VARCHAR( 255 ) NOT NULL AFTER `gr_2_subj` ", FALSE);
sql_query(" ALTER TABLE `{$g5['group_table']}` ADD `gr_4_subj` VARCHAR( 255 ) NOT NULL AFTER `gr_3_subj` ", FALSE);
sql_query(" ALTER TABLE `{$g5['group_table']}` ADD `gr_5_subj` VARCHAR( 255 ) NOT NULL AFTER `gr_4_subj` ", FALSE);
sql_query(" ALTER TABLE `{$g5['group_table']}` ADD `gr_6_subj` VARCHAR( 255 ) NOT NULL AFTER `gr_5_subj` ", FALSE);
sql_query(" ALTER TABLE `{$g5['group_table']}` ADD `gr_7_subj` VARCHAR( 255 ) NOT NULL AFTER `gr_6_subj` ", FALSE);
sql_query(" ALTER TABLE `{$g5['group_table']}` ADD `gr_8_subj` VARCHAR( 255 ) NOT NULL AFTER `gr_7_subj` ", FALSE);
sql_query(" ALTER TABLE `{$g5['group_table']}` ADD `gr_9_subj` VARCHAR( 255 ) NOT NULL AFTER `gr_8_subj` ", FALSE);
sql_query(" ALTER TABLE `{$g5['group_table']}` ADD `gr_10_subj` VARCHAR( 255 ) NOT NULL AFTER `gr_9_subj` ", FALSE);

// 게시판 테이블 필드 추가
sql_query(" ALTER TABLE `{$g5['board_table']}` ADD `bo_sort_field` VARCHAR( 255 ) NOT NULL AFTER `bo_use_email` ", FALSE);
sql_query(" ALTER TABLE `{$g5['board_table']}` ADD `bo_1_subj` VARCHAR( 255 ) NOT NULL AFTER `bo_sort_field` ", FALSE);
sql_query(" ALTER TABLE `{$g5['board_table']}` ADD `bo_2_subj` VARCHAR( 255 ) NOT NULL AFTER `bo_1_subj` ", FALSE);
sql_query(" ALTER TABLE `{$g5['board_table']}` ADD `bo_3_subj` VARCHAR( 255 ) NOT NULL AFTER `bo_2_subj` ", FALSE);
sql_query(" ALTER TABLE `{$g5['board_table']}` ADD `bo_4_subj` VARCHAR( 255 ) NOT NULL AFTER `bo_3_subj` ", FALSE);
sql_query(" ALTER TABLE `{$g5['board_table']}` ADD `bo_5_subj` VARCHAR( 255 ) NOT NULL AFTER `bo_4_subj` ", FALSE);
sql_query(" ALTER TABLE `{$g5['board_table']}` ADD `bo_6_subj` VARCHAR( 255 ) NOT NULL AFTER `bo_5_subj` ", FALSE);
sql_query(" ALTER TABLE `{$g5['board_table']}` ADD `bo_7_subj` VARCHAR( 255 ) NOT NULL AFTER `bo_6_subj` ", FALSE);
sql_query(" ALTER TABLE `{$g5['board_table']}` ADD `bo_8_subj` VARCHAR( 255 ) NOT NULL AFTER `bo_7_subj` ", FALSE);
sql_query(" ALTER TABLE `{$g5['board_table']}` ADD `bo_9_subj` VARCHAR( 255 ) NOT NULL AFTER `bo_8_subj` ", FALSE);
sql_query(" ALTER TABLE `{$g5['board_table']}` ADD `bo_10_subj` VARCHAR( 255 ) NOT NULL AFTER `bo_9_subj` ", FALSE);

// 게시판 리스트에서 코멘트를 포함하여 최근에 올라온 글을 확인하는 시간 필드 생성
$sql = " select bo_table from $g5[board_table] ";
$res = sql_query($sql);
for($i=0;$row=sql_fetch_array($res);$i++)
{
    sql_query(" ALTER TABLE `{$g5['write_prefix']}{$row[bo_table]}` ADD `wr_last` VARCHAR( 19 ) NOT NULL AFTER `wr_datetime` ", FALSE);
    $sql2 = " select count(*) as cnt from `{$g5['write_prefix']}{$row[bo_table]}` where wr_last <> '' ";
    $row2 = sql_fetch_array($sql2);
    if (!$row2[cnt]) // 원글에만 최근시간을 반영합니다.
        sql_query(" UPDATE `{$g5['write_prefix']}{$row[bo_table]}` set wr_last = wr_datetime WHERE wr_is_comment = 0 ");
}


// 4.08.00
// 정보공개를 바꾸면 일정기간 동안 변경할 수 없음
sql_query(" ALTER TABLE `{$g5[member_table]}` ADD `mb_open_date` DATE NOT NULL AFTER `mb_open` ", false);
sql_query(" ALTER TABLE `{$g5[config_table]}` ADD `cf_open_modify` INT NOT NULL AFTER `cf_stipulation` ", false);
// 게시물 추천테이블 생성
sql_query(" CREATE TABLE `{$g5[board_good_table]}` (
  `bg_id` int(11) NOT NULL auto_increment,
  `bo_table` varchar(20) NOT NULL default '',
  `wr_id` int(11) NOT NULL default '0',
  `mb_id` varchar(20) NOT NULL default '',
  `bg_flag` varchar(255) NOT NULL default '',
  `bg_datetime` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`bg_id`),
  UNIQUE KEY `fkey1` (`bo_table`,`wr_id`,`mb_id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ", false);


// 4.07.00
// 최근게시물에 회원아이디 필드 및 인덱스 추가
sql_query(" ALTER TABLE `{$g5['board_new_table']}` ADD `mb_id` VARCHAR( 20 ) NOT NULL ", false);
sql_query(" ALTER TABLE `{$g5['board_new_table']}` ADD INDEX `mb_id` ( `mb_id` ) ", false);

$sql = " select * from $g5[board_new_table] ";
$res = sql_query($sql);
for ($i=0; $row=sql_fetch_array($res); $i++)
{
    $ttmp = $g5[write_prefix].$row[bo_table];
    $sql2 = " select mb_id from $ttmp where wr_id = '$row[wr_id]' ";
    $row2 = sql_fetch($sql2);

    $sql3 = " update $g5[board_new_table] set mb_id = '$row2[mb_id]' where bn_id = '$row[bn_id]' ";
    sql_query($sql3, false);
}

/*
// 그룹접근회원테이블에 auto_increment 추가
sql_query(" ALTER TABLE $g5[group_member_table] CHANGE `gm_id` `gm_id` INT( 11 ) DEFAULT '0' NOT NULL AUTO_INCREMENT ", false);

// 로그인테이블에서 인덱스 삭제
sql_query(" ALTER TABLE `$g5[login_table]` DROP INDEX `lo_datetime` ", false);

// 회원테이블의 회원가입일시에 인덱스 추가
sql_query(" ALTER TABLE `$g5[member_table]` ADD INDEX `mb_datetime` ( `mb_datetime` ) ", false);

// 게시판설정 테이블에 업로드 개수, 이메일 사용 필드 추가
sql_query(" ALTER TABLE `$g5[board_table]` 
    ADD `bo_upload_count` TINYINT NOT NULL AFTER `bo_notice` ,
    ADD `bo_use_email` TINYINT NOT NULL AFTER `bo_upload_count` ", FALSE);
*/

/*
// 050831 막음
// 환경설정 테이블에 메일발송 설정 추가
sql_query(" ALTER TABLE `$g5[config_table]` 
    ADD `cf_email_use` TINYINT NOT NULL AFTER `cf_search_part` , 
    ADD `cf_email_wr_super_admin` TINYINT NOT NULL AFTER `cf_email_use` , 
    ADD `cf_email_wr_group_admin` TINYINT NOT NULL AFTER `cf_email_wr_super_admin` , 
    ADD `cf_email_wr_board_admin` TINYINT NOT NULL AFTER `cf_email_wr_group_admin` , 
    ADD `cf_email_wr_write` TINYINT NOT NULL AFTER `cf_email_wr_board_admin` ", FALSE);
sql_query(" ALTER TABLE `$g5[config_table]` 
    CHANGE `cf_comment_all_email` `cf_email_wr_comment_all` TINYINT DEFAULT '0' NOT NULL ", FALSE);
sql_query(" ALTER TABLE `$g5[config_table]` 
    ADD `cf_email_mb_super_admin` TINYINT NOT NULL AFTER `cf_email_wr_comment_all` , 
    ADD `cf_email_mb_member` TINYINT NOT NULL AFTER `cf_email_mb_super_admin` ,
    ADD `cf_email_po_super_admin` TINYINT NOT NULL AFTER `cf_email_mb_member` ", FALSE);


// 회원테이블에 SMS 수신여부 필드 추가
sql_query(" ALTER TABLE `$g5[member_table]` ADD `mb_sms` TINYINT NOT NULL AFTER `mb_mailling` ", FALSE);

// 게시판 인덱스 변경
$sql = " select bo_table from $g5[board_table] ";
$result = sql_query($sql);
while($row=sql_fetch_array($result))
{
    $row2 = sql_fetch(" select * from `{$g5[write_prefix]}{$row[bo_table]}` limit 1 ");
    if (!isset($row2[wr_is_comment]))
    {
        sql_query(" ALTER TABLE `{$g5[write_prefix]}{$row[bo_table]}` ADD `wr_is_comment` TINYINT NOT NULL AFTER `wr_parent` ", FALSE);
        sql_query(" ALTER TABLE `{$g5[write_prefix]}{$row[bo_table]}` DROP INDEX `wr_comment_num` ", FALSE);
        sql_query(" ALTER TABLE `{$g5[write_prefix]}{$row[bo_table]}` DROP INDEX `wr_num_reply_parent` ", FALSE);
        sql_query(" ALTER TABLE `{$g5[write_prefix]}{$row[bo_table]}` DROP INDEX `wr_parent_comment` ", FALSE);
        sql_query(" ALTER TABLE `{$g5[write_prefix]}{$row[bo_table]}` DROP INDEX `wr_is_comment` ", FALSE);
        sql_query(" ALTER TABLE `{$g5[write_prefix]}{$row[bo_table]}` ADD INDEX `wr_is_comment` (`wr_is_comment`, `wr_num`, `wr_reply`) ", FALSE);
        sql_query(" ALTER TABLE `{$g5[write_prefix]}{$row[bo_table]}` ADD INDEX `wr_num` (`wr_num`) ", FALSE);
        sql_query(" ALTER TABLE `{$g5[write_prefix]}{$row[bo_table]}` ADD INDEX `wr_parent` (`wr_parent`) ", FALSE);
        sql_query(" ALTER TABLE `{$g5[write_prefix]}{$row[bo_table]}` ADD INDEX `ca_name` (`ca_name`) ", FALSE);
        sql_query(" UPDATE `{$g5[write_prefix]}{$row[bo_table]}` set wr_is_comment = 1 where  wr_comment < 0 ", FALSE);
    }
}

// 파일테이블에 이미지 폭, 높이, 타입, 일시 넣기
// getimagesize() 함수보다 속도가 빠름
sql_query(" ALTER TABLE `$g5[board_file_table]` ADD `bf_filesize` INT NOT NULL , ADD `bf_width` INT NOT NULL , ADD `bf_height` SMALLINT NOT NULL , ADD `bf_type` TINYINT NOT NULL , ADD `bf_datetime` DATETIME NOT NULL ", FALSE);

// 이메일 인증사용
sql_query(" ALTER TABLE `$g5[member_table]` ADD `mb_email_certify` DATETIME NOT NULL AFTER `mb_intercept_date` ", FALSE);
sql_query(" ALTER TABLE `$g5[config_table]` ADD `cf_use_email_certify` TINYINT NOT NULL AFTER `cf_use_copy_log` ", FALSE);

// 최근게시물 라인수
sql_query(" ALTER TABLE `$g5[config_table]` ADD `cf_new_rows` INT NOT NULL AFTER `cf_login_skin` ", FALSE);

// 포인트 테이블에 필드 추가
sql_query(" ALTER TABLE `$g5[point_table]` ADD `po_rel_table` VARCHAR( 20 ) NOT NULL , ADD `po_rel_id` VARCHAR( 20 ) NOT NULL , ADD `po_rel_action` VARCHAR( 255 ) NOT NULL ", FALSE);

// 포인트 테이블의 회원아이디 길이 변경
sql_query(" ALTER TABLE `$g5[point_table]` CHANGE `mb_id` `mb_id` VARCHAR( 20 ) NOT NULL ", FALSE);

// 포인트 테이블의 인덱스 변경
sql_query(" ALTER TABLE `$g5[point_table]` DROP INDEX `index1` , ADD INDEX `index1` ( `mb_id` , `po_rel_table` , `po_rel_id` , `po_rel_action` ) ", FALSE);

// 투표 테이블에 투표한 회원 필드 추가
sql_query(" ALTER TABLE `$g5[poll_table]` ADD `mb_ids` TEXT NOT NULL ", FALSE);

// 환경설정 테이블에 여분필드 추가
sql_query(" ALTER TABLE `$g5[config_table]` ADD `cf_1` VARCHAR( 255 ) NOT NULL , ADD `cf_2` VARCHAR( 255 ) NOT NULL , ADD `cf_3` VARCHAR( 255 ) NOT NULL , ADD `cf_4` VARCHAR( 255 ) NOT NULL , ADD `cf_5` VARCHAR( 255 ) NOT NULL , ADD `cf_6` VARCHAR( 255 ) NOT NULL , ADD `cf_7` VARCHAR( 255 ) NOT NULL , ADD `cf_8` VARCHAR( 255 ) NOT NULL , ADD `cf_9` VARCHAR( 255 ) NOT NULL , ADD `cf_10` VARCHAR( 255 ) NOT NULL ", FALSE);

// 로그인스킨 필드 삭제
sql_query(" ALTER TABLE `$g5[config_table]` DROP `cf_login_skin` ", FALSE);

// 회원가입스킨 필드를 회원관련스킨 필드로 변경
sql_query(" ALTER TABLE `$g5[config_table]` CHANGE `cf_register_skin` `cf_member_skin` VARCHAR( 255 ) NOT NULL ", FALSE);

// 내부로그인 필드 추가
sql_query(" ALTER TABLE `$g5[config_table]` ADD `cf_login_skin` VARCHAR( 255 ) NOT NULL AFTER `cf_new_skin` ", FALSE);

// 접속자 스킨 필드 추가
sql_query(" ALTER TABLE `$g5[config_table]` ADD `cf_connect_skin` VARCHAR( 255 ) NOT NULL AFTER `cf_search_skin` ", FALSE);

// 파일 설명 사용 필드 추가
sql_query(" ALTER TABLE `$g5[board_table]` ADD `bo_use_file_content` TINYINT NOT NULL AFTER `bo_use_sideview` ", FALSE);

// 파일 테이블에 내용 필드 추가 (갤러리의 경우 해당 이미지에 대한 내용을 넣음)
sql_query(" ALTER TABLE `$g5[board_file_table]` ADD `bf_content` TEXT NOT NULL ", FALSE);

// 방문자로그삭제, 인기검색어삭제 필드 추가
sql_query(" ALTER TABLE `$g5[config_table]` ADD `cf_visit_del` INT NOT NULL AFTER `cf_memo_del` , ADD `cf_popular_del` INT NOT NULL AFTER `cf_visit_del` ", FALSE);

// 검색 스킨 필드 추가
sql_query(" ALTER TABLE `$g5[config_table]` ADD `cf_search_skin` VARCHAR( 255 ) NOT NULL AFTER `cf_new_skin` ", FALSE);

// 최근게시물 스킨 필드 추가
sql_query(" ALTER TABLE `$g5[config_table]` ADD `cf_new_skin` VARCHAR( 255 ) NOT NULL AFTER `cf_nick_modify` ", FALSE);

// 약관 필드명 변경
sql_query(" ALTER TABLE `$g5[config_table]` CHANGE `cf_provision` `cf_stipulation` TEXT NOT NULL ", FALSE);

// 게시판 글자 제한
sql_query(" ALTER TABLE `$g5[board_table]` ADD `bo_write_min` INT NOT NULL AFTER `bo_count_comment` , ADD `bo_write_max` INT NOT NULL AFTER `bo_write_min` , ADD `bo_comment_min` INT NOT NULL AFTER `bo_write_max` , ADD `bo_comment_max` INT NOT NULL AFTER `bo_comment_min` ", FALSE);


// 인기검색어 테이블 생성
$sql = " CREATE TABLE $g5[popular_table] (
  pp_id int(11) NOT NULL auto_increment,
  pp_word varchar(50) NOT NULL default '',
  pp_date date NOT NULL default '0000-00-00',
  pp_ip varchar(50) NOT NULL default '',
  PRIMARY KEY  (pp_id),
  UNIQUE KEY index1 (pp_date,pp_word,pp_ip)
) TYPE=MyISAM ";
sql_query($sql, FALSE);

sql_query(" ALTER TABLE `$g5[board_new_table]` ADD `wr_parent` INT NOT NULL AFTER `wr_id` ", FALSE);

sql_query(" ALTER TABLE `$g5[board_new_table]` CHANGE `wr_id` `wr_id` INT NOT NULL ", FALSE);
                                             
sql_query(" ALTER TABLE `$g5[poll_table]` ADD `po_point` INT NOT NULL AFTER `po_level` ", FALSE);

sql_query(" ALTER TABLE `$g5[point_table]` ADD `po_point` INT NOT NULL AFTER `po_level` ", FALSE);


$sql = " select bo_table from $g5[board_table] ";
$result = sql_query($sql);
while($row=sql_fetch_array($result))
{
    sql_query(" ALTER TABLE `{$g5[write_prefix]}{$row[bo_table]}` ADD `wr_comment_reply` VARCHAR( 255 ) NOT NULL AFTER `wr_comment` ", FALSE);
}


sql_query(" ALTER TABLE `$g5[config_table]` ADD `cf_use_copy_log` TINYINT NOT NULL AFTER `cf_use_norobot` ", FALSE);

sql_query(" ALTER TABLE `$g5[config_table]` ADD `cf_register_skin` VARCHAR( 255 ) DEFAULT 'basic' NOT NULL AFTER `cf_intercept_ip` ", FALSE);

sql_query(" ALTER TABLE `$g5[board_table]` ADD `bo_use_sideview` TINYINT NOT NULL AFTER `bo_disable_tags` ", FALSE);


// 회원메일테이블 생성
$sql = " CREATE TABLE $g5[mail_table] (
  ma_id int(11) NOT NULL auto_increment,
  ma_subject varchar(255) NOT NULL default '',
  ma_content mediumtext NOT NULL,
  ma_time datetime NOT NULL default '0000-00-00 00:00:00',
  ma_ip varchar(255) NOT NULL default '',
  ma_last_option text NOT NULL,
  PRIMARY KEY  (ma_id)
) TYPE=MyISAM ";
sql_query($sql, FALSE);


// auth table 생성
$sql = " CREATE TABLE $g5[auth_table] (
  mb_id varchar(255) NOT NULL default '',
  au_menu varchar(20) NOT NULL default '',
  au_auth set('r','w','d') NOT NULL default '',
  PRIMARY KEY  (mb_id,au_menu)
) TYPE=MyISAM ";
sql_query($sql, FALSE);
*/


echo "UPGRADE 완료.";

include_once("./admin.tail.php");
?>