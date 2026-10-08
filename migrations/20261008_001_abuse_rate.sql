-- @description 세션 교체와 동시 요청에 유지되는 자동화 요청 제한 저장소 추가
-- @if-table-missing {{abuse_rate_table}}
CREATE TABLE `{{abuse_rate_table}}` (
  `ar_key` char(64) NOT NULL,
  `ar_next` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ar_key`),
  KEY `ar_next` (`ar_next`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
