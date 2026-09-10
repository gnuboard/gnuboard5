-- @description KCP 통보 거래 바인딩 및 재통보 복구 이력 추가
-- @if-table-exists {{g5_shop_order_table}}
-- @if-column-missing {{g5_shop_order_table}} od_kcp_site_cd
ALTER TABLE `{{g5_shop_order_table}}` ADD `od_kcp_site_cd` varchar(5) NOT NULL DEFAULT '';
-- @if-table-exists {{g5_shop_personalpay_table}}
-- @if-column-missing {{g5_shop_personalpay_table}} pp_kcp_site_cd
ALTER TABLE `{{g5_shop_personalpay_table}}` ADD `pp_kcp_site_cd` varchar(5) NOT NULL DEFAULT '';
-- @if-table-exists {{g5_shop_default_table}}
-- @if-table-missing {{g5_shop_kcp_noti_table}}
CREATE TABLE `{{g5_shop_kcp_noti_table}}` (
  `kn_key` char(64) NOT NULL,
  `kn_trade` char(64) NOT NULL,
  `kn_noti_id` varchar(20) NOT NULL,
  `kn_op_cd` char(2) NOT NULL,
  `kn_payload` char(64) NOT NULL,
  `od_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `kn_plan` mediumtext NOT NULL,
  `kn_done` tinyint(4) NOT NULL DEFAULT '0',
  `kn_created_at` datetime NOT NULL,
  PRIMARY KEY (`kn_key`),
  KEY `kn_trade` (`kn_trade`),
  KEY `od_id` (`od_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
