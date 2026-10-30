# KCP REST API 결제

기존 KCP 결제창과 주문/개인결제 처리를 유지하면서 서버 통신을 HTTPS JSON API로 선택할 수 있다. 설정하지 않으면 기존 pp_cli 및 모바일 SOAP 방식으로 동작한다. 쇼핑몰 설정에 `de_kcp_payment_api` 컬럼을 추가하며 기본값은 `legacy`이다. 기존 설치는 관리자 DB 업그레이드에서 마이그레이션을 적용한다.

## 적용 범위

- PC·모바일 일반 주문 및 개인결제 승인: 신용카드, 계좌이체, 가상계좌, 휴대폰, KCP 제휴 간편결제.
- 모바일 거래등록: SOAP 대신 `/trade/register.do` 호출.
- 관리자 전체취소, 고객 취소, 주문 저장 실패 시 자동취소, 부분취소, 에스크로 배송등록: 서명된 취소 API 호출. 기존 부분취소 코드 RN07/STPA는 STPC로 변환한다.
- 가상계좌 입금 및 에스크로 통보는 기존 `settle_kcp_common.php` 처리와 검증을 유지한다.
- 현금영수증 개별 발급: `/gw/hub/v1/payment`로 주문·개인결제 원장 금액을 전송한다. 저장 실패 시 `/gw/mod/v1/cancel`로 발급을 취소한다. 결제와 함께 발급되는 현금영수증 응답도 기존 주문 처리로 전달된다.

