-- @description 비밀번호 찾기 요청 제한의 관리자 정책 설정 추가
-- @if-table-exists {{config_table}}
-- @if-column-missing {{config_table}} cf_password_lost_policy
ALTER TABLE `{{config_table}}` ADD `cf_password_lost_policy` varchar(255) NOT NULL DEFAULT '';
