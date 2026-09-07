-- @description KG이니시스 INIpay PRO 설정과 결제 이력 테이블 추가 (acdbcfd8b)
-- @if-table-exists {{g5_shop_default_table}}
-- @if-column-missing {{g5_shop_default_table}} de_inicis_pro_alert_use
ALTER TABLE `{{g5_shop_default_table}}`
  ADD `de_inicis_pro_alert_use` tinyint(4) NOT NULL DEFAULT '1';
-- @if-table-exists {{g5_shop_default_table}}
-- @if-table-missing {{g5_shop_inicis_pay_table}}
CREATE TABLE `{{g5_shop_inicis_pay_table}}` (
  `ip_id` int(11) NOT NULL AUTO_INCREMENT, `ip_oid` varchar(64) NOT NULL DEFAULT '',
  `ip_tid` varchar(80) NOT NULL DEFAULT '', `ip_auth_tid` varchar(80) NOT NULL DEFAULT '',
  `ip_mid` varchar(80) NOT NULL DEFAULT '', `ip_environment` varchar(10) NOT NULL DEFAULT '',
  `mb_id` varchar(20) NOT NULL DEFAULT '', `ip_amount` int(11) NOT NULL DEFAULT '0',
  `ip_pay_type` varchar(20) NOT NULL DEFAULT '', `ip_easy_pay` varchar(20) NOT NULL DEFAULT '',
  `ip_device` varchar(10) NOT NULL DEFAULT '', `ip_order_type` varchar(10) NOT NULL DEFAULT '',
  `ip_status` varchar(30) NOT NULL DEFAULT '', `ip_result_code` varchar(30) NOT NULL DEFAULT '',
  `ip_result_message` varchar(255) NOT NULL DEFAULT '', `ip_noti_status` varchar(30) NOT NULL DEFAULT '',
  `ip_noti_code` varchar(30) NOT NULL DEFAULT '', `ip_noti_message` varchar(255) NOT NULL DEFAULT '',
  `ip_noti_failed_count` int(11) NOT NULL DEFAULT '0', `ip_noti_at` datetime DEFAULT NULL,
  `ip_cancel_status` varchar(30) NOT NULL DEFAULT '', `ip_cancel_code` varchar(30) NOT NULL DEFAULT '',
  `ip_cancel_message` varchar(255) NOT NULL DEFAULT '', `ip_cancel_checked_at` datetime DEFAULT NULL,
  `ip_refund_required` tinyint(4) NOT NULL DEFAULT '0', `ip_vbank_due_at` datetime DEFAULT NULL,
  `ip_expired_at` datetime DEFAULT NULL, `ip_order_exists` tinyint(4) NOT NULL DEFAULT '0',
  `ip_approved_at` datetime DEFAULT NULL, `ip_ordered_at` datetime DEFAULT NULL,
  `ip_notified_at` datetime DEFAULT NULL, `ip_canceled_at` datetime DEFAULT NULL,
  `ip_created_at` datetime DEFAULT NULL, `ip_updated_at` datetime DEFAULT NULL,
  `ip_ip` varchar(45) NOT NULL DEFAULT '', `ip_event_count` int(11) NOT NULL DEFAULT '0',
  `ip_audit_error` tinyint(4) NOT NULL DEFAULT '0', `ip_alerted_at` datetime DEFAULT NULL,
  `ip_alert_key` varchar(64) NOT NULL DEFAULT '', `ip_pg_status` varchar(30) NOT NULL DEFAULT '',
  `ip_pg_amount` int(11) NOT NULL DEFAULT '0', `ip_pg_tid` varchar(80) NOT NULL DEFAULT '',
  `ip_pg_result_code` varchar(30) NOT NULL DEFAULT '', `ip_pg_message` varchar(255) NOT NULL DEFAULT '',
  `ip_pg_checked_at` datetime DEFAULT NULL, PRIMARY KEY (`ip_id`), UNIQUE KEY `ip_oid` (`ip_oid`),
  KEY `ip_tid` (`ip_tid`), KEY `ip_auth_tid` (`ip_auth_tid`), KEY `ip_status` (`ip_status`),
  KEY `ip_noti_status` (`ip_noti_status`), KEY `ip_cancel_status` (`ip_cancel_status`),
  KEY `ip_refund_required` (`ip_refund_required`), KEY `ip_updated_at` (`ip_updated_at`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- @if-table-exists {{g5_shop_default_table}}
-- @if-table-missing {{g5_shop_inicis_pay_event_table}}
CREATE TABLE `{{g5_shop_inicis_pay_event_table}}` (
  `pe_id` int(11) NOT NULL AUTO_INCREMENT, `ip_oid` varchar(64) NOT NULL DEFAULT '',
  `ip_tid` varchar(80) NOT NULL DEFAULT '', `pe_stage` varchar(30) NOT NULL DEFAULT '',
  `pe_status` varchar(30) NOT NULL DEFAULT '', `pe_code` varchar(30) NOT NULL DEFAULT '',
  `pe_message` varchar(255) NOT NULL DEFAULT '', `pe_source` varchar(10) NOT NULL DEFAULT '',
  `pe_ip` varchar(45) NOT NULL DEFAULT '', `pe_created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`pe_id`), KEY `ip_oid` (`ip_oid`), KEY `ip_tid` (`ip_tid`),
  KEY `pe_status` (`pe_status`), KEY `pe_created_at` (`pe_created_at`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
