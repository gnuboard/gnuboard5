-- @description 메일 인증 링크 유효시간 설정 추가
-- @skip-if-column {{config_table}} cf_email_certify_minutes

ALTER TABLE `{{config_table}}`
    ADD `cf_email_certify_minutes` int(11) NOT NULL DEFAULT '60' AFTER `cf_use_email_certify`;
