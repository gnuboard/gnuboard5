<?php
/**
 * @file include.adm.board_form_update.php
 * @author sol (ngleader@gmail.com)
 * @brief 게시판 삭제시 Syndication Ping
 *        gnuboard5/adm/board_form_update.php 파일에 추가
 *        include '../syndi/include/include.adm.board_form_update.php';
 */
if(!defined('_GNUBOARD_')) return;

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