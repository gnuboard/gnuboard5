-- @description PG별 승인 카드정보 기록 추가
-- @if-table-exists {{g5_shop_default_table}}
-- @if-table-missing {{g5_shop_pg_approval_log_table}}
CREATE TABLE `{{g5_shop_pg_approval_log_table}}` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pg` varchar(20) NOT NULL DEFAULT '',
  `oid` varchar(64) NOT NULL DEFAULT '',
  `tid` varchar(200) NOT NULL DEFAULT '',
  `device` varchar(10) NOT NULL DEFAULT '',
  `amount` bigint(20) NOT NULL DEFAULT '0',
  `result_code` varchar(20) NOT NULL DEFAULT '',
  `card_info` text NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `oid` (`oid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
