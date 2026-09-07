-- @description 과거 복합 스키마 변경의 부분 적용 상태 보정
ALTER TABLE `{{auth_table}}` MODIFY `au_menu` varchar(50) NOT NULL DEFAULT '';
-- @if-table-exists {{g5_shop_cart_table}}
-- @if-column-missing {{g5_shop_cart_table}} it_sc_method
ALTER TABLE `{{g5_shop_cart_table}}` ADD `it_sc_method` tinyint(4) NOT NULL DEFAULT '0';
-- @if-table-exists {{g5_shop_cart_table}}
-- @if-column-missing {{g5_shop_cart_table}} it_sc_price
ALTER TABLE `{{g5_shop_cart_table}}` ADD `it_sc_price` int(11) NOT NULL DEFAULT '0';
-- @if-table-exists {{g5_shop_cart_table}}
-- @if-column-missing {{g5_shop_cart_table}} it_sc_minimum
ALTER TABLE `{{g5_shop_cart_table}}` ADD `it_sc_minimum` int(11) NOT NULL DEFAULT '0';
-- @if-table-exists {{g5_shop_cart_table}}
-- @if-column-missing {{g5_shop_cart_table}} it_sc_qty
ALTER TABLE `{{g5_shop_cart_table}}` ADD `it_sc_qty` int(11) NOT NULL DEFAULT '0';
-- @if-table-exists {{g5_shop_order_data_table}}
-- @if-column-missing {{g5_shop_order_data_table}} mb_id
ALTER TABLE `{{g5_shop_order_data_table}}` ADD `mb_id` varchar(20) NOT NULL DEFAULT '';
-- @if-table-exists {{g5_shop_inicis_log_table}}
-- @if-column-missing {{g5_shop_inicis_log_table}} is_mail_send
ALTER TABLE `{{g5_shop_inicis_log_table}}` ADD `is_mail_send` tinyint(4) NOT NULL DEFAULT '1';

-- @if-column-missing {{config_table}} cf_google_clientid
ALTER TABLE `{{config_table}}` ADD `cf_google_clientid` varchar(100) NOT NULL DEFAULT '';
-- @if-column-missing {{config_table}} cf_google_secret
ALTER TABLE `{{config_table}}` ADD `cf_google_secret` varchar(100) NOT NULL DEFAULT '';
-- @if-column-missing {{config_table}} cf_naver_clientid
ALTER TABLE `{{config_table}}` ADD `cf_naver_clientid` varchar(100) NOT NULL DEFAULT '';
-- @if-column-missing {{config_table}} cf_naver_secret
ALTER TABLE `{{config_table}}` ADD `cf_naver_secret` varchar(100) NOT NULL DEFAULT '';
-- @if-column-missing {{config_table}} cf_kakao_rest_key
ALTER TABLE `{{config_table}}` ADD `cf_kakao_rest_key` varchar(100) NOT NULL DEFAULT '';
-- @if-column-missing {{config_table}} cf_social_servicelist
ALTER TABLE `{{config_table}}` ADD `cf_social_servicelist` varchar(255) NOT NULL DEFAULT '';
-- @if-column-missing {{config_table}} cf_payco_clientid
ALTER TABLE `{{config_table}}` ADD `cf_payco_clientid` varchar(100) NOT NULL DEFAULT '';
-- @if-column-missing {{config_table}} cf_payco_secret
ALTER TABLE `{{config_table}}` ADD `cf_payco_secret` varchar(100) NOT NULL DEFAULT '';
-- @if-column-missing {{config_table}} cf_captcha
ALTER TABLE `{{config_table}}` ADD `cf_captcha` varchar(100) NOT NULL DEFAULT '';
-- @if-column-missing {{config_table}} cf_recaptcha_site_key
ALTER TABLE `{{config_table}}` ADD `cf_recaptcha_site_key` varchar(100) NOT NULL DEFAULT '';
-- @if-column-missing {{config_table}} cf_recaptcha_secret_key
ALTER TABLE `{{config_table}}` ADD `cf_recaptcha_secret_key` varchar(100) NOT NULL DEFAULT '';
-- @if-column-missing {{config_table}} cf_member_img_width
ALTER TABLE `{{config_table}}` ADD `cf_member_img_width` int(11) NOT NULL DEFAULT '0';
-- @if-column-missing {{config_table}} cf_member_img_height
ALTER TABLE `{{config_table}}` ADD `cf_member_img_height` int(11) NOT NULL DEFAULT '0';

