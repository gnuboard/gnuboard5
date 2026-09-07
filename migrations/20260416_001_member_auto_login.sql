-- @description 안전한 자동 로그인 토큰 테이블 추가 (4d5c59766, KVE-2026-0610)
-- @if-table-missing {{member_auto_login_table}}
CREATE TABLE `{{member_auto_login_table}}` (
  `al_id` int(11) NOT NULL AUTO_INCREMENT, `mb_id` varchar(20) NOT NULL DEFAULT '',
  `al_token` varchar(64) NOT NULL DEFAULT '', `al_user_agent` varchar(255) NOT NULL DEFAULT '',
  `al_ip` varchar(45) NOT NULL DEFAULT '', `al_created` datetime DEFAULT NULL,
  `al_last_used` datetime DEFAULT NULL, `al_expire` datetime DEFAULT NULL,
  PRIMARY KEY (`al_id`), UNIQUE KEY `al_token` (`al_token`), KEY `mb_id` (`mb_id`), KEY `al_expire` (`al_expire`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
