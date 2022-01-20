<?php
  /**
  * 팝빌 홈택스(현금영수증) 연동 API PHP SDK Example
  *
  * PHP SDK 연동환경 설정방법 안내 : https://docs.popbill.com/htcashbill/tutorial/php
  * 업데이트 일자 : 2021-07-19
  * 연동기술지원 연락처 : 1600-9854
  * 연동기술지원 이메일 : code@linkhubcorp.com
  *
  * <테스트 연동개발 준비사항>
  * 1) 23, 26번 라인에 선언된 링크아이디(LinkID)와 비밀키(SecretKey)를
  *    링크허브 가입시 메일로 발급받은 인증정보를 참조하여 변경합니다.
  * 2) 팝빌 개발용 사이트(test.popbill.com)에 연동회원으로 가입합니다.
  * 3) 홈택스 인증 처리를 합니다. (부서사용자등록 / 공인인증서 등록)
  *    - 팝빌로그인 > [홈택스연동] > [환경설정] > [인증 관리] 메뉴
  *    - 홈택스연동 인증 관리 팝업 URL(GetCertificatePopUpURL API) 반환된 URL을 이용하여
  *      홈택스 인증 처리를 합니다.
  */

  require_once '../Popbill/PopbillHTCashbill.php';

  //링크 아이디
  $LinkID = 'TESTER';

  //발급받은 비밀키. 유출에 주의하시기 바랍니다.
  $SecretKey = 'SwWxqU+0TErBXy/9TVjIPEnI0VTUMMSQZtJf3Ed8q3I=';

  //통신방식 기본은 CURL , curl 사용에 문제가 있을경우 STREAM 사용가능.
  //STREAM 사용시에는 allow_url_fopen = on 으로 설정해야함.
  define('LINKHUB_COMM_MODE','CURL');

  $HTCashbillService = new HTCashbillService($LinkID, $SecretKey);

  // 연동환경 설정값, 개발용(true), 상업용(false)
  $HTCashbillService->IsTest(true);

  // 인증토큰에 대한 IP제한기능 사용여부, 권장(true)
  $HTCashbillService->IPRestrictOnOff(true);

  // 팝빌 API 서비스 고정 IP 사용여부, 기본값(false)
  $HTCashbillService->UseStaticIP(false);

  // 로컬서버 시간 사용 여부 true(기본값) - 사용, false(미사용)
  $HTCashbillService->UseLocalTimeYN(true);
?>
