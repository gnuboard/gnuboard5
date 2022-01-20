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
 * Updated : 2021-12-23
 *
 * Thanks for your interest.
 * We welcome any suggestions, feedbacks, blames or anything.
 * ======================================================================================
 */
require_once 'popbill.php';

class KakaoService extends PopbillBase
{

    public function __construct($LinkID, $SecretKey)
    {
        parent::__construct($LinkID, $SecretKey);
        $this->AddScope('153');
        $this->AddScope('154');
        $this->AddScope('155');
    }

    public function GetUnitCost($CorpNum, $MessageType)
    {
        return $this->executeCURL('/KakaoTalk/UnitCost?Type=' . $MessageType, $CorpNum)->unitCost;
    }

    public function GetMessages($CorpNum, $ReceiptNum, $UserID = null)
    {
        if (empty($ReceiptNum)) {
            throw new PopbillException('카카오톡 접수번호를 입력하지 않았습니다.');
        }
        $response = $this->executeCURL('/KakaoTalk/' . $ReceiptNum, $CorpNum, $UserID);
        $DetailInfo = new KakaoSentInfo();
        $DetailInfo->fromJsonInfo($response);

        return $DetailInfo;
    }

    public function GetMessagesRN($CorpNum, $RequestNum, $UserID = null)
    {
        if (empty($RequestNum)) {
            throw new PopbillException('카카오톡 전송요청번호를 입력하지 않았습니다.');
        }
        $response = $this->executeCURL('/KakaoTalk/Get/' . $RequestNum, $CorpNum, $UserID);
        $DetailInfo = new KakaoSentInfo();
        $DetailInfo->fromJsonInfo($response);

        return $DetailInfo;
    }

    public function ListPlusFriendID($CorpNum)
    {
        $PlusFriendList = array();
        $response = $this->executeCURL('/KakaoTalk/ListPlusFriendID', $CorpNum);

        for ($i = 0; $i < Count($response); $i++) {
            $PlusFriendObj = new PlusFriend();
            $PlusFriendObj->fromJsonInfo($response[$i]);
            $PlusFriendList[$i] = $PlusFriendObj;
        }

        return $PlusFriendList;
    }

    public function ListATSTemplate($CorpNum)
    {
        $result = $this->executeCURL('/KakaoTalk/ListATSTemplate', $CorpNum);

        $TemplateList = array();
        for ($i = 0; $i < Count($result); $i++) {
            $TemplateObj = new ATSTemplate();
            $TemplateObj->fromJsonInfo($result[$i]);
            $TemplateList[$i] = $TemplateObj;
        }

        return $TemplateList;
    }

    public function GetSenderNumberList($CorpNum)
    {
        return $this->executeCURL('/Message/SenderNumber', $CorpNum);
    }

    public function CancelReserve($CorpNum, $ReceiptNum, $UserID = null)
    {
        if (empty($ReceiptNum)) {
            throw new PopbillException('예약전송을 취소할 접수번호를 입력하지 않았습니다.');
        }
        return $this->executeCURL('/KakaoTalk/' . $ReceiptNum . '/Cancel', $CorpNum, $UserID);
    }

