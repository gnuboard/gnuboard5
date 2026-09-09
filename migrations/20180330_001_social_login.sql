-- @description 소셜 로그인 설정과 프로필 테이블 추가 (f7ac06d7d)
-- @if-column-missing {{config_table}} cf_social_login_use
ALTER TABLE `{{config_table}}`
  ADD `cf_social_login_use` tinyint(4) NOT NULL DEFAULT '0' AFTER `cf_googl_shorturl_apikey`;
-- 나머지 컬럼은 부분 적용 조합에서도 중복 오류가 발생하지 않도록 20260904_002에서 개별 보정한다.
-- @if-column-missing {{config_table}} cf_kakao_client_secret
ALTER TABLE `{{config_table}}` ADD `cf_kakao_client_secret` varchar(100) NOT NULL DEFAULT '';
-- @if-column-missing {{config_table}} cf_member_img_size
ALTER TABLE `{{config_table}}`
  ADD `cf_member_img_size` int(11) NOT NULL DEFAULT '0' AFTER `cf_member_icon_height`;
-- @if-table-missing {{social_profile_table}}
CREATE TABLE `{{social_profile_table}}` (
  `mp_no` int(11) NOT NULL AUTO_INCREMENT, `mb_id` varchar(255) NOT NULL DEFAULT '',
  `provider` varchar(50) NOT NULL DEFAULT '', `object_sha` varchar(45) NOT NULL DEFAULT '',
  `identifier` varchar(255) NOT NULL DEFAULT '', `profileurl` varchar(255) NOT NULL DEFAULT '',
  `photourl` varchar(255) NOT NULL DEFAULT '', `displayname` varchar(150) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `mp_register_day` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `mp_latest_day` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  UNIQUE KEY `mp_no` (`mp_no`), KEY `mb_id` (`mb_id`), KEY `provider` (`provider`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
