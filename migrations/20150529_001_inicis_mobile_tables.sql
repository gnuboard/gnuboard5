-- @description 모바일 이니시스 및 결제 임시저장 테이블 추가 (a7cfae126)
-- @if-table-exists {{g5_shop_cart_table}}
-- @if-table-missing {{g5_shop_inicis_log_table}}
CREATE TABLE `{{g5_shop_inicis_log_table}}` (
  `oid` bigint(20) unsigned NOT NULL, `P_TID` varchar(255) NOT NULL DEFAULT '',
  `P_MID` varchar(255) NOT NULL DEFAULT '', `P_AUTH_DT` varchar(255) NOT NULL DEFAULT '',
  `P_STATUS` varchar(255) NOT NULL DEFAULT '', `P_TYPE` varchar(255) NOT NULL DEFAULT '',
  `P_OID` varchar(255) NOT NULL DEFAULT '', `P_FN_NM` varchar(255) NOT NULL DEFAULT '',
  `P_AMT` int(11) NOT NULL DEFAULT '0', `P_RMESG1` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`oid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- @if-table-exists {{g5_shop_cart_table}}
-- @if-table-missing {{g5_shop_order_data_table}}
CREATE TABLE `{{g5_shop_order_data_table}}` (
  `od_id` bigint(20) unsigned NOT NULL, `dt_pg` varchar(255) NOT NULL DEFAULT '',
  `dt_data` text NOT NULL, `dt_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  KEY `od_id` (`od_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
