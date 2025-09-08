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
 * Author : Jeong YoHan (code@linkhubcorp.com)
 * Written : 2018-03-02
 * Updated : 2025-01-13
 *
 * Thanks for your interest.
 * We welcome any suggestions, feedbacks, blames or anything.
 * ======================================================================================
 */
require_once 'popbill.php';

class KakaoService extends PopbillBase {

    public function __construct($LinkID, $SecretKey)
    {
        parent::__construct($LinkID, $SecretKey);
        $this->AddScope('153');
        $this->AddScope('154');
        $this->AddScope('155');
    }

    // 전송 단가 확인
    public function GetUnitCost($CorpNum, $MessageType) {
        if($this->isNullOrEmpty($CorpNum)) {
            throw new PopbillException('팝빌회원 사업자번호가 입력되지 않았습니다.');
        }
        if($this->isNullOrEmpty($MessageType)) {
            throw new PopbillException('카카오톡 전송유형이 입력되지 않았습니다.');
        }

        return $this->executeCURL('/KakaoTalk/UnitCost?Type=' . $MessageType, $CorpNum)->unitCost;
    }

    // 알림톡/친구톡 전송내역 확인
    public function GetMessages($CorpNum, $ReceiptNum, $UserID = null) {
        if($this->isNullOrEmpty($CorpNum)) {
            throw new PopbillException('팝빌회원 사업자번호가 입력되지 않았습니다.');
        }
        if($this->isNullOrEmpty($ReceiptNum)) {
            throw new PopbillException('카카오톡 접수번호가 입력되지 않았습니다.');
        }

        $response = $this->executeCURL('/KakaoTalk/' . $ReceiptNum, $CorpNum, $UserID);
        $DetailInfo = new KakaoSentInfo();
        $DetailInfo->fromJsonInfo($response);

        return $DetailInfo;
    }

    // 알림톡/친구톡 전송내역 확인 (요청번호 할당)
    public function GetMessagesRN($CorpNum, $RequestNum, $UserID = null) {
        if($this->isNullOrEmpty($CorpNum)) {
            throw new PopbillException('팝빌회원 사업자번호가 입력되지 않았습니다.');
        }
        if($this->isNullOrEmpty($RequestNum)) {
            throw new PopbillException('카카오톡 전송요청번호가 입력되지 않았습니다.');
        }

        $response = $this->executeCURL('/KakaoTalk/Get/' . $RequestNum, $CorpNum, $UserID);
        $DetailInfo = new KakaoSentInfo();
        $DetailInfo->fromJsonInfo($response);

        return $DetailInfo;
    }

    // 카카오톡 채널 목록 확인
    public function ListPlusFriendID($CorpNum) {
        if($this->isNullOrEmpty($CorpNum)) {
            throw new PopbillException('팝빌회원 사업자번호가 입력되지 않았습니다.');
        }

        $PlusFriendList = array();
        $response = $this->executeCURL('/KakaoTalk/ListPlusFriendID', $CorpNum);

        for ($i = 0; $i < Count($response); $i++) {
            $PlusFriendObj = new PlusFriend();
            $PlusFriendObj->fromJsonInfo($response[$i]);
            $PlusFriendList[$i] = $PlusFriendObj;
        }

        return $PlusFriendList;
    }

    // 알림톡 템플릿 목록 확인
    public function ListATSTemplate($CorpNum) {
        if($this->isNullOrEmpty($CorpNum)) {
            throw new PopbillException('팝빌회원 사업자번호가 입력되지 않았습니다.');
        }

        $result = $this->executeCURL('/KakaoTalk/ListATSTemplate', $CorpNum);

        $TemplateList = array();
        for ($i = 0; $i < Count($result); $i++) {
            $TemplateObj = new ATSTemplate();
            $TemplateObj->fromJsonInfo($result[$i]);
            $TemplateList[$i] = $TemplateObj;
        }

        return $TemplateList;
    }

    // 발신번호 등록여부 확인
    public function CheckSenderNumber($CorpNum, $SenderNumber, $UserID = null) {
        if($this->isNullOrEmpty($CorpNum)) {
            throw new PopbillException('팝빌회원 사업자번호가 입력되지 않았습니다.');
        }
        if($this->isNullOrEmpty($SenderNumber)) {
            throw new PopbillException('발신번호가 입력되지 않았습니다.');
        }

        return $this->executeCURL('/KakaoTalk/CheckSenderNumber/' . $SenderNumber, $CorpNum, $UserID);
    }

