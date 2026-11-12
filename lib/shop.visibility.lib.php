<?php
if (!defined('_GNUBOARD_')) exit;

// cf_cert_use를 꺼도 기존 상품 분류의 명시적 인증 제한은 유지한다.
function shop_item_visibility_sql($alias = '')
{
    global $g5, $member, $config;
    if (!empty($member['mb_id']) && $member['mb_id'] === ($config['cf_admin'] ?? '')) return '1=1';
    if ($alias === '') $alias = $g5['g5_shop_item_table'];
    if (!preg_match('/^[a-zA-Z0-9_]+$/D', $alias)) return '1=0';
    $p = $alias.'.';
    $cert = !empty($member['mb_id']) && !empty($member['mb_certify']);
    $adult = !empty($member['mb_id']) && !empty($member['mb_adult']);
    $refresh = $cert && strlen((string)($member['mb_dupinfo'] ?? '')) === 64;
    $deny = array();
    if (!$cert || $refresh) $deny[] = 'g5_visibility_category.ca_cert_use <> 0';
    if (!$adult || $refresh) $deny[] = 'g5_visibility_category.ca_adult_use <> 0';
    if (!$deny) return '1=1';
    return "not exists (select 1 from {$g5['g5_shop_category_table']} g5_visibility_category"
        ." where g5_visibility_category.ca_id in ({$p}ca_id, {$p}ca_id2, {$p}ca_id3)"
        ." and (".implode(' or ', $deny)."))";
}

function shop_item_visible($it_id)
{
    global $g5;
    if (!is_scalar($it_id) || (string)$it_id === '') return false;
    $id = sql_escape_string((string)$it_id);
    $row = sql_fetch(" select it_id from {$g5['g5_shop_item_table']} where it_id = '$id' and ".shop_item_visibility_sql());
    return !empty($row['it_id']);
}

// 원 쿼리의 LIMIT, 정렬, 중복 행 및 열을 재평가하지 않고 접근 가능한 행만 남긴다.
function shop_visible_item_result($result)
{
    global $g5;
    $rows = array();
    $ids = array();
    while ($row = sql_fetch_array($result)) {
        if (!isset($row['it_id']) || (string)$row['it_id'] === '') continue;
        $rows[] = $row;
        $ids[(string)$row['it_id']] = true;
    }
    sql_free_result($result);
    $allowed = array();
    foreach (array_chunk(array_keys($ids), 500) as $chunk) {
        $quoted = array();
        foreach ($chunk as $id) $quoted[] = "'".sql_escape_string((string)$id)."'";
        $visible = sql_query("select it_id from {$g5['g5_shop_item_table']} where it_id in (".implode(',', $quoted).") and ".shop_item_visibility_sql());
        while ($row = sql_fetch_array($visible)) $allowed[(string)$row['it_id']] = true;
        sql_free_result($visible);
    }
    $filtered = array();
    foreach ($rows as $row) {
        if (isset($allowed[(string)$row['it_id']])) $filtered[] = $row;
    }
    return new G5_sql_rows($filtered);
}
