-- @description 모바일 이니시스 승인번호 필드 추가 (6495aa007)
-- @if-table-exists {{g5_shop_inicis_log_table}}
-- @if-column-missing {{g5_shop_inicis_log_table}} P_AUTH_NO
ALTER TABLE `{{g5_shop_inicis_log_table}}` ADD `P_AUTH_NO` varchar(255) NOT NULL DEFAULT '' AFTER `P_FN_NM`;
