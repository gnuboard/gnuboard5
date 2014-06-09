<?php
/**
 * @file include.bbs.delete_all.php
 * @author sol (ngleader@gmail.com)
 * @brief 글 삭제시 Syndication Ping
 *        gnuboard5/bbs/bbs.delete_all.php 파일에 추가
 *        include '../syndi/include/include.bbs.delete_all.php';
 */
if(!defined('_GNUBOARD_')) return;

if(!$write || !$row) return;

// 비회원 access가 불가능 한 게시판이면 pass
$sql = 'select count(*) as cnt from ' . $g5['board_table'] . ' b, '. $g5['group_table'] . ' g where b.bo_table=\''. $bo_table .'\' and b.bo_read_level=1 and b.bo_list_level=1 and g.gr_use_access=0 and g.gr_id = b.gr_id';
$cnt_row = sql_fetch($sql);
if($cnt_row['cnt']<1) return;;

$syndi_dir = realpath(dirname(__FILE__) .'/../');

// include config & Syndication Ping class
include_once $syndi_dir . '/config/site.config.php';
include_once $syndi_dir . '/libs/SyndicationHandler.class.php';
include_once $syndi_dir . '/libs/SyndicationPing.class.php';

$sql = "select wr_subject from $write_table where wr_id='" .$row['wr_id'] ."'";
$subject_row = sql_fetch($sql);

$_link = './bbs/board.php?bo_table=%s&wr_id=%s';
$_sql = "insert into {$g5['syndi_log_table']} (content_id, bbs_id, title, link_alternative, delete_date) values('%s','%s','%s','%s','%s')";
sql_query(sprintf($_sql, $row['wr_id'], $bo_table, addslashes($subject_row['wr_subject']), sprintf($_link, $bo_table, $row['wr_id']), date('YmdHis')), false);

$oPing = new SyndicationPing;
$oPing->setId(SyndicationHandler::getTag('channel', $bo_table));
$oPing->setType('deleted');
$oPing->request();
?>