<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

if (!defined('G4_USE_SHOP') || !G4_USE_SHOP) return;

include_once(G4_LIB_PATH.'/shop.lib.php');
include_once(G4_LIB_PATH.'/thumbnail.lib.php');

//------------------------------------------------------------------------------
// 쇼핑몰 상수 모음 시작
//------------------------------------------------------------------------------

define('G4_SHOP_DIR', 'shop');

define('G4_SHOP_PATH',  G4_PATH.'/'.G4_SHOP_DIR);
define('G4_SHOP_URL',   G4_URL.'/'.G4_SHOP_DIR);
define('G4_MSHOP_PATH', G4_MOBILE_PATH.'/'.G4_SHOP_DIR);
define('G4_MSHOP_URL',  G4_MOBILE_URL.'/'.G4_SHOP_DIR);

// 보안서버주소 설정
if (G4_HTTPS_DOMAIN) {
    define('G4_HTTPS_SHOP_URL', G4_HTTPS_DOMAIN.'/'.G4_SHOP_DIR);
    define('G4_HTTPS_MSHOP_URL', G4_HTTPS_DOMAIN.'/'.G4_MOBILE_DIR.'/'.G4_SHOP_DIR);
} else {
    define('G4_HTTPS_SHOP_URL', G4_SHOP_URL);
    define('G4_HTTPS_MSHOP_URL', G4_MSHOP_URL);
}

// 미수금에 대한 QUERY 문
// 테이블 a 는 주문서 ($g4[shop_order_table])
// 테이블 b 는 장바구니 ($g4[shop_cart_table])
define(_MISU_QUERY_, "
    count(distinct a.od_id) as ordercount, /* 주문서건수 */
    count(b.ct_id) as itemcount, /* 상품건수 */
    (SUM(b.ct_price * b.ct_qty) + a.od_send_cost) as orderamount , /* 주문합계 */
    (SUM(IF(b.ct_status = '취소' OR b.ct_status = '반품' OR b.ct_status = '품절', b.ct_price * b.ct_qty, 0))) as ordercancel, /* 주문취소 */
    (a.od_receipt_bank + a.od_receipt_card + a.od_receipt_hp + a.od_receipt_point) as receiptamount, /* 입금합계 */
    (a.od_refund_amount + a.od_cancel_card) as receiptcancel, /* 입금취소 */
    (
        (SUM(b.ct_price * b.ct_qty) + a.od_send_cost) -
        (SUM(IF(b.ct_status = '취소' OR b.ct_status = '반품' OR b.ct_status = '품절', b.ct_price * b.ct_qty, 0))) -
        a.od_dc_amount -
        (a.od_receipt_bank + a.od_receipt_card + a.od_receipt_hp + a.od_receipt_point) +
        (a.od_refund_amount + a.od_cancel_card)
    ) as misu /* 미수금 = 주문합계 - 주문취소 - DC - 입금합계 + 입금취소 */");
//------------------------------------------------------------------------------
// 쇼핑몰 상수 모음 끝
//------------------------------------------------------------------------------


//==============================================================================
// 쇼핑몰 필수 실행코드 모음 시작
//==============================================================================
// 쇼핑몰 설정값 배열변수
$default = sql_fetch(" select * from {$g4['shop_default_table']} ");

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
                    ADD `io_id` VARCHAR(255) NOT NULL DEFAULT '' AFTER `ct_qty`,
                    ADD `io_type` TINYINT(4) NOT NULL DEFAULT '0' AFTER `io_id`,
                    ADD `io_price` INT(11) NOT NULL DEFAULT '0' AFTER `io_type` ", false);
}

// it_brand 추가
/*
$sql = " select it_brand from {$g4['shop_item_table']} limit 1 ";
$result = sql_query($sql, false);
if(!$result) {
    sql_query(" ALTER TABLE `{$g4['shop_item_table']}`
                    ADD `it_brand` VARCHAR(255) NOT NULL DEFAULT '' AFTER `it_origin`,
                    ADD `it_model` VARCHAR(255) NOT NULL DEFAULT '' AFTER `it_brand` ", false);
}
*/

//==============================================================================
// 쇼핑몰 필수 실행코드 모음 끝
//==============================================================================
?>