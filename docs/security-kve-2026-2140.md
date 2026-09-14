# KVE-2026-2140 주문 상태·복귀·복구

## 구현 범위

1차 세션 검사에 주문별 복귀 토큰, 영속 승인 기록, 데이터 정리와 운영 대조 도구를 추가했다. PC·모바일 Toss와 모바일 KCP의 반환·승인·주문 저장을 대상으로 한다. 공통 임시 저장의 필드 제한과 비밀번호 보호는 다른 PG에도 적용된다. 다른 PG의 쿠키 없는 복귀·승인 재시도를 이 구현이 대신 처리하지는 않는다.

- 임시 저장은 256비트 난수 토큰을 발급한다. DB에는 SHA-256 해시만 저장하고, 토큰 원문은 `X-G5-Order-State` 응답 헤더로 현재 탭에 전달한다.
- 토큰은 주문번호·PG·회원/비회원·장바구니·일반/개인결제·저장 본문 해시·만료 시각에 연결된다. 원문 임시 데이터와 비밀번호 해시를 읽기 전에 토큰과 최소 메타데이터를 검증한다.
- 새 테이블 `order_access`의 주문별 잠금과 최종 처리 시 장바구니별 잠금을 사용한다. 서로 다른 세션/프로세스에서도 승인부터 주문 저장까지 중복 처리를 막는다. 같은 장바구니의 다른 미완료 승인도 차단한다.
- 폼을 표시할 때 주문별 체크아웃 nonce와 장바구니를 세션에 등록한다. 다른 탭이 현재 주문 세션을 바꾸더라도 원래 탭은 자기 주문을 저장·복원한다. 같은 상품 장바구니를 두 번 결제하는 것은 허용하지 않는다.
- 쿠키 없는 복귀는 토큰으로 서버 상태를 복원한다. 회원 주문의 경우 해당 결제 요청 안에서만 원래 회원 컨텍스트를 적용하고 로그인 세션은 발급하지 않는다. 다른 회원으로 로그인한 브라우저의 토큰 사용은 거부한다. 결제 토큰은 해당 주문에 대한 bearer 권한이므로 로그·URL 보관에 주의한다.
- 최종 주문의 이름·주소·금액·포인트·쿠폰·배송비 등 업무 필드는 서버가 저장한 데이터로 복원한다. 결제 키·PG 암호문 등 승인 응답 필드는 별도로 검증한다. 정상 결제 금액은 기존 서버 장바구니 계산값 및 개인결제 금액과 대조한다.
- 공통 임시 저장은 `lib/shop_order_fields.lib.php`의 기본 주문서/PG별 허용 목록만 저장한다. 비밀번호와 토큰은 복원용 데이터에 보관하지 않는다. 사용자 스킨의 추가 업무 필드는 서버 허용 목록에 명시적으로 추가해야 한다.
- 비회원 비밀번호는 서버 해시로 분리 보관하며, 최종 주문에서 재해싱하지 않는다. 완료 후 임시 행·토큰·해시·응답 상태를 정리한다. 주문 POST 로그에서 비밀번호와 상태/체크아웃 토큰을 제외한다.

## 승인 상태와 복구

| 상태 | 처리 |
|---|---|
| `pending` | 승인 미시작. 기본 복귀 수명 2시간. `G5_ORDER_DATA_ACCESS_TTL`로 조정 가능 |
| `approving` | PG 요청 직전에 금액·요청 식별자를 영속 기록. 응답 유실 가능성이 있으므로 무조건 재승인하지 않음 |
| `approved` | 검증된 PG 결과를 저장. 후속 주문 저장을 계속할 수 있음 |
| `finalizing` | 주문 DB 반영을 시작함. 일부만 저장되었을 가능성이 있음 |
| `completed` | 주문 처리가 끝남. 토큰·비밀번호 해시·PG 응답은 지워짐 |
| `cancel_pending` | 취소 요청 시작. 결과 대조 전에는 승인 결과를 재사용하지 않음 |
| `cancelled`, `failed` | 확인된 종료 상태. 유예 후 정리 가능 |
| `legacy`, `unknown` | 운영자 대조 필요. 자동 삭제·재승인·토큰 발급 금지 |
| `purged` | 임시 개인정보 및 인증 상태 제거. 주문번호 재사용 방지용 기록만 유지 |