[현금영수증 발급](https://developer.kcp.co.kr/reference/cash-issue)과 [취소](https://developer.kcp.co.kr/reference/cash-cancel)는 선택한 연동 방식을 따른다. REST 오류 시 pp_cli/SOAP으로 재시도하지 않는다. 기존 모듈 선택 시 기존 연동을 유지한다.

[본인확인](https://developer.kcp.co.kr/guide/cert)은 별도 서비스이다. `plugin/kcpcert` 및 `plugin/kcpcert_v2`는 기본환경설정의 `cf_cert_*`와 본인확인 인증키를 사용하며, 쇼핑몰의 `de_kcp_payment_api` 선택과 독립적으로 동작한다. 결제창 인증 UI와 입금 통보 수신은 서버 API 전송 방식 선택과 별개로 기존 흐름을 유지한다.

## 설정

PHP의 cURL(TLS 1.2 이상), OpenSSL(SHA256/RSA), JSON, iconv 확장이 필요하다. KCP에서 해당 사이트코드의 API 이용 및 결제수단을 활성화하고 서비스 인증서와 PKCS8 개인키를 발급받는다. 테스트와 운영의 사이트코드·인증서·개인키가 일치해야 한다. 첨부 샘플의 인증서는 제품 소스에 포함하지 않는다.

공개 테스트 환경은 별도 발급 없이 [KCP PG-API 안내](https://developer.kcp.co.kr/guide/pg-api)의 [테스트 인증서](https://developer.kcp.co.kr/download/cert/splCert.pem)와 [테스트 개인키](https://developer.kcp.co.kr/download/cert/splPrikeyPKCS8.pem)를 사용할 수 있다. 공식 전환 샘플의 테스트 개인키 비밀번호는 `changeit`이다. 제공된 영카트 ZIP에는 인증서만 포함되어 있으므로 개인키는 공식 경로에서 추가로 받는다. 인증서 갱신 시 키 일치 여부와 유효기간을 확인한다. 운영용은 파트너관리자 → 기술관리센터 → 인증센터 → KCP-PG API에서 발급한다.

인증서와 개인키를 웹 루트 및 저장소 **밖**에 두고 PHP 실행 사용자만 읽도록 설정한다. `config.php` 등 서버 설정에서 다음 상수를 선언한다. 개인키 비밀번호를 저장소에 커밋하지 않는다.

```php
define('G5_KCP_REST_CERT_PATH', '/home/shop-secrets/kcp/splCert.pem');
define('G5_KCP_REST_PRIVATE_KEY_PATH', '/home/shop-secrets/kcp/splPrikeyPKCS8.pem');
define('G5_KCP_REST_PRIVATE_KEY_PASSWORD', getenv('KCP_PRIVATE_KEY_PASSWORD'));
// 필요한 경우에만 발급받은 5자리 전체 코드 지정. 기본값은 기존 관리자 SITE CODE를 사용한다.
// define('G5_KCP_REST_SITE_CODE', 'SRXXX');
```

테스트/운영 구분은 기존 쇼핑몰 설정의 결제 테스트(`de_card_test`)를 따른다. 테스트 기본 코드는 일반 T0000, 에스크로 T0007이다. 별도 발급받은 테스트 코드가 있으면 `G5_KCP_REST_SITE_CODE`를 설정한다. REST에서는 PAYCO 선택 시 기존 S6729 코드로 자동 변경하지 않으므로 해당 사이트코드의 간편결제 계약/테스트 가능 여부를 확인한다.

쇼핑몰설정 → KCP 결제 연동 방식에서 ‘KCP REST API’을 선택하면 활성화된다. ‘KCP 결제 모듈 (기본)’을 선택하면 기존 방식으로 돌아간다. 미설정 상점은 기존 방식을 사용하며, 이전 실험용 `G5_KCP_PAYMENT_API` 상수는 더 이상 전환에 사용하지 않는다. 전환 전 진행 중 결제를 종료하고 미완료 승인/취소를 KCP 관리자에서 대조한다. 과거 거래를 취소할 때도 원거래와 동일한 상점 및 테스트/운영 환경을 사용해야 한다. 사이트코드 자체를 바꾸는 상점 이관은 지원하지 않는다.

## 주문 안전성 및 오류 처리

PC REST 결제는 인증창 호출 전에 주문 임시저장과 토큰 발급을 수행한다. PC·모바일 모두 기존 주문 상태 잠금과 승인 응답 저장을 사용한다. 승인 금액은 주문 최종 계산값 또는 개인결제 DB 금액이며, 세션 주문번호와 저장한 결제수단을 검증한다. API에는 `ordr_mony`, `ordr_no`, `pay_type`을 보내고 반환된 거래번호·금액·수단도 대조한다. 카드 포인트/쿠폰 결제는 `card_mny`가 아닌 전체 `amount`로 확인한다.

취소에는 사이트코드·거래번호·취소유형의 RSA SHA256 서명을 사용한다. 승인 전에 개인키와 인증서의 일치도 확인하여 자동취소에 필요한 설정 누락을 차단한다. 부분취소는 요청 금액·잔액·복합과세 합계 및 응답 금액을 검증한다.

TLS 인증서/호스트 검증을 켜고 고정된 KCP 주소만 사용한다. HTTP 리다이렉트, 잘못된 JSON, 예상과 다른 성공 응답 및 타임아웃은 성공으로 처리하지 않는다. 이런 경우 R999를 반환하며 자동 재시도나 pp_cli 대체 호출을 하지 않는다. 승인 요청 후 결과가 불명확하면 기존 주문 상태가 `approving`에 남아 같은 주문의 재승인을 막는다. KCP 관리자에서 주문번호/거래번호로 대조하고 기존 미완료 주문 복구 절차에 따라 처리한다. 취소 결과가 불명확한 경우에도 재취소 전에 KCP 내역을 대조한다.

API의 UTF-8 응답을 기존 KCP 호출부가 기대하는 CP949로 변환하므로 주문 저장부의 기존 한글 변환은 그대로 둔다. 인증서, 개인키, 암호화 승인 전문은 출력하거나 로그에 기록하지 않는다.

## 검증

외부 결제 없이 생성한 임시 인증서와 모의 응답으로 검증한다.

```sh
php tests/kcp_rest_test.php
php tests/kcp_rest_test.php mobile
php -n tests/kcp_rest_transport_test.php
php -n -d extension=iconv -d extension=ctype tests/kcp_rest_hub_test.php
php -n -d extension=iconv -d extension=ctype tests/kcp_rest_hub_test.php mobile
php -n -d extension=iconv -d extension=ctype tests/kcp_rest_hub_test.php desktop personal
php -n -d extension=iconv -d extension=ctype tests/kcp_rest_hub_test.php mobile personal
```

PHP 8.4.27에서 REST 단위 검사 77개(PC·모바일 각각), 통신 검사 16개, 실제 승인 처리 파일을 사용하는 4개 주문/개인결제 통합 시나리오를 검증했다. 통합 시나리오는 승인 복구 후 자동취소와 가상계좌 V000 발급취소도 포함한다. 기존 주문 필드 18개·호환성 35개·접근 경계 10개 및 간편결제 회귀 검사도 통과했다.

2026-10-29 PHP 8.4.27에서 `kcp_cash_gateway()`를 통해 KCP 테스트 서버에 현금영수증 1,100원을 발급하고 전체 취소했다. 거래번호 `26552234057023`, 발급 승인번호 `590951805`, 취소 승인번호 `590951806`; 두 HTTPS 요청 모두 HTTP 200, `res_cd=0000`이었다. PHP의 `exec`, `shell_exec`, `system`, `passthru`를 비활성화하고 SITE KEY 없이 실행했다. 이 검증은 API 발급·취소 검증이며 브라우저/DB 저장 전체 흐름 검증과 구분한다.

실제 KCP 인증창/승인/취소 검증은 발급받은 테스트 인증서와 개인키를 설치한 별도 테스트 상점에서 수행해야 한다. PC·모바일 각각 일반 주문/개인결제, 카드·계좌이체·가상계좌·휴대폰·계약된 간편결제, 전체/부분취소, 복합과세, 자동취소, 에스크로 배송등록 및 가상계좌 통보를 확인한 뒤 운영에 적용한다. 오프라인 테스트 통과만으로 KCP 실거래 검증 완료로 보지 않는다.

## 참고 규격

- 제공 자료: `youngcart_API_Sample.zip` 내 표준결제 연동가이드 v1.0.2 및 PHP 샘플.
- [KCP REST 전환 안내](https://developer.kcp.co.kr/guide/rest-api-guide)
- [승인](https://developer.kcp.co.kr/reference/pay-approve), [거래등록](https://developer.kcp.co.kr/reference/regist)
- [취소](https://developer.kcp.co.kr/reference/cancel), [서명](https://developer.kcp.co.kr/guide/signdata), [에스크로 상태변경](https://developer.kcp.co.kr/reference/escrow)

테스트 계측: 테스트 모드에서만 g5_kcp_rest_test_trace 함수가 정의되어 있으면 REST 전송 후 목적지, HTTP 상태, 주문/거래번호, 금액, 응답코드를 전달합니다. 인증서·개인키·서명·암호화 전문은 제외합니다. 운영 요청에는 호출되지 않습니다. 계측 함수와 로그는 테스트 서버의 웹 루트 밖에서 관리하십시오.
