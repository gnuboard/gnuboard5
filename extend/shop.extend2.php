<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

if (!defined('G5_USE_SHOP') || !G5_USE_SHOP) return;

/*
// uniqid 테이블이 없을 경우 생성
if(!sql_query(" select uq_id from {$g5['uniqid_table']} limit 1 ", false)) {
    sql_query(" CREATE TABLE IF NOT EXISTS `{$g5['uniqid_table']}` (
                  `uq_id` bigint(20) unsigned NOT NULL,
                  PRIMARY KEY (`uq_id`)
                ) ", false);
}

// 상품옵션 테이블 생성
if(!sql_query(" select io_id from {$g5['g5_shop_item_option_table']} limit 1 ", false)) {
    sql_query(" CREATE TABLE IF NOT EXISTS `{$g5['g5_shop_item_option_table']}` (
                    `io_no` INT(11) NOT NULL AUTO_INCREMENT,
                    `io_id` VARCHAR(255) NOT NULL DEFAULT '',
                    `io_type` TINYINT(4) NOT NULL DEFAULT '0',
                    `it_id` VARCHAR(20) NOT NULL DEFAULT '',
                    `io_price` INT(11) NOT NULL DEFAULT '0',
                    `io_stock_qty` INT(11) NOT NULL DEFAULT '0',
                    `io_noti_qty` INT(11) NOT NULL DEFAULT '0',
                    `io_use` TINYINT(4) NOT NULL DEFAULT '0',
                    PRIMARY KEY (`io_no`),
                    KEY `io_id` (`io_id`),
                    KEY `it_id` (`it_id`)
                ) ", false);
    sql_query(" ALTER TABLE `{$g5['g5_shop_item_table']}`
                    ADD `it_option_subject` VARCHAR(255) NOT NULL DEFAULT '' AFTER `it_origin`,
                    ADD `it_supply_subject` VARCHAR(255) NOT NULL DEFAULT '' AFTER `it_option` ", false);
}

// uq_id 필드추가
$sql = " select uq_id from {$g5['g5_shop_cart_table']} limit 1 ";
$result = sql_query($sql, false);
if(!$result) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_cart_table']}` ADD `uq_id` BIGINT(20) unsigned NOT NULL AFTER `ct_id` ", false);
    sql_query(" ALTER TABLE `{$g5['g5_shop_order_table']}` ADD `uq_id` BIGINT(20) unsigned NOT NULL AFTER `od_id` ", false);
    sql_query(" ALTER TABLE `{$g5['g5_shop_order_table']}` MODIFY COLUMN od_id BIGINT(20) unsigned NOT NULL ", false);
    sql_query(" ALTER TABLE `{$g5['g5_shop_cart_table']}` ADD INDEX uq_id (uq_id) ", false);
    sql_query(" ALTER TABLE `{$g5['g5_shop_order_table']}` ADD UNIQUE uq_id (uq_id) ", false);
    sql_query(" ALTER TABLE `{$g5['g5_shop_order_table']}` DROP INDEX index1", false);
}

// 가격필드명변경
$sql = " select it_price from {$g5['g5_shop_item_table']} limit 1 ";
$result = sql_query($sql, false);
if(!$result) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_item_table']}`
                    CHANGE `it_amount` `it_price` INT(11) NOT NULL DEFAULT '0',
                    CHANGE `it_cust_amount` `it_cust_price` INT(11) NOT NULL DEFAULT '0' ", false);
    sql_query(" ALTER TABLE `{$g5['g5_shop_cart_table']}`
                    CHANGE `ct_amount` `ct_price` INT(11) NOT NULL DEFAULT '0',
                    ADD `it_name` VARCHAR(255) NOT NULL DEFAULT '' AFTER `it_id` ", false);
}

// od_mobile 추가
$sql = " select od_mobile from {$g5['g5_shop_order_table']} limit 1 ";
$result = sql_query($sql, false);
if(!$result) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_order_table']}`
                    ADD `od_mobile` TINYINT(4) NOT NULL DEFAULT '0' AFTER `od_time` ", false);
}

// ct_option 추가
$sql = " select ct_option from {$g5['g5_shop_cart_table']} limit 1 ";
$result = sql_query($sql, false);
if(!$result) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_cart_table']}`
                    ADD `ct_option` VARCHAR(255) NOT NULL DEFAULT '' AFTER `ct_stock_use`,
                    ADD `io_id` VARCHAR(255) NOT NULL DEFAULT '' AFTER `ct_qty`,
                    ADD `io_type` TINYINT(4) NOT NULL DEFAULT '0' AFTER `io_id`,
                    ADD `io_price` INT(11) NOT NULL DEFAULT '0' AFTER `io_type` ", false);
}

// it_brand 추가
$sql = " select it_brand from {$g5['g5_shop_item_table']} limit 1 ";
$result = sql_query($sql, false);
if(!$result) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_item_table']}`
                    ADD `it_brand` VARCHAR(255) NOT NULL DEFAULT '' AFTER `it_origin`,
                    ADD `it_model` VARCHAR(255) NOT NULL DEFAULT '' AFTER `it_brand` ", false);
}

// sms_cont5 필드추가
$sql = " select de_sms_cont5 from {$g5['g5_shop_default_table']} ";
$result = sql_query($sql, false);
if (!$result) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_default_table']}`
                    ADD `de_sms_cont5` VARCHAR(255) NOT NULL DEFAULT '' AFTER `de_sms_cont4`,
                    ADD `de_sms_use5` TINYINT(4) NOT NULL DEFAULT '0' AFTER `de_sms_use4` ", false);
}

// 모바일 상품유형 필드 추가
$sql = " select de_mobile_type1_list_use from {$g5['g5_shop_default_table']} ";
$result = sql_query($sql, false);
if (!$result) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_default_table']}`
                    ADD `de_mobile_type1_list_use` TINYINT(4) NOT NULL DEFAULT '0' AFTER `de_type5_img_height`,
                    ADD `de_mobile_type1_list_skin` VARCHAR(255) NOT NULL DEFAULT '' AFTER `de_mobile_type1_list_use`,
                    ADD `de_mobile_type1_list_row` INT(11) NOT NULL DEFAULT '0' AFTER `de_mobile_type1_list_skin`,
                    ADD `de_mobile_type1_img_width` INT(11) NOT NULL DEFAULT '0' AFTER `de_mobile_type1_list_row`,
                    ADD `de_mobile_type1_img_height` INT(11) NOT NULL DEFAULT '0' AFTER `de_mobile_type1_img_width`,
                    ADD `de_mobile_type2_list_use` TINYINT(4) NOT NULL DEFAULT '0' AFTER `de_mobile_type1_img_height`,
                    ADD `de_mobile_type2_list_skin` VARCHAR(255) NOT NULL DEFAULT '' AFTER `de_mobile_type2_list_use`,
                    ADD `de_mobile_type2_list_row` INT(11) NOT NULL DEFAULT '0' AFTER `de_mobile_type2_list_skin`,
                    ADD `de_mobile_type2_img_width` INT(11) NOT NULL DEFAULT '0' AFTER `de_mobile_type2_list_row`,
                    ADD `de_mobile_type2_img_height` INT(11) NOT NULL DEFAULT '0' AFTER `de_mobile_type2_img_width`,
                    ADD `de_mobile_type3_list_use` TINYINT(4) NOT NULL DEFAULT '0' AFTER `de_mobile_type2_img_height`,
                    ADD `de_mobile_type3_list_skin` VARCHAR(255) NOT NULL DEFAULT '' AFTER `de_mobile_type3_list_use`,
                    ADD `de_mobile_type3_list_row` INT(11) NOT NULL DEFAULT '0' AFTER `de_mobile_type3_list_skin`,
                    ADD `de_mobile_type3_img_width` INT(11) NOT NULL DEFAULT '0' AFTER `de_mobile_type3_list_row`,
                    ADD `de_mobile_type3_img_height` INT(11) NOT NULL DEFAULT '0' AFTER `de_mobile_type3_img_width`,
                    ADD `de_mobile_type4_list_use` TINYINT(4) NOT NULL DEFAULT '0' AFTER `de_mobile_type3_img_height`,
                    ADD `de_mobile_type4_list_skin` VARCHAR(255) NOT NULL DEFAULT '' AFTER `de_mobile_type4_list_use`,
                    ADD `de_mobile_type4_list_row` INT(11) NOT NULL DEFAULT '0' AFTER `de_mobile_type4_list_skin`,
                    ADD `de_mobile_type4_img_width` INT(11) NOT NULL DEFAULT '0' AFTER `de_mobile_type4_list_row`,
                    ADD `de_mobile_type4_img_height` INT(11) NOT NULL DEFAULT '0' AFTER `de_mobile_type4_img_width`,
                    ADD `de_mobile_type5_list_use` TINYINT(4) NOT NULL DEFAULT '0' AFTER `de_mobile_type4_img_height`,
                    ADD `de_mobile_type5_list_skin` VARCHAR(255) NOT NULL DEFAULT '' AFTER `de_mobile_type5_list_use`,
                    ADD `de_mobile_type5_list_row` INT(11) NOT NULL DEFAULT '0' AFTER `de_mobile_type5_list_skin`,
                    ADD `de_mobile_type5_img_width` INT(11) NOT NULL DEFAULT '0' AFTER `de_mobile_type5_list_row`,
                    ADD `de_mobile_type5_img_height` INT(11) NOT NULL DEFAULT '0' AFTER `de_mobile_type5_img_width`
                    ", false);
}

// it_id type 수정
$sql = " SHOW COLUMNS FROM `{$g5['g5_shop_item_table']}` WHERE field = 'it_id' ";
$row = sql_fetch($sql);
if(intval(preg_replace("/[^0-9]/", "", $row['Type'])) != 20) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_item_table']}` MODIFY COLUMN it_id VARCHAR(20) NOT NULL DEFAULT '' ", false);
    sql_query(" ALTER TABLE `{$g5['g5_shop_cart_table']}` MODIFY COLUMN it_id VARCHAR(20) NOT NULL DEFAULT '' ", false);
    sql_query(" ALTER TABLE `{$g5['g5_shop_item_qa_table']}` MODIFY COLUMN it_id VARCHAR(20) NOT NULL DEFAULT '' ", false);
    sql_query(" ALTER TABLE `{$g5['g5_shop_item_use_table']}` MODIFY COLUMN it_id VARCHAR(20) NOT NULL DEFAULT '' ", false);
    sql_query(" ALTER TABLE `{$g5['g5_shop_item_relation_table']}` MODIFY COLUMN it_id VARCHAR(20) NOT NULL DEFAULT '' ", false);
    sql_query(" ALTER TABLE `{$g5['g5_shop_item_relation_table']}` MODIFY COLUMN it_id2 VARCHAR(20) NOT NULL DEFAULT '' ", false);
    sql_query(" ALTER TABLE `{$g5['g5_shop_event_item_table']}` MODIFY COLUMN it_id VARCHAR(20) NOT NULL DEFAULT '' ", false);
    sql_query(" ALTER TABLE `{$g5['g5_shop_wish_table']}` MODIFY COLUMN it_id VARCHAR(20) NOT NULL DEFAULT '' ", false);
}

// 상품요약정보 필드추가
$sql = " select it_info_gubun from {$g5['g5_shop_item_table']} limit 1 ";
$result = sql_query($sql, false);
if(!$result) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_item_table']}` ADD `it_info_gubun` VARCHAR(50) NOT NULL DEFAULT '' AFTER `it_tel_inq`,
                    ADD `it_info_value` TEXT NOT NULL AFTER `it_info_gubun` ", false);
}

// 상품이미지 필드추가
$sql = " select it_img1 from {$g5['g5_shop_item_table']} limit 1 ";
$result = sql_query($sql, false);
if(!$result) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_item_table']}`
                    ADD `it_img1` VARCHAR(255) NOT NULL DEFAULT '' AFTER `it_info_value`,
                    ADD `it_img2` VARCHAR(255) NOT NULL DEFAULT '' AFTER `it_img1`,
                    ADD `it_img3` VARCHAR(255) NOT NULL DEFAULT '' AFTER `it_img2`,
                    ADD `it_img4` VARCHAR(255) NOT NULL DEFAULT '' AFTER `it_img3`,
                    ADD `it_img5` VARCHAR(255) NOT NULL DEFAULT '' AFTER `it_img4`,
                    ADD `it_img6` VARCHAR(255) NOT NULL DEFAULT '' AFTER `it_img5`,
                    ADD `it_img7` VARCHAR(255) NOT NULL DEFAULT '' AFTER `it_img6`,
                    ADD `it_img8` VARCHAR(255) NOT NULL DEFAULT '' AFTER `it_img7`,
                    ADD `it_img9` VARCHAR(255) NOT NULL DEFAULT '' AFTER `it_img8`,
                    ADD `it_img10` VARCHAR(255) NOT NULL DEFAULT '' AFTER `it_img9` ", false);
}

// 관련상품 정렬을 위한 ir_no 필드 추가
$sql = " select ir_no from {$g5['g5_shop_item_relation_table']} limit 1 ";
$result = sql_query($sql, false);
if(!$result) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_item_relation_table']}`
                    ADD `ir_no` INT(11) NOT NULL DEFAULT '0' AFTER `it_id2` ", false);
}

if (!isset($it['it_mobile_explan'])) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_item_table']}`
                    ADD `it_mobile_explan` TEXT NOT NULL AFTER `it_explan`,
                    ADD `it_mobile_head_html` TEXT NOT NULL AFTER `it_tail_html`,
                    ADD `it_mobile_tail_html` TEXT NOT NULL AFTER `it_mobile_head_html` ", false);
}

// de_guest_cart_use 필드추가
$sql = " select de_guest_cart_use from {$g5['g5_shop_default_table']} ";
$result = sql_query($sql, false);
if(!$result) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_cart_table']}`
                    ADD `mb_id` VARCHAR(255) NOT NULL DEFAULT '' AFTER `uq_id` ", false);
    sql_query(" ALTER TABLE `{$g5['g5_shop_default_table']}`
                    ADD `de_cart_keep_term` INT(11) NOT NULL DEFAULT '0' AFTER `de_code_dup_use`,
                    ADD `de_guest_cart_use` TINYINT(4) NOT NULL DEFAULT '0' AFTER `de_cart_keep_term` ", false);
}

// 포인트타입 필드 추가
$sql = " select it_point_type from {$g5['g5_shop_item_table']} limit 1 ";
$result = sql_query($sql, false);
if(!$result) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_item_table']}`
                    ADD `it_point_type` TINYINT(4) NOT NULL DEFAULT '0' AFTER `it_point` ", false);
}

// 쿠폰테이블
$sql = " DESCRIBE `{$g5['g5_shop_coupon_table']}` ";
$result = sql_query($sql, false);
if(!$result) {
    sql_query(" CREATE TABLE IF NOT EXISTS `{$g5['g5_shop_coupon_table']}` (
                  `cp_no` INT(11) NOT NULL AUTO_INCREMENT,
                  `cp_id` VARCHAR(255) NOT NULL DEFAULT '',
                  `cp_subject` VARCHAR(255) NOT NULL DEFAULT '',
                  `cp_method` TINYINT(4) NOT NULL DEFAULT '0',
                  `cp_target` VARCHAR(255) NOT NULL DEFAULT '',
                  `mb_id` VARCHAR(255) NOT NULL DEFAULT '',
                  `cp_start` DATE NOT NULL DEFAULT '0000-00-00',
                  `cp_end` DATE NOT NULL DEFAULT '0000-00-00',
                  `cp_type` TINYINT(4) NOT NULL DEFAULT '0',
                  `cp_amount` INT(11) NOT NULL DEFAULT '0',
                  `cp_trunc` INT(11) NOT NULL DEFAULT '0',
                  `cp_minimum` INT(11) NOT NULL DEFAULT '0',
                  `cp_maximum` INT(11) NOT NULL DEFAULT '0',
                  `cp_used` TINYINT(4) NOT NULL DEFAULT '0',
                  `cp_datetime` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
                  PRIMARY KEY (`cp_no`),
                  UNIQUE KEY `cp_id` (`cp_id`),
                  KEY `mb_id` (`mb_id`)
                )", false);
}

// 쿠폰관련필드 추가
$sql = " select cp_amount from {$g5['g5_shop_cart_table']} limit 1 ";
$result = sql_query($sql, false);
if(!$result) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_cart_table']}`
                    ADD `cp_amount` INT(11) NOT NULL DEFAULT '0' AFTER `ct_point` ", false);
    sql_query(" ALTER TABLE `{$g5['g5_shop_order_table']}`
                    ADD `od_coupon` INT(11) NOT NULL DEFAULT '0' AFTER `od_dc_amount`,
                    ADD `od_send_coupon` INT(11) NOT NULL DEFAULT '0' AFTER `od_send_cost` ", false);
}

// 쿠폰사용정보필드추가
$sql = " select od_id from {$g5['g5_shop_coupon_table']} limit 1 ";
$result = sql_query($sql, false);
if(!$result) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_coupon_table']}`
                    ADD `od_id` BIGINT(20) UNSIGNED NOT NULL AFTER `cp_maximum`,
                    ADD `cp_used_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `cp_used` ", false);
}

// 장바구니 선택필드추가
$sql = " select ct_select from {$g5['g5_shop_cart_table']} limit 1 ";
$result = sql_query($sql, false);
if(!$result) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_cart_table']}`
                    ADD `ct_select` TINYINT(4) NOT NULL DEFAULT '0' AFTER `ct_direct` ", true);
}

// 개별배송비 필드 추가
$sql = " select it_sc_type from {$g5['g5_shop_item_table']} limit 1 ";
$result = sql_query($sql, false);
if(!$result) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_item_table']}`
                    ADD `it_sc_type` TINYINT(4) NOT NULL DEFAULT '0' AFTER `it_stock_qty`,
                    ADD `it_sc_method` TINYINT(4) NOT NULL DEFAULT '0' AFTER `it_sc_type`,
                    ADD `it_sc_amount` INT(11) NOT NULL DEFAULT '0' AFTER `it_sc_method`,
                    ADD `it_sc_minimum` INT(11) NOT NULL DEFAULT '0' AFTER `it_sc_amount`,
                    ADD `it_sc_qty` INT(11) NOT NULL DEFAULT '0' AFTER `it_sc_minimum` ", false);
}

// 장바구니 배송비필드 추가
$sql = " select ct_send_cost from {$g5['g5_shop_cart_table']} limit 1 ";
$result = sql_query($sql, false);
if(!$result) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_cart_table']}`
                    ADD `ct_send_cost` TINYINT(11) NOT NULL DEFAULT '0' AFTER `io_price` ", false);
}

// 결제필드 변경
$sql = " select od_temp_amount from {$g5['g5_shop_order_table']} limit 1 ";
$result = sql_query($sql, false);
if(!$result) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_order_table']}`
                    ADD `od_temp_amount` INT(11) NOT NULL DEFAULT '0' AFTER `od_send_coupon`,
                    ADD `od_receipt_amount` INT(11) NOT NULL DEFAULT '0' AFTER `od_temp_point`,
                    ADD `od_receipt_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `od_bank_account` ", false);
}

// 추가배송비 테이블
$sql = " select sc_id from {$g5['g5_shop_sendcost_table']} limit 1 ";
$result = sql_query($sql, false);
if(!$result) {
    sql_query(" CREATE TABLE IF NOT EXISTS `{$g5['g5_shop_sendcost_table']}` (
                  `sc_id` INT(11) NOT NULL AUTO_INCREMENT,
                  `sc_name` VARCHAR(255) NOT NULL DEFAULT '',
                  `sc_zip1` VARCHAR(10) NOT NULL DEFAULT '',
                  `sc_zip2` VARCHAR(10) NOT NULL DEFAULT '',
                  `sc_amount` INT(11) NOT NULL DEFAULT '0',
                  PRIMARY KEY (`sc_id`),
                  KEY `sc_zip1` (`sc_zip1`),
                  KEY `sc_zip2` (`sc_zip2`)
                )", false);
}

// od_send_cost2 추가
$sql = " select od_send_cost2 from {$g5['g5_shop_order_table']} limit 1 ";
$result = sql_query($sql, false);
if(!$result) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_order_table']}`
                    ADD `od_send_cost2` INT(11) NOT NULL DEFAULT '0' AFTER `od_send_coupon` ", false);
}

// 복합과세 필드 추가
$sql = " select de_tax_flag_use from {$g5['g5_shop_default_table']} ";
$result = sql_query($sql, false);
if(!$result) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_default_table']}`
                    ADD `de_tax_flag_use` TINYINT(4) NOT NULL DEFAULT '0' AFTER `de_escrow_use` ", false);
    sql_query(" ALTER TABLE `{$g5['g5_shop_item_table']}`
                    ADD `it_notax` TINYINT(4) NOT NULL DEFAULT '0' AFTER `it_point_type` ", false);
}

// 에스크로필드 추가
$sql = " select od_tno from {$g5['g5_shop_order_table']} limit 1 ";
$result = sql_query($sql, false);
if(!$result) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_order_table']}`
                    ADD `od_tno` VARCHAR(255) NOT NULL DEFAULT '' AFTER `od_settle_case`,
                    ADD `od_escrow` TINYINT(4) NOT NULL DEFAULT '0' AFTER `od_tno` ", true);
}

// shop_request 테이블이 없을 경우 생성
if(!sql_query(" select rq_id from {$g5['g5_shop_request_table']} limit 1 ", false)) {
    sql_query(" CREATE TABLE IF NOT EXISTS `{$g5['g5_shop_request_table']}` (
                  `rq_id` INT(11) NOT NULL AUTO_INCREMENT,
                  `rq_type` TINYINT(4) NOT NULL DEFAULT '0',
                  `rq_parent` INT(11) NOT NULL DEFAULT '0',
                  `od_id` BIGINT(20) unsigned NOT NULL,
                  `ct_id` VARCHAR(255) NOT NULL DEFAULT '',
                  `mb_id` VARCHAR(20) NOT NULL DEFAULT '',
                  `rq_content` TEXT NOT NULL,
                  `rq_status` TINYINT(4) NOT NULL DEFAULT '0',
                  `rq_item` TEXT NOT NULL,
                  `dl_company` INT(11) NOT NULL DEFAULT '0',
                  `rq_invoice` VARCHAR(255) NOT NULL DEFAULT '',
                  `rq_amount1` INT(11) NOT NULL DEFAULT '0',
                  `rq_amount2` INT(11) NOT NULL DEFAULT '0',
                  `rq_amount3` INT(11) NOT NULL DEFAULT '0',
                  `rq_account` VARCHAR(255) NOT NULL DEFAULT '',
                  `rq_ip` VARCHAR(255) NOT NULL DEFAULT '',
                  `rq_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
                  PRIMARY KEY (`rq_id`)
                ) ", false);
}

// 수량변경 history 기록
if(!sql_query(" select od_mod_history from {$g5['g5_shop_order_table']} limit 1 ", false)) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_order_table']}`
                    ADD `od_mod_history` TEXT NOT NULL AFTER `od_shop_memo` ", true);
}

// 주문정보에 복합결제 필드추가
if(!sql_query(" select od_tax_flag from {$g5['g5_shop_order_table']} limit 1 ", false)) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_order_table']}`
                    ADD `od_tax_flag` TINYINT(4) NOT NULL DEFAULT '0' AFTER `od_escrow` ", true);
}
*/

