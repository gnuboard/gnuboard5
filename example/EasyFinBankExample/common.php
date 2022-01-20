<?php
  /**
  * 팝빌 계좌조회 API PHP SDK Example
  *
  * PHP SDK 연동환경 설정방법 안내 : https://docs.popbill.com/easyfinbank/tutorial/php
  * 업데이트 일자 : 2021-12-23
  * 연동기술지원 연락처 : 1600-9854
  * 연동기술지원 이메일 : code@linkhubcorp.com
  */

  require_once '../Popbill/PopbillEasyFinBank.php';

  // 링크 아이디
  $LinkID = 'TESTER';

  // 발급받은 비밀키. 유출에 주의하시기 바랍니다.
  $SecretKey = 'SwWxqU+0TErBXy/9TVjIPEnI0VTUMMSQZtJf3Ed8q3I=';

  // 통신방식 기본은 CURL , curl 사용에 문제가 있을경우 STREAM 사용가능.
  // STREAM 사용시에는 php.ini 파일의 allow_url_fopen = on 으로 설정해야함.
  define('LINKHUB_COMM_MODE','CURL');

  $EasyFinBankService = new EasyFinBankService($LinkID, $SecretKey);

  // 연동환경 설정값, 개발용(true), 상업용(false)
  $EasyFinBankService->IsTest(true);

  // 인증토큰에 대한 IP제한기능 사용여부, 권장(true)
  $EasyFinBankService->IPRestrictOnOff(true);

  // 팝빌 API 서비스 고정 IP 사용여부, 기본값(false)
  $EasyFinBankService->UseStaticIP(false);

  // 로컬서버 시간 사용 여부 true(기본값) - 사용, false(미사용)
  $EasyFinBankService->UseLocalTimeYN(true);
?>
