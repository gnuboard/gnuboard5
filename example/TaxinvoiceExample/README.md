popbill.taxinvoice.example.php
==============================

팝빌 세금계산서 SDK Example for PHP 5
####Requirements
+ php 5.2+
+ curl
+ openssl 1.0.1g+ (don't forget about heartbleed.)

####예제 목록
+ common.php - 공통부분
+ CheckMgtKeyInUse.php - 문서번호 확인
+ RegistIssue.php - 즉시 발행
+ Register.php - 임시저장
+ Update.php - 수정
+ Issue.php - 발행
+ CancelIssue.php - 발행취소
+ Send>Send.php - [발행예정]
+ CancelSend.php - [발행예정] 취소
+ Accept.php - [발행예정] 승인
+ Deny.php - [발행예정] 거부
+ Delete.php - 삭제
+ RegistRequest.php - [역발행] 즉시 요청
+ Request.php - 역발행요청
+ CancelRequest.php - 역발행요청 취소
+ Refuse.php - 역발행요청 거부
+ SendToNTS.php - 국세청 즉시전송
+ GetInfo.php - 상태 확인
+ GetInfos.php - 상태 대량 확인
+ GetDetailInfo.php - 상세정보 확인
+ Search.php - 목록 조회
+ GetLogs.php - 상태 변경이력 확인
+ GetURL.php - 세금계산서 문서함 관련 URL
+ GetPopUpURL.php - 세금계산서 보기 URL
+ GetPrintURL.php - 세금계산서 인쇄 [공급자/공급받는자] URL
+ GetEPrintURL.php - 세금계산서 인쇄 [공급받는자용] URL
+ GetMassPrintURL.php - 세금계산서 대량 인쇄 URL
+ GetMailURL.php - 세금계산서 메일링크 URL
+ GetAccessURL.php - 팝빌 로그인 URL
+ GetSealURL.php - 인감 및 첨부문서 등록 URL
+ AttachFile.php - 첨부파일 추가
+ DeleteFile.php - 첨부파일 삭제
+ GetFiles.php - 첨부파일 목록 확인
+ SendEmail.php - 메일 전송
+ SendSMS.php - 문자 전송
+ SendFAX.php - 팩스 전송
+ AttachStatement.php - 전자명세서 첨부
+ DetachStatement.php - 전자명세서 첨부해제
+ GetEmailPublicKeys.php - 유통사업자 메일 목록 확인
+ AssignMgtKey.php - 문서번호 할당
+ ListEmailConfig.php - 세금계산서 알림메일 전송목록 조회
+ UpdateEmailConfig.php - 세금계산서 알림메일 전송설정 수정
+ GetTaxCertURL.php - 공인인증서 등록 URL
+ GetCertificateExpireDate.php - 공인인증서 만료일 확인
+ CheckCertValidation.php - 공인인증서 유효성 확인
+ GetBalance.php - 연동회원 잔여포인트 확인
+ GetChargeURL.php - 연동회원 포인트충전 URL
+ GetPartnerBalance.php - 파트너 잔여포인트 확인
+ GetPartnerURL.php - 파트너 포인트충전 URL
+ GetUnitCost.php - 발행 단가 확인
+ GetChargeInfo.php - 과금정보 확인
+ CheckIsMember.php - 연동회원 가입여부 확인
+ CheckID.php - 아이디 중복 확인
+ JoinMember.php - 연동회원 신규가입
+ GetCorpInfo.php - 회사정보 확인
+ UpdateCorpInfo.php - 회사정보 수정
+ RegistContact.php - 담당자 등록
+ ListContact.php - 담당자 목록 확인
+ UpdateContact.php - 담당자 정보 수정