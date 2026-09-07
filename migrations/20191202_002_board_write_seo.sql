-- @description 게시판별 글 테이블에 짧은 주소 필드 추가 (22aad37bf)
-- @foreach-write-table-if-column-missing wr_seo_title
ALTER TABLE `{{write_table}}` ADD `wr_seo_title` varchar(200) NOT NULL DEFAULT '' AFTER `wr_content`, ADD INDEX `wr_seo_title` (`wr_seo_title`);
