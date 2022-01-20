<?php
/**
 * =====================================================================================
 * Class for base module for Popbill API SDK. It include base functionality for
 * RESTful web service request and parse json result. It uses Linkhub module
 * to accomplish authentication APIs.
 *
 * This module uses curl and openssl for HTTPS Request. So related modules must
 * be installed and enabled.
 *
 * http://www.linkhub.co.kr
 * Author : Kim Seongjun (pallet027@gmail.com)
 * Written : 2015-06-15
 * Contributor : Jeong YoHan (code@linkhubcorp.com)
 * Updated : 2021-12-09
 *
 * Thanks for your interest.
 * We welcome any suggestions, feedbacks, blames or anything.
 * ======================================================================================
 */
require_once 'popbill.php';

class TaxinvoiceService extends PopbillBase
{

    public function __construct($LinkID, $SecretKey)
    {
        parent::__construct($LinkID, $SecretKey);
        $this->AddScope('110');
    }

    //팝빌 세금계산서 연결 url
    public function GetURL($CorpNum, $UserID, $TOGO)
    {
        return $this->executeCURL('/Taxinvoice/?TG=' . $TOGO, $CorpNum, $UserID)->url;
    }

    //문서번호 사용여부 확인
    public function CheckMgtKeyInUse($CorpNum, $MgtKeyType, $MgtKey)
    {
        if (is_null($MgtKey) || empty($MgtKey)) {
            throw new PopbillException('문서번호가 입력되지 않았습니다.');
        }
        try {
            $response = $this->executeCURL('/Taxinvoice/' . $MgtKeyType . '/' . $MgtKey, $CorpNum);
            return is_null($response->itemKey) == false;
        } catch (PopbillException $pe) {
            if ($pe->getCode() == -11000005) {
                return false;
            }
            throw $pe;
        }
    }

    //즉시발행
    public function RegistIssue($CorpNum, $Taxinvoice, $UserID = null, $writeSpecification = false, $forceIssue = false, $memo = null, $emailSubject = null, $dealInvoiceMgtKey = null)
    {
        if ($writeSpecification) {
            $Taxinvoice->writeSpecification = $writeSpecification;
        }
        if ($forceIssue) {
            $Taxinvoice->forceIssue = $forceIssue;
        }

        if (!is_null($memo) || !empty($memo)) {
            $Taxinvoice->memo = $memo;
        }
        if (!is_null($emailSubject) || !empty($emailSubject)) {
            $Taxinvoice->emailSubject = $emailSubject;
        }
        if (!is_null($dealInvoiceMgtKey) || !empty($dealInvoiceMgtKey)) {
            $Taxinvoice->dealInvoiceMgtKey = $dealInvoiceMgtKey;
        }

        $postdata = json_encode($Taxinvoice);
        return $this->executeCURL('/Taxinvoice', $CorpNum, $UserID, true, 'ISSUE', $postdata);
    }

    //임시저장
    public function Register($CorpNum, $Taxinvoice, $UserID = null, $writeSpecification = false)
    {
        if ($writeSpecification) {
            $Taxinvoice->writeSpecification = $writeSpecification;
        }
        $postdata = json_encode($Taxinvoice);
        return $this->executeCURL('/Taxinvoice', $CorpNum, $UserID, true, null, $postdata);
    }

    //삭제
    public function Delete($CorpNum, $MgtKeyType, $MgtKey, $UserID = null)
    {
        if (is_null($MgtKey) || empty($MgtKey)) {
            throw new PopbillException('문서번호가 입력되지 않았습니다.');
        }
        return $this->executeCURL('/Taxinvoice/' . $MgtKeyType . '/' . $MgtKey, $CorpNum, $UserID, true, 'DELETE', '');
    }

    //수정
    public function Update($CorpNum, $MgtKeyType, $MgtKey, $Taxinvoice, $UserID = null, $writeSpecification = false)
    {
        if (is_null($MgtKey) || empty($MgtKey)) {
            throw new PopbillException('문서번호가 입력되지 않았습니다.');
        }
        if ($writeSpecification) {
            $Taxinvoice->writeSpecification = $writeSpecification;
        }

        $postdata = json_encode($Taxinvoice);
        return $this->executeCURL('/Taxinvoice/' . $MgtKeyType . '/' . $MgtKey, $CorpNum, $UserID, true, 'PATCH', $postdata);
    }

    //발행예정
    public function Send($CorpNum, $MgtKeyType, $MgtKey, $Memo = '', $EmailSubject = '', $UserID = null)
    {
        if (is_null($MgtKey) || empty($MgtKey)) {
            throw new PopbillException('문서번호가 입력되지 않았습니다.');
        }

        $Request = new MemoRequest();
        $Request->memo = $Memo;
        $Request->emailSubject = $EmailSubject;
        $postdata = json_encode($Request);

        return $this->executeCURL('/Taxinvoice/' . $MgtKeyType . '/' . $MgtKey, $CorpNum, $UserID, true, 'SEND', $postdata);
    }

    //발행예정취소
    public function CancelSend($CorpNum, $MgtKeyType, $MgtKey, $Memo = '', $UserID = null)
    {
        if (is_null($MgtKey) || empty($MgtKey)) {
            throw new PopbillException('문서번호가 입력되지 않았습니다.');
        }
        $Request = new MemoRequest();
        $Request->memo = $Memo;
        $postdata = json_encode($Request);

        return $this->executeCURL('/Taxinvoice/' . $MgtKeyType . '/' . $MgtKey, $CorpNum, $UserID, true, 'CANCELSEND', $postdata);
    }

    //발행예정 승인
    public function Accept($CorpNum, $MgtKeyType, $MgtKey, $Memo = '', $UserID = null)
    {
        if (is_null($MgtKey) || empty($MgtKey)) {
            throw new PopbillException('문서번호가 입력되지 않았습니다.');
        }
        $Request = new MemoRequest();
        $Request->memo = $Memo;
        $postdata = json_encode($Request);

        return $this->executeCURL('/Taxinvoice/' . $MgtKeyType . '/' . $MgtKey, $CorpNum, $UserID, true, 'ACCEPT', $postdata);
    }

    //발행예정 거부
    public function Deny($CorpNum, $MgtKeyType, $MgtKey, $Memo = '', $UserID = null)
    {
        if (is_null($MgtKey) || empty($MgtKey)) {
            throw new PopbillException('문서번호가 입력되지 않았습니다.');
        }
        $Request = new MemoRequest();
        $Request->memo = $Memo;
        $postdata = json_encode($Request);

        return $this->executeCURL('/Taxinvoice/' . $MgtKeyType . '/' . $MgtKey, $CorpNum, $UserID, true, 'DENY', $postdata);
    }

    //발행
    public function Issue($CorpNum, $MgtKeyType, $MgtKey, $Memo = '', $EmailSubject = null, $ForceIssue = false, $UserID = null)
    {
        if (is_null($MgtKey) || empty($MgtKey)) {
            throw new PopbillException('문서번호가 입력되지 않았습니다.');
        }
        $Request = new IssueRequest();
        $Request->memo = $Memo;
        $Request->emailSubject = $EmailSubject;
        $Request->forceIssue = $ForceIssue;
        $postdata = json_encode($Request);

        return $this->executeCURL('/Taxinvoice/' . $MgtKeyType . '/' . $MgtKey, $CorpNum, $UserID, true, 'ISSUE', $postdata);
    }

