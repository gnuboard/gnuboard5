<?php
  /**
  * 팝빌 팩스 API PHP SDK Example
  *
  * PHP SDK 연동환경 설정방법 안내 : https://docs.popbill.com/fax/tutorial/php
  * 업데이트 일자 : 2021-12-23
  * 연동기술지원 연락처 : 1600-9854
  * 연동기술지원 이메일 : code@linkhubcorp.com
  *
  * <테스트 연동개발 준비사항>
  * 1) 19, 22번 라인에 선언된 링크아이디(LinkID)와 비밀키(SecretKey)를
  *    링크허브 가입시 메일로 발급받은 인증정보를 참조하여 변경합니다.
  * 2) 팝빌 개발용 사이트(test.popbill.com)에 연동회원으로 가입합니다.
  */

  require_once '../Popbill/PopbillFax.php';

  // 링크아이디
  $LinkID = 'TESTER';

  // 비밀키
  $SecretKey = 'SwWxqU+0TErBXy/9TVjIPEnI0VTUMMSQZtJf3Ed8q3I=';

  // 통신방식 기본은 CURL , curl 사용에 문제가 있을경우 STREAM 사용가능.
  // STREAM 사용시에는 php.ini의 allow_url_fopen = on 으로 설정해야함.
  define('LINKHUB_COMM_MODE','CURL');

  $FaxService = new FaxService($LinkID, $SecretKey);

  // 연동환경 설정값, 개발용(true), 상업용(false)
  $FaxService->IsTest(true);

  // 인증토큰에 대한 IP제한기능 사용여부, 권장(true)
  $FaxService->IPRestrictOnOff(true);

  // 팝빌 API 서비스 고정 IP 사용여부, 기본값(false)
  $FaxService->UseStaticIP(false);

  // 로컬서버 시간 사용 여부 true(기본값) - 사용, false(미사용)
  $FaxService->UseLocalTimeYN(true);
?>
