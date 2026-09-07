-- @description 그누보드 5.4.1.3 스크랩·짧은 URL 설정 추가 (ffc43056b)
-- @if-column-missing {{member_table}} mb_scrap_cnt
ALTER TABLE `{{member_table}}` ADD `mb_scrap_cnt` int(11) NOT NULL DEFAULT '0' AFTER `mb_memo_cnt`;
-- @if-column-missing {{config_table}} cf_bbs_rewrite
ALTER TABLE `{{config_table}}` ADD `cf_bbs_rewrite` tinyint(4) NOT NULL DEFAULT '0' AFTER `cf_link_target`;
-- @if-table-missing {{g5_shop_post_log_table}}
-- @if-table-exists {{g5_shop_cart_table}}
CREATE TABLE `{{g5_shop_post_log_table}}` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT, `oid` bigint(20) unsigned NOT NULL,
  `mb_id` varchar(255) NOT NULL DEFAULT '', `post_data` text NOT NULL,
  `ol_code` varchar(255) NOT NULL DEFAULT '', `ol_msg` text NOT NULL,
  `ol_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ol_ip` varchar(25) NOT NULL DEFAULT '', PRIMARY KEY (`log_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
