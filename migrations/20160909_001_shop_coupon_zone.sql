-- @description 쿠폰존 테이블과 쿠폰 연결 필드 추가 (6e1aa4ac0)
-- @if-table-exists {{g5_shop_cart_table}}
-- @if-table-missing {{g5_shop_coupon_zone_table}}
CREATE TABLE `{{g5_shop_coupon_zone_table}}` (
  `cz_id` int(11) NOT NULL AUTO_INCREMENT, `cz_type` tinyint(4) NOT NULL DEFAULT '0',
  `cz_subject` varchar(255) NOT NULL DEFAULT '', `cz_start` date NOT NULL DEFAULT '0000-00-00',
  `cz_end` date NOT NULL DEFAULT '0000-00-00', `cz_file` varchar(255) NOT NULL DEFAULT '',
  `cz_period` int(11) NOT NULL DEFAULT '0', `cz_point` int(11) NOT NULL DEFAULT '0',
  `cp_method` tinyint(4) NOT NULL DEFAULT '0', `cp_target` varchar(255) NOT NULL DEFAULT '',
  `cp_price` int(11) NOT NULL DEFAULT '0', `cp_type` tinyint(4) NOT NULL DEFAULT '0',
  `cp_trunc` int(11) NOT NULL DEFAULT '0', `cp_minimum` int(11) NOT NULL DEFAULT '0',
  `cp_maximum` int(11) NOT NULL DEFAULT '0', `cz_download` int(11) NOT NULL DEFAULT '0',
  `cz_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00', PRIMARY KEY (`cz_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- @if-table-exists {{g5_shop_coupon_table}}
-- @if-column-missing {{g5_shop_coupon_table}} cz_id
ALTER TABLE `{{g5_shop_coupon_table}}` ADD `cz_id` int(11) NOT NULL DEFAULT '0' AFTER `mb_id`;