Toss는 안정적인 `Idempotency-Key`로 승인한다. 응답을 놓친 요청과 승인 후 중단된 요청은 PG 조회를 먼저 수행하고 주문번호·결제 키·금액·상태를 대조한다. 취소·실패한 거래의 저장된 성공 응답을 재사용하지 않는다. PG가 같은 거래의 `IN_PROGRESS` 상태를 확인한 경우에만 동일 키로 승인 재시도하며, 멱등 키 유효기간보다 짧은 14일로 제한한다. 조회 자체가 실패하면 재승인하지 않는다. 취소에도 동일 거래의 고정 멱등 키와 거래번호 대조를 적용한다.

KCP는 검증된 승인 결과를 별도 기록해 같은 요청 재처리 시 결과를 복원한다. 통신 도중 중단되어 승인 결과가 없는 경우에는 KCP 거래 원장을 확인한 운영자가 복구 자료를 제공해야 한다. 자동 조회 API를 임의로 가정하지 않았다.

일반 주문이 `finalizing`에서 중단됐으나 주문 행이 없고 원래 선택 장바구니가 남아 있으면 승인된 거래를 계속 처리한다. 주문 행이 이미 생겼거나 개인결제가 일부 반영된 경우에는 자동 덮어쓰기/재승인하지 않는다. 포인트·쿠폰·주문·장바구니를 운영자가 대조한 뒤 완료 처리한다. 기존 주문/장바구니 테이블이 MyISAM인 환경에서 전체 업무 트랜잭션을 원자적으로 되돌릴 수 있다고 가정하지 않는다.

## 설치·배포

신규 설치 SQL과 `migrations/20260914_001_order_access_state.sql`을 함께 제공한다. 기존 설치는 최고관리자 DB업그레이드에서 선행 마이그레이션을 처리한 후 이 마이그레이션을 적용한다. 일반 페이지에서는 DDL을 실행하지 않는다. 새 테이블은 InnoDB를 사용한다. MySQL 5.0의 연결당 이름 잠금 하나 제약에 맞춰 주문 잠금과 장바구니 잠금은 각각 별도 비영속 DB 연결을 사용한다. 기존 업무·포인트 DB 연결의 잠금과 분리되며, 연결 종료 시 서버가 잠금을 해제한다. 이 처리에는 일반 결제 요청당 최대 2개의 추가 DB 연결이 필요하다. 연결·잠금 획득 실패나 잠금 연결 유실이 확인되면 승인 상태 처리를 중단한다. 정리 도구는 항목별로 잠금을 해제하여 처리 건수만큼 연결이 누적되지 않는다.

배포 순서:

1. 신규 결제 접수를 일시 중단하고 DB 및 웹 공개 경로 밖의 미완료 거래 백업을 확보한다.
2. 미완료 주문·PG 승인 내역을 확인한다. 승인된 거래를 정리 대상으로 표시하지 않는다.
3. 마이그레이션과 PHP/JS 변경을 함께 적용한다. 사용자 스킨에도 체크아웃 필드, 응답 헤더 수신 및 복귀 토큰 전달 변경을 반영한다. 새 PHP에 예전 JS가 캐시되지 않도록 배포 캐시를 갱신한다.
4. 반환 URL의 HTTPS와 토큰 전달을 확인한다. Toss 성공/실패 URL과 KCP Ret_URL에 `g5_order_state`가 전달된다. KCP의 쿠키 재전송 우회는 토큰 형태를 인식하는 것뿐이며, 실제 인증은 엔드포인트의 DB 해시 검증으로 수행한다.
5. 웹서버·프록시·APM에서 결제 복귀 URL의 쿼리와 Referer를 로그에 기록하지 않거나 `g5_order_state`를 마스킹한다. 예를 들어 Nginx 결제 반환 로그는 `$request_uri`/`$request` 대신 쿼리 없는 `$uri`를 사용한다. 응답은 `no-store` 및 `Referrer-Policy: no-referrer`를 설정한다. 응답 헤더와 POST 본문을 별도 수집하는 서비스에도 같은 마스킹을 적용한다.
6. 계약된 PG 테스트 환경에서 외부 앱 복귀·성공·실패·취소·재통보를 확인한 후 접수를 재개한다. 코드 push와 운영 배포/PG 검증은 구분한다.

## 기존 요청과 정리 도구

`php tools/shop-order-maintenance.php`는 CLI 전용이며 기본값은 읽기 전용 목록이다. 웹에서는 실행되지 않는다. 목록과 오류에 토큰·비밀번호·전체 개인정보·PG 응답을 출력하지 않는다.