/*
// notax 필드추가
$sql = " select ct_notax from {$g5['g5_shop_cart_table']} limit 1 ";
$result = sql_query($sql, false);
if(!$result) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_cart_table']}`
                    ADD `ct_notax` TINYINT(4) NOT NULL DEFAULT '0' AFTER `ct_qty` ", true);
}
*/

/*
// 쇼핑몰 스킨 필드 추가
if (!isset($default['de_shop_skin'])) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_default_table']}`
                    ADD `de_shop_skin` VARCHAR(255) NOT NULL DEFAULT '' AFTER `de_admin_info_email`,
                    ADD `de_shop_mobile_skin` VARCHAR(255) NOT NULL DEFAULT '' AFTER `de_shop_skin` ", false);
}

// 모바일 상품유형 필드 수정
if (!sql_query(" select de_mobile_type1_list_mod from {$g5['g5_shop_default_table']} ", false)) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_default_table']}`
                    CHANGE `de_mobile_type1_list_row` `de_mobile_type1_list_mod` INT(11) NOT NULL DEFAULT '0',
                    CHANGE `de_mobile_type2_list_row` `de_mobile_type2_list_mod` INT(11) NOT NULL DEFAULT '0',
                    CHANGE `de_mobile_type3_list_row` `de_mobile_type3_list_mod` INT(11) NOT NULL DEFAULT '0',
                    CHANGE `de_mobile_type4_list_row` `de_mobile_type4_list_mod` INT(11) NOT NULL DEFAULT '0',
                    CHANGE `de_mobile_type5_list_row` `de_mobile_type5_list_mod` INT(11) NOT NULL DEFAULT '0' ", true);
}

// 분류 모바일 필드명 수정
if(!sql_query(" select ca_mobile_list_mod from {$g5['g5_shop_category_table']} limit 1 ", false)) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_category_table']}`
                    CHANGE `ca_mobile_list_row` `ca_mobile_list_mod` INT(11) NOT NULL DEFAULT '0' ", true);
}

// 과세, 비과세 금액 필드 추가
if(!sql_query(" select od_tax_mny from {$g5['g5_shop_order_table']} limit 1 ", false)) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_order_table']}`
                    ADD `od_tax_mny` INT(11) NOT NULL DEFAULT '0' AFTER `od_tax_flag`,
                    ADD `od_vat_mny` INT(11) NOT NULL DEFAULT '0' AFTER `od_tax_mny`,
                    ADD `od_free_mny` INT(11) NOT NULL DEFAULT '0' AFTER `od_vat_mny` ", true);
}

// cart uq_id를 od_id로 변경
if(!sql_query(" select od_id from {$g5['g5_shop_cart_table']} limit 1 ", false)) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_cart_table']}`
                    CHANGE `uq_id` `od_id` BIGINT(2) UNSIGNED NOT NULL ", true);
}

// it_mobile_name 필드추가
if(!sql_query(" select it_mobile_name from {$g5['g5_shop_item_table']} limit 1 ", false)) {
    sql_query( " ALTER TABLE `{$g5['g5_shop_item_table']}`
                    ADD `it_mobile_name` VARCHAR(255) NOT NULL DEFAULT '' AFTER `it_name` ", true);
}

// 개인결제 테이블추가
if(!sql_query(" select pp_id from {$g5['g5_shop_personalpay_table']} limit 1 ", false)) {
    sql_query(" CREATE TABLE IF NOT EXISTS `{$g5['g5_shop_personalpay_table']}` (
                  `pp_id` BIGINT(20) unsigned NOT NULL,
                  `od_id` BIGINT(20) unsigned NOT NULL,
                  `pp_name` VARCHAR(255) NOT NULL DEFAULT '',
                  `pp_content` TEXT NOT NULL,
                  `pp_use` TINYINT(4) NOT NULL DEFAULT '0',
                  `pp_price` INT(11) NOT NULL DEFAULT '0',
                  `pp_tno` varchar(255) NOT NULL DEFAULT '',
                  `pp_app_no` varchar(20) NOT NULL DEFAULT '',
                  `pp_receipt_price` INT(11) NOT NULL DEFAULT '0',
                  `pp_settle_case` VARCHAR(255) NOT NULL DEFAULT '',
                  `pp_bank_account` VARCHAR(255) NOT NULL DEFAULT '',
                  `pp_deposit_name` VARCHAR(255) NOT NULL DEFAULT '',
                  `pp_receipt_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
                  `pp_receipt_ip` VARCHAR(255) NOT NULL DEFAULT '',
                  `pp_shop_memo` TEXT NOT NULL,
                  `pp_ip` VARCHAR(255) NOT NULL DEFAULT '',
                  `pp_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
                  PRIMARY KEY (`pp_id`),
                  KEY `od_id` (`od_id`)
                )", true);
}

// od_app_no 필드 추가
if(!sql_query(" select od_app_no from {$g5['g5_shop_order_table']} limit 1 ", false)) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_order_table']}`
                    ADD `od_app_no` varchar(20) NOT NULL DEFAULT '' AFTER `od_tno` ", true);
}

// 배송지이력 테이블추가
if(!sql_query(" DESCRIBE `{$g5['g5_shop_order_address_table']}` ", false)) {
    sql_query(" CREATE TABLE IF NOT EXISTS `{$g5['g5_shop_order_address_table']}` (
                  `ad_id` int(11) NOT NULL AUTO_INCREMENT,
                  `mb_id` varchar(255) NOT NULL DEFAULT '',
                  `ad_subject` varchar(255) NOT NULL DEFAULT '',
                  `ad_default` tinyint(4) NOT NULL DEFAULT '0',
                  `ad_name` varchar(255) NOT NULL DEFAULT '',
                  `ad_tel` varchar(255) NOT NULL DEFAULT '',
                  `ad_hp` varchar(255) NOT NULL DEFAULT '',
                  `ad_zip1` char(3) NOT NULL DEFAULT '',
                  `ad_zip2` char(3) NOT NULL DEFAULT '',
                  `ad_addr1` varchar(255) NOT NULL DEFAULT '',
                  `ad_addr2` varchar(255) NOT NULL DEFAULT '',
                  PRIMARY KEY (`ad_id`),
                  KEY `mb_id` (`mb_id`)
                )", true);
}

// 포인트 설정필드 변경
if(!sql_query(" select de_settle_min_point from {$g5['g5_shop_default_table']} ", false)) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_default_table']}`
                    CHANGE `de_point_settle` `de_settle_min_point` int(11) NOT NULL DEFAULT '0',
                    ADD `de_settle_max_point` int(11) NOT NULL DEFAULT '0' AFTER `de_settle_min_point`,
                    ADD `de_settle_point_unit` int(11) NOT NULL DEFAULT '0' AFTER `de_settle_max_point`,
                    DROP `de_point_per` ", true);
}

// 주문 금액 등의 필드 추가
if(!sql_query(" select od_cart_count from {$g5['g5_shop_order_table']} limit 1 ", false)) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_order_table']}`
                    ADD `od_cart_count` int(11) NOT NULL DEFAULT '0' AFTER `od_memo`,
                    ADD `od_cart_price` int(11) NOT NULL DEFAULT '0' AFTER `od_cart_count`,
                    ADD `od_cart_coupon` int(11) NOT NULL DEFAULT '0' AFTER `od_cart_price`,
                    ADD `od_cancel_price` int(11) NOt NULL DEFAULT '0' AFTER `od_receipt_price`,
                    ADD `od_status` varchar(255) NOT NULL DEFAULT '' AFTER `od_mod_history` ", true);
}

// order amount 필드명 수정
if(sql_query(" select od_cart_amount from {$g5['g5_shop_order_table']} limit 1", false)) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_order_table']}`
                    CHANGE `od_cart_amount` `od_cart_price` int(11) NOT NULL DEFAULT '0',
                    CHANGE `od_receipt_amount` `od_receipt_price` int(11) NOT NULL DEFAULT '0',
                    CHANGE `od_cancel_amount` `od_cancel_price` int(11) NOT NULL DEFAULT '0' ", true);
}

// amount 필드명 수정
if(sql_query(" select cp_amount from {$g5['g5_shop_cart_table']} limit 1", false)) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_personalpay_table']}`
                    CHANGE `pp_amount` `pp_price` int(11) NOT NULL DEFAULT '0',
                    CHANGE `pp_receipt_amount` `pp_receipt_price` int(11) NOT NULL DEFAULT '0' ", true);
    sql_query(" ALTER TABLE `{$g5['g5_shop_cart_table']}`
                    CHANGE `cp_amount` `cp_price` int(11) NOT NULL DEFAULT '0' ", true);
    sql_query(" ALTER TABLE `{$g5['g5_shop_coupon_table']}`
                    CHANGE `cp_amount` `cp_price` int(11) NOT NULL DEFAULT '0' ", true);
    sql_query(" ALTER TABLE `{$g5['g5_shop_sendcost_table']}`
                    CHANGE `sc_amount` `sc_price` int(11) NOT NULL DEFAULT '0' ", true);
    sql_query(" ALTER TABLE `{$g5['g5_shop_item_table']}`
                    CHANGE `it_sc_amount` `it_sc_price` int(11) NOT NULL DEFAULT '0' ", true);
}

// 미수 필드 추가
if(!sql_query(" select od_misu from {$g5['g5_shop_order_table']} limit 1 ", false)) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_order_table']}`
                    ADD `od_misu` int(11) NOT NULL DEFAULT '0' AFTER `od_coupon` ", true);
}

// 쿠폰로그 테이블추가
if(!isset($g5['g5_shop_coupon_log_table']))
    die_utf8('dbconfig.php 파일에 $g5[\'g5_shop_coupon_log_table\']    = G5_SHOP_TABLE_PREFIX.\'coupon_log\';            // 쿠폰사용정보 테이블 추가해주세요.');
if(!sql_query(" DESCRIBE `{$g5['g5_shop_coupon_log_table']}` ", false)) {
    sql_query(" CREATE TABLE IF NOT EXISTS `{$g5['g5_shop_coupon_log_table']}` (
                  `cl_id` int(11) NOT NULL AUTO_INCREMENT,
                  `cp_id` varchar(255) NOT NULL DEFAULT '',
                  `mb_id` varchar(255) NOT NULL DEFAULT '',
                  `od_id` bigint(20) NOT NULL,
                  `cp_price` int(11) NOT NULL DEFAULT '0',
                  `cl_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                  PRIMARY KEY (`cl_id`),
                  KEY `mb_id` (`mb_id`),
                  KEY `od_id` (`od_id`)
                )", true);
    sql_query(" ALTER TABLE `{$g5['g5_shop_coupon_table']}`
                    DROP `od_id`,
                    DROP `cp_used_time`,
                    DROP `cp_used` ", true);
}

// 환불필드 추가
if(!sql_query(" select od_refund_price from {$g5['g5_shop_order_table']} limit 1 ", false)) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_order_table']}`
                    ADD `od_refund_price` int(11) NOT NULL DEFAULT '0' AFTER `od_receipt_point` ", true);
}

// 카테고리 인증설정 필드명 변경
if(sql_query(" select ca_hp_cert_use from {$g5['g5_shop_category_table']} limit 1 ", false)) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_category_table']}`
                    CHANGE `ca_hp_cert_use` `ca_cert_use` tinyint(4) NOT NULL DEFAULT '0',
                    CHANGE `ca_adult_cert_use` `ca_adult_use` tinyint(4) NOT NULL DEFAULT '0' ", true);
}

if(!sql_query(" select ca_cert_use from {$g5['g5_shop_category_table']} limit 1 ", false)) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_category_table']}`
                    ADD `ca_cert_use` tinyint(4) NOT NULL DEFAULT '0' AFTER `ca_mb_id`,
                    ADD `ca_adult_use` tinyint(4) NOT NULL DEFAULT '0' AFTER `ca_cert_use` ", true);
}

// 최소 최대구매수량 필드추가
if(!sql_query(" select it_buy_min_qty from {$g5['g5_shop_item_table']} limit 1 ", false)) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_item_table']}`
                    ADD `it_buy_min_qty` int(11) NOT NULL DEFAULT '0' AFTER `it_sc_qty`,
                    ADD `it_buy_max_qty` int(11) NOT NULL DEFAULT '0' AFTER `it_buy_min_qty` ", true);
}

// 상품, 카테고리 여분필드 추가
if(!sql_query(" select it_1 from {$g5['g5_shop_item_table']} limit 1", false)) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_item_table']}`
                    ADD `it_1_subj` varchar(255) NOT NULL DEFAULT '' AFTER `it_img10`,
                    ADD `it_2_subj` varchar(255) NOT NULL DEFAULT '' AFTER `it_1_subj`,
                    ADD `it_3_subj` varchar(255) NOT NULL DEFAULT '' AFTER `it_2_subj`,
                    ADD `it_4_subj` varchar(255) NOT NULL DEFAULT '' AFTER `it_3_subj`,
                    ADD `it_5_subj` varchar(255) NOT NULL DEFAULT '' AFTER `it_4_subj`,
                    ADD `it_6_subj` varchar(255) NOT NULL DEFAULT '' AFTER `it_5_subj`,
                    ADD `it_7_subj` varchar(255) NOT NULL DEFAULT '' AFTER `it_6_subj`,
                    ADD `it_8_subj` varchar(255) NOT NULL DEFAULT '' AFTER `it_7_subj`,
                    ADD `it_9_subj` varchar(255) NOT NULL DEFAULT '' AFTER `it_8_subj`,
                    ADD `it_10_subj` varchar(255) NOT NULL DEFAULT '' AFTER `it_9_subj`,
                    ADD `it_1` varchar(255) NOT NULL DEFAULT '' AFTER `it_10_subj`,
                    ADD `it_2` varchar(255) NOT NULL DEFAULT '' AFTER `it_1`,
                    ADD `it_3` varchar(255) NOT NULL DEFAULT '' AFTER `it_2`,
                    ADD `it_4` varchar(255) NOT NULL DEFAULT '' AFTER `it_3`,
                    ADD `it_5` varchar(255) NOT NULL DEFAULT '' AFTER `it_4`,
                    ADD `it_6` varchar(255) NOT NULL DEFAULT '' AFTER `it_5`,
                    ADD `it_7` varchar(255) NOT NULL DEFAULT '' AFTER `it_6`,
                    ADD `it_8` varchar(255) NOT NULL DEFAULT '' AFTER `it_7`,
                    ADD `it_9` varchar(255) NOT NULL DEFAULT '' AFTER `it_8`,
                    ADD `it_10` varchar(255) NOT NULL DEFAULT '' AFTER `it_9` ", true);
    sql_query(" ALTER TABLE `{$g5['g5_shop_category_table']}`
                    ADD `ca_1_subj` varchar(255) NOT NULL DEFAULT '' AFTER `ca_adult_use`,
                    ADD `ca_2_subj` varchar(255) NOT NULL DEFAULT '' AFTER `ca_1_subj`,
                    ADD `ca_3_subj` varchar(255) NOT NULL DEFAULT '' AFTER `ca_2_subj`,
                    ADD `ca_4_subj` varchar(255) NOT NULL DEFAULT '' AFTER `ca_3_subj`,
                    ADD `ca_5_subj` varchar(255) NOT NULL DEFAULT '' AFTER `ca_4_subj`,
                    ADD `ca_6_subj` varchar(255) NOT NULL DEFAULT '' AFTER `ca_5_subj`,
                    ADD `ca_7_subj` varchar(255) NOT NULL DEFAULT '' AFTER `ca_6_subj`,
                    ADD `ca_8_subj` varchar(255) NOT NULL DEFAULT '' AFTER `ca_7_subj`,
                    ADD `ca_9_subj` varchar(255) NOT NULL DEFAULT '' AFTER `ca_8_subj`,
                    ADD `ca_10_subj` varchar(255) NOT NULL DEFAULT '' AFTER `ca_9_subj`,
                    ADD `ca_1` varchar(255) NOT NULL DEFAULT '' AFTER `ca_10_subj`,
                    ADD `ca_2` varchar(255) NOT NULL DEFAULT '' AFTER `ca_1`,
                    ADD `ca_3` varchar(255) NOT NULL DEFAULT '' AFTER `ca_2`,
                    ADD `ca_4` varchar(255) NOT NULL DEFAULT '' AFTER `ca_3`,
                    ADD `ca_5` varchar(255) NOT NULL DEFAULT '' AFTER `ca_4`,
                    ADD `ca_6` varchar(255) NOT NULL DEFAULT '' AFTER `ca_5`,
                    ADD `ca_7` varchar(255) NOT NULL DEFAULT '' AFTER `ca_6`,
                    ADD `ca_8` varchar(255) NOT NULL DEFAULT '' AFTER `ca_7`,
                    ADD `ca_9` varchar(255) NOT NULL DEFAULT '' AFTER `ca_8`,
                    ADD `ca_10` varchar(255) NOT NULL DEFAULT '' AFTER `ca_9` ", true);
}

// 모바일 이벤트 필드 추가
if(!sql_query(" select ev_mobile_skin from {$g5['g5_shop_event_table']} limit 1 ", false)) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_event_table']}`
                    ADD `ev_mobile_skin` varchar(255) NOT NULL DEFAULT '' AFTER `ev_skin`,
                    ADD `ev_mobile_img_width` int(11) NOT NULL DEFAULT '0' AFTER `ev_list_row`,
                    ADD `ev_mobile_img_height` int(11) NOT NULL DEFAULT '0' AFTER `ev_mobile_img_width`,
                    ADD `ev_mobile_list_mod` int(11) NOT NULL DEFAULT '0' AFTER `ev_mobile_img_height` ", true);
}

// 쇼핑몰설정 테이블에 배송업체 필드 추가
if(!sql_query(" select de_delivery_company from {$g5['g5_shop_default_table']} limit 1 ", false)) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_default_table']}`
                    ADD `de_delivery_company` varchar(255) NOT NULL DEFAULT '' AFTER `de_level_sell` ", true);
}

// 주문서 테이블에 배송업체 필드 추가
if(!sql_query(" select od_delivery_company from {$g5['g5_shop_order_table']} limit 1 ", false)) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_order_table']}`
                    CHANGE `dl_id` `od_delivery_company` varchar(255) NOT NULL DEFAULT '' ", true);
}

// 주문서 삭제 테이블추가
if(!sql_query(" DESCRIBE `{$g5['g5_shop_order_delete_table']}` ", false)) {
    sql_query(" CREATE TABLE IF NOT EXISTS `{$g5['g5_shop_order_delete_table']}` (
                  `de_id` int(11) NOT NULL AUTO_INCREMENT,
                  `de_key` varchar(255) NOT NULL,
                  `de_data` longtext NOT NULL,
                  PRIMARY KEY (`de_id`)
                )", true);
}

// 장바구니 테이블에 입금 상태 추가
$sql = " SHOW COLUMNS from {$g5['g5_shop_cart_table']} LIKE 'ct_status' ";
$row= sql_fetch($sql);
if(stripos($row['Type'], 'enum') !== false) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_cart_table']}`
                    CHANGE `ct_status` `ct_status` varchar(255) NOT NULL DEFAULT '' ", true);
}
*/