    //발행취소
    public function CancelIssue($CorpNum, $MgtKeyType, $MgtKey, $Memo = '', $UserID = null)
    {
        if (is_null($MgtKey) || empty($MgtKey)) {
            throw new PopbillException('문서번호가 입력되지 않았습니다.');
        }
        $Request = new MemoRequest();
        $Request->memo = $Memo;
        $postdata = json_encode($Request);

        return $this->executeCURL('/Taxinvoice/' . $MgtKeyType . '/' . $MgtKey, $CorpNum, $UserID, true, 'CANCELISSUE', $postdata);
    }

    //역)즉시 요청
    public function RegistRequest($CorpNum, $Taxinvoice, $Memo = '', $UserID = null)
    {
        if (!is_null($Memo) || !empty($memo)) {
            $Taxinvoice->memo = $Memo;
        }

        $postdata = json_encode($Taxinvoice);

        return $this->executeCURL('/Taxinvoice', $CorpNum, $UserID, true, 'REQUEST', $postdata);
    }

    //역)발행요청
    public function Request($CorpNum, $MgtKeyType, $MgtKey, $Memo = '', $UserID = null)
    {
        if (is_null($MgtKey) || empty($MgtKey)) {
            throw new PopbillException('문서번호가 입력되지 않았습니다.');
        }
        $Request = new MemoRequest();
        $Request->memo = $Memo;
        $postdata = json_encode($Request);

        return $this->executeCURL('/Taxinvoice/' . $MgtKeyType . '/' . $MgtKey, $CorpNum, $UserID, true, 'REQUEST', $postdata);
    }

    //역)발행요청 거부
    public function Refuse($CorpNum, $MgtKeyType, $MgtKey, $Memo = '', $UserID = null)
    {
        if (is_null($MgtKey) || empty($MgtKey)) {
            throw new PopbillException('문서번호가 입력되지 않았습니다.');
        }
        $Request = new MemoRequest();
        $Request->memo = $Memo;
        $postdata = json_encode($Request);

        return $this->executeCURL('/Taxinvoice/' . $MgtKeyType . '/' . $MgtKey, $CorpNum, $UserID, true, 'REFUSE', $postdata);
    }

    //역)발행요청 취소
    public function CancelRequest($CorpNum, $MgtKeyType, $MgtKey, $Memo = '', $UserID = null)
    {
        if (is_null($MgtKey) || empty($MgtKey)) {
            throw new PopbillException('문서번호가 입력되지 않았습니다.');
        }
        $Request = new MemoRequest();
        $Request->memo = $Memo;
        $postdata = json_encode($Request);

        return $this->executeCURL('/Taxinvoice/' . $MgtKeyType . '/' . $MgtKey, $CorpNum, $UserID, true, 'CANCELREQUEST', $postdata);
    }

    // 전자세금계산서 초대량 발행 접수
    public function BulkSubmit($CorpNum, $SubmitID, $taxinvoiceList, $ForceIssue = null, $UserID = null)
    {
        if (is_null($SubmitID) || empty($SubmitID)) {
            throw new PopbillException('제출아이디가 입력되지 않았습니다.');
        }
        if (is_null($taxinvoiceList) || empty($taxinvoiceList)) {
            throw new PopbillException('세금계산 정보가 입력되지 않았습니다.');
        }

        $Request = new BulkRequest();
        if($ForceIssue == true){
            $Request->forceIssue = $ForceIssue;
        }
        $Request->invoices = $taxinvoiceList;

        $postdata = json_encode($Request);

        return $this->executeCURL('/Taxinvoice', $CorpNum, $UserID, true, 'BULKISSUE', $postdata, false, null, false, $SubmitID);
    }

    // 초대량 접수결과 확인
    public function GetBulkResult($CorpNum, $SubmitID, $UserID = null)
    {
        if (is_null($SubmitID) || empty($SubmitID)) {
            throw new PopbillException('제출아이디가 입력되지 않았습니다.');
        }

        $response = $this->executeCURL('/Taxinvoice/BULK/' . $SubmitID . '/State', $CorpNum, $UserID);

        $bulkResult = new BulkTaxinvoiceResult();
        $bulkResult->fromJsonInfo($response);
        return $bulkResult;
    }

    //국세청 즉시전송 요청
    public function SendToNTS($CorpNum, $MgtKeyType, $MgtKey, $UserID = null)
    {
        if (is_null($MgtKey) || empty($MgtKey)) {
            throw new PopbillException('문서번호가 입력되지 않았습니다.');
        }

        return $this->executeCURL('/Taxinvoice/' . $MgtKeyType . '/' . $MgtKey, $CorpNum, $UserID, true, 'NTS', '');
    }

    //알림메일 재전송
    public function SendEmail($CorpNum, $MgtKeyType, $MgtKey, $Receiver, $UserID = null)
    {
        if (is_null($MgtKey) || empty($MgtKey)) {
            throw new PopbillException('문서번호가 입력되지 않았습니다.');
        }

        $Request = array('receiver' => $Receiver);
        $postdata = json_encode($Request);

        return $this->executeCURL('/Taxinvoice/' . $MgtKeyType . '/' . $MgtKey, $CorpNum, $UserID, true, 'EMAIL', $postdata);
    }

    //알림문자 재전송
    public function SendSMS($CorpNum, $MgtKeyType, $MgtKey, $Sender, $Receiver, $Contents, $UserID = null)
    {
        if (is_null($MgtKey) || empty($MgtKey)) {
            throw new PopbillException('문서번호가 입력되지 않았습니다.');
        }

        $Request = array('receiver' => $Receiver, 'sender' => $Sender, 'contents' => $Contents);
        $postdata = json_encode($Request);

        return $this->executeCURL('/Taxinvoice/' . $MgtKeyType . '/' . $MgtKey, $CorpNum, $UserID, true, 'SMS', $postdata);
    }

    //알림팩스 재전송
    public function SendFAX($CorpNum, $MgtKeyType, $MgtKey, $Sender, $Receiver, $UserID = null)
    {
        if (is_null($MgtKey) || empty($MgtKey)) {
            throw new PopbillException('문서번호가 입력되지 않았습니다.', -99999999);
        }

        $Request = array('receiver' => $Receiver, 'sender' => $Sender);
        $postdata = json_encode($Request);

        return $this->executeCURL('/Taxinvoice/' . $MgtKeyType . '/' . $MgtKey, $CorpNum, $UserID, true, 'FAX', $postdata);
    }

    //세금계산서 요약정보 및 상태정보 확인
    public function GetInfo($CorpNum, $MgtKeyType, $MgtKey)
    {
        if (is_null($MgtKey) || empty($MgtKey)) {
            throw new PopbillException('문서번호가 입력되지 않았습니다.');
        }
        $result = $this->executeCURL('/Taxinvoice/' . $MgtKeyType . '/' . $MgtKey, $CorpNum);

        $TaxinvoiceInfo = new TaxinvoiceInfo();
        $TaxinvoiceInfo->fromJsonInfo($result);
        return $TaxinvoiceInfo;
    }