    // 발신번호 목록 확인
    public function GetSenderNumberList($CorpNum) {
        if($this->isNullOrEmpty($CorpNum)) {
            throw new PopbillException('팝빌회원 사업자번호가 입력되지 않았습니다.');
        }

        return $this->executeCURL('/Message/SenderNumber', $CorpNum);
    }

    // 예약전송 취소 (접수번호)
    public function CancelReserve($CorpNum, $ReceiptNum, $UserID = null) {
        if($this->isNullOrEmpty($CorpNum)) {
            throw new PopbillException('팝빌회원 사업자번호가 입력되지 않았습니다.');
        }
        if($this->isNullOrEmpty($ReceiptNum)) {
            throw new PopbillException('예약전송을 취소할 접수번호가 입력되지 않았습니다.');
        }

        return $this->executeCURL('/KakaoTalk/' . $ReceiptNum . '/Cancel', $CorpNum, $UserID);
    }

    // 예약전송 전체 취소 (전송 요청번호)
    public function CancelReserveRN($CorpNum, $RequestNum, $UserID = null) {
        if($this->isNullOrEmpty($CorpNum)) {
            throw new PopbillException('팝빌회원 사업자번호가 입력되지 않았습니다.');
        }
        if($this->isNullOrEmpty($RequestNum)) {
            throw new PopbillException('예약전송을 취소할 전송요청번호가 입력되지 않았습니다.');
        }

        return $this->executeCURL('/KakaoTalk/Cancel/' . $RequestNum, $CorpNum, $UserID);
    }

    // 예약전송 일부 취소 (접수번호)
    public function CancelReservebyRCV($CorpNum, $ReceiptNum, $ReceiveNum, $UserID = null) {
        if($this->isNullOrEmpty($CorpNum)) {
            throw new PopbillException('팝빌회원 사업자번호가 입력되지 않았습니다.');
        }
        if($this->isNullOrEmpty($ReceiptNum)) {
            throw new PopbillException('예약전송 취소할 접수번호가 입력되지 않았습니다.');
        }
        if($this->isNullOrEmpty($ReceiveNum)) {
            throw new PopbillException('예약전송 취소할 수신번호가 입력되지 않았습니다.');
        }

        $postdata = json_encode($ReceiveNum);

        return $this->executeCURL('/KakaoTalk/' . $ReceiptNum . '/Cancel', $CorpNum, $UserID, true, null, $postdata);
    }

    // 예약전송 일부 취소 (전송 요청번호)
    public function CancelReserveRNbyRCV($CorpNum, $RequestNum, $ReceiveNum, $UserID = null) {
        if($this->isNullOrEmpty($CorpNum)) {
            throw new PopbillException('팝빌회원 사업자번호가 입력되지 않았습니다.');
        }
        if($this->isNullOrEmpty($RequestNum)) {
            throw new PopbillException('예약전송 취소할 전송요청번호가 입력되지 않았습니다.');
        }
        if($this->isNullOrEmpty($ReceiveNum)) {
            throw new PopbillException('예약전송 취소할 수신번호가 입력되지 않았습니다.');
        }

        $postdata = json_encode($ReceiveNum);

        return $this->executeCURL('/KakaoTalk/Cancel/' . $RequestNum, $CorpNum, $UserID, true, null, $postdata);
    }

    public function GetURL($CorpNum, $UserID, $TOGO)
    {
        $URI = '/KakaoTalk/?TG=';

        if ($TOGO == "SENDER") {
            $URI = '/Message/?TG=';
        }

        $response = $this->executeCURL($URI . $TOGO, $CorpNum, $UserID);
        return $response->url;
    }

    // 플러스친구 계정관리 팝업 URL
    public function GetPlusFriendMgtURL($CorpNum, $UserID = null) {
        if($this->isNullOrEmpty($CorpNum)) {
            throw new PopbillException('팝빌회원 사업자번호가 입력되지 않았습니다.');
        }

        $response = $this->executeCURL('/KakaoTalk/?TG=PLUSFRIEND', $CorpNum, $UserID);
        return $response->url;
    }

