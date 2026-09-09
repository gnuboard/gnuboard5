<?php
// 실행: php tests/object_cache.php (DB 없이 실행, PHP 5.2 문법 사용)
if (PHP_SAPI !== 'cli') exit;
define('_GNUBOARD_', true);
require dirname(dirname(__FILE__)).'/lib/Cache/obj.class.php';

function expect_cache($condition, $message) {
    if (!$condition) {
        fwrite(STDERR, "실패: ".$message."\n");
        exit(1);
    }
}

$cache = new G5_object_cache();
$types = array('bbs', 'content', 'shop', 'plugin', '', '0', 'bbs:plugin', 'plugin/group');
foreach ($types as $type) {
    expect_cache($cache->get($type, 'missing') === false, 'cache miss');
    expect_cache($cache->exists($type, 'missing') === false, '없는 키 존재 여부');
    expect_cache($cache->delete($type, 'missing') === false, '없는 키 삭제');
    expect_cache($cache->set($type, 'same-key', $type, 'same-group') === null, 'set 반환값');
    $cache->set($type, 'same-key', 'other-group', 'other-group');
    $cache->set($type, 'other-key', 'other-key', 'same-group');
}
foreach ($types as $type) {
    expect_cache($cache->get($type, 'same-key', 'same-group') === $type, '타입 격리: '.$type);
    expect_cache($cache->get($type, 'same-key', 'other-group') === 'other-group', '그룹 격리');
    expect_cache($cache->get($type, 'other-key', 'same-group') === 'other-key', '키 격리');
}
foreach ($types as $index => $type) {
    expect_cache($cache->delete($type, 'same-key', 'same-group') === true, '정상 삭제');
    expect_cache($cache->delete($type, 'same-key', 'same-group') === false, '중복 삭제');
    expect_cache(!$cache->exists($type, 'same-key', 'same-group'), '삭제한 값 없음');
    foreach ($types as $other_index => $other_type) {
        if ($other_index > $index) {
            expect_cache($cache->get($other_type, 'same-key', 'same-group') === $other_type, '삭제 시 다른 타입 유지');
        }
    }
    expect_cache($cache->get($type, 'same-key', 'other-group') === 'other-group', '삭제 시 다른 그룹 유지');
    expect_cache($cache->get($type, 'other-key', 'same-group') === 'other-key', '삭제 시 다른 키 유지');
}
foreach ($types as $type) {
    foreach (array(null, false, 0, '0', '', array()) as $value) {
        $cache->set($type, 'value', $value);
        expect_cache($cache->exists($type, 'value'), '빈 값도 존재함');
        expect_cache($cache->get($type, 'value') === $value, '값과 자료형 보존');
        expect_cache($cache->delete($type, 'value') === true, '빈 값 삭제');
    }
    $cache->set($type, 'default-data');
    expect_cache($cache->get($type, 'default-data', 'default') === array(), '기본 데이터와 기본 그룹');
    $cache->set($type, '', 'empty-group', '');
    $cache->set($type, '', 'default-group');
    expect_cache($cache->get($type, '', '') === 'empty-group', '빈 그룹과 빈 키');
    expect_cache($cache->get($type, '') === 'default-group', '빈 그룹과 기본 그룹 격리');
    $cache->set($type, 123, 'numeric', 456);
    expect_cache($cache->get($type, '123', '456') === 'numeric', 'PHP 배열의 정수 키 호환');

    $original = new stdClass();
    $original->value = 'stored';
    $cache->set($type, 'object', $original);
    $original->value = 'changed';
    $read = $cache->get($type, 'object');
    expect_cache($read !== $original && $read->value === 'stored', '저장 시 clone');
    $read->value = 'changed again';
    expect_cache($cache->get($type, 'object')->value === 'stored', '조회 시 clone');
}

// public 멤버와 메서드는 같은 저장소를 사용한다.
$cache->writes['direct']['key'] = 'bbs';
$cache->contents['direct']['key'] = 'content';
$cache->etcs['shop']['direct']['key'] = 'shop';
foreach (array('bbs', 'content', 'shop') as $type) {
    expect_cache($cache->get($type, 'key', 'direct') === $type, 'public 멤버에서 메서드로 조회');
    $cache->set($type, 'key', 'updated', 'direct');
}
expect_cache($cache->writes['direct']['key'] === 'updated', 'writes 단일 저장소');
expect_cache($cache->contents['direct']['key'] === 'updated', 'contents 단일 저장소');
expect_cache($cache->etcs['shop']['direct']['key'] === 'updated', 'etcs 단일 저장소');
$fresh = new G5_object_cache();
expect_cache(!$fresh->exists('shop', 'key', 'direct'), '인스턴스 간 공유 안 함');

// 실제 코어 호출부를 실행하고 DB 조회만 대역으로 교체한다.
require dirname(dirname(__FILE__)).'/lib/get_data.lib.php';
require dirname(dirname(__FILE__)).'/lib/shop.data.lib.php';
function sql_fetch($sql) {
    $GLOBALS['cache_test_queries'][] = $sql;
    return $GLOBALS['cache_test_row'];
}
function sql_real_escape_string($value) { return addslashes($value); }
$g5 = array('write_prefix' => 'g5_write_', 'g5_shop_item_table' => 'g5_shop_item');
$g5_object = new G5_object_cache();
$cache_test_queries = array();
$cache_test_row = array('it_id' => 'item1');
foreach (array('', 'and it_use = 1') as $query) {
    $group = $query ? 'shop_'.md5($query) : '';
    $g5_object->set('plugin', 'item1', array('it_id' => 'plugin'), $group);
    $before = count($cache_test_queries);
    expect_cache(get_shop_item('item1', true, $query) === $cache_test_row, 'shop 최초 조회');
    expect_cache(get_shop_item('item1', true, $query) === $cache_test_row, 'shop 캐시 조회');
    expect_cache(count($cache_test_queries) === $before + 1, 'shop 그룹별 DB 조회 1회');
    expect_cache($g5_object->get('plugin', 'item1', $group) === array('it_id' => 'plugin'), 'shop 적재 시 plugin 유지');
}
$cache_test_row = array('co_id' => 'about', 'co_subject' => '소개');
expect_cache(get_content_by_field('g5_content', 'content', 'co_id', 'about') === $cache_test_row, 'content 분기');
expect_cache($g5_object->get('content', 'about', 'content') === $cache_test_row, 'content 캐시 적재');
$before = count($cache_test_queries);
expect_cache(get_content_db('about', true) === $cache_test_row, 'content 캐시 재사용');
expect_cache(count($cache_test_queries) === $before, 'content 추가 DB 조회 없음');
$cache_test_row = array('wr_id' => 7, 'wr_subject' => '게시글');
expect_cache(get_content_by_field('g5_write_free', 'bbs', 'wr_id', '7') === $cache_test_row, 'bbs 분기');
expect_cache($g5_object->get('bbs', 7, 'free') === $cache_test_row, 'bbs 게시판별 캐시 적재');
echo "객체 캐시 회귀 테스트 통과\n";
