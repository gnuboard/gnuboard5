<?php
/**
 * @file include.adm.board_delete.inc.php
 * @author sol (ngleader@gmail.com)
 * @brief 게시판 삭제시 Syndication Ping
 *        gnuboard5/adm/board_delete.inc.php 파일에 추가
 *        include '../syndi/include/include.adm.board_delete.inc.php';
 */
if(!defined('_GNUBOARD_')) return;

if(!$tmp_bo_table) return;

$syndi_dir = realpath(dirname(__FILE__) .'/../');

// include config & Syndication Ping class
include_once $syndi_dir . '/config/site.config.php';
include_once $syndi_dir . '/libs/SyndicationHandler.class.php';
include_once $syndi_dir . '/libs/SyndicationPing.class.php';

$oPing = new SyndicationPing;
$oPing->setId(SyndicationHandler::getTag('site'));
$oPing->setType('channel');

// delete log
$_sql = "delete from {$g5['syndi_log_table']} where  bbs_id='%s'";
sql_query(sprintf($_sql, $tmp_bo_table));
	
$oPing->request();
?>