-- @description 설정·QA·로그인·방문·쇼핑몰 설정 기본키 정비 (1e72d7e81)
-- @if-column-missing {{qa_config_table}} qa_id
ALTER TABLE `{{qa_config_table}}` ADD `qa_id` int(11) NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`qa_id`);
-- @if-column-missing {{config_table}} cf_id
ALTER TABLE `{{config_table}}` ADD `cf_id` int(11) NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`cf_id`);
-- @if-column-missing {{login_table}} lo_id
ALTER TABLE `{{login_table}}` ADD `lo_id` int(11) NOT NULL AUTO_INCREMENT FIRST, DROP PRIMARY KEY, ADD PRIMARY KEY (`lo_id`), ADD UNIQUE KEY `lo_ip_unique` (`lo_ip`);
-- @if-column-missing {{g5_shop_default_table}} de_id
-- @if-table-exists {{g5_shop_default_table}}
ALTER TABLE `{{g5_shop_default_table}}` ADD `de_id` int(11) NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`de_id`);
-- @if-table-exists {{visit_table}}
ALTER TABLE `{{visit_table}}` CHANGE `vi_id` `vi_id` int(11) NOT NULL AUTO_INCREMENT;