```sh
php tools/shop-order-maintenance.php --action=list
php tools/shop-order-maintenance.php --action=cleanup
php tools/shop-order-maintenance.php --action=cleanup --apply
php tools/shop-order-maintenance.php --action=reconcile-toss --order=주문번호 --apply
php tools/shop-order-maintenance.php --action=quarantine-legacy --order=주문번호 --apply
```

`cleanup`은 기본 24시간의 유예 후 완료·확인된 실패·취소 및 만료된 미승인 Toss/KCP 요청만 처리한다. 1회 최대 500건이다. `--grace=초`는 최소 1시간이다. `approving/approved/finalizing/unknown/cancel_pending`과 검토 전 과거 데이터는 자동 정리하지 않는다. 운영 환경에서 명시적으로 예약 실행할 수 있다. 일반 조회 요청에 정리 작업을 붙이지 않는다.

1차 패치의 주문·세션·본문 해시 검증을 통과하는 과거 요청은 새 상태로 전환할 수 있다. 그보다 오래된 요청은 주문번호 또는 PAYREQ_MAP만으로 신뢰하거나 토큰을 재발급하지 않는다. `quarantine-legacy`는 지정 주문의 평문 비밀번호를 제거하고 해시를 별도 보관하되 접근 불가능한 `legacy` 상태로 격리한다. 승인된 미완료 거래를 먼저 PG/주문 자료와 대조해야 한다.

KCP의 불명확한 승인 결과는 원장을 확인한 뒤 다음 명령으로 반영한다. JSON에는 `orderId`, `amount`, `tno`, `res_cd`와 해당 결제수단의 `app_time`, `app_no`, 은행/가상계좌 등의 실제 승인 결과를 넣는다. 자료는 웹 공개 경로 밖에 보관한다. 이 명령은 운영자의 원장 확인을 명시적으로 신뢰하며, 파일 자체를 PG 서명으로 간주하지 않는다.

```sh
php tools/shop-order-maintenance.php --action=reconcile-kcp --order=주문번호 --receipt=/private/verified-kcp.json --verified --apply
php tools/shop-order-maintenance.php --action=complete-reviewed --order=주문번호 --verified --apply
php tools/shop-order-maintenance.php --action=purge-reviewed-legacy --order=주문번호 --receipt=/private/legacy-review.json --verified --apply
```

`complete-reviewed`는 `finalizing` 거래의 주문·PG·장바구니·포인트·쿠폰 대조를 마친 경우에만 사용한다. 저장된 거래번호도 승인 기록과 비교한다. 기존 데이터 삭제 자료는 `orderId`, `resolution`(`unpaid/cancelled/completed`), `reference`(원장 대조 기록)를 포함한다. `completed`를 선택하면 실제 저장된 주문/개인결제의 거래번호가 있어야 한다. 이 도구는 불명확한 거래를 자동으로 미승인으로 판정하지 않는다.

## 검증 방법과 한계

- `php tests/shop_order_access_test.php`: 1차 세션 상태의 안전한 전환 경계와 저장 허용 목록.
- `node tests/shop_order_state_browser_test.js`: 토큰 헤더·폼 전달·콜백 URL 인코딩. DOM 대역이며 실제 PG SDK 실행은 아니다.
- `tests/shop_order_state_test.py`: 실제 PHP/DB/HTTP, 쿠키 없는 Toss/KCP 복귀, 위조 토큰, 주문/금액/키 변조, 다중 탭, 병렬 프로세스 승인, 응답 유실·부분 저장·취소 상태, 정리와 과거 요청, 신규/기존/쇼핑몰 미설치 마이그레이션.
- `tests/shop_order_finalize_test.py`: 별도 테스트 설치본에서 PG 전송 클래스만 대체하고 실제 PC·모바일 주문 확정, 개인결제, 비회원 주문 조회, 완료 정리, 중복 POST를 실행한다. 테스트가 끝나면 원래 전송 클래스를 복원한다.

Python 검증에는 `G5_ORDER_TEST_CONFIG`로 별도 설치본의 JSON(`root`, `php`, `ini`, `port`)을 지정한다. 해당 설치본은 loopback DB 주소와 `issue48_` 접두어의 데이터베이스를 사용해야 하며 `data/.order-state-test`에 `KVE-2026-2140-local-test`를 적어 명시적으로 표시한다. fixture PHP는 운영 DB에서 실행을 거부한다. 테스트 파일은 Git에는 보관하지만 일반 배포 아카이브에서 제외한다.

