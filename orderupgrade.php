<?php
include_once('./_common.php');

if($is_admin != 'super')
    alert('최고관리자로 로그인 후 실행해 주십시오.', G5_URL);

$g5['title'] = '장바구니 테이블 업그레이드';
include_once(G5_PATH.'/head.sub.php');

// 배송비정보 필드 cart 테이블에 추가
if(!sql_query(" select it_sc_type from {$g5['g5_shop_cart_table']} limit 1 ", false)) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_cart_table']}`
                    ADD `it_sc_type` tinyint(4) NOT NULL DEFAULT '0' AFTER `it_name`,
                    ADD `it_sc_method` tinyint(4) NOT NULL DEFAULT '0' AFTER `it_sc_type`,
                    ADD `it_sc_price` int(11) NOT NULL DEFAULT '0' AFTER `it_sc_method`,
                    ADD `it_sc_minimum` int(11) NOT NULL DEFAULT '0' AFTER `it_sc_price`,
                    ADD `it_sc_qty` int(11) NOT NULL DEFAULT '0' AFTER `it_sc_minimum` ", true);

    // cart 테이블에 상품의 배송비관련 정보 기록
    $sql = " select ct_id, it_id from {$g5['g5_shop_cart_table']} order by ct_id ";
    $result = sql_query($sql);

    for($i=0; $row=sql_fetch_array($result); $i++) {
        $sql = " select it_id, it_sc_type, it_sc_method, it_sc_price, it_sc_minimum, it_sc_qty
                    from {$g5['g5_shop_item_table']}
                    where it_id = '{$row['it_id']}' ";
        $it = sql_fetch($sql);

        if(!$it['it_id'])
            continue;

        $sql = " update {$g5['g5_shop_cart_table']}
                    set it_sc_type      = '{$it['it_sc_type']}',
                        it_sc_method    = '{$it['it_sc_method']}',
                        it_sc_price     = '{$it['it_sc_price']}',
                        it_sc_minimum   = '{$it['it_sc_minimum']}',
                        it_sc_qty       = '{$it['it_sc_qty']}'
                    where ct_id = '{$row['ct_id']}' ";
        sql_query($sql);
    }
}

// 장바구니 상품 주문폼 등록시간 기록 필드 추가
if(!sql_query(" select ct_select_time from {$g5['g5_shop_cart_table']} limit 1 ", false)) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_cart_table']}`
                    ADD `ct_select_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `ct_select` ", true);
}

// 모바일 이니시스 계좌이체 결과 전달을 위한 테이블 추가
if(!sql_query(" DESCRIBE {$g5['g5_shop_inicis_log_table']} ", false)) {
    sql_query(" CREATE TABLE IF NOT EXISTS `{$g5['g5_shop_inicis_log_table']}` (
                  `oid` bigint(20) unsigned NOT NULL,
                  `P_TID` varchar(255) NOT NULL DEFAULT '',
                  `P_MID` varchar(255) NOT NULL DEFAULT '',
                  `P_AUTH_DT` varchar(255) NOT NULL DEFAULT '',
                  `P_STATUS` varchar(255) NOT NULL DEFAULT '',
                  `P_TYPE` varchar(255) NOT NULL DEFAULT '',
                  `P_OID` varchar(255) NOT NULL DEFAULT '',
                  `P_FN_NM` varchar(255) NOT NULL DEFAULT '',
                  `P_AUTH_NO` varchar(255) NOT NULL DEFAULT '',
                  `P_AMT` int(11) NOT NULL DEFAULT '0',
                  `P_RMESG1` varchar(255) NOT NULL DEFAULT '',
                  PRIMARY KEY (`oid`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8 ", true);
}

// cart 테이블 index 추가
if(!sql_fetch(" show keys from {$g5['g5_shop_cart_table']} where Key_name = 'ct_status' ")) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_cart_table']}`
                    ADD INDEX `it_id` (`it_id`),
                    ADD INDEX `ct_status` (`ct_status`) ", true);
}

