<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <link rel="stylesheet" type="text/css" href="/Example.css" media="screen"/>

    <title>팝빌 SDK PHP 5.X Example.</title>
</head>
<body>
<div id="content">
    <p class="heading1">팝빌 전자명세서 SDK PHP 5.X Example.</p>
    <br/>
    <fieldset class="fieldset1">
        <legend>전자명세서 발행</legend>
        <ul>
            <li><a href="CheckMgtKeyInUse.php">CheckMgtKeyInUse</a> (문서번호 확인)</li>
            <li><a href="RegistIssue.php">RegistIssue</a> (즉시 발행)</li>
            <li><a href="Register.php">Register</a> (임시저장)</li>
            <li><a href="Update.php">Update</a> (수정)</li>
            <li><a href="Issue.php">Issue</a> (발행)</li>
            <li><a href="CancelIssue.php">CancelIssue</a> (발행취소)</li>
            <li><a href="Delete.php">Delete</a> (삭제)</li>
        </ul>
    </fieldset>
    <fieldset class="fieldset1">
        <legend>전자명세서 정보확인</legend>
        <ul>
            <li><a href="GetInfo.php">GetInfo</a> (상태 확인)</li>
            <li><a href="GetInfos.php">GetInfos</a> (상태 대량 확인)</li>
            <li><a href="GetDetailInfo.php">GetDetailInfo</a> (상세정보 확인)</li>
            <li><a href="Search.php">Search</a> (목록 조회)</li>
            <li><a href="GetLogs.php">GetLogs</a> (상태 변경이력 확인)</li>
            <li><a href="GetURL.php">GetURL</a> (전자명세서 문서함 관련 URL)</li>
        </ul>
    </fieldset>
    <fieldset class="fieldset1">
        <legend>전자명세서 보기/인쇄</legend>
        <ul>
            <li><a href="GetPopUpURL.php">GetPopUpURL</a> (전자명세서 보기 URL)</li>
            <li><a href="GetPrintURL.php">GetPrintURL</a> (전자명세서 인쇄 [공급자] URL)</li>
            <li><a href="GetViewURL.php">GetViewURL</a> (전자명세서 보기 URL - 메뉴/버튼없는 뷰)</li>
            <li><a href="GetEPrintURL.php">GetEPrintURL</a> (전자명세서 인쇄 [공급받는자용] URL)</li>
            <li><a href="GetMassPrintURL.php">GetMassPrintURL</a> (전자명세서 대량 인쇄 URL)</li>
            <li><a href="GetMailURL.php">GetMailURL</a> (전자명세서 메일링크 URL)</li>
        </ul>
    </fieldset>
    <fieldset class="fieldset1">
        <legend>부가기능</legend>
        <ul>
            <li><a href="GetAccessURL.php">GetAccessURL</a> (팝빌 로그인 URL)</li>
            <li><a href="GetSealURL.php">GetSealURL</a> (인감 및 첨부문서 등록 URL)</li>
            <li><a href="AttachFile.php">AttachFile</a> (첨부파일 추가)</li>
            <li><a href="DeleteFile.php">DeleteFile</a> (첨부파일 삭제)</li>
            <li><a href="GetFiles.php">GetFiles</a> (첨부파일 목록 확인)</li>
            <li><a href="SendEmail.php">SendEmail</a> (메일 전송)</li>
            <li><a href="SendSMS.php">SendSMS</a> (문자 전송)</li>
            <li><a href="SendFAX.php">SendFAX</a> (팩스 전송)</li>
            <li><a href="FAXSend.php">FAXSend</a> (선팩스 전송)</li>
            <li><a href="AttachStatement.php">AttachStatement</a> (전자명세서 첨부)</li>
            <li><a href="DetachStatement.php">DetachStatement</a> (전자명세서 첨부해제)</li>
            <li><a href="ListEmailConfig.php">ListEmailConfig</a> (전자명세서 알림메일 전송목록 조회)</li>
            <li><a href="UpdateEmailConfig.php">UpdateEmailConfig</a> (전자명세서 알림메일 전송설정 수정)</li>
        </ul>
    </fieldset>
    <fieldset class="fieldset1">
        <legend>포인트관리</legend>
        <ul>
            <li><a href="GetBalance.php">GetBalance</a> (연동회원 잔여포인트 확인)</li>
            <li><a href="GetChargeURL.php">GetChargeURL</a> (연동회원 포인트충전 URL)</li>
            <li><a href="GetPaymentURL.php">GetPaymentURL</a> (연동회원 포인트 결제내역 URL)</li>
            <li><a href="GetUseHistoryURL.php">GetUseHistoryURL</a> (연동회원 사용내역 URL)</li>
            <li><a href="GetPartnerBalance.php">GetPartnerBalance</a> (파트너 잔여포인트 확인)</li>
            <li><a href="GetPartnerURL.php">GetPartnerURL</a> (파트너 포인트충전 URL)</li>
            <li><a href="GetUnitCost.php">GetUnitCost</a> (발행 단가 확인)</li>
            <li><a href="GetChargeInfo.php">GetChargeInfo</a> (과금정보 확인)</li>
        </ul>
    </fieldset>
    <fieldset class="fieldset1">
        <legend>회원정보</legend>
        <ul>
            <li><a href="CheckIsMember.php">CheckIsMember</a> (연동회원 가입여부 확인)</li>
            <li><a href="CheckID.php">CheckID</a> (아이디 중복 확인)</li>
            <li><a href="JoinMember.php">JoinMember</a> (연동회원 신규가입)</li>
            <li><a href="GetCorpInfo.php">GetCorpInfo</a> (회사정보 확인)</li>
            <li><a href="UpdateCorpInfo.php">UpdateCorpInfo</a> (회사정보 수정)</li>
            <li><a href="RegistContact.php">RegistContact</a> (담당자 등록)</li>
            <li><a href="GetContactInfo.php">GetContactInfo</a> (담당자 정보 확인)</li>
            <li><a href="ListContact.php">ListContact</a> (담당자 목록 확인)</li>
            <li><a href="UpdateContact.php">UpdateContact</a> (담당자 정보 수정)</li>
        </ul>
    </fieldset>
</div>
</body>
</html>
