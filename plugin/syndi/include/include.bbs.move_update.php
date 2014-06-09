<?php
/**
 * @file include.bbs.move_update.php
 * @author sol (ngleader@gmail.com)
 * @brief 글 등록/수정시 Syndication Ping
 *        gnuboard5/bbs/move_update.php 파일에 추가
 *        include '../syndi/include/include.bbs.move_update.php';
 */
if(!defined('_GNUBOARD_')) return;

if($sw != 'move' && $sw != 'copy') return;

$syndi_dir = realpath(dirname(__FILE__) .'/../');

// include config & Syndication Ping class
include_once $syndi_dir . '/config/site.config.php';
include_once $syndi_dir . '/libs/SyndicationHandler.class.php';
include_once $syndi_dir . '/libs/SyndicationPing.class.php';

if($sw == "copy")
{
	$oPing = new SyndicationPing;
	$oPing->setId(SyndicationHandler::getTag('channel', $bo_table));
	$oPing->setType('article');
	$oPing->request();

	unset($oPing);
}

for($i=0, $c=count($_POST['chk_bo_table']); $i<$c; $i++) 
{
	$oPing = new SyndicationPing;
	$oPing->setId(SyndicationHandler::getTag('channel', $_POST['chk_bo_table'][$i]));
	$oPing->setType('article');
	$oPing->request();

	unset($oPing);
}
?>