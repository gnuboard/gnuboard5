-- @description 주문 취소·환불 공통 이력 테이블 추가 (0301c45f5)
-- @if-table-exists {{g5_shop_order_table}}
-- @if-table-missing {{g5_shop_order_cancel_log_table}}
CREATE TABLE `{{g5_shop_order_cancel_log_table}}` (
  `cl_id` int(11) NOT NULL AUTO_INCREMENT, `od_id` bigint(20) unsigned NOT NULL,
  `mb_id` varchar(255) NOT NULL DEFAULT '', `cl_pg` varchar(30) NOT NULL DEFAULT '',
  `cl_pg_version` varchar(30) NOT NULL DEFAULT '', `cl_settle_case` varchar(100) NOT NULL DEFAULT '',
  `cl_test` tinyint(4) NOT NULL DEFAULT '0', `cl_source` varchar(20) NOT NULL DEFAULT '',
  `cl_type` varchar(20) NOT NULL DEFAULT '', `cl_status` varchar(30) NOT NULL DEFAULT '',
  `cl_origin_tno` varchar(255) NOT NULL DEFAULT '', `cl_cancel_tno` varchar(255) NOT NULL DEFAULT '',
  `cl_pg_order_no` varchar(100) NOT NULL DEFAULT '', `cl_seq` int(11) NOT NULL DEFAULT '0',
  `cl_pg_seq` varchar(20) NOT NULL DEFAULT '', `cl_idempotency_key` char(32) NOT NULL DEFAULT '',
  `cl_request_amount` int(11) NOT NULL DEFAULT '0', `cl_approved_amount` int(11) NOT NULL DEFAULT '0',
  `cl_remaining_amount` int(11) NOT NULL DEFAULT '0', `cl_tax_mny` int(11) NOT NULL DEFAULT '0',
  `cl_vat_mny` int(11) NOT NULL DEFAULT '0', `cl_free_mny` int(11) NOT NULL DEFAULT '0',
  `cl_refund_before` int(11) NOT NULL DEFAULT '0', `cl_refund_after` int(11) NOT NULL DEFAULT '0',
  `cl_result_code` varchar(50) NOT NULL DEFAULT '', `cl_result_msg` varchar(255) NOT NULL DEFAULT '',
  `cl_reason` varchar(255) NOT NULL DEFAULT '', `cl_request_data` mediumtext NOT NULL,
  `cl_response_data` mediumtext NOT NULL, `cl_noti_data` mediumtext NOT NULL,
  `cl_ip` varchar(45) NOT NULL DEFAULT '',
  `cl_requested_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `cl_approved_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `cl_local_applied_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `cl_created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `cl_updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`cl_id`), KEY `od_id` (`od_id`), KEY `cl_pg` (`cl_pg`),
  KEY `cl_pg_version` (`cl_pg_version`), KEY `cl_status` (`cl_status`),
  KEY `cl_idempotency_key` (`cl_idempotency_key`), KEY `cl_origin_tno` (`cl_origin_tno`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
