-- @description 광고성 정보 수신 동의와 수신일 필드 추가 (66f6a75a1)
-- @if-column-missing {{config_table}} cf_use_promotion
ALTER TABLE `{{config_table}}` ADD `cf_use_promotion` tinyint(1) NOT NULL DEFAULT '0' AFTER `cf_privacy`;
-- @if-column-missing {{member_table}} mb_marketing_agree
ALTER TABLE `{{member_table}}`
  ADD `mb_marketing_agree` tinyint(1) NOT NULL DEFAULT '0' AFTER `mb_scrap_cnt`;
