-- @description LG 가상계좌 통보 거래 바인딩 및 재통보 복구 이력 추가
-- @if-table-exists {{g5_shop_order_table}}
-- @if-column-missing {{g5_shop_order_table}} od_lg_mid
ALTER TABLE `{{g5_shop_order_table}}` ADD `od_lg_mid` varchar(104) NOT NULL DEFAULT '';
-- @if-table-exists {{g5_shop_personalpay_table}}
-- @if-column-missing {{g5_shop_personalpay_table}} pp_lg_mid
ALTER TABLE `{{g5_shop_personalpay_table}}` ADD `pp_lg_mid` varchar(104) NOT NULL DEFAULT '';
-- @if-table-exists {{g5_shop_default_table}}
-- @if-table-missing {{g5_shop_lg_noti_table}}
CREATE TABLE `{{g5_shop_lg_noti_table}}` (
  `ln_key` char(64) NOT NULL,
  `ln_trade` char(64) NOT NULL,
  `ln_seq` int(11) NOT NULL,
  `ln_flag` char(1) NOT NULL,
  `ln_payload` char(64) NOT NULL,
  `od_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `ln_plan` mediumtext NOT NULL,
  `ln_done` tinyint(4) NOT NULL DEFAULT '0',
  `ln_created_at` datetime NOT NULL,
  PRIMARY KEY (`ln_key`),
  KEY `ln_trade` (`ln_trade`),
  KEY `od_id` (`od_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
