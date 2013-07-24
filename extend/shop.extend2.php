<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

if (!defined('G4_USE_SHOP') || !G4_USE_SHOP) return;

/*
// uniqid 테이블이 없을 경우 생성
if(!sql_query(" select uq_id from {$g4['uniqid_table']} limit 1 ", false)) {
    sql_query(" CREATE TABLE IF NOT EXISTS `{$g4['uniqid_table']}` (
                  `uq_id` bigint(20) unsigned NOT NULL,
                  PRIMARY KEY (`uq_id`)
                ) ", false);
}

// 상품옵션 테이블 생성
if(!sql_query(" select io_id from {$g4['shop_item_option_table']} limit 1 ", false)) {
    sql_query(" CREATE TABLE IF NOT EXISTS `{$g4['shop_item_option_table']}` (
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
    sql_query(" ALTER TABLE `{$g4['shop_item_table']}`
                    ADD `it_option_subject` VARCHAR(255) NOT NULL DEFAULT '' AFTER `it_origin`,
                    ADD `it_supply_subject` VARCHAR(255) NOT NULL DEFAULT '' AFTER `it_option` ", false);
}

// uq_id 필드추가
$sql = " select uq_id from {$g4['shop_cart_table']} limit 1 ";
$result = sql_query($sql, false);
if(!$result) {
    sql_query(" ALTER TABLE `{$g4['shop_cart_table']}` ADD `uq_id` BIGINT(20) unsigned NOT NULL AFTER `ct_id` ", false);
    sql_query(" ALTER TABLE `{$g4['shop_order_table']}` ADD `uq_id` BIGINT(20) unsigned NOT NULL AFTER `od_id` ", false);
    sql_query(" ALTER TABLE `{$g4['shop_card_history_table']}` ADD `uq_id` BIGINT(20) unsigned NOT NULL AFTER `od_id` ", false);
    sql_query(" ALTER TABLE `{$g4['shop_order_table']}` MODIFY COLUMN od_id BIGINT(20) unsigned NOT NULL ", false);
    sql_query(" ALTER TABLE `{$g4['shop_card_history_table']}` MODIFY COLUMN od_id BIGINT(20) unsigned NOT NULL ", false);
    sql_query(" ALTER TABLE `{$g4['shop_cart_table']}` ADD INDEX uq_id (uq_id) ", false);
    sql_query(" ALTER TABLE `{$g4['shop_order_table']}` ADD UNIQUE uq_id (uq_id) ", false);
    sql_query(" ALTER TABLE `{$g4['shop_order_table']}` DROP INDEX index1", false);
}

// 가격필드명변경
$sql = " select it_price from {$g4['shop_item_table']} limit 1 ";
$result = sql_query($sql, false);
if(!$result) {
    sql_query(" ALTER TABLE `{$g4['shop_item_table']}`
                    CHANGE `it_amount` `it_price` INT(11) NOT NULL DEFAULT '0',
                    CHANGE `it_cust_amount` `it_cust_price` INT(11) NOT NULL DEFAULT '0' ", false);
    sql_query(" ALTER TABLE `{$g4['shop_cart_table']}`
                    CHANGE `ct_amount` `ct_price` INT(11) NOT NULL DEFAULT '0',
                    ADD `it_name` VARCHAR(255) NOT NULL DEFAULT '' AFTER `it_id` ", false);
}

// od_mobile 추가
$sql = " select od_mobile from {$g4['shop_order_table']} limit 1 ";
$result = sql_query($sql, false);
if(!$result) {
    sql_query(" ALTER TABLE `{$g4['shop_order_table']}`
                    ADD `od_mobile` TINYINT(4) NOT NULL DEFAULT '0' AFTER `od_time` ", false);
}

// ct_option 추가
$sql = " select ct_option from {$g4['shop_cart_table']} limit 1 ";
$result = sql_query($sql, false);
if(!$result) {
    sql_query(" ALTER TABLE `{$g4['shop_cart_table']}`
                    ADD `ct_option` VARCHAR(255) NOT NULL DEFAULT '' AFTER `ct_stock_use`,
                    ADD `ct_num` INT(11) NOT NULL DEFAULT '0' AFTER `ct_qty`,
                    ADD `io_id` VARCHAR(255) NOT NULL DEFAULT '' AFTER `ct_qty`,
                    ADD `io_type` TINYINT(4) NOT NULL DEFAULT '0' AFTER `io_id`,
                    ADD `io_price` INT(11) NOT NULL DEFAULT '0' AFTER `io_type` ", false);
}

// ct_num 추가
$sql = " select ct_num from {$g4['shop_cart_table']} limit 1 ";
$result = sql_query($sql, false);
if(!$result) {
    sql_query(" ALTER TABLE `{$g4['shop_cart_table']}`
                    ADD `ct_num` INT(11) NOT NULL DEFAULT '0' AFTER `ct_qty` ", false);
}

// it_brand 추가
$sql = " select it_brand from {$g4['shop_item_table']} limit 1 ";
$result = sql_query($sql, false);
if(!$result) {
    sql_query(" ALTER TABLE `{$g4['shop_item_table']}`
                    ADD `it_brand` VARCHAR(255) NOT NULL DEFAULT '' AFTER `it_origin`,
                    ADD `it_model` VARCHAR(255) NOT NULL DEFAULT '' AFTER `it_brand` ", false);
}

// sms_cont5 필드추가
$sql = " select de_sms_cont5 from {$g4['shop_default_table']} ";
$result = sql_query($sql, false);
if (!$result) {
    sql_query(" ALTER TABLE `{$g4['shop_default_table']}`
                    ADD `de_sms_cont5` VARCHAR(255) NOT NULL DEFAULT '' AFTER `de_sms_cont4`,
                    ADD `de_sms_use5` TINYINT(4) NOT NULL DEFAULT '0' AFTER `de_sms_use4` ", false);
}

// 모바일 상품유형 필드 추가
$sql = " select de_mobile_type1_list_use from {$g4['shop_default_table']} ";
$result = sql_query($sql, false);
if (!$result) {
    sql_query(" ALTER TABLE `{$g4['shop_default_table']}`
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
$sql = " SHOW COLUMNS FROM `{$g4['shop_item_table']}` WHERE field = 'it_id' ";
$row = sql_fetch($sql);
if(intval(preg_replace("/[^0-9]/", "", $row['Type'])) != 20) {
    sql_query(" ALTER TABLE `{$g4['shop_item_table']}` MODIFY COLUMN it_id VARCHAR(20) NOT NULL DEFAULT '' ", false);
    sql_query(" ALTER TABLE `{$g4['shop_cart_table']}` MODIFY COLUMN it_id VARCHAR(20) NOT NULL DEFAULT '' ", false);
    sql_query(" ALTER TABLE `{$g4['shop_item_qa_table']}` MODIFY COLUMN it_id VARCHAR(20) NOT NULL DEFAULT '' ", false);
    sql_query(" ALTER TABLE `{$g4['shop_item_use_table']}` MODIFY COLUMN it_id VARCHAR(20) NOT NULL DEFAULT '' ", false);
    sql_query(" ALTER TABLE `{$g4['shop_item_relation_table']}` MODIFY COLUMN it_id VARCHAR(20) NOT NULL DEFAULT '' ", false);
    sql_query(" ALTER TABLE `{$g4['shop_item_relation_table']}` MODIFY COLUMN it_id2 VARCHAR(20) NOT NULL DEFAULT '' ", false);
    sql_query(" ALTER TABLE `{$g4['shop_event_item_table']}` MODIFY COLUMN it_id VARCHAR(20) NOT NULL DEFAULT '' ", false);
    sql_query(" ALTER TABLE `{$g4['shop_wish_table']}` MODIFY COLUMN it_id VARCHAR(20) NOT NULL DEFAULT '' ", false);
}

// 상품요약정보 필드추가
$sql = " select it_info_gubun from {$g4['shop_item_table']} limit 1 ";
$result = sql_query($sql, false);
if(!$result) {
    sql_query(" ALTER TABLE `{$g4['shop_item_table']}` ADD `it_info_gubun` VARCHAR(50) NOT NULL DEFAULT '' AFTER `it_tel_inq`,
                    ADD `it_info_value` TEXT NOT NULL AFTER `it_info_gubun` ", false);
}

// 상품이미지 필드추가
$sql = " select it_img1 from {$g4['shop_item_table']} limit 1 ";
$result = sql_query($sql, false);
if(!$result) {
    sql_query(" ALTER TABLE `{$g4['shop_item_table']}`
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
$sql = " select ir_no from {$g4['shop_item_relation_table']} limit 1 ";
$result = sql_query($sql, false);
if(!$result) {
    sql_query(" ALTER TABLE `{$g4['shop_item_relation_table']}`
                    ADD `ir_no` INT(11) NOT NULL DEFAULT '0' AFTER `it_id2` ", false);
}

if (!isset($it['it_mobile_explan'])) {
    sql_query(" ALTER TABLE `{$g4['shop_item_table']}`
                    ADD `it_mobile_explan` TEXT NOT NULL AFTER `it_explan`,
                    ADD `it_mobile_head_html` TEXT NOT NULL AFTER `it_tail_html`,
                    ADD `it_mobile_tail_html` TEXT NOT NULL AFTER `it_mobile_head_html` ", false);
}

// de_guest_cart_use 필드추가
$sql = " select de_guest_cart_use from {$g4['shop_default_table']} ";
$result = sql_query($sql, false);
if(!$result) {
    sql_query(" ALTER TABLE `{$g4['shop_cart_table']}`
                    ADD `mb_id` VARCHAR(255) NOT NULL DEFAULT '' AFTER `uq_id` ", false);
    sql_query(" ALTER TABLE `{$g4['shop_default_table']}`
                    ADD `de_cart_keep_term` INT(11) NOT NULL DEFAULT '0' AFTER `de_code_dup_use`,
                    ADD `de_guest_cart_use` TINYINT(4) NOT NULL DEFAULT '0' AFTER `de_cart_keep_term` ", false);
}

// 포인트타입 필드 추가
$sql = " select it_point_type from {$g4['shop_item_table']} limit 1 ";
$result = sql_query($sql, false);
if(!$result) {
    sql_query(" ALTER TABLE `{$g4['shop_item_table']}`
                    ADD `it_point_type` TINYINT(4) NOT NULL DEFAULT '0' AFTER `it_point` ", false);
}

// 쿠폰테이블
$sql = " DESCRIBE `{$g4['shop_coupon_table']}` ";
$result = sql_query($sql, false);
if(!$result) {
    sql_query(" CREATE TABLE IF NOT EXISTS `{$g4['shop_coupon_table']}` (
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
$sql = " select cp_amount from {$g4['shop_cart_table']} limit 1 ";
$result = sql_query($sql, false);
if(!$result) {
    sql_query(" ALTER TABLE `{$g4['shop_cart_table']}`
                    ADD `cp_amount` INT(11) NOT NULL DEFAULT '0' AFTER `ct_point` ", false);
    sql_query(" ALTER TABLE `{$g4['shop_order_table']}`
                    ADD `od_coupon` INT(11) NOT NULL DEFAULT '0' AFTER `od_dc_amount`,
                    ADD `od_send_coupon` INT(11) NOT NULL DEFAULT '0' AFTER `od_send_cost` ", false);
}

// 쿠폰사용정보필드추가
$sql = " select od_id from {$g4['shop_coupon_table']} limit 1 ";
$result = sql_query($sql, false);
if(!$result) {
    sql_query(" ALTER TABLE `{$g4['shop_coupon_table']}`
                    ADD `od_id` BIGINT(20) UNSIGNED NOT NULL AFTER `cp_maximum`,
                    ADD `cp_used_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `cp_used` ", false);
}

// 장바구니 선택필드추가
$sql = " select ct_select from {$g4['shop_cart_table']} limit 1 ";
$result = sql_query($sql, false);
if(!$result) {
    sql_query(" ALTER TABLE `{$g4['shop_cart_table']}`
                    ADD `ct_select` TINYINT(4) NOT NULL DEFAULT '0' AFTER `ct_direct` ", true);
}

// 개별배송비 필드 추가
$sql = " select it_sc_type from {$g4['shop_item_table']} limit 1 ";
$result = sql_query($sql, false);
if(!$result) {
    sql_query(" ALTER TABLE `{$g4['shop_item_table']}`
                    ADD `it_sc_type` TINYINT(4) NOT NULL DEFAULT '0' AFTER `it_stock_qty`,
                    ADD `it_sc_method` TINYINT(4) NOT NULL DEFAULT '0' AFTER `it_sc_type`,
                    ADD `it_sc_amount` INT(11) NOT NULL DEFAULT '0' AFTER `it_sc_method`,
                    ADD `it_sc_minimum` INT(11) NOT NULL DEFAULT '0' AFTER `it_sc_amount`,
                    ADD `it_sc_qty` INT(11) NOT NULL DEFAULT '0' AFTER `it_sc_minimum` ", false);
}

// 장바구니 배송비필드 추가
$sql = " select ct_send_cost from {$g4['shop_cart_table']} limit 1 ";
$result = sql_query($sql, false);
if(!$result) {
    sql_query(" ALTER TABLE `{$g4['shop_cart_table']}`
                    ADD `ct_send_cost` TINYINT(11) NOT NULL DEFAULT '0' AFTER `io_price` ", false);
}

// 결제필드 변경
$sql = " select od_temp_amount from {$g4['shop_order_table']} limit 1 ";
$result = sql_query($sql, false);
if(!$result) {
    sql_query(" ALTER TABLE `{$g4['shop_order_table']}`
                    ADD `od_temp_amount` INT(11) NOT NULL DEFAULT '0' AFTER `od_send_coupon`,
                    ADD `od_receipt_amount` INT(11) NOT NULL DEFAULT '0' AFTER `od_temp_point`,
                    ADD `od_receipt_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `od_bank_account` ", false);
}

// 추가배송비 테이블
$sql = " select sc_id from {$g4['shop_sendcost_table']} limit 1 ";
$result = sql_query($sql, false);
if(!$result) {
    sql_query(" CREATE TABLE IF NOT EXISTS `{$g4['shop_sendcost_table']}` (
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
$sql = " select od_send_cost2 from {$g4['shop_order_table']} limit 1 ";
$result = sql_query($sql, false);
if(!$result) {
    sql_query(" ALTER TABLE `{$g4['shop_order_table']}`
                    ADD `od_send_cost2` INT(11) NOT NULL DEFAULT '0' AFTER `od_send_coupon` ", false);
}

// 복합과세 필드 추가
$sql = " select de_tax_flag_use from {$g4['shop_default_table']} ";
$result = sql_query($sql, false);
if(!$result) {
    sql_query(" ALTER TABLE `{$g4['shop_default_table']}`
                    ADD `de_tax_flag_use` TINYINT(4) NOT NULL DEFAULT '0' AFTER `de_escrow_use` ", false);
    sql_query(" ALTER TABLE `{$g4['shop_item_table']}`
                    ADD `it_notax` TINYINT(4) NOT NULL DEFAULT '0' AFTER `it_point_type` ", false);
}

// 에스크로필드 추가
$sql = " select od_tno from {$g4['shop_order_table']} limit 1 ";
$result = sql_query($sql, false);
if(!$result) {
    sql_query(" ALTER TABLE `{$g4['shop_order_table']}`
                    ADD `od_tno` VARCHAR(255) NOT NULL DEFAULT '' AFTER `od_settle_case`,
                    ADD `od_escrow` TINYINT(4) NOT NULL DEFAULT '0' AFTER `od_tno` ", true);
}
*/

// shop_request 테이블이 없을 경우 생성
if(!sql_query(" select rq_id from {$g4['shop_request_table']} limit 1 ", false)) {
    sql_query(" CREATE TABLE IF NOT EXISTS `{$g4['shop_request_table']}` (
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
if(!sql_query(" select od_mod_history from {$g4['shop_order_table']} limit 1 ", false)) {
    sql_query(" ALTER TABLE `{$g4['shop_order_table']}`
                    ADD `od_mod_history` TEXT NOT NULL AFTER `od_shop_memo` ", true);
}

// 주문정보에 복합결제 필드추가
if(!sql_query(" select od_tax_flag from {$g4['shop_order_table']} limit 1 ", false)) {
    sql_query(" ALTER TABLE `{$g4['shop_order_table']}`
                    ADD `od_tax_flag` TINYINT(4) NOT NULL DEFAULT '0' AFTER `od_escrow` ", true);
}
?>