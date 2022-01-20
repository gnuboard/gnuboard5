<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <link rel="stylesheet" type="text/css" href="/Example.css" media="screen"/>

    <title>팝빌 SDK PHP 5.X Example.</title>
</head>
<body>
<div id="content">
    <p class="heading1">팝빌 홈택스연동(전자세금계산서) API SDK PHP 5.x Example.</p>
    <br/>
    <fieldset class="fieldset1">
        <legend>홈택스 전자세금계산서 매입/매출 내역 수집</legend>
        <ul>
            <li><a href="RequestJob.php">RequestJob</a> (수집 요청) </li>
            <li><a href="GetJobState.php">GetJobState</a> (수집 상태 확인) </li>
            <li><a href="ListActiveJob.php">ListActiveJob</a> (수집 상태 목록 확인) </li>
        </ul>
    </fieldset>
    <fieldset class="fieldset1">
        <legend>홈택스 전자세금계산서 매입/매출 내역 수집 결과 조회</legend>
        <ul>
            <li><a href="Search.php">Search</a> (수집 결과 조회) </li>
            <li><a href="Summary.php">Summary</a> (수집 결과 요약정보 조회) </li>
            <li><a href="GetTaxinvoice.php">GetTaxinvoice</a> (상세정보 확인 (JSON) </li>
            <li><a href="GetXML.php">GetXML</a> (상세정보 확인 (XML) </li>
            <li><a href="GetPopUpURL.php">GetPopUpURL</a> (홈택스 전자세금계산서 보기 팝업 URL) </li>
            <li><a href="GetPrintURL.php">GetPrintURL</a> (홈택스 전자세금계산서 인쇄 팝업 URL) </li>
        </ul>
    </fieldset>
    <fieldset class="fieldset1">
        <legend>홈택스연동 인증 관리</legend>
        <ul>
            <li><a href="GetCertificatePopUpURL.php">GetCertificatePopUpURL</a> (홈택스연동 인증 관리 팝업 URL) </li>
            <li><a href="GetCertificateExpireDate.php">GetCertificateExpireDate</a> (홈택스연동 공인인증서 만료일자 확인) </li>
            <li><a href="CheckCertValidation.php">CheckCertValidation</a> (홈택스 공인인증서 로그인 테스트) </li>
            <li><a href="RegistDeptUser.php">RegistDeptUser</a> (부서사용자 계정등록) </li>
            <li><a href="CheckDeptUser.php">CheckDeptUser</a> (부서사용자 등록정보 확인) </li>
            <li><a href="CheckLoginDeptUser.php">CheckLoginDeptUser</a> (부서사용자 로그인 테스트) </li>
            <li><a href="DeleteDeptUser.php">DeleteDeptUser</a> (부서사용자 등록정보 삭제) </li>
        </ul>
    </fieldset>
    <fieldset class="fieldset1">
        <legend>포인트 관리 / 정액제 신청</legend>
        <ul>
            <li><a href="GetBalance.php">GetBalance</a> (연동회원 잔여포인트 확인) </li>
            <li><a href="GetChargeURL.php">GetChargeURL</a> (연동회원 포인트충전 URL) </li>
            <li><a href="GetPaymentURL.php">GetPaymentURL</a> (연동회원 포인트 결제내역 URL)</li>
            <li><a href="GetUseHistoryURL.php">GetUseHistoryURL</a> (연동회원 사용내역 URL)</li>
            <li><a href="GetPartnerBalance.php">GetPartnerBalance</a> (파트너 잔여포인트 확인) </li>
            <li><a href="GetPartnerURL.php">GetPartnerURL</a> (파트너 포인트충전 URL) </li>
            <li><a href="GetChargeInfo.php">GetChargeInfo</a> (과금정보 확인) </li>
            <li><a href="GetFlatRatePopUpURL.php">GetFlatRatePopUpURL</a> (정액제 서비스 신청 URL) </li>
            <li><a href="GetFlatRateState.php">GetFlatRateState</a> (정액제 서비스 상태 확인) </li>

        </ul>
    </fieldset>
    <fieldset class="fieldset1">
        <legend>회원정보</legend>
        <ul>
            <li><a href="CheckIsMember.php">CheckIsMember</a> (연동회원 가입여부 확인) </li>
            <li><a href="CheckID.php">CheckID</a> (아이디 중복 확인) </li>
            <li><a href="JoinMember.php">JoinMember</a> (연동회원 신규가입) </li>
            <li><a href="GetAccessURL.php">GetAccessURL</a> (팝빌 로그인 URL) </li>
            <li><a href="GetCorpInfo.php">GetCorpInfo</a> (회사정보 확인) </li>
            <li><a href="UpdateCorpInfo.php">UpdateCorpInfo</a> (회사정보 수정) </li>
            <li><a href="RegistContact.php">RegistContact</a> (담당자 등록) </li>
            <li><a href="GetContactInfo.php">GetContactInfo</a> (담당자 정보 확인)</li>
            <li><a href="ListContact.php">ListContact</a> (담당자 목록 확인) </li>
            <li><a href="UpdateContact.php">UpdateContact</a> (담당자 정보 수정) </li>
        </ul>
    </fieldset>
</div>
</body>
</html>
