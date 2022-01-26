<?php
    require_once G5_LIB_PATH.'/popbill/PopbillMessaging.php';

    // 링크아이디
    $linkid = $config['cf_popbill_id'];
    //echo '링크아이디 : '.$linkid.'<br>';

    // 비밀키
    $secretkey = $config['cf_popbill_pw'];
    //echo '비밀키 : '.$secretkey.'<br>';

    // 팝빌 회원 사업자번호, "-"제외 10자리
    $corpnum = $config['cf_popbill_co_no'];
    //echo '사업자번호 : '.$testCorpNum.'<br>';

    //통신방식 기본은 CURL , curl 사용에 문제가 있을경우 STREAM 사용가능.
    //STREAM 사용시에는 allow_url_fopen = on 으로 설정해야함.
    define('LINKHUB_COMM_MODE','CURL');

    $MessagingService = new MessagingService($linkid, $secretkey);

    // 연동환경 설정값, 개발용(true), 상업용(false)
    $MessagingService->IsTest(true);

    // 인증토큰에 대한 IP제한기능 사용여부, 권장(true)
    $MessagingService->IPRestrictOnOff(true);

    // 팝빌 API 서비스 고정 IP 사용여부, 기본값(false)
    $MessagingService->UseStaticIP(false);

    // 로컬시스템 시간 사용 여부 true(기본값) - 사용, false(미사용)
    $MessagingService->UseLocalTimeYN(true);
?>