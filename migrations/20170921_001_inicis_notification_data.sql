-- @description 모바일 이니시스 통지 원문·메일 상태 필드 추가 (db7e07da2)
-- @if-table-exists {{g5_shop_inicis_log_table}}
-- @if-column-missing {{g5_shop_inicis_log_table}} post_data
ALTER TABLE `{{g5_shop_inicis_log_table}}`
    ADD `post_data` text NOT NULL AFTER `P_RMESG1`;
-- @if-table-exists {{g5_shop_inicis_log_table}}
-- @if-column-missing {{g5_shop_inicis_log_table}} is_mail_send
ALTER TABLE `{{g5_shop_inicis_log_table}}` ADD `is_mail_send` tinyint(4) NOT NULL DEFAULT '1' AFTER `post_data`;
