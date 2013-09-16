<?php
/**
 * @file include.bbs.write_update.php
 * @author sol (ngleader@gmail.com)
 * @brief 글 등록/수정시 Syndication Ping
 *        gnuboard5/bbs/write_update.php 파일에 추가
 *        include '../syndi/include/include.bbs.write_update.php';
 */
if(!defined('_GNUBOARD_')) return;

if(!$board) return;

// 비밀 게시판이면 pass
if($board['bo_use_secret'] && $secret) return;

// 비회원 사용자가 볼 수 없다면 pass
if($board['bo_list_level']>1 || $board['bo_view_level']>1) return;

if($w == 'u' && $wr && !$wr['wr_id']) return;


// 수정 대상 또는 신규 입력한 id가 있다면 ping을 보냄
if($wr['wr_id'] || $wr_id)
{
	$syndi_dir = realpath(dirname(__FILE__) .'/../');

	// include config & Syndication Ping class
	include $syndi_dir . '/config/site.config.php';
	include $syndi_dir . '/libs/SyndicationHandler.class.php';
	include $syndi_dir . '/libs/SyndicationPing.class.php';

	$oPing = new SyndicationPing;
	$oPing->setId(SyndicationHandler::getTag('channel', $board['bo_table']));
	$oPing->setType('article');

	// if deleted 
	$_sql = "delete from {$g5['syndi_log_table']} where content_id='%s' and bbs_id='%s'";
	sql_query(sprintf($_sql, $wr_id ? $wr_id : $wr[wr_id], $board['bo_table']));
		
	$oPing->request();
}
?>