    //세금계산서 상세정보 확인
    public function GetDetailInfo($CorpNum, $MgtKeyType, $MgtKey)
    {
        if (is_null($MgtKey) || empty($MgtKey)) {
            throw new PopbillException('문서번호가 입력되지 않았습니다.');
        }

        $result = $this->executeCURL('/Taxinvoice/' . $MgtKeyType . '/' . $MgtKey . '?Detail', $CorpNum);

        $TaxinvoiceDetail = new Taxinvoice();
        $TaxinvoiceDetail->fromJsonInfo($result);

        return $TaxinvoiceDetail;
    }

    //세금계산서 요약정보 다량확인 최대 1000건
    public function GetInfos($CorpNum, $MgtKeyType, $MgtKeyList = array())
    {
        if (is_null($MgtKeyList) || empty($MgtKeyList)) {
            throw new PopbillException('문서번호가 입력되지 않았습니다.');
        }

        $postdata = json_encode($MgtKeyList);

        $TaxinvoiceInfoList = array();

        $result = $this->executeCURL('/Taxinvoice/' . $MgtKeyType, $CorpNum, null, true, null, $postdata);

        for ($i = 0; $i < Count($result); $i++) {
            $TaxinvoiceInfo = new TaxinvoiceInfo();
            $TaxinvoiceInfo->fromJsonInfo($result[$i]);
            $TaxinvoiceInfoList[$i] = $TaxinvoiceInfo;
        }

        return $TaxinvoiceInfoList;
    }

    //세금계산서 문서이력 확인
    public function GetLogs($CorpNum, $MgtKeyType, $MgtKey)
    {
        if (is_null($MgtKey) || empty($MgtKey)) {
            throw new PopbillException('문서번호가 입력되지 않았습니다.');
        }
        $result = $this->executeCURL('/Taxinvoice/' . $MgtKeyType . '/' . $MgtKey . '/Logs', $CorpNum);
        $TaxinvoiceLogList = array();

        for ($i = 0; $i < Count($result); $i++) {
            $TaxinvoiceLog = new TaxinvoiceLog();
            $TaxinvoiceLog->fromJsonInfo($result[$i]);
            $TaxinvoiceLogList[$i] = $TaxinvoiceLog;
        }

        return $TaxinvoiceLogList;
    }

    //파일첨부
    public function AttachFile($CorpNum, $MgtKeyType, $MgtKey, $FilePath, $UserID = null)
    {
        if (is_null($MgtKey) || empty($MgtKey)) {
            throw new PopbillException('문서번호가 입력되지 않았습니다.');
        }

        if (mb_detect_encoding($this->GetBasename($FilePath)) == 'CP949') {
            $FilePath = iconv('CP949', 'UTF-8', $FilePath);
        }
        $FileName = $this->GetBasename($FilePath);
        $postdata = array('Filedata' => '@' . $FilePath . ';filename=' . $FileName);

        return $this->executeCURL('/Taxinvoice/' . $MgtKeyType . '/' . $MgtKey . '/Files', $CorpNum, $UserID, true, null, $postdata, true);
    }


    //첨부파일 목록 확인
    public function GetFiles($CorpNum, $MgtKeyType, $MgtKey)
    {
        if (is_null($MgtKey) || empty($MgtKey)) {
            throw new PopbillException('문서번호가 입력되지 않았습니다.');
        }
        return $this->executeCURL('/Taxinvoice/' . $MgtKeyType . '/' . $MgtKey . '/Files', $CorpNum);
    }

    //첨부파일 삭제
    public function DeleteFile($CorpNum, $MgtKeyType, $MgtKey, $FileID, $UserID = null)
    {
        if (is_null($MgtKey) || empty($MgtKey)) {
            throw new PopbillException('문서번호가 입력되지 않았습니다.');
        }
        if (is_null($FileID) || empty($FileID)) {
            throw new PopbillException('파일아이디가 입력되지 않았습니다.');
        }
        return $this->executeCURL('/Taxinvoice/' . $MgtKeyType . '/' . $MgtKey . '/Files/' . $FileID, $CorpNum, $UserID, true, 'DELETE', '');
    }

    //팝업URL
    public function GetPopUpURL($CorpNum, $MgtKeyType, $MgtKey, $UserID = null)
    {
        if (is_null($MgtKey) || empty($MgtKey)) {
            throw new PopbillException('문서번호가 입력되지 않았습니다.');
        }

        return $this->executeCURL('/Taxinvoice/' . $MgtKeyType . '/' . $MgtKey . '?TG=POPUP', $CorpNum, $UserID)->url;
    }

    //인쇄URL
    public function GetPrintURL($CorpNum, $MgtKeyType, $MgtKey, $UserID = null)
    {
        if (is_null($MgtKey) || empty($MgtKey)) {
            throw new PopbillException('문서번호가 입력되지 않았습니다.');
        }

        return $this->executeCURL('/Taxinvoice/' . $MgtKeyType . '/' . $MgtKey . '?TG=PRINT', $CorpNum, $UserID)->url;
    }

    //구버전 양식 인쇄URL
    public function GetOldPrintURL($CorpNum, $MgtKeyType, $MgtKey, $UserID = null)
    {
        if (is_null($MgtKey) || empty($MgtKey)) {
            throw new PopbillException('문서번호가 입력되지 않았습니다.');
        }

        return $this->executeCURL('/Taxinvoice/' . $MgtKeyType . '/' . $MgtKey . '?TG=PRINTOLD', $CorpNum, $UserID)->url;
    }

    //인쇄URL
    public function GetViewURL($CorpNum, $MgtKeyType, $MgtKey, $UserID = null)
    {
        if (is_null($MgtKey) || empty($MgtKey)) {
            throw new PopbillException('문서번호가 입력되지 않았습니다.');
        }

        return $this->executeCURL('/Taxinvoice/' . $MgtKeyType . '/' . $MgtKey . '?TG=VIEW', $CorpNum, $UserID)->url;
    }

    //공급받는자 인쇄URL
    public function GetEPrintURL($CorpNum, $MgtKeyType, $MgtKey, $UserID = null)
    {
        if (is_null($MgtKey) || empty($MgtKey)) {
            throw new PopbillException('문서번호가 입력되지 않았습니다.');
        }

        return $this->executeCURL('/Taxinvoice/' . $MgtKeyType . '/' . $MgtKey . '?TG=EPRINT', $CorpNum, $UserID)->url;
    }

    //공급받는자 메일URL
    public function GetMailURL($CorpNum, $MgtKeyType, $MgtKey, $UserID = null)
    {
        if (is_null($MgtKey) || empty($MgtKey)) {
            throw new PopbillException('문서번호가 입력되지 않았습니다.');
        }

        return $this->executeCURL('/Taxinvoice/' . $MgtKeyType . '/' . $MgtKey . '?TG=MAIL', $CorpNum, $UserID)->url;
    }