    // 발신번호 관리 팝업 URL
    public function GetSenderNumberMgtURL($CorpNum, $UserID = null) {
        if($this->isNullOrEmpty($CorpNum)) {
            throw new PopbillException('팝빌회원 사업자번호가 입력되지 않았습니다.');
        }

        $response = $this->executeCURL('/Message/?TG=SENDER', $CorpNum, $UserID);
        return $response->url;
    }

    // 알림톡 템플릿관리 팝업 URL
    public function GetATSTemplateMgtURL($CorpNum, $UserID = null) {
        if($this->isNullOrEmpty($CorpNum)) {
            throw new PopbillException('팝빌회원 사업자번호가 입력되지 않았습니다.');
        }

        $response = $this->executeCURL('/KakaoTalk/?TG=TEMPLATE', $CorpNum, $UserID);
        return $response->url;
    }

    // 알림톡 템플릿 정보 확인
    public function GetATSTemplate($CorpNum, $TemplateCode, $UserID = null) {
        if($this->isNullOrEmpty($CorpNum)) {
            throw new PopbillException('팝빌회원 사업자번호가 입력되지 않았습니다.');
        }
        if($this->isNullOrEmpty($TemplateCode)) {
            throw new PopbillException('템플릿코드가 입력되지 않았습니다.');
        }

        $result = $this->executeCURL('/KakaoTalk/GetATSTemplate/'.$TemplateCode, $CorpNum, $UserID);

        $TemplateInfo = new ATSTemplate();
        $TemplateInfo->fromJsonInfo($result);

        return $TemplateInfo;
    }

    // 카카오톡 전송내역 팝업 URL
    public function GetSentListURL($CorpNum, $UserID = null) {
        if($this->isNullOrEmpty($CorpNum)) {
            throw new PopbillException('팝빌회원 사업자번호가 입력되지 않았습니다.');
        }

        $response = $this->executeCURL('/KakaoTalk/?TG=BOX', $CorpNum, $UserID);
        return $response->url;
    }

    // 전송내역 목록 조회
    public function Search($CorpNum, $SDate, $EDate, $State = array(), $Item = array(), $ReserveYN = null, $SenderYN = false, $Page = null, $PerPage = null, $Order = null, $UserID = null, $QString = null) {
        if($this->isNullOrEmpty($CorpNum)) {
            throw new PopbillException('팝빌회원 사업자번호가 입력되지 않았습니다.');
        }
        if($this->isNullOrEmpty($SDate)) {
            throw new PopbillException('시작일자가 입력되지 않았습니다.');
        }
        if(!$this->isValidDate($SDate)) {
            throw new PopbillException('시작일자가 유효하지 않습니다.');
        }
        if($this->isNullOrEmpty($EDate)) {
            throw new PopbillException('종료일자가 입력되지 않았습니다.');
        }
        if(!$this->isValidDate($EDate)) {
            throw new PopbillException('종료일자가 유효하지 않습니다.');
        }
        if($this->isNullOrEmpty($State)) {
            throw new PopbillException('전송상태가 입력되지 않았습니다.');
        }

        $uri = '/KakaoTalk/Search';
        $uri .= '?SDate=' . $SDate;
        $uri .= '&EDate=' . $EDate;
        $uri .= '&State=' . implode(',', $State);
        
        if(!$this->isNullOrEmpty($Item)) {
            $uri .= '&Item=' . implode(',', $Item);
        }
        if(!is_null($ReserveYN) && $ReserveYN != "") {
            if($ReserveYN) {
                $uri .= '&ReserveYN=1';
            }else{
                $uri .= '&ReserveYN=0';
            }
        }
        if ($SenderYN) {
            $uri .= '&SenderOnly=1';
        } else {
            $uri .= '&SenderOnly=0';
        }
        if(!$this->isNullOrEmpty($Page)) {
            $uri .= '&Page=' . $Page;
        }
        if(!$this->isNullOrEmpty($PerPage)) {
            $uri .= '&PerPage=' . $PerPage;
        }
        if(!$this->isNullOrEmpty($Order)) {
            $uri .= '&Order=' . $Order;
        }
        if(!$this->isNullOrEmpty($QString)) {
            $uri .= '&QString=' . urlencode($QString);
        }

        $response = $this->executeCURL($uri, $CorpNum, $UserID);

        $SearchList = new KakaoSearchResult();
        $SearchList->fromJsonInfo($response);

        return $SearchList;

    }

