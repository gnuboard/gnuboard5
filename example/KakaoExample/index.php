<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <link rel="stylesheet" type="text/css" href="/Example.css" media="screen"/>

    <title>팝빌 SDK PHP 5.X Example.</title>
</head>
<body>
<div id="content">
    <p class="heading1">팝빌 카카오톡 SDK PHP 5.X Example.</p>
    <br/>
    <fieldset class="fieldset1">
        <legend>카카오톡 채널 관리</legend>
        <ul>
            <li><a href="GetPlusFriendMgtURL.php">GetPlusFriendMgtURL</a> (카카오톡 채널 관리 팝업 URL)</li>
            <li><a href="ListPlusFriendID.php">ListPlusFriendID</a> (카카오톡 채널 목록 확인)</li>
        </ul>
    </fieldset>
    <fieldset class="fieldset1">
        <legend>발신번호 관리</legend>
        <ul>
            <li><a href="GetSenderNumberMgtURL.php">GetSenderNumberMgtURL</a> (발신번호 관리 팝업 URL)</li>
            <li><a href="GetSenderNumberList.php">GetSenderNumberList</a> (발신번호 목록 확인)</li>
        </ul>
    </fieldset>
    <fieldset class="fieldset1">
        <legend>알림톡 템플릿 관리</legend>
        <ul>
            <li><a href="GetATSTemplateMgtURL.php">GetATSTemplateMgtURL</a> (알림톡 템플릿관리 팝업 URL)</li>
            <li><a href="GetATSTemplate.php">GetATSTemplate</a> (알림톡 템플릿 정보 확인)</li>
            <li><a href="ListATSTemplate.php">ListATSTemplate</a> (알림톡 템플릿 목록 확인)</li>
        </ul>
    </fieldset>
    <fieldset class="fieldset1">
        <legend>알림톡 / 친구톡 전송</legend>
        <fieldset class="fieldset2">
            <legend>알림톡 전송</legend>
            <ul>
                <li><a href="SendATS_one.php">SendATS</a> (알림톡 단건 전송)</li>
                <li><a href="SendATS_same.php">SendATS</a> (알림톡 동일내용 대량 전송)</li>
                <li><a href="SendATS_multi.php">SendATS</a> (알림톡 개별내용 대량 전송)</li>
            </ul>
        </fieldset>
        <fieldset class="fieldset2">
            <legend>친구톡 텍스트 전송</legend>
            <ul>
                <li><a href="SendFTS_one.php">SendFTS</a> (친구톡 텍스트 단건 전송)</li>
                <li><a href="SendFTS_same.php">SendFTS</a> (친구톡 텍스트 동일내용 대량전송)</li>
                <li><a href="SendFTS_multi.php">SendFTS</a> (친구톡 텍스트 개별내용 대량전송)</li>
            </ul>
        </fieldset>
        <fieldset class="fieldset2">
            <legend>친구톡 이미지 전송</legend>
            <ul>
                <li><a href="SendFMS_one.php">SendFMS</a> (친구톡 이미지 단건 전송)</li>
                <li><a href="SendFMS_same.php">SendFMS</a> (친구톡 이미지 동일내용 대량전송)</li>
                <li><a href="SendFMS_multi.php">SendFMS</a> (친구톡 이미지 개별내용 대량전송)</li>
            </ul>
        </fieldset>
        <fieldset class="fieldset2">
            <legend>예약전송 취소</legend>
            <ul>
                <li><a href="CancelReserve.php">CancelReserve</a> (예약전송 취소)</li>
                <li><a href="CancelReserveRN.php">CancelReserveRN</a> (예약전송 취소 - 요청번호 할당)</li>
            </ul>
        </fieldset>
    </fieldset>
    <fieldset class="fieldset1">
        <legend>정보확인</legend>
        <ul>
            <li><a href="GetMessages.php">GetMessages</a> (알림톡/친구톡 전송내역 확인)</li>
            <li><a href="GetMessagesRN.php">GetMessagesRN</a> (알림톡/친구톡 전송내역 확인 - 요청번호 할당)</li>
            <li><a href="Search.php">Search</a> (전송내역 목록 조회)</li>
            <li><a href="GetSentListURL.php">GetSentListURL</a> (카카오톡 전송내역 팝업 URL)</li>
        </ul>
    </fieldset>
    <fieldset class="fieldset1">
        <legend>포인트관리</legend>
        <ul>
            <li><a href="GetBalance.php">GetBalance</a> (연동회원 잔여포인트 확인)</li>
            <li><a href="GetChargeURL.php">GetChargeURL</a> (연동회원 포인트 충전 팝업 URL)</li>
            <li><a href="GetPaymentURL.php">GetPaymentURL</a> (연동회원 포인트 결제내역 URL)</li>
            <li><a href="GetUseHistoryURL.php">GetUseHistoryURL</a> (연동회원 사용내역 URL)</li>
            <li><a href="GetPartnerBalance.php">GetPartnerBalance</a> (파트너 잔여포인트 확인)</li>
            <li><a href="GetPartnerURL.php">GetPartnerURL</a> (파트너 포인트충전 URL)</li>
            <li><a href="GetUnitCost.php">GetUnitCost</a> (전송단가 확인)</li>
            <li><a href="GetChargeInfo.php">GetChargeInfo</a> (과금정보 확인)</li>
        </ul>
    </fieldset>

    <fieldset class="fieldset1">
        <legend>회원관리</legend>
        <ul>
            <li><a href="CheckIsMember.php">CheckIsMember</a> (연동회원 가입여부 확인)</li>
            <li><a href="CheckID.php">CheckID</a> (연동회원 아이디 중복 확인)</li>
            <li><a href="JoinMember.php">JoinMember</a> (연동회원사 신규가입)</li>
            <li><a href="GetAccessURL.php">GetAccessURL</a> (팝빌 로그인 URL)</li>
            <li><a href="RegistContact.php">RegistContact</a> (담당자 추가)</li>
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
