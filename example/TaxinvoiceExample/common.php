<?php
  /**
  * 팝빌 전자세금계산서 API PHP SDK Example
  *
  * PHP SDK 연동환경 설정방법 안내 : https://docs.popbill.com/taxinvoice/tutorial/php
  * 업데이트 일자 : 2021-07-15
  * 연동기술지원 연락처 : 1600-9854
  * 연동기술지원 이메일 : code@linkhubcorp.com
  *
  * <테스트 연동개발 준비사항>
  * 1) 23, 27번 라인에 선언된 링크아이디(LinkID)와 비밀키(SecretKey)를
  *    링크허브 가입시 메일로 발급받은 인증정보를 참조하여 변경합니다.
  * 2) 팝빌 개발용 사이트(test.popbill.com)에 연동회원으로 가입합니다.
  * 3) 전자세금계산서 발행을 위해 공인인증서를 등록합니다.
  *    - 팝빌사이트 로그인 > [전자세금계산서] > [환경설정] > [공인인증서 관리]
  *    - 공인인증서 등록 팝업 URL (GetTaxCertURL API)을 이용하여 등록
  *
  */

  require_once '../Popbill/PopbillTaxinvoice.php';

  // 링크아이디
  $LinkID = 'TESTER';

  // 비밀키
  $SecretKey = 'SwWxqU+0TErBXy/9TVjIPEnI0VTUMMSQZtJf3Ed8q3I=';

  // 통신방식 기본은 CURL , curl 사용에 문제가 있을경우 STREAM 사용가능.
  // STREAM 사용시에는 php.ini의 allow_url_fopen = on 으로 설정해야함.
  define('LINKHUB_COMM_MODE','CURL');

  $TaxinvoiceService = new TaxinvoiceService($LinkID, $SecretKey);

  // 연동환경 설정값, 개발용(true), 상업용(false)
  $TaxinvoiceService->IsTest(true);

  // 인증토큰에 대한 IP제한기능 사용여부, 권장(true)
  $TaxinvoiceService->IPRestrictOnOff(true);

  // 팝빌 API 서비스 고정 IP 사용여부, 기본값(false)
  $TaxinvoiceService->UseStaticIP(false);

  // 로컬서버 시간 사용 여부 true(기본값) - 사용, false(미사용)
  $TaxinvoiceService->UseLocalTimeYN(true);
?>