    //세금계산서 다량인쇄 URL
    public function GetMassPrintURL($CorpNum, $MgtKeyType, $MgtKeyList = array(), $UserID = null)
    {
        if (is_null($MgtKeyList) || empty($MgtKeyList)) {
            throw new PopbillException('문서번호가 입력되지 않았습니다.');
        }

        $postdata = json_encode($MgtKeyList);

        return $this->executeCURL('/Taxinvoice/' . $MgtKeyType . '?Print', $CorpNum, $UserID, true, null, $postdata)->url;
    }

    //회원인증서 만료일 확인
    public function GetCertificateExpireDate($CorpNum)
    {
        return $this->executeCURL('/Taxinvoice?cfg=CERT', $CorpNum)->certificateExpiration;
    }

    //발행단가 확인
    public function GetUnitCost($CorpNum)
    {
        return $this->executeCURL('/Taxinvoice?cfg=UNITCOST', $CorpNum)->unitCost;
    }

    //대용량 연계사업자 유통메일목록 확인
    public function GetEmailPublicKeys($CorpNum)
    {
        return $this->executeCURL('/Taxinvoice/EmailPublicKeys', $CorpNum);
    }

    //세금계산서 조회
    public function Search($CorpNum, $MgtKeyType, $DType, $SDate, $EDate, $State = array(), $Type = array(), $TaxType = array(), $LateOnly = null, $Page = null, $PerPage = null, $Order = null,
                           $TaxRegIDType = null, $TaxRegIDYN = null, $TaxRegID = null, $QString = null, $InterOPYN = null, $UserID = null, $IssueType = array(),
                           $CloseDownState = array(), $MgtKey = null, $RegType = array())
    {
        if (is_null($DType) || $DType === "") {
            throw new PopbillException(-99999999, '일자유형이 입력되지 않았습니다.');
        }

        if (is_null($SDate) || $SDate === "") {
            throw new PopbillException(-99999999, '시작일자가 입력되지 않았습니다.');
        }

        if (is_null($EDate) || $EDate === "") {
            throw new PopbillException(-99999999, '종료일자가 입력되지 않았습니다.');
        }

        $uri = '/Taxinvoice/' . $MgtKeyType . '?';
        $uri .= 'DType=' . $DType;
        $uri .= '&SDate=' . $SDate;
        $uri .= '&EDate=' . $EDate;

        if (!is_null($State) || !empty($State)) {
            $uri .= '&State=' . implode(',', $State);
        }

        if (!is_null($Type) || !empty($Type)) {
            $uri .= '&Type=' . implode(',', $Type);
        }

        if (!is_null($TaxType) || !empty($TaxType)) {
            $uri .= '&TaxType=' . implode(',', $TaxType);
        }

        if (!is_null($IssueType) || !empty($IssueType)) {
            $uri .= '&IssueType=' . implode(',', $IssueType);
        }

        if (!is_null($RegType) || !empty($RegType)) {
            $uri .= '&RegType=' . implode(',', $RegType);
        }

        if (!is_null($LateOnly) || !empty($LateOnly)) {
            $uri .= '&LateOnly=' . $LateOnly;
        }

        if (!is_null($CloseDownState) || !empty($CloseDownState)) {
            $uri .= '&CloseDownState=' . implode(',', $CloseDownState);
        }

        if (!empty($TaxRegIDType)) {
            $uri .= '&TaxRegIDType=' . $TaxRegIDType;
        }

        if (!empty($TaxRegIDType)) {
            $uri .= '&TaxRegIDYN=' . $TaxRegIDYN;
        }

        $uri .= '&TaxRegID=' . $TaxRegID;

        if (!is_null($QString) || !empty($QString)) {
            $uri .= '&QString=' . $QString;
        }

        $uri .= '&Order=' . $Order;
        $uri .= '&Page=' . $Page;
        $uri .= '&PerPage=' . $PerPage;
        $uri .= '&InterOPYN=' . $InterOPYN;

        if (!empty($MgtKey)) {
            $uri .= '&MgtKey=' . $MgtKey;
        }

        $response = $this->executeCURL($uri, $CorpNum, $UserID);

        $SearchList = new TISearchResult();
        $SearchList->fromJsonInfo($response);

        return $SearchList;

    }

    // 전자명세서 첨부
    public function AttachStatement($CorpNum, $MgtKeyType, $MgtKey, $SubItemCode, $SubMgtKey, $UserID = null)
    {
        $uri = '/Taxinvoice/' . $MgtKeyType . '/' . $MgtKey . '/AttachStmt';

        $Request = new StmtRequest();
        $Request->ItemCode = $SubItemCode;
        $Request->MgtKey = $SubMgtKey;
        $postdata = json_encode($Request);

        return $this->executeCURL($uri, $CorpNum, $UserID, true, "", $postdata);
    }

    // 전자명세서 첨부해제
    public function DetachStatement($CorpNum, $MgtKeyType, $MgtKey, $SubItemCode, $SubMgtKey, $UserID = null)
    {
        $uri = '/Taxinvoice/' . $MgtKeyType . '/' . $MgtKey . '/DetachStmt';

        $Request = new StmtRequest();
        $Request->ItemCode = $SubItemCode;
        $Request->MgtKey = $SubMgtKey;
        $postdata = json_encode($Request);

        return $this->executeCURL($uri, $CorpNum, $UserID, true, "", $postdata);
    }

    public function GetChargeInfo($CorpNum, $UserID = null)
    {
        $uri = '/Taxinvoice/ChargeInfo';

        $response = $this->executeCURL($uri, $CorpNum, $UserID);
        $ChargeInfo = new ChargeInfo();
        $ChargeInfo->fromJsonInfo($response);

        return $ChargeInfo;
    }

    // 문서문서번호 할당
    public function AssignMgtKey($CorpNum, $MgtKeyType, $itemKey, $MgtKey, $UserID = null)
    {
        if (is_null($MgtKey) || empty($MgtKey)) {
            throw new PopbillException('할당할 문서문서번호가 입력되지 않았습니다.');
        }
        $uri = '/Taxinvoice/' . $itemKey . '/' . $MgtKeyType;
        $postdata = 'MgtKey=' . $MgtKey;

        return $this->executeCURL($uri, $CorpNum, $UserID, true, "", $postdata, false, 'application/x-www-form-urlencoded; charset=utf-8');
    }

    //세금계산서 관련 메일전송 항목에 대한 전송여부 목록 반환
    public function ListEmailConfig($CorpNum, $UserID = null)
    {
        $EmailSendConfigList = array();

        $result = $this->executeCURL('/Taxinvoice/EmailSendConfig', $CorpNum, $UserID);

        for ($i = 0; $i < Count($result); $i++) {
            $EmailSendConfig = new EmailSendConfig();
            $EmailSendConfig->fromJsonInfo($result[$i]);
            $EmailSendConfigList[$i] = $EmailSendConfig;
        }
        return $EmailSendConfigList;
    }

    // 전자세금계산서 관련 메일전송 항목에 대한 전송여부를 수정
    public function UpdateEmailConfig($corpNum, $emailType, $sendYN, $userID = null)
    {
        $sendYNString = $sendYN ? 'True' : 'False';
        $uri = '/Taxinvoice/EmailSendConfig?EmailType=' . $emailType . '&SendYN=' . $sendYNString;

        return $result = $this->executeCURL($uri, $corpNum, $userID, true);
    }