// 상품테이블에 검색을 위하여 태그없는 상품설명 저장용 필드 추가
if(!sql_query(" select it_explan2 from {$g5['g5_shop_item_table']} limit 1 ", false)) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_item_table']}`
                    ADD `it_explan2` MEDIUMTEXT NOT NULL AFTER `it_explan` ", true);
}

// de_rel_list_use 추가
if(!sql_query(" select de_rel_list_use from {$g5['g5_shop_default_table']} ", false)) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_default_table']}`
                    ADD `de_rel_list_use` tinyint(4) NOT NULL DEFAULT '0' AFTER `de_mobile_type5_img_height`,
                    ADD `de_rel_list_skin` varchar(255) NOT NULL DEFAULT '' AFTER `de_rel_list_use`,
                    ADD `de_search_list_skin` varchar(255) NOT NULL DEFAULT '' AFTER `de_rel_img_height`,
                    ADD `de_search_list_mod` int(11) NOT NULL DEFAULT '0' AFTER `de_search_list_skin`,
                    ADD `de_search_list_row` int(11) NOT NULL DEFAULT '0' AFTER `de_search_list_mod`,
                    ADD `de_search_img_width` int(11) NOT NULL DEFAULT '0' AFTER `de_search_list_row`,
                    ADD `de_search_img_height` int(11) NOT NULL DEFAULT '0' AFTER `de_search_img_width` ", true);
}

// 사용후기 쓰기 설정 추가
if(!sql_query(" select de_item_use_write from {$g5['g5_shop_default_table']} ", false)) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_default_table']}`
                    ADD `de_item_use_write` tinyint(4) NOT NULL DEFAULT '0' AFTER `de_item_use_use` ", true);
}
?>