// 결제정보 임시저장 테이블 추가
if(isset($g5['g5_shop_order_data_table']) && !sql_query(" DESCRIBE {$g5['g5_shop_order_data_table']} ", false)) {
    sql_query(" CREATE TABLE IF NOT EXISTS `{$g5['g5_shop_order_data_table']}` (
                  `od_id` bigint(20) unsigned NOT NULL,
                  `dt_pg` varchar(255) NOT NULL DEFAULT '',
                  `dt_data` text NOT NULL,
                  `dt_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                  KEY `od_id` (`od_id`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8;", true);
}

// 모바일 이니시스 결제정보 테이블 필드 추가
if(!sql_query(" select P_AUTH_NO from {$g5['g5_shop_inicis_log_table']} limit 1 ", false)) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_inicis_log_table']}`
                    ADD `P_AUTH_NO` varchar(255) NOT NULL DEFAULT '' AFTER `P_FN_NM` ", true);
}

// 모바일 이니시스 noti 받는 테이블 필드 추가
if(!sql_query(" select post_data from {$g5['g5_shop_inicis_log_table']} limit 1 ", false)) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_inicis_log_table']}`
                    ADD `post_data` text NOT NULL AFTER `P_RMESG1`,
                    ADD `is_mail_send` tinyint(4) NOT NULL DEFAULT '1' AFTER `post_data` ", true);
}

// 테스트 주문필드 추가
if(!sql_query(" select od_test from {$g5['g5_shop_order_table']} limit 1 ", false)) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_order_table']}`
                    ADD `od_test` tinyint(4) NOT NULL DEFAULT '0' AFTER `od_settle_case` ", true);
}

// 주문정보 임시저장 테이블에 장바구니 ID 필드 추가
if(!sql_query(" select cart_id from {$g5['g5_shop_order_data_table']} limit 1 ", false)) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_order_data_table']}`
                    ADD `cart_id` bigint(20) unsigned NOT NULL AFTER `od_id`,
                    ADD `mb_id` varchar(20) NOT NULL DEFAULT '' AFTER `cart_id` ", true);
}

// 쿠폰존 테이블 추가
if(isset($g5['g5_shop_coupon_zone_table'])) {
    if(!sql_query(" DESCRIBE {$g5['g5_shop_coupon_zone_table']} ", false)) {
        sql_query(" CREATE TABLE IF NOT EXISTS `{$g5['g5_shop_coupon_zone_table']}` (
                      `cz_id` int(11) NOT NULL AUTO_INCREMENT,
                      `cz_type` tinyint(4) NOT NULL DEFAULT '0',
                      `cz_subject` varchar(255) NOT NULL DEFAULT '',
                      `cz_start` DATE NOT NULL DEFAULT '0000-00-00',
                      `cz_end` DATE NOT NULL DEFAULT '0000-00-00',
                      `cz_file` varchar(255) NOT NULL DEFAULT '',
                      `cz_period` int(11) NOT NULL DEFAULT '0',
                      `cz_point` INT(11) NOT NULL DEFAULT '0',
                      `cp_method` TINYINT(4) NOT NULL DEFAULT '0',
                      `cp_target` VARCHAR(255) NOT NULL DEFAULT '',
                      `cp_price` INT(11) NOT NULL DEFAULT '0',
                      `cp_type` TINYINT(4) NOT NULL DEFAULT '0',
                      `cp_trunc` INT(11) NOT NULL DEFAULT '0',
                      `cp_minimum` INT(11) NOT NULL DEFAULT '0',
                      `cp_maximum` INT(11) NOT NULL DEFAULT '0',
                      `cz_download` int(11) NOT NULL DEFAULT '0',
                      `cz_datetime` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
                      PRIMARY KEY (`cz_id`)
                    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 ", true);
    }
}

// 쿠폰테이블에 cz_id 필드 추가
if(!sql_query(" select cz_id from {$g5['g5_shop_coupon_table']} limit 1 ", false)) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_coupon_table']}`
                    ADD `cz_id` int(11) NOT NULL DEFAULT '0' AFTER `mb_id` ", true);
}

echo '<p>테이블 업그레이드 완료!</p>';

include_once(G5_PATH.'/tail.sub.php');