    // 공인인증서 유효성 확인
    public function CheckCertValidation($corpNum, $userID = null)
    {
        return $this->executeCURL('/Taxinvoice/CertCheck', $corpNum, $userID);
    }

    //팝빌 인감 및 첨부문서 등록 URL
    public function GetSealURL($CorpNum, $UserID)
    {
        $response = $this->executeCURL('/?TG=SEAL', $CorpNum, $UserID);
        return $response->url;
    }

    //공인인증서 등록 URL
    public function GetTaxCertURL($CorpNum, $UserID)
    {
        $response = $this->executeCURL('/?TG=CERT', $CorpNum, $UserID);
        return $response->url;
    }

    // PDF URL
    public function GetPDFURL($CorpNum, $MgtKeyType, $MgtKey, $UserID = null)
    {
        if (is_null($MgtKey) || empty($MgtKey)) {
            throw new PopbillException('문서번호가 입력되지 않았습니다.');
        }

        return $this->executeCURL('/Taxinvoice/' . $MgtKeyType . '/' . $MgtKey . '?TG=PDF', $CorpNum, $UserID)->url;
    }

    // get PDF
    public function GetPDF($CorpNum, $MgtKeyType, $MgtKey, $UserID = null)
    {
        if (is_null($MgtKey) || empty($MgtKey)) {
            throw new PopbillException('문서번호가 입력되지 않았습니다.');
        }

        return $this->executeCURL('/Taxinvoice/' . $MgtKeyType . '/' . $MgtKey . '?PDF', $CorpNum, $UserID);
    }

    // 국세청 즉시전송 확인함수
    public function GetSendToNTSConfig($CorpNum, $UserID = null)
    {
        return $this->executeCURL('/Taxinvoice/SendToNTSConfig', $CorpNum, $UserID)->sendToNTS;
    }
}

class Taxinvoice
{
    public $closeDownState;
    public $closeDownStateDate;

    public $writeSpecification;
    public $emailSubject;
    public $memo;
    public $dealInvoiceMgtKey;

    public $writeDate;
    public $chargeDirection;
    public $issueType;
    public $issueTiming;
    public $taxType;
    public $invoicerCorpNum;
    public $invoicerMgtKey;
    public $invoicerTaxRegID;
    public $invoicerCorpName;
    public $invoicerCEOName;
    public $invoicerAddr;
    public $invoicerBizClass;
    public $invoicerBizType;
    public $invoicerContactName;
    public $invoicerDeptName;
    public $invoicerTEL;
    public $invoicerHP;
    public $invoicerEmail;
    public $invoicerSMSSendYN;

    public $invoiceeCorpNum;
    public $invoiceeType;
    public $invoiceeMgtKey;
    public $invoiceeTaxRegID;
    public $invoiceeCorpName;
    public $invoiceeCEOName;
    public $invoiceeAddr;
    public $invoiceeBizClass;
    public $invoiceeBizType;
    public $invoiceeContactName1;
    public $invoiceeDeptName1;
    public $invoiceeTEL1;
    public $invoiceeHP1;
    public $invoiceeEmail2;
    public $invoiceeContactName2;
    public $invoiceeDeptName2;
    public $invoiceeTEL2;
    public $invoiceeHP2;
    public $invoiceeEmail1;
    public $invoiceeSMSSendYN;

    public $trusteeCorpNum;
    public $trusteeMgtKey;
    public $trusteeTaxRegID;
    public $trusteeCorpName;
    public $trusteeCEOName;
    public $trusteeAddr;
    public $trusteeBizClass;
    public $trusteeBizType;
    public $trusteeContactName;
    public $trusteeDeptName;
    public $trusteeTEL;
    public $trusteeHP;
    public $trusteeEmail;
    public $trusteeSMSSendYN;

    public $taxTotal;
    public $supplyCostTotal;
    public $totalAmount;
    public $modifyCode;
    public $purposeType;
    public $serialNum;
    public $cash;
    public $chkBill;
    public $credit;
    public $note;
    public $remark1;
    public $remark2;
    public $remark3;
    public $kwon;
    public $ho;
    public $businessLicenseYN;
    public $bankBookYN;
    public $faxsendYN;
    public $faxreceiveNum;
    public $originalTaxinvoiceKey;
    public $ntsconfirmNum;
    public $detailList;
    public $addContactList;

    public $orgNTSConfirmNum;

