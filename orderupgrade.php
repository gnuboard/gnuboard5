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

// cart 테이블 index 추가
if(!sql_fetch(" show keys from {$g5['g5_shop_cart_table']} where Key_name = 'ct_status' ")) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_cart_table']}`
                    ADD INDEX `it_id` (`it_id`),
                    ADD INDEX `ct_status` (`ct_status`) ", true);
}

echo '<p>장바구니 테이블 업그레이드 완료!</p>';

include_once(G5_PATH.'/tail.sub.php');
?>