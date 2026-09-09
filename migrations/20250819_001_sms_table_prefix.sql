-- @description SMS5 테이블에 설치 접두어 적용 (cb1fadba4)
-- @if-table-exists sms5_config
-- @if-table-missing {{sms5_config_table}}
RENAME TABLE `sms5_config` TO `{{sms5_config_table}}`;
-- @if-table-exists sms5_write
-- @if-table-missing {{sms5_write_table}}
RENAME TABLE `sms5_write` TO `{{sms5_write_table}}`;
-- @if-table-exists sms5_history
-- @if-table-missing {{sms5_history_table}}
RENAME TABLE `sms5_history` TO `{{sms5_history_table}}`;
-- @if-table-exists sms5_book
-- @if-table-missing {{sms5_book_table}}
RENAME TABLE `sms5_book` TO `{{sms5_book_table}}`;
-- @if-table-exists sms5_book_group
-- @if-table-missing {{sms5_book_group_table}}
RENAME TABLE `sms5_book_group` TO `{{sms5_book_group_table}}`;
-- @if-table-exists sms5_form
-- @if-table-missing {{sms5_form_table}}
RENAME TABLE `sms5_form` TO `{{sms5_form_table}}`;
-- @if-table-exists sms5_form_group
-- @if-table-missing {{sms5_form_group_table}}
RENAME TABLE `sms5_form_group` TO `{{sms5_form_group_table}}`;