    function fromjsonInfo($jsonInfo)
    {
        isset($jsonInfo->closeDownState) ? ($this->closeDownState = $jsonInfo->closeDownState) : null;
        isset($jsonInfo->closeDownStateDate) ? ($this->closeDownStateDate = $jsonInfo->closeDownStateDate) : null;

        isset($jsonInfo->writeSpecification) ? ($this->writeSpecification = $jsonInfo->writeSpecification) : null;
        isset($jsonInfo->writeDate) ? ($this->writeDate = $jsonInfo->writeDate) : null;
        isset($jsonInfo->chargeDirection) ? ($this->chargeDirection = $jsonInfo->chargeDirection) : null;
        isset($jsonInfo->issueType) ? ($this->issueType = $jsonInfo->issueType) : null;
        isset($jsonInfo->issueTiming) ? ($this->issueTiming = $jsonInfo->issueTiming) : null;
        isset($jsonInfo->taxType) ? ($this->taxType = $jsonInfo->taxType) : null;
        isset($jsonInfo->invoicerCorpNum) ? ($this->invoicerCorpNum = $jsonInfo->invoicerCorpNum) : null;
        isset($jsonInfo->invoicerMgtKey) ? ($this->invoicerMgtKey = $jsonInfo->invoicerMgtKey) : null;
        isset($jsonInfo->invoicerTaxRegID) ? ($this->invoicerTaxRegID = $jsonInfo->invoicerTaxRegID) : null;
        isset($jsonInfo->invoicerCorpName) ? ($this->invoicerCorpName = $jsonInfo->invoicerCorpName) : null;
        isset($jsonInfo->invoicerCEOName) ? ($this->invoicerCEOName = $jsonInfo->invoicerCEOName) : null;
        isset($jsonInfo->invoicerAddr) ? ($this->invoicerAddr = $jsonInfo->invoicerAddr) : null;
        isset($jsonInfo->invoicerBizClass) ? ($this->invoicerBizClass = $jsonInfo->invoicerBizClass) : null;
        isset($jsonInfo->invoicerBizType) ? ($this->invoicerBizType = $jsonInfo->invoicerBizType) : null;
        isset($jsonInfo->invoicerContactName) ? ($this->invoicerContactName = $jsonInfo->invoicerContactName) : null;
        isset($jsonInfo->invoicerDeptName) ? ($this->invoicerDeptName = $jsonInfo->invoicerDeptName) : null;
        isset($jsonInfo->invoicerTEL) ? ($this->invoicerTEL = $jsonInfo->invoicerTEL) : null;
        isset($jsonInfo->invoicerHP) ? ($this->invoicerHP = $jsonInfo->invoicerHP) : null;
        isset($jsonInfo->invoicerEmail) ? ($this->invoicerEmail = $jsonInfo->invoicerEmail) : null;
        isset($jsonInfo->invoicerSMSSendYN) ? ($this->invoicerSMSSendYN = $jsonInfo->invoicerSMSSendYN) : null;

        isset($jsonInfo->invoiceeCorpNum) ? ($this->invoiceeCorpNum = $jsonInfo->invoiceeCorpNum) : null;
        isset($jsonInfo->invoiceeType) ? ($this->invoiceeType = $jsonInfo->invoiceeType) : null;
        isset($jsonInfo->invoiceeMgtKey) ? ($this->invoiceeMgtKey = $jsonInfo->invoiceeMgtKey) : null;
        isset($jsonInfo->invoiceeTaxRegID) ? ($this->invoiceeTaxRegID = $jsonInfo->invoiceeTaxRegID) : null;
        isset($jsonInfo->invoiceeCorpName) ? ($this->invoiceeCorpName = $jsonInfo->invoiceeCorpName) : null;
        isset($jsonInfo->invoiceeCEOName) ? ($this->invoiceeCEOName = $jsonInfo->invoiceeCEOName) : null;
        isset($jsonInfo->invoiceeAddr) ? ($this->invoiceeAddr = $jsonInfo->invoiceeAddr) : null;
        isset($jsonInfo->invoiceeBizClass) ? ($this->invoiceeBizClass = $jsonInfo->invoiceeBizClass) : null;
        isset($jsonInfo->invoiceeBizType) ? ($this->invoiceeBizType = $jsonInfo->invoiceeBizType) : null;
        isset($jsonInfo->invoiceeContactName1) ? ($this->invoiceeContactName1 = $jsonInfo->invoiceeContactName1) : null;
        isset($jsonInfo->invoiceeDeptName1) ? ($this->invoiceeDeptName1 = $jsonInfo->invoiceeDeptName1) : null;
        isset($jsonInfo->invoiceeTEL1) ? ($this->invoiceeTEL1 = $jsonInfo->invoiceeTEL1) : null;
        isset($jsonInfo->invoiceeHP1) ? ($this->invoiceeHP1 = $jsonInfo->invoiceeHP1) : null;
        isset($jsonInfo->invoiceeEmail2) ? ($this->invoiceeEmail2 = $jsonInfo->invoiceeEmail2) : null;
        isset($jsonInfo->invoiceeContactName2) ? ($this->invoiceeContactName2 = $jsonInfo->invoiceeContactName2) : null;
        isset($jsonInfo->invoiceeDeptName2) ? ($this->invoiceeDeptName2 = $jsonInfo->invoiceeDeptName2) : null;
        isset($jsonInfo->invoiceeTEL2) ? ($this->invoiceeTEL2 = $jsonInfo->invoiceeTEL2) : null;
        isset($jsonInfo->invoiceeHP2) ? ($this->invoiceeHP2 = $jsonInfo->invoiceeHP2) : null;
        isset($jsonInfo->invoiceeEmail1) ? ($this->invoiceeEmail1 = $jsonInfo->invoiceeEmail1) : null;
        isset($jsonInfo->invoiceeSMSSendYN) ? ($this->invoiceeSMSSendYN = $jsonInfo->invoiceeSMSSendYN) : null;

        isset($jsonInfo->trusteeCorpNum) ? ($this->trusteeCorpNum = $jsonInfo->trusteeCorpNum) : null;
        isset($jsonInfo->trusteeMgtKey) ? ($this->trusteeMgtKey = $jsonInfo->trusteeMgtKey) : null;
        isset($jsonInfo->trusteeTaxRegID) ? ($this->trusteeTaxRegID = $jsonInfo->trusteeTaxRegID) : null;
        isset($jsonInfo->trusteeCorpName) ? ($this->trusteeCorpName = $jsonInfo->trusteeCorpName) : null;
        isset($jsonInfo->trusteeCEOName) ? ($this->trusteeCEOName = $jsonInfo->trusteeCEOName) : null;
        isset($jsonInfo->trusteeAddr) ? ($this->trusteeAddr = $jsonInfo->trusteeAddr) : null;
        isset($jsonInfo->trusteeBizClass) ? ($this->trusteeBizClass = $jsonInfo->trusteeBizClass) : null;
        isset($jsonInfo->trusteeBizType) ? ($this->trusteeBizType = $jsonInfo->trusteeBizType) : null;
        isset($jsonInfo->trusteeContactName) ? ($this->trusteeContactName = $jsonInfo->trusteeContactName) : null;
        isset($jsonInfo->trusteeDeptName) ? ($this->trusteeDeptName = $jsonInfo->trusteeDeptName) : null;
        isset($jsonInfo->trusteeTEL) ? ($this->trusteeTEL = $jsonInfo->trusteeTEL) : null;
        isset($jsonInfo->trusteeHP) ? ($this->trusteeHP = $jsonInfo->trusteeHP) : null;
        isset($jsonInfo->trusteeEmail) ? ($this->trusteeEmail = $jsonInfo->trusteeEmail) : null;
        isset($jsonInfo->trusteeSMSSendYN) ? ($this->trusteeSMSSendYN = $jsonInfo->trusteeSMSSendYN) : null;

        isset($jsonInfo->taxTotal) ? ($this->taxTotal = $jsonInfo->taxTotal) : null;
        isset($jsonInfo->supplyCostTotal) ? ($this->supplyCostTotal = $jsonInfo->supplyCostTotal) : null;
        isset($jsonInfo->totalAmount) ? ($this->totalAmount = $jsonInfo->totalAmount) : null;
        isset($jsonInfo->modifyCode) ? ($this->modifyCode = $jsonInfo->modifyCode) : null;
        isset($jsonInfo->purposeType) ? ($this->purposeType = $jsonInfo->purposeType) : null;
        isset($jsonInfo->serialNum) ? ($this->serialNum = $jsonInfo->serialNum) : null;
        isset($jsonInfo->cash) ? ($this->cash = $jsonInfo->cash) : null;
        isset($jsonInfo->chkBill) ? ($this->chkBill = $jsonInfo->chkBill) : null;
        isset($jsonInfo->credit) ? ($this->credit = $jsonInfo->credit) : null;
        isset($jsonInfo->note) ? ($this->note = $jsonInfo->note) : null;
        isset($jsonInfo->remark1) ? ($this->remark1 = $jsonInfo->remark1) : null;
        isset($jsonInfo->remark2) ? ($this->remark2 = $jsonInfo->remark2) : null;
        isset($jsonInfo->remark3) ? ($this->remark3 = $jsonInfo->remark3) : null;
        isset($jsonInfo->kwon) ? ($this->kwon = $jsonInfo->kwon) : null;
        isset($jsonInfo->ho) ? ($this->ho = $jsonInfo->ho) : null;
        isset($jsonInfo->businessLicenseYN) ? ($this->businessLicenseYN = $jsonInfo->businessLicenseYN) : null;
        isset($jsonInfo->bankBookYN) ? ($this->bankBookYN = $jsonInfo->bankBookYN) : null;
        isset($jsonInfo->faxsendYN) ? ($this->faxsendYN = $jsonInfo->faxsendYN) : null;
        isset($jsonInfo->faxreceiveNum) ? ($this->faxreceiveNum = $jsonInfo->faxreceiveNum) : null;
        isset($jsonInfo->originalTaxinvoiceKey) ? ($this->originalTaxinvoiceKey = $jsonInfo->originalTaxinvoiceKey) : null;

        isset($jsonInfo->orgNTSConfirmNum) ? ($this->orgNTSConfirmNum = $jsonInfo->orgNTSConfirmNum) : null;
        isset($jsonInfo->ntsconfirmNum) ? ($this->ntsconfirmNum = $jsonInfo->ntsconfirmNum) : null;

        if (isset($jsonInfo->detailList)) {
            $DetailList = array();
            for ($i = 0; $i < Count($jsonInfo->detailList); $i++) {
                $TaxinvoiceDetailObj = new TaxinvoiceDetail();
                $TaxinvoiceDetailObj->fromJsonInfo($jsonInfo->detailList[$i]);
                $DetailList[$i] = $TaxinvoiceDetailObj;
            }
            $this->detailList = $DetailList;
        }

        if (isset($jsonInfo->addContactList)) {
            $contactList = array();
            for ($i = 0; $i < Count($jsonInfo->addContactList); $i++) {
                $TaxinvoiceContactObj = new TaxinvoiceAddContact();
                $TaxinvoiceContactObj->fromJsonInfo($jsonInfo->addContactList[$i]);
                $contactList[$i] = $TaxinvoiceContactObj;
            }

            $this->addContactList = $contactList;
        }
    }

}

