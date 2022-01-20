<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <link rel="stylesheet" type="text/css" href="/Example.css" media="screen"/>

    <title>팝빌 SDK PHP 5.X Example.</title>
</head>
<body>
<div id="content">
    <p class="heading1">팝빌 팩스 SDK PHP 5.X Example.</p>
    <br/>
    <fieldset class="fieldset1">
        <legend>발신번호 사전등록</legend>
        <ul>
            <li><a href="GetSenderNumberMgtURL.php">GetSenderNumberMgtURL</a> (팩스 발신번호 관리 팝업 URL)</li>
            <li><a href="GetSenderNumberList.php">GetSenderNumberList</a> (팩스 발신번호 목록 확인)</li>
        </ul>
    </fieldset>
    <fieldset class="fieldset1">
        <legend>팩스 전송</legend>
        <ul>
            <li><a href="SendFAX.php">SendFAX</a> (팩스 전송. 파일(최대 20개) 1건 전송)</li>
            <li><a href="SendFAX_Multi.php">SendFAX_Multi</a> (팩스 전송. 파일(최대 20개) 동보 전송(수신번호 최대 1000개))</li>
            <li><a href="SendFAXBinary.php">SendFAXBinary</a> (팩스 전송. 바이너리 데이터(최대 20개) 1건 전송)</li>
            <li><a href="SendFAXBinary_Multi.php">SendFAXBinary_Multi</a> (팩스 전송. 바이너리 데이터(최대 20개) 동보 전송(수신번호 최대 1000개))</li>
            <li><a href="ResendFAX.php">ResendFAX</a> (팩스 재전송)</li>
            <li><a href="ResendFAXRN.php">ResendFAXRN</a> (팩스 재전송 - 요청번호할당)</li>
            <li><a href="ResendFAX_Multi.php">ResendFAX_Multi</a> (팩스 동보 재전송)</li>
            <li><a href="ResendFAXRN_Multi.php">ResendFAXRN_Multi</a> (팩스 동보 재전송 - 요청번호할당)</li>
            <li><a href="CancelReserve.php">CancelReserve</a> (예약전송 팩스 취소)</li>
            <li><a href="CancelReserveRN.php">CancelReserveRN</a> (예약전송 팩스 취소 - 요청번호할당)</li>
        </ul>
    </fieldset>
    <fieldset class="fieldset1">
        <legend>전송내역조회</legend>
        <ul>
            <li><a href="GetFaxDetail.php">GetFaxDetail</a> (팩스전송 전송결과 확인)</li>
            <li><a href="GetFaxDetailRN.php">GetFaxDetailRN</a> (팩스전송 전송결과 확인 - 요청번호할당)</li>
            <li><a href="Search.php">Search</a> (팩스전송 목록조회)</li>
            <li><a href="GetSentListURL.php">GetSentListURL</a> (팩스 전송내역 팝업 URL)</li>
            <li><a href="GetPreviewURL.php">GetPreviewURL</a> (팩스 미리보기 팝업 URL)</li>
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