    // 과금정보 확인
    public function GetChargeInfo($CorpNum, $MessageType, $UserID = null)
    {
        if($this->isNullOrEmpty($CorpNum)) {
            throw new PopbillException('팝빌회원 사업자번호가 입력되지 않았습니다.');
        }
        if($this->isNullOrEmpty($MessageType)) {
            throw new PopbillException('카카오톡 전송유형이 입력되지 않았습니다.');
        }

        $uri = '/KakaoTalk/ChargeInfo?Type=' . $MessageType;

        $response = $this->executeCURL($uri, $CorpNum, $UserID);
        $ChargeInfo = new ChargeInfo();
        $ChargeInfo->fromJsonInfo($response);

        return $ChargeInfo;
    }

    // 친구톡(이미지)
    public function SendFMS($CorpNum, $PlusFriendID, $Sender = null, $Content = null, $AltContent = null, $AltSendType = null, $AdsYN = false, $Messages = array(), $Btns = array(), $ReserveDT = null, $FilePaths = array(), $ImageURL = null, $UserID = null, $RequestNum = null, $AltSubject = null)
    {
        if($this->isNullOrEmpty($CorpNum)) {
            throw new PopbillException('팝빌회원 사업자번호가 입력되지 않았습니다.');
        }
        if($this->isNullOrEmpty($PlusFriendID)) {
            throw new PopbillException('카카오톡 채널 검색용 아이디가 입력되지 않았습니다.');
        }
        if($this->isNullOrEmpty($Messages)) {
            throw new PopbillException('카카오톡 전송정보가 입력되지 않았습니다.');
        }
        if($this->isNullOrEmpty($FilePaths)) {
            throw new PopbillException('전송할 이미지 파일 경로가 입력되지 않았습니다.');
        }
        if(!$this->isNullOrEmpty($ReserveDT) && !$this->isValidDT($ReserveDT)) {
            throw new PopbillException('전송 예약일시가 유효하지 않습니다.');
        }

        $Request = array();

        if(!$this->isNullOrEmpty($PlusFriendID)) $Request['plusFriendID'] = $PlusFriendID;
        if(!$this->isNullOrEmpty($Sender)) $Request['snd'] = $Sender;
        if(!$this->isNullOrEmpty($Content)) $Request['content'] = $Content;
        if(!$this->isNullOrEmpty($AltSubject)) $Request['altSubject'] = $AltSubject;
        if(!$this->isNullOrEmpty($AltContent)) $Request['altContent'] = $AltContent;
        if(!$this->isNullOrEmpty($AltSendType)) $Request['altSendType'] = $AltSendType;
        if(!$this->isNullOrEmpty($ReserveDT)) $Request['sndDT'] = $ReserveDT;
        if(!$this->isNullOrEmpty($AdsYN)) $Request['adsYN'] = $AdsYN;
        if(!$this->isNullOrEmpty($ImageURL)) $Request['imageURL'] = $ImageURL;
        if(!$this->isNullOrEmpty($RequestNum)) $Request['requestNum'] = $RequestNum;
        if(!$this->isNullOrEmpty($Btns)) $Request['btns'] = $Btns;

        $Request['msgs'] = $Messages;
        $postdata = array();
        $postdata['form'] = json_encode($Request);

        $i = 0;

        foreach ($FilePaths as $FilePath) {
            $postdata['file'] = '@' . $FilePath;
        }

        return $this->executeCURL('/FMS', $CorpNum, $UserID, true, null, $postdata, true)->receiptNum;
    }