class TaxinvoiceDetail
{
    public $serialNum;
    public $purchaseDT;
    public $itemName;
    public $spec;
    public $qty;
    public $unitCost;
    public $supplyCost;
    public $tax;
    public $remark;

    public function fromJsonInfo($jsonInfo)
    {
        isset($jsonInfo->serialNum) ? $this->serialNum = $jsonInfo->serialNum : null;
        isset($jsonInfo->purchaseDT) ? $this->purchaseDT = $jsonInfo->purchaseDT : null;
        isset($jsonInfo->itemName) ? $this->itemName = $jsonInfo->itemName : null;
        isset($jsonInfo->spec) ? $this->spec = $jsonInfo->spec : null;
        isset($jsonInfo->qty) ? $this->qty = $jsonInfo->qty : null;
        isset($jsonInfo->unitCost) ? $this->unitCost = $jsonInfo->unitCost : null;
        isset($jsonInfo->supplyCost) ? $this->supplyCost = $jsonInfo->supplyCost : null;
        isset($jsonInfo->tax) ? $this->tax = $jsonInfo->tax : null;
        isset($jsonInfo->remark) ? $this->remark = $jsonInfo->remark : null;
    }

}

class BulkRequest
{
    public $forceIssue;
    public $invoices;
}

class BulkTaxinvoiceResult
{
    public $code;
    public $message;
    public $submitID;
    public $submitCount;
    public $successCount;
    public $failCount;
    public $txState;
    public $txResultCode;
    public $txStartDT;
    public $txEndDT;
    public $receiptDT;
    public $receiptID;
    public $issueResult;

    public function fromJsonInfo($jsonInfo)
    {
        isset($jsonInfo->code) ? $this->code = $jsonInfo->code : null;
        isset($jsonInfo->message) ? $this->message = $jsonInfo->message : null;
        isset($jsonInfo->submitID) ? $this->submitID = $jsonInfo->submitID : null;
        isset($jsonInfo->submitCount) ? $this->submitCount = $jsonInfo->submitCount : null;
        isset($jsonInfo->successCount) ? $this->successCount = $jsonInfo->successCount : null;
        isset($jsonInfo->failCount) ? $this->failCount = $jsonInfo->failCount : null;
        isset($jsonInfo->txState) ? $this->txState = $jsonInfo->txState : null;
        isset($jsonInfo->txResultCode) ? $this->txResultCode = $jsonInfo->txResultCode : null;
        isset($jsonInfo->txStartDT) ? $this->txStartDT = $jsonInfo->txStartDT : null;
        isset($jsonInfo->txEndDT) ? $this->txEndDT = $jsonInfo->txEndDT : null;
        isset($jsonInfo->receiptDT) ? $this->receiptDT = $jsonInfo->receiptDT : null;
        isset($jsonInfo->receiptID) ? $this->receiptID = $jsonInfo->receiptID : null;

        $InfoIssueResult = array();

        for ($i = 0; $i < Count($jsonInfo->issueResult); $i++) {
            $InfoObj = new BulkTaxinvoiceIssueResult();
            $InfoObj->fromJsonInfo($jsonInfo->issueResult[$i]);
            $InfoIssueResult[$i] = $InfoObj;
        }
        $this->issueResult = $InfoIssueResult;
    }
}

class BulkTaxinvoiceIssueResult
{
    public $invoicerMgtKye;
    public $trusteeMgtKye;
    public $code;
    public $ntsconfirmNum;
    public $issueDT;

    public function fromJsonInfo($jsonInfo)
    {
        isset($jsonInfo->invoicerMgtKey) ? $this->invoicerMgtKey = $jsonInfo->invoicerMgtKey : null;
        isset($jsonInfo->trusteeMgtKey) ? $this->trusteeMgtKey = $jsonInfo->trusteeMgtKey : null;
        isset($jsonInfo->code) ? $this->code = $jsonInfo->code : null;
        isset($jsonInfo->ntsconfirmNum) ? $this->ntsconfirmNum = $jsonInfo->ntsconfirmNum : null;
        isset($jsonInfo->issueDT) ? $this->issueDT = $jsonInfo->issueDT : null;
    }
}

class TaxinvoiceAddContact
{
    public $serialNum;
    public $email;
    public $contactName;

    public function fromJsonInfo($jsonInfo)
    {
        isset($jsonInfo->serialNum) ? $this->serialNum = $jsonInfo->serialNum : null;
        isset($jsonInfo->email) ? $this->email = $jsonInfo->email : null;
        isset($jsonInfo->contactName) ? $this->contactName = $jsonInfo->contactName : null;
    }
}

class TISearchResult
{
    public $code;
    public $total;
    public $perPage;
    public $pageNum;
    public $pageCount;
    public $message;
    public $list;

    public function fromJsonInfo($jsonInfo)
    {
        isset($jsonInfo->code) ? $this->code = $jsonInfo->code : null;
        isset($jsonInfo->total) ? $this->total = $jsonInfo->total : null;
        isset($jsonInfo->perPage) ? $this->perPage = $jsonInfo->perPage : null;
        isset($jsonInfo->pageCount) ? $this->pageCount = $jsonInfo->pageCount : null;
        isset($jsonInfo->pageNum) ? $this->pageNum = $jsonInfo->pageNum : null;
        isset($jsonInfo->message) ? $this->message = $jsonInfo->message : null;

        $InfoList = array();

        for ($i = 0; $i < Count($jsonInfo->list); $i++) {
            $InfoObj = new TaxinvoiceInfo();
            $InfoObj->fromJsonInfo($jsonInfo->list[$i]);
            $InfoList[$i] = $InfoObj;
        }
        $this->list = $InfoList;
    }
}


class TaxinvoiceInfo
{
    public $closeDownState;
    public $closeDownStateDate;

