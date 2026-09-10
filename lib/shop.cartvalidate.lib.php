<?php
if (!defined('_GNUBOARD_')) exit;

// KVE-2026-2345: 요청의 옵션 종류는 가격 계산의 근거가 될 수 없다.
// DB 조회와 분리하여 신규 요청과 기존 장바구니에 같은 규칙을 적용한다.
function shop_validate_cart_rows($item, $options, $rows, $check_price = false)
{
    $error = '상품 또는 옵션 정보가 올바르지 않습니다. 장바구니에서 삭제한 뒤 다시 담아 주십시오.';
    if (empty($item['it_id']) || empty($item['it_use']) || !empty($item['it_tel_inq']) || !$rows) {
        return array('error' => $error);
    }

    $option_map = array();
    $has_selection = false;
    foreach ($options as $option) {
        if ((string) $option['it_id'] !== (string) $item['it_id']) {
            return array('error' => $error);
        }
        if ((string) $option['io_type'] === '0') {
            $has_selection = true;
        }
        $key = $option['io_type'].':'.$option['io_id'];
        if (isset($option_map[$key])) {
            return array('error' => $error);
        }
        $option_map[$key] = $option;
    }

    $validated = array();
    $quantities = array();
    $base_qty = 0;
    $total = 0;
    foreach ($rows as $row) {
        // 요청값의 형식을 먼저 확인한다.
        if (!isset($row['io_id'], $row['io_type'], $row['ct_qty']) ||
            !is_string($row['io_id']) ||
            !(is_string($row['io_type']) || is_int($row['io_type'])) ||
            !in_array((string) $row['io_type'], array('0', '1'), true) ||
            !(is_string($row['ct_qty']) || is_int($row['ct_qty'])) ||
            !preg_match('/^[1-9][0-9]*$/D', (string) $row['ct_qty']) ||
            (float) $row['ct_qty'] > 2147483647) {
            return array('error' => $error);
        }

        // 서버 상품·옵션 정보로 종류와 가격을 결정한다.
        $type = (int) $row['io_type'];
        if (!$validated && $type !== 0) {
            return array('error' => $error);
        }
        $id = $row['io_id'];
        $key = $type.':'.$id;
        if ($id === '') {
            if ($type !== 0 || $has_selection) {
                return array('error' => $error);
            }
            $price = 0;
            $stock = (int) $item['it_stock_qty'];
        } else {
            if (!isset($option_map[$key]) || empty($option_map[$key]['io_use'])) {
                return array('error' => $error);
            }
            $option = $option_map[$key];
            $type = (int) $option['io_type'];
            $price = (int) $option['io_price'];
            $stock = (int) $option['io_stock_qty'];
        }

        // 누적 재고와 저장된 가격을 검사한다.
        $qty = (int) $row['ct_qty'];
        $quantities[$key] = isset($quantities[$key]) ? $quantities[$key] + $qty : $qty;
        if ($quantities[$key] > $stock) {
            return array('error' => '상품 또는 옵션의 재고수량이 부족합니다.');
        }
        $unit_price = $type === 1 ? $price : (int) $item['it_price'] + $price;
        if ($unit_price < 0) {
            return array('error' => $error);
        }
        if ($check_price && (!isset($row['ct_price'], $row['io_price']) ||
            (int) $row['ct_price'] !== (int) $item['it_price'] || (int) $row['io_price'] !== $price)) {
            return array('error' => '상품 또는 옵션 금액이 변경되었습니다. 장바구니를 다시 확인해 주십시오.');
        }

        if ($type === 0) {
            $base_qty += $qty;
        }
        $row['io_type'] = $type;
        $row['io_price'] = $price;
        $row['ct_price'] = (int) $item['it_price'];
        $row['ct_qty'] = $qty;
        $validated[] = $row;
        $total += $unit_price * $qty;
    }

    if (!$base_qty || (!empty($item['it_buy_min_qty']) && $base_qty < $item['it_buy_min_qty']) ||
        (!empty($item['it_buy_max_qty']) && $base_qty > $item['it_buy_max_qty'])) {
        return array('error' => '상품의 선택옵션과 최소/최대 구매수량을 확인해 주십시오.');
    }

    return array('error' => '', 'rows' => $validated, 'total' => $total);
}

function shop_cart_option_data($it_id)
{
    global $g5;
    $id = sql_escape_string($it_id);
    $item = sql_fetch(" select * from {$g5['g5_shop_item_table']} where it_id = '$id' ");
    $result = sql_query(" select * from {$g5['g5_shop_item_option_table']} where it_id = '$id' ");
    $options = array();
    while ($row = sql_fetch_array($result)) {
        $options[] = $row;
    }
    return array($item, $options);
}