    // 친구톡(텍스트)
    public function SendFTS($CorpNum, $PlusFriendID, $Sender = null, $Content = null, $AltContent = null, $AltSendType = null, $AdsYN = false, $Messages = array(), $Btns = array(), $ReserveDT = null, $UserID = null, $RequestNum = null, $AltSubject = null)
    {
        if($this->isNullOrEmpty($CorpNum)) {
            throw new PopbillException('팝빌회원 사업자번호가 입력되지 않았습니다.');
        }
        if($this->isNullOrEmpty($PlusFriendID)) {
            throw new PopbillException('카카오톡 채널 검색용 아이디가 입력되지 않았습니다.');
        }
        if($this->isNullOrEmpty($Messages)) {
            throw new PopbillException('카카오톡 전송정보가 입력되지 않았습니다.');
        }
        if(!$this->isNullOrEmpty($ReserveDT) && !$this->isValidDT($ReserveDT)) {
            throw new PopbillException('전송 예약일시가 유효하지 않습니다.');
        }

        $Request = array();

        if(!$this->isNullOrEmpty($PlusFriendID)) $Request['plusFriendID'] = $PlusFriendID;
        if(!$this->isNullOrEmpty($Sender)) $Request['snd'] = $Sender;
        if(!$this->isNullOrEmpty($Content)) $Request['content'] = $Content;
        if(!$this->isNullOrEmpty($AltSubject)) $Request['altSubject'] = $AltSubject;
        if(!$this->isNullOrEmpty($AltContent)) $Request['altContent'] = $AltContent;
        if(!$this->isNullOrEmpty($AltSendType)) $Request['altSendType'] = $AltSendType;
        if(!$this->isNullOrEmpty($ReserveDT)) $Request['sndDT'] = $ReserveDT;
        if(!$this->isNullOrEmpty($AdsYN)) $Request['adsYN'] = $AdsYN;
        if(!$this->isNullOrEmpty($RequestNum)) $Request['requestNum'] = $RequestNum;
        if(!$this->isNullOrEmpty($Btns)) $Request['btns'] = $Btns;

        $Request['msgs'] = $Messages;
        $postdata = json_encode($Request);

        return $this->executeCURL('/FTS', $CorpNum, $UserID, true, null, $postdata)->receiptNum;
    }

    // 알림톡 단건전송
    public function SendATS($CorpNum, $TemplateCode, $Sender = null, $Content = null, $AltContent = null, $AltSendType = null, $Messages = array(), $ReserveDT = null, $UserID = null, $RequestNum = null, $Btns = null, $AltSubject = null)
    {
        if($this->isNullOrEmpty($CorpNum)) {
            throw new PopbillException('팝빌회원 사업자번호가 입력되지 않았습니다.');
        }
        if($this->isNullOrEmpty($TemplateCode)) {
            throw new PopbillException('승인된 알림톡 템플릿코드가 입력되지 않았습니다.');
        }
        if($this->isNullOrEmpty($Messages)) {
            throw new PopbillException('카카오톡 전송정보가 입력되지 않았습니다.');
        }
        if(!$this->isNullOrEmpty($ReserveDT) && !$this->isValidDT($ReserveDT)) {
            throw new PopbillException('전송 예약일시가 유효하지 않습니다.');
        }

        $Request = array();

        if(!$this->isNullOrEmpty($TemplateCode)) $Request['templateCode'] = $TemplateCode;
        if(!$this->isNullOrEmpty($Sender)) $Request['snd'] = $Sender;
        if(!$this->isNullOrEmpty($Content)) $Request['content'] = $Content;
        if(!$this->isNullOrEmpty($AltSubject)) $Request['altSubject'] = $AltSubject;
        if(!$this->isNullOrEmpty($AltContent)) $Request['altContent'] = $AltContent;
        if(!$this->isNullOrEmpty($AltSendType)) $Request['altSendType'] = $AltSendType;
        if(!$this->isNullOrEmpty($ReserveDT)) $Request['sndDT'] = $ReserveDT;
        if(!$this->isNullOrEmpty($RequestNum)) $Request['requestNum'] = $RequestNum;
        if(!$this->isNullOrEmpty($Btns)) $Request['btns'] = $Btns;

        $Request['msgs'] = $Messages;

        $postdata = json_encode($Request);

        return $this->executeCURL('/ATS', $CorpNum, $UserID, true, null, $postdata)->receiptNum;
    }
}

class ENumKakaoType
{
    const ATS = 'ATS';
    const FTS = 'FTS';
    const FMS = 'FMS';
}

class KakaoSearchResult
{
    public $code;
    public $message;
    public $total;
    public $perPage;
    public $pageNum;
    public $pageCount;

    public $list;

    function fromJsonInfo($jsonInfo)
    {

        isset($jsonInfo->code) ? ($this->code = $jsonInfo->code) : null;
        isset($jsonInfo->message) ? ($this->message = $jsonInfo->message) : null;
        isset($jsonInfo->total) ? ($this->total = $jsonInfo->total) : null;
        isset($jsonInfo->perPage) ? ($this->perPage = $jsonInfo->perPage) : null;
        isset($jsonInfo->pageNum) ? ($this->pageNum = $jsonInfo->pageNum) : null;
        isset($jsonInfo->pageCount) ? ($this->pageCount = $jsonInfo->pageCount) : null;

        $DetailList = array();
        for ($i = 0; $i < Count($jsonInfo->list); $i++) {
            $SentInfo = new KakaoSentInfoDetail();
            $SentInfo->fromJsonInfo($jsonInfo->list[$i]);
            $DetailList[$i] = $SentInfo;
        }
        $this->list = $DetailList;
    }
}