-- @if-column-missing {{memo_table}} me_type
ALTER TABLE `{{memo_table}}` ADD `me_type` enum('send','recv') NOT NULL DEFAULT 'recv';
-- @if-column-missing {{memo_table}} me_send_ip
ALTER TABLE `{{memo_table}}` ADD `me_send_ip` varchar(100) NOT NULL DEFAULT '';
ALTER TABLE `{{memo_table}}` MODIFY `me_id` int(11) NOT NULL AUTO_INCREMENT;
-- @if-column-missing {{board_file_table}} bf_thumburl
ALTER TABLE `{{board_file_table}}` ADD `bf_thumburl` varchar(255) NOT NULL DEFAULT '';
-- @if-column-missing {{board_file_table}} bf_storage
ALTER TABLE `{{board_file_table}}` ADD `bf_storage` varchar(50) NOT NULL DEFAULT '';

-- @if-column-missing {{member_table}} mb_marketing_date
ALTER TABLE `{{member_table}}` ADD `mb_marketing_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00';
-- @if-column-missing {{member_table}} mb_thirdparty_agree
ALTER TABLE `{{member_table}}` ADD `mb_thirdparty_agree` tinyint(1) NOT NULL DEFAULT '0';
-- @if-column-missing {{member_table}} mb_thirdparty_date
ALTER TABLE `{{member_table}}` ADD `mb_thirdparty_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00';
-- @if-column-missing {{member_table}} mb_agree_log
ALTER TABLE `{{member_table}}` ADD `mb_agree_log` text NOT NULL;
-- @if-column-missing {{member_table}} mb_mailling_date
ALTER TABLE `{{member_table}}` ADD `mb_mailling_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00';
-- @if-column-missing {{member_table}} mb_sms_date
ALTER TABLE `{{member_table}}` ADD `mb_sms_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00';

-- @if-table-exists {{g5_shop_default_table}}
-- @if-column-missing {{g5_shop_default_table}} de_inicis_pro_reconcile_use
ALTER TABLE `{{g5_shop_default_table}}` ADD `de_inicis_pro_reconcile_use` tinyint(4) NOT NULL DEFAULT '0';
-- @if-table-exists {{g5_shop_default_table}}
-- @if-column-missing {{g5_shop_default_table}} de_inicis_pro_log_days
ALTER TABLE `{{g5_shop_default_table}}` ADD `de_inicis_pro_log_days` int(11) NOT NULL DEFAULT '365';
-- @if-table-exists {{g5_shop_default_table}}
-- @if-column-missing {{g5_shop_default_table}} de_inicis_pro_summary_days
ALTER TABLE `{{g5_shop_default_table}}` ADD `de_inicis_pro_summary_days` int(11) NOT NULL DEFAULT '1825';
-- @if-table-exists {{g5_shop_default_table}}
-- @if-column-missing {{g5_shop_default_table}} de_inicis_pro_monitor_at
ALTER TABLE `{{g5_shop_default_table}}` ADD `de_inicis_pro_monitor_at` datetime DEFAULT NULL;
-- @if-table-exists {{g5_shop_default_table}}
-- @if-column-missing {{g5_shop_default_table}} de_inicis_pro_monitor_message
ALTER TABLE `{{g5_shop_default_table}}` ADD `de_inicis_pro_monitor_message` varchar(255) NOT NULL DEFAULT '';

