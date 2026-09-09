-- @description 그누보드 5.4 SEO·메모·첨부 스키마 추가 (22aad37bf)
-- @if-column-missing {{content_table}} co_seo_title
ALTER TABLE `{{content_table}}` ADD `co_seo_title` varchar(200) NOT NULL DEFAULT '' AFTER `co_content`, ADD INDEX `co_seo_title` (`co_seo_title`);
-- @if-column-missing {{memo_table}} me_send_id
ALTER TABLE `{{memo_table}}`
  ADD `me_send_id` int(11) NOT NULL DEFAULT '0';
-- @if-column-missing {{member_table}} mb_memo_cnt
ALTER TABLE `{{member_table}}` ADD `mb_memo_cnt` int(11) NOT NULL DEFAULT '0' AFTER `mb_memo_call`;
-- @if-column-missing {{board_file_table}} bf_fileurl
ALTER TABLE `{{board_file_table}}`
  ADD `bf_fileurl` varchar(255) NOT NULL DEFAULT '' AFTER `bf_content`;