class KakaoSentInfo
{
    public $contentType;
    public $templateCode;
    public $plusFriendID;
    public $sendNum;
    public $altSubject;
    public $altContent;
    public $altSendType;
    public $reserveDT;
    public $adsYN;
    public $imageURL;
    public $sendCnt;
    public $successCnt;
    public $failCnt;
    public $altCnt;
    public $cancelCnt;

    public $msgs;
    public $btns;

    function fromJsonInfo($jsonInfo)
    {

        isset($jsonInfo->contentType) ? ($this->contentType = $jsonInfo->contentType) : null;
        isset($jsonInfo->templateCode) ? ($this->templateCode = $jsonInfo->templateCode) : null;
        isset($jsonInfo->plusFriendID) ? ($this->plusFriendID = $jsonInfo->plusFriendID) : null;
        isset($jsonInfo->sendNum) ? ($this->sendNum = $jsonInfo->sendNum) : null;
        isset($jsonInfo->altSubject) ? ($this->altSubject = $jsonInfo->altSubject) : null;
        isset($jsonInfo->altContent) ? ($this->altContent = $jsonInfo->altContent) : null;
        isset($jsonInfo->altSendType) ? ($this->altSendType = $jsonInfo->altSendType) : null;
        isset($jsonInfo->reserveDT) ? ($this->reserveDT = $jsonInfo->reserveDT) : null;
        isset($jsonInfo->adsYN) ? ($this->adsYN = $jsonInfo->adsYN) : null;
        isset($jsonInfo->imageURL) ? ($this->imageURL = $jsonInfo->imageURL) : null;
        isset($jsonInfo->sendCnt) ? ($this->sendCnt = $jsonInfo->sendCnt) : null;
        isset($jsonInfo->successCnt) ? ($this->successCnt = $jsonInfo->successCnt) : null;
        isset($jsonInfo->failCnt) ? ($this->failCnt = $jsonInfo->failCnt) : null;
        isset($jsonInfo->altCnt) ? ($this->altCnt = $jsonInfo->altCnt) : null;
        isset($jsonInfo->cancelCnt) ? ($this->cancelCnt = $jsonInfo->cancelCnt) : null;

        if (isset($jsonInfo->msgs)) {
            $msgsList = array();
            for ($i = 0; $i < Count($jsonInfo->msgs); $i++) {
                $kakaoDetail = new KakaoSentInfoDetail();
                $kakaoDetail->fromJsonInfo($jsonInfo->msgs[$i]);
                $msgsList[$i] = $kakaoDetail;
            }
            $this->msgs = $msgsList;
        } // end of if

        if (isset($jsonInfo->btns)) {
            $btnsList = array();
            for ($i = 0; $i < Count($jsonInfo->btns); $i++) {
                $buttonDetail = new KakaoButton();
                $buttonDetail->fromJsonInfo($jsonInfo->btns[$i]);
                $btnsList[$i] = $buttonDetail;
            }
            $this->btns = $btnsList;
        }

    }

} // end of KakaoSentInfo class

class KakaoSentInfoDetail
{
    public $state;
    public $sendDT;
    public $receiveNum;
    public $receiveName;
    public $content;
    public $result;
    public $resultDT;
    public $altSubject;
    public $altContent;
    public $contentType;
    public $altContentType;
    public $altSendDT;
    public $altResult;
    public $altResultDT;
    public $reserveDT;
    public $receiptNum;
    public $requestNum;
    public $interOPRefKey;