-- @if-table-exists {{g5_shop_inicis_pay_table}}
-- @if-column-missing {{g5_shop_inicis_pay_table}} ip_environment
ALTER TABLE `{{g5_shop_inicis_pay_table}}` ADD `ip_environment` varchar(10) NOT NULL DEFAULT '';
-- @if-table-exists {{g5_shop_inicis_pay_table}}
-- @if-column-missing {{g5_shop_inicis_pay_table}} ip_easy_pay
ALTER TABLE `{{g5_shop_inicis_pay_table}}` ADD `ip_easy_pay` varchar(20) NOT NULL DEFAULT '';
-- @if-table-exists {{g5_shop_inicis_pay_table}}
-- @if-column-missing {{g5_shop_inicis_pay_table}} ip_noti_status
ALTER TABLE `{{g5_shop_inicis_pay_table}}` ADD `ip_noti_status` varchar(30) NOT NULL DEFAULT '';
-- @if-table-exists {{g5_shop_inicis_pay_table}}
-- @if-column-missing {{g5_shop_inicis_pay_table}} ip_noti_code
ALTER TABLE `{{g5_shop_inicis_pay_table}}` ADD `ip_noti_code` varchar(30) NOT NULL DEFAULT '';
-- @if-table-exists {{g5_shop_inicis_pay_table}}
-- @if-column-missing {{g5_shop_inicis_pay_table}} ip_noti_message
ALTER TABLE `{{g5_shop_inicis_pay_table}}` ADD `ip_noti_message` varchar(255) NOT NULL DEFAULT '';
-- @if-table-exists {{g5_shop_inicis_pay_table}}
-- @if-column-missing {{g5_shop_inicis_pay_table}} ip_noti_failed_count
ALTER TABLE `{{g5_shop_inicis_pay_table}}` ADD `ip_noti_failed_count` int(11) NOT NULL DEFAULT '0';
-- @if-table-exists {{g5_shop_inicis_pay_table}}
-- @if-column-missing {{g5_shop_inicis_pay_table}} ip_noti_at
ALTER TABLE `{{g5_shop_inicis_pay_table}}` ADD `ip_noti_at` datetime DEFAULT NULL;
-- @if-table-exists {{g5_shop_inicis_pay_table}}
-- @if-column-missing {{g5_shop_inicis_pay_table}} ip_cancel_status
ALTER TABLE `{{g5_shop_inicis_pay_table}}` ADD `ip_cancel_status` varchar(30) NOT NULL DEFAULT '';
-- @if-table-exists {{g5_shop_inicis_pay_table}}
-- @if-column-missing {{g5_shop_inicis_pay_table}} ip_cancel_code
ALTER TABLE `{{g5_shop_inicis_pay_table}}` ADD `ip_cancel_code` varchar(30) NOT NULL DEFAULT '';
-- @if-table-exists {{g5_shop_inicis_pay_table}}
-- @if-column-missing {{g5_shop_inicis_pay_table}} ip_cancel_message
ALTER TABLE `{{g5_shop_inicis_pay_table}}` ADD `ip_cancel_message` varchar(255) NOT NULL DEFAULT '';
-- @if-table-exists {{g5_shop_inicis_pay_table}}
-- @if-column-missing {{g5_shop_inicis_pay_table}} ip_cancel_checked_at
ALTER TABLE `{{g5_shop_inicis_pay_table}}` ADD `ip_cancel_checked_at` datetime DEFAULT NULL;
-- @if-table-exists {{g5_shop_inicis_pay_table}}
-- @if-column-missing {{g5_shop_inicis_pay_table}} ip_refund_required
ALTER TABLE `{{g5_shop_inicis_pay_table}}` ADD `ip_refund_required` tinyint(4) NOT NULL DEFAULT '0';
-- @if-table-exists {{g5_shop_inicis_pay_table}}
-- @if-column-missing {{g5_shop_inicis_pay_table}} ip_vbank_due_at
ALTER TABLE `{{g5_shop_inicis_pay_table}}` ADD `ip_vbank_due_at` datetime DEFAULT NULL;
-- @if-table-exists {{g5_shop_inicis_pay_table}}
-- @if-column-missing {{g5_shop_inicis_pay_table}} ip_expired_at
ALTER TABLE `{{g5_shop_inicis_pay_table}}` ADD `ip_expired_at` datetime DEFAULT NULL;
-- @if-table-exists {{g5_shop_inicis_pay_table}}
-- @if-column-missing {{g5_shop_inicis_pay_table}} ip_audit_error
ALTER TABLE `{{g5_shop_inicis_pay_table}}` ADD `ip_audit_error` tinyint(4) NOT NULL DEFAULT '0';
-- @if-table-exists {{g5_shop_inicis_pay_table}}
-- @if-column-missing {{g5_shop_inicis_pay_table}} ip_alerted_at
ALTER TABLE `{{g5_shop_inicis_pay_table}}` ADD `ip_alerted_at` datetime DEFAULT NULL;
-- @if-table-exists {{g5_shop_inicis_pay_table}}
-- @if-column-missing {{g5_shop_inicis_pay_table}} ip_alert_key
ALTER TABLE `{{g5_shop_inicis_pay_table}}` ADD `ip_alert_key` varchar(64) NOT NULL DEFAULT '';
-- @if-table-exists {{g5_shop_inicis_pay_table}}
-- @if-column-missing {{g5_shop_inicis_pay_table}} ip_pg_status
ALTER TABLE `{{g5_shop_inicis_pay_table}}` ADD `ip_pg_status` varchar(30) NOT NULL DEFAULT '';
-- @if-table-exists {{g5_shop_inicis_pay_table}}
-- @if-column-missing {{g5_shop_inicis_pay_table}} ip_pg_amount
ALTER TABLE `{{g5_shop_inicis_pay_table}}` ADD `ip_pg_amount` int(11) NOT NULL DEFAULT '0';
-- @if-table-exists {{g5_shop_inicis_pay_table}}
-- @if-column-missing {{g5_shop_inicis_pay_table}} ip_pg_tid
ALTER TABLE `{{g5_shop_inicis_pay_table}}` ADD `ip_pg_tid` varchar(80) NOT NULL DEFAULT '';
-- @if-table-exists {{g5_shop_inicis_pay_table}}
-- @if-column-missing {{g5_shop_inicis_pay_table}} ip_pg_result_code
ALTER TABLE `{{g5_shop_inicis_pay_table}}` ADD `ip_pg_result_code` varchar(30) NOT NULL DEFAULT '';
-- @if-table-exists {{g5_shop_inicis_pay_table}}
-- @if-column-missing {{g5_shop_inicis_pay_table}} ip_pg_message
ALTER TABLE `{{g5_shop_inicis_pay_table}}` ADD `ip_pg_message` varchar(255) NOT NULL DEFAULT '';
-- @if-table-exists {{g5_shop_inicis_pay_table}}
-- @if-column-missing {{g5_shop_inicis_pay_table}} ip_pg_checked_at
ALTER TABLE `{{g5_shop_inicis_pay_table}}` ADD `ip_pg_checked_at` datetime DEFAULT NULL;
-- @if-table-exists {{g5_shop_inicis_pay_table}}
-- @if-index-missing {{g5_shop_inicis_pay_table}} ip_noti_status
ALTER TABLE `{{g5_shop_inicis_pay_table}}` ADD KEY `ip_noti_status` (`ip_noti_status`);
-- @if-table-exists {{g5_shop_inicis_pay_table}}
-- @if-index-missing {{g5_shop_inicis_pay_table}} ip_cancel_status
ALTER TABLE `{{g5_shop_inicis_pay_table}}` ADD KEY `ip_cancel_status` (`ip_cancel_status`);
-- @if-table-exists {{g5_shop_inicis_pay_table}}
-- @if-index-missing {{g5_shop_inicis_pay_table}} ip_refund_required
ALTER TABLE `{{g5_shop_inicis_pay_table}}` ADD KEY `ip_refund_required` (`ip_refund_required`);

ALTER TABLE `{{content_table}}` MODIFY `co_seo_title` varchar(255) NOT NULL DEFAULT '';
-- @foreach-write-table
ALTER TABLE `{{write_table}}` MODIFY `wr_seo_title` varchar(255) NOT NULL DEFAULT '';
