<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

$tno = isset($_POST['tid']) ? clean_xss_tags($_POST['tid']) : '';

// 카드 코드
$card_code = isset($_POST['cardcd']) ? clean_xss_tags($_POST['cardcd']) : '';
$card_mask_number = isset($_POST['cardno']) ? clean_xss_tags($_POST['cardno']) : '';
$card_billkey = isset($_POST['billkey']) ? clean_xss_tags($_POST['billkey']) : '';

$pgauthdate = isset($_POST['pgauthdate']) ? clean_xss_tags($_POST['pgauthdate']) : '';
$pgauthtime = isset($_POST['pgauthtime']) ? clean_xss_tags($_POST['pgauthtime']) : '';
$authkey = isset($_POST['pgauthtime']) ? clean_xss_tags($_POST['pgauthtime']) : '';

// 카드이름의 경우 
$card_name = ($card_code && isset($CARD_CODE[$card_code])) ? $CARD_CODE[$card_code] : $card_code;

$app_time = $pgauthdate.$pgauthtime;
$app_no = $authkey;
$amount = isset($_POST['od_price']) ? (int) $_POST['od_price'] : 0;
