<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <link rel="stylesheet" type="text/css" href="../Example.css" media="screen"/>

    <title>팝빌 SDK PHP 5.X Example.</title>
</head>
<body>
<div id="content">
    <p class="heading1">팝빌 문자메시지 SDK PHP 5.X Example.</p>
    <br/>
    <fieldset class="fieldset1">
        <legend>발신번호 사전등록</legend>
        <ul>
            <li><a href="GetSenderNumberMgtURL.php">GetSenderNumberMgtURL</a> (문자 발신번호 관리 팝업 URL)</li>
            <li><a href="GetSenderNumberList.php">GetSenderNumberList</a> (문자 발신번호 목록 확인)</li>
        </ul>
    </fieldset>
    <fieldset class="fieldset1">
        <legend>문자 전송</legend>
        <fieldset class="fieldset2">
            <legend>단문 문자 전송</legend>
            <ul>
                <li><a href="SendSMS.php">SendSMS</a> (단문 문자메시지 1건 전송)</li>
                <li><a href="SendSMS_Multi.php">SendSMS</a> (단문 문자메시지 다량(최대1000건) 전송)</li>
            </ul>
        </fieldset>
        <fieldset class="fieldset2">
            <legend>장문 문자 전송</legend>
            <ul>
                <li><a href="SendLMS.php">SendLMS</a> (장문 문자메시지 1건 전송)</li>
                <li><a href="SendLMS_Multi.php">SendLMS</a> (장문 문자메시지 다량(최대1000건) 전송)</li>
            </ul>
        </fieldset>
        <fieldset class="fieldset2">
            <legend>단/장문 문자 자동인식 전송</legend>
            <ul>
                <li><a href="SendXMS.php">SendXMS</a> (단/장문 자동인식 문자메시지 1건 전송)</li>
                <li><a href="SendXMS_Multi.php">SendXMS</a> (단/장문 자동인식 문자메시지 다량(최대1000건) 전송)</li>
            </ul>
        </fieldset>
        <fieldset class="fieldset2">
            <legend>포토 문자 전송</legend>
            <ul>
                <li><a href="SendMMS.php">SendMMS</a> (포토 문자메시지 1건 전송)</li>
                <li><a href="SendMMS_Multi.php">SendMMS</a> (포토 문자메시지 (최대1000건) 전송)</li>
            </ul>
        </fieldset>
        <fieldset class="fieldset2">
            <legend>예약전송 취소</legend>
            <ul>
                <li><a href="CancelReserve.php">CancelReserve</a> (예약문자 메시지 예약취소)</li>
                <li><a href="CancelReserveRN.php">CancelReserveRN</a> (예약문자 메시지 예약취소 - 요청번호 할당)</li>
            </ul>
        </fieldset>

    </fieldset>
    <fieldset class="fieldset1">
        <legend>정보확인</legend>
        <ul>
            <li><a href="GetMessages.php">GetMessages</a> (문자메시지 전송결과 확인)</li>
            <li><a href="GetMessagesRN.php">GetMessagesRN</a> (문자메시지 전송결과 확인 - 요청번호 할당)</li>
            <li><a href="GetStates.php">GetStates</a> (문자메시지 전송결과 요약정보 확인)</li>
            <li><a href="Search.php">Search</a> (문자전송 목록 조회)</li>
            <li><a href="GetSentListURL.php">GetSentListURL</a> (문자 전송내역 팝업 URL)</li>
            <li><a href="GetAutoDenyList.php">GetAutoDenyList</a> (080 수신거부 목록 확인)</li>
        </ul>
    </fieldset>
    <fieldset class="fieldset1">
        <legend>포인트 관리</legend>
        <ul>
            <li><a href="GetBalance.php">GetBalance</a> (연동회원 잔여포인트 확인)</li>
            <li><a href="GetChargeURL.php">GetChargeURL</a> (연동회원 포인트충전 URL)</li>
            <li><a href="GetPaymentURL.php">GetPaymentURL</a> (연동회원 포인트 결제내역 URL)</li>
            <li><a href="GetUseHistoryURL.php">GetUseHistoryURL</a> (연동회원 사용내역 URL)</li>
            <li><a href="GetPartnerBalance.php">GetPartnerBalance</a> (파트너 잔여포인트 확인)</li>
            <li><a href="GetPartnerURL.php">GetPartnerURL</a> (파트너 포인트충전 URL)</li>
            <li><a href="GetUnitCost.php">GetUnitCost</a> (전송 단가 확인)</li>
            <li><a href="GetChargeInfo.php">GetChargeInfo</a> (과금정보 확인)</li>
        </ul>
    </fieldset>
    <fieldset class="fieldset1">
        <legend>회원정보</legend>
        <ul>
            <li><a href="CheckIsMember.php">CheckIsMember</a> (연동회원 가입여부 확인)</li>
            <li><a href="CheckID.php">CheckID</a> (아이디 중복 확인)</li>
            <li><a href="JoinMember.php">JoinMember</a> (연동회원 신규가입)</li>
            <li><a href="GetAccessURL.php">GetAccessURL</a> (팝빌 로그인 URL)</li>
            <li><a href="RegistContact.php">RegistContact</a> (담당자 등록)</li>
            <li><a href="GetContactInfo.php">GetContactInfo</a> (담당자 정보 확인)</li>
            <li><a href="ListContact.php">ListContact</a> (담당자 목록 확인)</li>
            <li><a href="UpdateContact.php">UpdateContact</a> (담당자 정보 수정)</li>
            <li><a href="GetCorpInfo.php">GetCorpInfo</a> (회사정보 확인)</li>
            <li><a href="UpdateCorpInfo.php">UpdateCorpInfo</a> (회사정보 수정)</li>
        </ul>
    </fieldset>
</div>
</body>
</html>
