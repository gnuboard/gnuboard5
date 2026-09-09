# 객체 캐시의 타입 격리와 호환성

`G5_object_cache`는 한 요청 안에서 사용하는 메모리 캐시다. `common.php`에서
요청마다 객체를 생성하며 요청 간 값 공유, TTL, Redis/Memcached 연동은 제공하지 않는다.

## 메서드 계약

캐시 주소는 `(type, group, key)`로 결정된다. 타입, 그룹, 키 중 하나라도 다르면
별도의 값을 저장한다. `bbs`, `content`, `shop`과 플러그인에서 정한 타입 모두에 적용된다.
인자 순서와 기본값은 기존과 같다.

```php
$cache->set($type, $key, $data = array(), $group = 'default');
$cache->get($type, $key, $group = 'default');
$cache->exists($type, $key, $group = 'default');
$cache->delete($type, $key, $group = 'default');
```

- `type`, `group`, `key`는 문자열 또는 정수 식별자를 사용한다. 배열과 객체를
  식별자로 사용하는 것은 지원하지 않는다. PHP 배열 키 규칙에 따라 정수 `123`과
  문자열 `'123'`은 같은 키다. 타입에도 같은 규칙이 적용된다.
- 빈 문자열은 각 차원에서 유효하다. 빈 그룹 `''`은 생략 시 사용하는 `'default'`와 다르다.
  식별자에 별도의 정규화나 구분자 연결을 하지 않는다.
- `set()`은 값을 저장하고 명시적인 반환값 없이 `NULL`을 반환한다.
- `get()`은 저장한 값을 반환하며, 없는 값은 `false`를 반환한다.
  저장한 `false`와 cache miss는 `exists()`로 구분한다.
- `exists()`는 `NULL`, `false`, `0`, `'0'`, `''`, 빈 배열을 저장한 경우에도 `true`다.
- `delete()`는 해당 주소만 제거하며 성공 시 `true`, 없는 값이면 `false`를 반환한다.
- 객체는 저장 시와 조회 시 각각 `clone`한다. 기존과 같은 얕은 복사이며,
  객체 내부의 중첩 객체나 배열 내부 객체까지 재귀적으로 복사하지 않는다.
- 빈 타입·그룹 버킷은 정리하지 않는다. 기타 타입을 처음 조회할 때도 빈 타입 버킷이
  만들어질 수 있으며 요청 종료 시 함께 해제된다.

```php
$cache->set('shop', 'same-key', 'shop-value', 'same-group');
$cache->set('plugin', 'same-key', 'plugin-value', 'same-group');

$cache->get('shop', 'same-key', 'same-group'); // 'shop-value'
$cache->delete('plugin', 'same-key', 'same-group');
$cache->exists('shop', 'same-key', 'same-group'); // true
```

## public 멤버 호환성과 업그레이드

세 public 멤버는 제거하지 않고 직접 접근을 deprecated로 표시했다.
`$writes[$group][$key]`와 `$contents[$group][$key]` 구조는 유지한다.
기타 타입의 충돌을 해결하기 위해 `$etcs`에는 타입 차원을 추가했다.

| 타입 | 기존 저장 구조 | 변경 후 저장 구조 |
| --- | --- | --- |
| `bbs` | `$writes[$group][$key]` | 동일 |
| `content` | `$contents[$group][$key]` | 동일 |
| 그 외 | `$etcs[$group][$key]` | `$etcs[$type][$group][$key]` |

메서드와 public 멤버는 같은 저장소를 사용한다. 호환용 데이터를 별도로 복제하거나
기존 `$etcs` 주소로 fallback하지 않는다. 기존 주소에는 타입 정보가 없어 어느 타입의
값인지 복원할 수 없기 때문이다. 메서드만 사용하는 호출부는 변경할 필요가 없다.

외부 테마·플러그인이 `$etcs`를 직접 읽거나 쓰거나 삭제한다면 다음과 같이 수정해야 한다.
기존의 서로 다른 타입 간 캐시 공유에 의존했다면 공유할 호출부에서 동일한 타입을
명시적으로 사용해야 한다.

```php
// 변경 전: shop 등 모든 기타 타입이 같은 주소를 사용했다.
$cache->etcs[$group][$key] = $item;
$item = $cache->etcs[$group][$key];
unset($cache->etcs[$group][$key]);

// 변경 후 권장: 메서드로 타입과 존재 여부를 명시한다.
$cache->set('shop', $key, $item, $group);
if ($cache->exists('shop', $key, $group)) {
    $item = $cache->get('shop', $key, $group);
}
$cache->delete('shop', $key, $group);

// 직접 접근이 불가피한 기존 코드의 새 주소 (deprecated)
$cache->etcs['shop'][$group][$key] = $item;
```

2026-09-09 저장소 PHP 코드 검색에서는 클래스 외부의 캐시 public 멤버 직접 접근을
확인하지 못했다. 코어의 `bbs`, `content`, `shop` 호출은 메서드를 사용한다.
[공개 구현](https://github.com/gnuboard/gnuboard5/blob/master/lib/Cache/obj.class.php)과
[원 제보](https://sir.kr/boards/g5_issues/3)도 검토했다. 공개 웹에서
`g5_object->writes`, `g5_object->contents`, `g5_object->etcs`를 검색했으나
외부 플러그인의 직접 접근 사례는 확인하지 못했다. 검색에 잡히지 않는 코드의
호환성까지 보장하는 결과는 아니므로 public 멤버 제거는 이번 변경에 포함하지 않는다.

## 검증

```sh
php -l lib/Cache/obj.class.php
php -l tests/object_cache.php
php tests/object_cache.php
```

회귀 테스트는 타입·그룹·키 격리, 삭제 범위, 빈 값과 기본값, 객체 clone,
public 멤버와 메서드의 저장소 일치, 인스턴스 간 격리를 확인한다.
DB 대역을 사용해 실제 `get_shop_item()`의 빈 그룹 및 `shop_{query hash}` 그룹,
`get_content_by_field()`의 `bbs`/`content` 적재와 `get_content_db()`의 재사용도 확인한다.
실제 DB나 웹 세션은 필요하지 않다.

구현과 테스트는 PHP 5.2.17에서 지원하는 문법으로 작성했다. PHP 8.4.22와 PHP 5.2.17
CLI에서 변경 파일의 구문 검사와 회귀 테스트가 모두 통과했다. PHP 5.2.17은
[공식 소스 배포본](https://museum.php.net/php5/php-5.2.17.tar.gz)을 임시 디렉터리에서
CLI와 PCRE를 포함하도록 빌드해 검증했다. 시스템 PHP는 변경하지 않았다.
이 검증은 객체 캐시와 위 코어 호출부에 한정하며, 사이트 전체의 PHP 5.2 호환성을
검증한 것은 아니다.