    public $itemKey;
    public $stateCode;
    public $taxType;
    public $purposeType;
    public $modifyCode;
    public $issueType;
    public $writeDate;
    public $lateIssueYN;
    public $invoicerCorpName;
    public $invoicerCorpNum;
    public $invoicerMgtKey;
    public $invoicerPrintYN;
    public $invoiceeCorpName;
    public $invoiceeCorpNum;
    public $invoiceeMgtKey;
    public $invoiceePrintYN;
    public $trusteeCorpName;
    public $trusteeCorpNum;
    public $trusteeMgtKey;
    public $trusteePrintYN;
    public $supplyCostTotal;
    public $taxTotal;
    public $issueDT;
    public $preIssueDT;
    public $stateDT;
    public $openYN;
    public $openDT;
    public $ntsresult;
    public $ntsconfirmNum;
    public $ntssendDT;
    public $ntsresultDT;
    public $ntssendErrCode;
    public $stateMemo;

    public $interOPYN;

    public function fromJsonInfo($jsonInfo)
    {
        isset($jsonInfo->closeDownState) ? ($this->closeDownState = $jsonInfo->closeDownState) : null;
        isset($jsonInfo->closeDownStateDate) ? ($this->closeDownStateDate = $jsonInfo->closeDownStateDate) : null;

        isset($jsonInfo->itemKey) ? $this->itemKey = $jsonInfo->itemKey : null;
        isset($jsonInfo->stateCode) ? $this->stateCode = $jsonInfo->stateCode : null;
        isset($jsonInfo->taxType) ? $this->taxType = $jsonInfo->taxType : null;
        isset($jsonInfo->purposeType) ? $this->purposeType = $jsonInfo->purposeType : null;
        isset($jsonInfo->modifyCode) ? $this->modifyCode = $jsonInfo->modifyCode : null;
        isset($jsonInfo->issueType) ? $this->issueType = $jsonInfo->issueType : null;
        isset($jsonInfo->lateIssueYN) ? $this->lateIssueYN = $jsonInfo->lateIssueYN : null;
        isset($jsonInfo->writeDate) ? $this->writeDate = $jsonInfo->writeDate : null;
        isset($jsonInfo->invoicerCorpName) ? $this->invoicerCorpName = $jsonInfo->invoicerCorpName : null;
        isset($jsonInfo->invoicerCorpNum) ? $this->invoicerCorpNum = $jsonInfo->invoicerCorpNum : null;
        isset($jsonInfo->invoicerMgtKey) ? $this->invoicerMgtKey = $jsonInfo->invoicerMgtKey : null;
        isset($jsonInfo->invoicerPrintYN) ? $this->invoicerPrintYN = $jsonInfo->invoicerPrintYN : null;
        isset($jsonInfo->invoiceeCorpName) ? $this->invoiceeCorpName = $jsonInfo->invoiceeCorpName : null;
        isset($jsonInfo->invoiceeCorpNum) ? $this->invoiceeCorpNum = $jsonInfo->invoiceeCorpNum : null;
        isset($jsonInfo->invoiceeMgtKey) ? $this->invoiceeMgtKey = $jsonInfo->invoiceeMgtKey : null;
        isset($jsonInfo->invoiceePrintYN) ? $this->invoiceePrintYN = $jsonInfo->invoiceePrintYN : null;
        isset($jsonInfo->trusteeCorpName) ? $this->trusteeCorpName = $jsonInfo->trusteeCorpName : null;
        isset($jsonInfo->trusteeCorpNum) ? $this->trusteeCorpNum = $jsonInfo->trusteeCorpNum : null;
        isset($jsonInfo->trusteeMgtKey) ? $this->trusteeMgtKey = $jsonInfo->trusteeMgtKey : null;
        isset($jsonInfo->trusteePrintYN) ? $this->trusteePrintYN = $jsonInfo->trusteePrintYN : null;
        isset($jsonInfo->supplyCostTotal) ? $this->supplyCostTotal = $jsonInfo->supplyCostTotal : null;
        isset($jsonInfo->taxTotal) ? $this->taxTotal = $jsonInfo->taxTotal : null;
        isset($jsonInfo->issueDT) ? $this->issueDT = $jsonInfo->issueDT : null;
        isset($jsonInfo->preIssueDT) ? $this->preIssueDT = $jsonInfo->preIssueDT : null;
        isset($jsonInfo->stateDT) ? $this->stateDT = $jsonInfo->stateDT : null;
        isset($jsonInfo->openYN) ? $this->openYN = $jsonInfo->openYN : null;
        isset($jsonInfo->openDT) ? $this->openDT = $jsonInfo->openDT : null;
        isset($jsonInfo->ntsresult) ? $this->ntsresult = $jsonInfo->ntsresult : null;
        isset($jsonInfo->ntsconfirmNum) ? $this->ntsconfirmNum = $jsonInfo->ntsconfirmNum : null;
        isset($jsonInfo->ntssendDT) ? $this->ntssendDT = $jsonInfo->ntssendDT : null;
        isset($jsonInfo->ntsresultDT) ? $this->ntsresultDT = $jsonInfo->ntsresultDT : null;
        isset($jsonInfo->ntssendErrCode) ? $this->ntssendErrCode = $jsonInfo->ntssendErrCode : null;
        isset($jsonInfo->stateMemo) ? $this->stateMemo = $jsonInfo->stateMemo : null;
        isset($jsonInfo->interOPYN) ? $this->interOPYN = $jsonInfo->interOPYN : null;
    }
}


class TaxinvoiceLog
{
    public $ip;
    public $docLogType;
    public $log;
    public $procType;
    public $procCorpName;
    public $procContactName;
    public $procMemo;
    public $regDT;

    function fromJsonInfo($jsonInfo)
    {
        isset($jsonInfo->ip) ? $this->ip = $jsonInfo->ip : null;
        isset($jsonInfo->docLogType) ? $this->docLogType = $jsonInfo->docLogType : null;
        isset($jsonInfo->log) ? $this->log = $jsonInfo->log : null;
        isset($jsonInfo->procType) ? $this->procType = $jsonInfo->procType : null;
        isset($jsonInfo->procCorpName) ? $this->procCorpName = $jsonInfo->procCorpName : null;
        isset($jsonInfo->procContactName) ? $this->procContactName = $jsonInfo->procContactName : null;
        isset($jsonInfo->procMemo) ? $this->procMemo = $jsonInfo->procMemo : null;
        isset($jsonInfo->regDT) ? $this->regDT = $jsonInfo->regDT : null;
    }
}


class ENumMgtKeyType
{
    const SELL = 'SELL';
    const BUY = 'BUY';
    const TRUSTEE = 'TRUSTEE';
}

class MemoRequest
{
    public $memo;
    public $emailSubject;
}

class IssueRequest
{
    public $memo;
    public $emailSubject;
    public $forceIssue;
}

class StmtRequest
{
    public $ItemCode;
    public $MgtKey;
}

class EmailSendConfig
{
    public $emailType;
    public $sendYN;

    function fromJsonInfo($jsonInfo)
    {
        isset($jsonInfo->emailType) ? $this->emailType = $jsonInfo->emailType : null;
        isset($jsonInfo->sendYN) ? $this->sendYN = $jsonInfo->sendYN : null;
    }
}

?>