    public function CancelReserveRN($CorpNum, $RequestNum, $UserID = null)
    {
        if (empty($RequestNum)) {
            throw new PopbillException('예약전송을 취소할 전송요청번호를 입력하지 않았습니다.');
        }
        return $this->executeCURL('/KakaoTalk/Cancel/' . $RequestNum, $CorpNum, $UserID);
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

    //플러스친구 계정관리 팝업 URL
    public function GetPlusFriendMgtURL($CorpNum, $UserID)
    {
        $response = $this->executeCURL('/KakaoTalk/?TG=PLUSFRIEND', $CorpNum, $UserID);
        return $response->url;
    }

    //발신번호 관리 팝업 URL
    public function GetSenderNumberMgtURL($CorpNum, $UserID)
    {
        $response = $this->executeCURL('/Message/?TG=SENDER', $CorpNum, $UserID);
        return $response->url;
    }

    //알림톡 템플릿관리 팝업 URL
    public function GetATSTemplateMgtURL($CorpNum, $UserID)
    {
        $response = $this->executeCURL('/KakaoTalk/?TG=TEMPLATE', $CorpNum, $UserID);
        return $response->url;
    }

    //알림톡 템플릿 정보 확인
    public function GetATSTemplate($CorpNum, $TemplateCode, $UserID = null)
    {
        if (is_null($TemplateCode) || $TemplateCode === "") {
            throw new PopbillException('템플릿코드가 입력되지 않았습니다.');
        }

        $result = $this->executeCURL('/KakaoTalk/GetATSTemplate/'.$TemplateCode, $CorpNum, $UserID);

        $TemplateInfo = new ATSTemplate();
        $TemplateInfo->fromJsonInfo($result);

        return $TemplateInfo;
    }

    //카카오톡 전송내역 팝업 URL
    public function GetSentListURL($CorpNum, $UserID)
    {
        $response = $this->executeCURL('/KakaoTalk/?TG=BOX', $CorpNum, $UserID);
        return $response->url;
    }


    public function Search($CorpNum, $SDate, $EDate, $State = array(), $Item = array(), $ReserveYN = '', $SenderYN = false, $Page = null, $PerPage = null, $Order = null, $UserID = null, $QString = null)
    {
        if (is_null($SDate) || $SDate === "") {
            throw new PopbillException('시작일자가 입력되지 않았습니다.');
        }

        if (is_null($EDate) || $EDate === "") {
            throw new PopbillException('종료일자가 입력되지 않았습니다.');
        }

        $uri = '/KakaoTalk/Search?SDate=' . $SDate;
        $uri .= '&EDate=' . $EDate;

        if (!is_null($State) || !empty($State)) {
            $uri .= '&State=' . implode(',', $State);
        }
        if (!is_null($Item) || !empty($Item)) {
            $uri .= '&Item=' . implode(',', $Item);
        }

        $uri .= '&ReserveYN=' . $ReserveYN;

        if ($SenderYN) {
            $uri .= '&SenderYN=1';
        }

        $uri .= '&Page=' . $Page;
        $uri .= '&PerPage=' . $PerPage;
        $uri .= '&Order=' . $Order;

        if (!is_null($QString) || !empty($QString)) {
            $uri .= '&QString=' . $QString;
        }

        $response = $this->executeCURL($uri, $CorpNum, $UserID);

        $SearchList = new KakaoSearchResult();
        $SearchList->fromJsonInfo($response);

        return $SearchList;

    }

    public function GetChargeInfo($CorpNum, $MessageType, $UserID = null)
    {
        $uri = '/KakaoTalk/ChargeInfo?Type=' . $MessageType;

        $response = $this->executeCURL($uri, $CorpNum, $UserID);
        $ChargeInfo = new ChargeInfo();
        $ChargeInfo->fromJsonInfo($response);

        return $ChargeInfo;
    }

    public function SendFMS($CorpNum, $PlusFriendID, $Sender, $Content, $AltContent, $AltSendType, $AdsYN, $Messages = array(), $Btns = array(), $ReserveDT = null, $FilePaths = array(), $ImageURL = null, $UserID = null, $RequestNum = null)
    {

        $Request = array();

        if (empty($PlusFriendID) == false) $Request['plusFriendID'] = $PlusFriendID;
        if (empty($Sender) == false) $Request['snd'] = $Sender;
        if (empty($Content) == false) $Request['content'] = $Content;
        if (empty($AltContent) == false) $Request['altContent'] = $AltContent;
        if (empty($AltSendType) == false) $Request['altSendType'] = $AltSendType;
        if (empty($ReserveDT) == false) $Request['sndDT'] = $ReserveDT;
        if (empty($AdsYN) == false) $Request['adsYN'] = $AdsYN;
        if (empty($ImageURL) == false) $Request['imageURL'] = $ImageURL;
        if (empty($RequestNum) == false) $Request['requestNum'] = $RequestNum;

        $Request['msgs'] = $Messages;
        $Request['btns'] = $Btns;
        $postdata = array();
        $postdata['form'] = json_encode($Request);

        $i = 0;

        foreach ($FilePaths as $FilePath) {
            $postdata['file'] = '@' . $FilePath;
        }

        return $this->executeCURL('/FMS', $CorpNum, $UserID, true, null, $postdata, true)->receiptNum;
    }

    public function SendFTS($CorpNum, $PlusFriendID, $Sender, $Content, $AltContent, $AltSendType, $AdsYN, $Messages = array(), $Btns = array(), $ReserveDT = null, $UserID = null, $RequestNum = null)
    {
        $Request = array();

        if (empty($PlusFriendID) == false) $Request['plusFriendID'] = $PlusFriendID;
        if (empty($Sender) == false) $Request['snd'] = $Sender;
        if (empty($Content) == false) $Request['content'] = $Content;
        if (empty($AltContent) == false) $Request['altContent'] = $AltContent;
        if (empty($AltSendType) == false) $Request['altSendType'] = $AltSendType;
        if (empty($ReserveDT) == false) $Request['sndDT'] = $ReserveDT;
        if (empty($AdsYN) == false) $Request['adsYN'] = $AdsYN;
        if (empty($RequestNum) == false) $Request['requestNum'] = $RequestNum;

        $Request['msgs'] = $Messages;
        $Request['btns'] = $Btns;
        $postdata = json_encode($Request);

        return $this->executeCURL('/FTS', $CorpNum, $UserID, true, null, $postdata)->receiptNum;
    }

    public function SendATS($CorpNum, $TemplateCode, $Sender, $Content, $AltContent, $AltSendType, $Messages = array(), $ReserveDT = null, $UserID = null, $RequestNum = null, $Btns = null)
    {
        $Request = array();

        if (empty($TemplateCode) == false) $Request['templateCode'] = $TemplateCode;
        if (empty($Sender) == false) $Request['snd'] = $Sender;
        if (empty($Content) == false) $Request['content'] = $Content;
        if (empty($AltContent) == false) $Request['altContent'] = $AltContent;
        if (empty($AltSendType) == false) $Request['altSendType'] = $AltSendType;
        if (empty($ReserveDT) == false) $Request['sndDT'] = $ReserveDT;
        if (empty($RequestNum) == false) $Request['requestNum'] = $RequestNum;
        $Request['msgs'] = $Messages;
        if (is_null($Btns) == false) $Request['btns'] = $Btns;

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

    public function fromJsonInfo($jsonInfo)
    {
        isset($jsonInfo->templateCode) ? $this->templateCode = $jsonInfo->templateCode : null;
        isset($jsonInfo->templateName) ? $this->templateName = $jsonInfo->templateName : null;
        isset($jsonInfo->template) ? $this->template = $jsonInfo->template : null;
        isset($jsonInfo->plusFriendID) ? $this->plusFriendID = $jsonInfo->plusFriendID : null;
        isset($jsonInfo->ads) ? $this->ads = $jsonInfo->ads : null;
        isset($jsonInfo->appendix) ? $this->appendix = $jsonInfo->appendix : null;

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

    function fromJsonInfo($jsonInfo)
    {
        isset($jsonInfo->n) ? $this->n = $jsonInfo->n : null;
        isset($jsonInfo->t) ? $this->t = $jsonInfo->t : null;
        isset($jsonInfo->u1) ? $this->u1 = $jsonInfo->u1 : null;
        isset($jsonInfo->u2) ? $this->u2 = $jsonInfo->u2 : null;
    }
}

class PlusFriend
{
    public $plusFriendID;
    public $plusFriendName;
    public $regDT;

    function fromJsonInfo($jsonInfo)
    {
        isset($jsonInfo->plusFriendID) ? $this->plusFriendID = $jsonInfo->plusFriendID : null;
        isset($jsonInfo->plusFriendName) ? $this->plusFriendName = $jsonInfo->plusFriendName : null;
        isset($jsonInfo->regDT) ? $this->regDT = $jsonInfo->regDT : null;
    }
}


?>