    public function fromJsonInfo($jsonInfo)
    {
        isset($jsonInfo->state) ? ($this->state = $jsonInfo->state) : null;
        isset($jsonInfo->sendDT) ? ($this->sendDT = $jsonInfo->sendDT) : null;
        isset($jsonInfo->receiveNum) ? ($this->receiveNum = $jsonInfo->receiveNum) : null;
        isset($jsonInfo->receiveName) ? ($this->receiveName = $jsonInfo->receiveName) : null;
        isset($jsonInfo->content) ? ($this->content = $jsonInfo->content) : null;
        isset($jsonInfo->result) ? ($this->result = $jsonInfo->result) : null;
        isset($jsonInfo->resultDT) ? ($this->resultDT = $jsonInfo->resultDT) : null;
        isset($jsonInfo->altSubject) ? ($this->altSubject = $jsonInfo->altSubject) : null;
        isset($jsonInfo->altContent) ? ($this->altContent = $jsonInfo->altContent) : null;
        isset($jsonInfo->contentType) ? ($this->contentType = $jsonInfo->contentType) : null;
        isset($jsonInfo->altContentType) ? ($this->altContentType = $jsonInfo->altContentType) : null;
        isset($jsonInfo->altSendDT) ? ($this->altSendDT = $jsonInfo->altSendDT) : null;
        isset($jsonInfo->altResult) ? ($this->altResult = $jsonInfo->altResult) : null;
        isset($jsonInfo->altResultDT) ? ($this->altResultDT = $jsonInfo->altResultDT) : null;
        isset($jsonInfo->reserveDT) ? ($this->reserveDT = $jsonInfo->reserveDT) : null;
        isset($jsonInfo->receiptNum) ? ($this->receiptNum = $jsonInfo->receiptNum) : null;
        isset($jsonInfo->requestNum) ? ($this->requestNum = $jsonInfo->requestNum) : null;
        isset($jsonInfo->interOPRefKey) ? ($this->interOPRefKey = $jsonInfo->interOPRefKey) : null;
    }
}

class ATSTemplate
{
    public $templateCode;
    public $templateName;
    public $template;
    public $plusFriendID;
    public $ads;
    public $appendix;
    public $btns;
    public $secureYN;
    public $state;
    public $stateDT;

    public function fromJsonInfo($jsonInfo)
    {
        isset($jsonInfo->templateCode) ? $this->templateCode = $jsonInfo->templateCode : null;
        isset($jsonInfo->templateName) ? $this->templateName = $jsonInfo->templateName : null;
        isset($jsonInfo->template) ? $this->template = $jsonInfo->template : null;
        isset($jsonInfo->plusFriendID) ? $this->plusFriendID = $jsonInfo->plusFriendID : null;
        isset($jsonInfo->ads) ? $this->ads = $jsonInfo->ads : null;
        isset($jsonInfo->appendix) ? $this->appendix = $jsonInfo->appendix : null;
        isset($jsonInfo->secureYN) ? $this->secureYN = $jsonInfo->secureYN : null;
        isset($jsonInfo->state) ? $this->state = $jsonInfo->state : null;
        isset($jsonInfo->stateDT) ? $this->stateDT = $jsonInfo->stateDT : null;

        if(isset($jsonInfo->btns)){
            $InfoList = array();
            for ($i = 0; $i < Count($jsonInfo->btns); $i++) {
                $InfoObj = new KakaoButton();
                $InfoObj->fromJsonInfo($jsonInfo->btns[$i]);
                $InfoList[$i] = $InfoObj;
            }
            $this->btns = $InfoList;
        }
    }
}

class KakaoButton
{
    public $n;
    public $t;
    public $u1;
    public $u2;
    public $tg;

    function fromJsonInfo($jsonInfo)
    {
        isset($jsonInfo->n) ? $this->n = $jsonInfo->n : null;
        isset($jsonInfo->t) ? $this->t = $jsonInfo->t : null;
        isset($jsonInfo->u1) ? $this->u1 = $jsonInfo->u1 : null;
        isset($jsonInfo->u2) ? $this->u2 = $jsonInfo->u2 : null;
        isset($jsonInfo->tg) ? $this->tg = $jsonInfo->tg : null;
    }
}

class PlusFriend
{
    public $plusFriendID;
    public $plusFriendName;
    public $regDT;
    public $state;
    public $stateDT;

    function fromJsonInfo($jsonInfo)
    {
        isset($jsonInfo->plusFriendID) ? $this->plusFriendID = $jsonInfo->plusFriendID : null;
        isset($jsonInfo->plusFriendName) ? $this->plusFriendName = $jsonInfo->plusFriendName : null;
        isset($jsonInfo->regDT) ? $this->regDT = $jsonInfo->regDT : null;
        isset($jsonInfo->state) ? $this->state = $jsonInfo->state : null;
        isset($jsonInfo->stateDT) ? $this->stateDT = $jsonInfo->stateDT : null;
    }
}


?>
