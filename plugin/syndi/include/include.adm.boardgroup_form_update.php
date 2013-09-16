<?php
/**
 * @file include.adm.boardgroup_form_update.php
 * @author sol (ngleader@gmail.com)
 * @brief 게시판 삭제시 Syndication Ping
 *        gnuboard5/adm/board_delete.inc.php 파일에 추가
 *        include '../syndi/include/gnuboard5_euckr/include.adm.boardgroup_form_update.php';
 */
if(!defined('_GNUBOARD_')) return;

// group 수정이 아니면
if(!$_POST[gr_id]) return;
if($w!='u') return;

$syndi_dir = realpath(dirname(__FILE__) .'/../');

// include config & Syndication Ping class
include_once $syndi_dir . '/config/site.config.php';
include_once $syndi_dir . '/libs/SyndicationHandler.class.php';
include_once $syndi_dir . '/libs/SyndicationPing.class.php';

$oPing = new SyndicationPing;
$oPing->setId(SyndicationHandler::getTag('site'));
$oPing->setType('channel');

$oPing->request();
?>