실제 Toss/KCP 네트워크 승인·취소·재통보, PG 앱/브라우저별 쿠키 정책, 다른 PHP/DB 버전, 모든 사용자 스킨의 호환성은 이 로컬 대역 검증으로 확정하지 않는다. KCP 불명확 거래와 부분 주문 저장은 위 운영자 대조 경로로 처리하며 무조건 자동 복구한다고 안내하지 않는다.

Toss API 근거: [결제 API](https://docs.tosspayments.com/reference), [멱등성과 결제 후처리](https://docs.tosspayments.com/guides/v2/get-started/llms-quick-reference).

## 최소 버전 호환 처리

PHP 최소 버전은 기존 PHP 5.2.17을 유지한다. 공통 주문 라이브러리의 익명 함수와 `__DIR__`, PHP 7 전용 난수·역직렬화 옵션 의존을 제거했다. PC·모바일 주문서의 익명 콜백과 PHP 5.4 이후 JSON 출력 옵션, Toss 클래스의 스칼라·반환·프로퍼티 타입 선언도 구버전에서 읽을 수 있게 변경했다. 운영 도구는 구버전 HTTP 상태 헤더·JSON 출력·예외 처리를 사용한다.

`lib/shop_order_compat.lib.php`에서 다음을 공통 처리한다.

- 난수는 `random_bytes`, 강한 OpenSSL 난수, `/dev/urandom`, `MCRYPT_DEV_URANDOM` 순으로 사용 가능한 안전한 소스를 찾는다. 약한 난수로 대체하지 않으며 안전한 소스가 없으면 결제를 진행하지 않는다.
- 토큰·해시는 길이와 자료형을 확인하고 같은 길이의 문자열은 끝까지 비교한다.
- 기존 Base64/serialize 임시 데이터는 배열·문자열·정수·유한 실수·불리언·null만 읽는 전용 파서로 복원한다. 객체·참조·잘못된 길이·과도한 크기/깊이는 거부하며 PHP 객체 생성이나 `unserialize()`를 호출하지 않는다. 원문 길이와 바이너리 문자열을 유지하므로 기존 주문 필드와 KCP 응답을 읽을 수 있다.

인증 토큰 생성과 주문 상태 처리에는 hash(SHA-256)·JSON 및 안전한 난수원이 필요하다. PG 통신에는 해당 PG가 요구하는 cURL/TLS 환경이 별도로 필요하며, PHP 구문 호환이 구형 TLS 라이브러리로 실제 PG 연결까지 가능함을 보장하지 않는다. 사용자 주문 스킨의 토큰 전달 반영 요건은 그대로 유지한다.

검증 명령:

```sh
php tests/shop_order_compat_test.php
php -d disable_functions=random_bytes,openssl_random_pseudo_bytes,mcrypt_create_iv tests/shop_order_compat_test.php --no-rng
php tests/shop_order_lock_test.php 127.0.0.1:포트 issue48_테스트DB
```

설계 근거: [MySQL의 GET_LOCK 버전별 동작](https://dev.mysql.com/blog-archive/making-get_lock-behavior-more-predictable-cross-version-with-query-rewrite/)과 [mysql_connect의 new_link 옵션](https://www.php.net/manual/en/function.mysql-connect.php)을 기준으로 잠금 연결을 분리했다. PHP의 [random_bytes 지원 범위](https://www.php.net/manual/en/function.random-bytes.php)와 [PHP 7의 역직렬화 필터 도입](https://www.php.net/manual/en/migration70.new-features.php)을 확인하고 구버전용 처리를 추가했다. DB 파일 잠금은 여러 웹서버 간 보호를 보장하지 않으므로 사용하지 않는다.

호환 함수와 구형 mysql 드라이버의 단일 잠금 동작 대역은 PHP 5.2.17·7.4·8.4에서 검사한다. 실제 잠금 검사는 root/빈 비밀번호인 격리 MySQL에서 업무·주문·장바구니 연결 분리, 다른 프로세스의 경합, 종료·연결 단절 시 해제와 유실 감지를 확인한다. MySQL 5.0 서버 자체의 실행 검증과 실제 PG 통신은 이 검사에 포함하지 않는다. 기존 주문 상태·최종 주문 회귀 검증도 별도 설치본에서 실행한다.