// 모든 상품을 먼저 검증한다. 바로구매/옵션수정의 선삭제와 다중 상품의 부분 저장을 막는다.
function shop_validate_cart_request($post, $multi = false)
{
    $error = '장바구니 요청 정보가 올바르지 않습니다.';
    $products = array();
    if (empty($post['it_id']) || !is_array($post['it_id']) ||
        array_keys($post['it_id']) !== range(0, count($post['it_id']) - 1)) {
        return array('error' => $error);
    }
    foreach ($post['it_id'] as $i => $id) {
        if ($multi && empty($post['chk_it_id'][$i])) {
            continue;
        }
        if (!is_string($id) || $id === '' || safe_replace_regex($id, 'it_id') !== $id || isset($products[$id])) {
            return array('error' => $error);
        }
        foreach (array('io_id', 'io_type', 'ct_qty') as $field) {
            if (!isset($post[$field][$id]) || !is_array($post[$field][$id]) || !$post[$field][$id]) {
                return array('error' => $error);
            }
        }
        $keys = range(0, count($post['io_id'][$id]) - 1);
        foreach (array('io_id', 'io_type', 'ct_qty') as $field) {
            if (array_keys($post[$field][$id]) !== $keys) {
                return array('error' => $error);
            }
        }
        $rows = array();
        foreach ($keys as $k) {
            $io_id = $post['io_id'][$id][$k];
            if (!is_string($io_id) || preg_replace(G5_OPTION_ID_FILTER, '', $io_id) !== $io_id ||
                (isset($post['io_value'][$id][$k]) && !is_string($post['io_value'][$id][$k]))) {
                return array('error' => $error);
            }
            $rows[] = array('io_id' => $io_id, 'io_type' => $post['io_type'][$id][$k], 'ct_qty' => $post['ct_qty'][$id][$k]);
        }
        list($item, $options) = shop_cart_option_data($id);
        $validated = shop_validate_cart_rows($item, $options, $rows);
        if ($validated['error'] !== '') {
            return $validated;
        }
        $validated['item'] = $item;
        $products[$id] = $validated;
    }
    return array('error' => $products ? '' : $error, 'products' => $products);
}

// 기존 행과 합친 결과도 삭제/UPDATE 전에 모든 상품에 대해 확인한다.
// 동시 요청과 SQL 실패의 원자성까지 보장하는 잠금/트랜잭션은 아니다.
function shop_validate_cart_merge($cart_id, $products, $direct = false, $replace = false)
{
    global $g5;
    $cart_id = sql_escape_string($cart_id);
    foreach ($products as $it_id => $product) {
        $id = sql_escape_string($it_id);
        $rows = array();
        if (!$replace) {
            $exclude = $direct ? ' and ct_direct <> 1 ' : '';
            $result = sql_query(" select * from {$g5['g5_shop_cart_table']} where od_id = '$cart_id' and it_id = '$id' and ct_status = '쇼핑' $exclude order by ct_id asc ");
            while ($row = sql_fetch_array($result)) {
                $rows[] = $row;
            }
        }
        $rows = array_merge($rows, $product['rows']);
        list($item, $options) = shop_cart_option_data($it_id);
        $validated = shop_validate_cart_rows($item, $options, $rows, true);
        if ($validated['error'] !== '') {
            return $validated['error'];
        }

        // 바로구매에서는 다른 장바구니가 선택해 둔 수량도 선삭제 전에 검사한다.
        if ($direct) {
            $quantities = array();
            foreach ($rows as $row) {
                $key = $row['io_type'].':'.$row['io_id'];
                $quantities[$key] = isset($quantities[$key]) ? $quantities[$key] + $row['ct_qty'] : $row['ct_qty'];
            }
            foreach ($product['rows'] as $row) {
                $io_id = sql_escape_string($row['io_id']);
                $type = (int) $row['io_type'];
                $reserved = sql_fetch(" select SUM(ct_qty) as cnt from {$g5['g5_shop_cart_table']} where od_id <> '$cart_id' and it_id = '$id' and io_id = '$io_id' and io_type = '$type' and ct_stock_use = 0 and ct_status = '쇼핑' and ct_select = '1' ");
                $stock = $row['io_id'] === '' ? get_it_stock_qty($it_id) : get_option_stock_qty($it_id, $row['io_id'], $type);
                if ($quantities[$type.':'.$row['io_id']] + (int) $reserved['cnt'] > $stock) {
                    return '상품 또는 옵션의 재고수량이 부족합니다.';
                }
            }
        }
    }
    return '';
}

// 주문 준비/확정 시에는 선택된 행만 검증하여 보조옵션 단독 선택과 과거 조작 행도 차단한다.
function shop_validate_order_cart($cart_id)
{
    global $g5;
    $id = sql_escape_string($cart_id);
    $result = sql_query(" select * from {$g5['g5_shop_cart_table']} where od_id = '$id' and ct_select = '1' order by ct_id asc ");
    $products = array();
    while ($row = sql_fetch_array($result)) {
        if ($row['ct_status'] !== '쇼핑') {
            return '이미 처리되었거나 올바르지 않은 장바구니입니다.';
        }
        $products[$row['it_id']][] = $row;
    }
    if (!$products) {
        return '주문하실 상품을 선택해 주십시오.';
    }
    foreach ($products as $it_id => $rows) {
        list($item, $options) = shop_cart_option_data($it_id);
        $validated = shop_validate_cart_rows($item, $options, $rows, true);
        if ($validated['error'] !== '') {
            return $validated['error'];
        }
    }
    return '';
}
