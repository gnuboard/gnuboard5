<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

include_once(G5_LIB_PATH.'/mailer.lib.php');

// 데이터베이스(db) 쓰기 실패 메일입니다.

//------------------------------------------------------------------------------
// 운영자에게 메일보내기
//------------------------------------------------------------------------------
$subject = $config['cf_title'].' - 정기구독 데이터베이스(db) 쓰기 실패 메일 ('.$od_name.'_'.$od_id.')';
ob_start();
include G5_SUBSCRIPTION_PATH.'/mail/fail_db_mail_html_template.php';
$content = ob_get_contents();
ob_end_clean();

mailer($od_name, $od_email, $config['cf_admin_email'], $subject, $content, 1);
//------------------------------------------------------------------------------

//------------------------------------------------------------------------------
// 주문자에게 메일보내기
//------------------------------------------------------------------------------
$subject = $config['cf_title'].' - 정기구독 데이터베이스(db) 쓰기 실패 ('.$od_id.')';
ob_start();
include G5_SUBSCRIPTION_PATH.'/mail/fail_db_mail_html_template.php';
$content = ob_get_contents();
ob_end_clean();

// mailer($config['cf_admin_email_name'], $config['cf_admin_email'], $od_email, $subject, $content, 1);
//------------------------------------------------------------------------------