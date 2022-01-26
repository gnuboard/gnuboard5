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
 * Written : 2014-04-15
 * Contributor : Jeong YoHan (code@linkhubcorp.com)
 * Updated : 2021-12-09
 *
 * Thanks for your interest.
 * We welcome any suggestions, feedbacks, blames or anything.
 * ======================================================================================
 */
require_once G5_LIB_PATH.'/popbill/popbill.php';

class MessagingService extends PopbillBase
{

    public function __construct($linkid, $secretkey)
    {
        parent::__construct($linkid, $secretkey);
        $this->AddScope('150');
        $this->AddScope('151');
        $this->AddScope('152');
    }

    //발행단가 확인
    public function GetUnitCost($corpnum, $MessageType)
    {
        return $this->executeCURL('/Message/UnitCost?Type=' . $MessageType, $corpnum)->unitCost;
    }


    /* 단문메시지 전송
    *	$corpnum => 발송사업자번호
    *	$Sender	=> 동보전송용 발신번호 미기재시 개별메시지 발신번호로 전송. 발신번호가 없는 개별메시지에만 동보처리함.
    *	$Content => 동보전송용 발신내용 미기재시 개별메시지 내용으로 전송, 발신내용이 없는 개별메시지에만 동보처리함.
    *	$Messages => 발신메시지 최대 1000건, 배열
    *		'snd' => 개별발신번호
    *		'rcv' => 수신번호, 필수
    *		'rcvnm' => 수신자 성명
    *		'msg' => 메시지 내용, 미기재시 동보메시지로 전송함.
    *	$ReserveDT	=> 예약전송시 예약시간 yyyyMMddHHmmss 형식으로 기재
    *	$UserID		=> 발신자 팝빌 회원아이디
    *	$SenderName	=> 동보전송용 발신자명 미기재시 개별메시지 발신자명으로 전송
    */
    public function SendSMS($corpnum, $Sender, $Content, $Messages = array(), $ReserveDT = null, $adsYN = false, $UserID = null, $SenderName = null, $SystemYN = false, $RequestNum = null)
    {
        return $this->SendMessage(ENumMessageType::SMS, $corpnum, $Sender, $SenderName, null, $Content, $Messages, $ReserveDT, $adsYN, $UserID, $SystemYN, $RequestNum);
    }

    /* 장문메시지 전송
    *	$corpnum => 발송사업자번호
    *	$Sender	=> 동보전송용 발신번호 미기재시 개별메시지 발신번호로 전송. 발신번호가 없는 개별메시지에만 동보처리함.
    *	$Subject => 동보전송용 제목 미기재시 개별메시지 제목으로 전송, 제목이 없는 개별메시지에만 동보처리함.
    *	$Content => 동보전송용 발신내용 미기재시 개별베시지 내용으로 전송, 발신내용이 없는 개별메시지에만 동보처리함.
    *	$Messages => 발신메시지 최대 1000건, 배열
    *		'snd' => 개별발신번호
    *		'rcv' => 수신번호, 필수
    *		'rcvnm' => 수신자 성명
    *		'msg' => 메시지 내용, 미기재시 동보메시지로 전송함.
    *		'sjt' => 제목, 미기재시 동보 제목으로 전송함.
  	*	$ReserveDT	=> 예약전송시 예약시간 yyyyMMddHHmmss 형식으로 기재
  	*	$UserID		=> 발신자 팝빌 회원아이디
    *	$SenderName	=> 동보전송용 발신자명 미기재시 개별메시지 발신자명으로 전송
    */
    public function SendLMS($corpnum, $Sender, $Subject, $Content, $Messages = array(), $ReserveDT = null, $adsYN = false, $UserID = null, $SenderName = null, $SystemYN = false, $RequestNum = null)
    {
        return $this->SendMessage(ENumMessageType::LMS, $corpnum, $Sender, $SenderName, $Subject, $Content, $Messages, $ReserveDT, $adsYN, $UserID, $SystemYN, $RequestNum);
    }

    /* 장/단문메시지 전송 - 메지시 길이에 따라 단문과 장문을 선택하여 전송합니다.
    *	$corpnum => 발송사업자번호
    *	$Sender	=> 동보전송용 발신번호 미기재시 개별메시지 발신번호로 전송. 발신번호가 없는 개별메시지에만 동보처리함.
    *	$Subject => 동보전송용 제목 미기재시 개별메시지 제목으로 전송, 제목이 없는 개별메시지에만 동보처리함.
    *	$Content => 동보전송용 발신내용 미기재시 개별베시지 내용으로 전송, 발신내용이 없는 개별메시지에만 동보처리함.
    *	$Messages => 발신메시지 최대 1000건, 배열
    *		'snd' => 개별발신번호
    *		'rcv' => 수신번호, 필수
    *		'rcvnm' => 수신자 성명
    *		'msg' => 메시지 내용, 미기재시 동보메시지로 전송함.
    *		'sjt' => 제목, 미기재시 동보 제목으로 전송함.
    *	$ReserveDT	=> 예약전송시 예약시간 yyyyMMddHHmmss 형식으로 기재
    *	$UserID		=> 발신자 팝빌 회원아이디
    *	$SenderName	=> 동보전송용 발신자명 미기재시 개별메시지 발신자명으로 전송
    */
    public function SendXMS($corpnum, $Sender, $Subject, $Content, $Messages = array(), $ReserveDT = null, $adsYN = false, $UserID = null, $SenderName = null, $SystemYN = false, $RequestNum = null)
    {
        return $this->SendMessage(ENumMessageType::XMS, $corpnum, $Sender, $SenderName, $Subject, $Content, $Messages, $ReserveDT, $adsYN, $UserID, $SystemYN, $RequestNum);
    }

    /* MMS 메시지 전송
    *	$corpnum => 발송사업자번호
    *	$Sender	=> 동보전송용 발신번호 미기재시 개별메시지 발신번호로 전송. 발신번호가 없는 개별메시지에만 동보처리함.
    *	$Subject => 동보전송용 제목 미기재시 개별메시지 제목으로 전송, 제목이 없는 개별메시지에만 동보처리함.
    *	$Content => 동보전송용 발신내용 미기재시 개별베시지 내용으로 전송, 발신내용이 없는 개별메시지에만 동보처리함.
    *	$Messages => 발신메시지 최대 1000건, 배열
    *		'snd' => 개별발신번호
    *		'rcv' => 수신번호, 필수
    *		'rcvnm' => 수신자 성명
    *		'msg' => 메시지 내용, 미기재시 동보메시지로 전송함.
    *		'sjt' => 제목, 미기재시 동보 제목으로 전송함.
    *	$FilePaths	=> 전송할 파일경로 문자열
      *	$ReserveDT	=> 예약전송시 예약시간 yyyyMMddHHmmss 형식으로 기재
      *	$UserID		=> 발신자 팝빌 회원아이디
    *	$SenderName	=> 동보전송용 발신자명 미기재시 개별메시지 발신자명으로 전송
    */
    public function SendMMS($corpnum, $Sender, $Subject, $Content, $Messages = array(), $FilePaths = array(), $ReserveDT = null, $adsYN = false, $UserID = null, $SenderName = null, $SystemYN = false, $RequestNum = null)
    {
        if (empty($Messages)) {
            throw new PopbillException('전송할 메시지가 입력되지 않았습니다.');
        }

        if (empty($FilePaths)) {
            throw new PopbillException('발신파일 목록이 입력되지 않았습니다.');
        }

        $Request = array();

        if (empty($Sender) == false) $Request['snd'] = $Sender;
        if (empty($SenderName) == false) $Request['sndnm'] = $SenderName;
        if (empty($Content) == false) $Request['content'] = $Content;
        if (empty($Subject) == false) $Request['subject'] = $Subject;
        if (empty($ReserveDT) == false) $Request['sndDT'] = $ReserveDT;
        if (empty($RequestNum) == false) $Request['requestNum'] = $RequestNum;

        if ($adsYN) $Request['adsYN'] = $adsYN;
        if ($SystemYN) $Request['systemYN'] = $SystemYN;

        $Request['msgs'] = $Messages;

        $postdata = array();
        $postdata['form'] = json_encode($Request);

        $i = 0;

        foreach ($FilePaths as $FilePath) {
            $postdata['file'] = '@' . $FilePath;
        }

        return $this->executeCURL('/MMS', $corpnum, $UserID, true, null, $postdata, true)->receiptNum;
    }


    /* 전송메시지 내역 및 전송상태 확인
    *	$corpnum => 발송사업자번호
    *	$ReceiptNum	=> 접수번호
    *	$UserID	=> 팝빌 회원아이디
    */
    public function GetMessages($corpnum, $ReceiptNum, $UserID = null)
    {
        if (empty($ReceiptNum)) {
            throw new PopbillException('확인할 접수번호를 입력하지 않았습니다.');
        }
        $result = $this->executeCURL('/Message/' . $ReceiptNum, $corpnum, $UserID);

        $MessageInfoList = array();

        for ($i = 0; $i < Count($result); $i++) {
            $MsgInfo = new MessageInfo();
            $MsgInfo->fromJsonInfo($result[$i]);
            $MessageInfoList[$i] = $MsgInfo;
        }
        return $MessageInfoList;
    }

    /* 전송메시지 내역 및 전송상태 확인
*	$corpnum => 발송사업자번호
*	$RequestNum	=> 전송요청번호
*	$UserID	=> 팝빌 회원아이디
*/
    public function GetMessagesRN($corpnum, $RequestNum, $UserID = null)
    {
        if (empty($RequestNum)) {
            throw new PopbillException('확인할 전송요청번호를 입력하지 않았습니다.');
        }
        $result = $this->executeCURL('/Message/Get/' . $RequestNum, $corpnum, $UserID);

        $MessageInfoList = array();

        for ($i = 0; $i < Count($result); $i++) {
            $MsgInfo = new MessageInfo();
            $MsgInfo->fromJsonInfo($result[$i]);
            $MessageInfoList[$i] = $MsgInfo;
        }
        return $MessageInfoList;
    }

    /* 예약전송 취소
    *	$corpnum => 발송사업자번호
    *	$ReceiptNum	=> 접수번호
    *	$UserID	=> 팝빌 회원아이디
    */
    public function CancelReserve($corpnum, $ReceiptNum, $UserID = null)
    {
        if (empty($ReceiptNum)) {
            throw new PopbillException('예약전송 취소할 접수번호를 입력하지 않았습니다.');
        }
        return $this->executeCURL('/Message/' . $ReceiptNum . '/Cancel', $corpnum, $UserID);
    }

    /* 예약전송 취소
*	$corpnum => 발송사업자번호
*	$RequestNum	=> 전송요청번호
*	$UserID	=> 팝빌 회원아이디
*/
    public function CancelReserveRN($corpnum, $RequestNum, $UserID = null)
    {
        if (empty($RequestNum)) {
            throw new PopbillException('예약전송 취소할 전송요청번호를 입력하지 않았습니다.');
        }
        return $this->executeCURL('/Message/Cancel/' . $RequestNum, $corpnum, $UserID);
    }

    private function SendMessage($MessageType, $corpnum, $Sender, $SenderName, $Subject, $Content, $Messages = array(), $ReserveDT = null, $adsYN = false, $UserID = null, $SystemYN = false, $RequestNum = null)
    {
        if (empty($Messages)) {
            throw new PopbillException('전송할 메시지가 입력되지 않았습니다.');
        }

        $Request = array();

        if (empty($Sender) == false) $Request['snd'] = $Sender;
        if (empty($SenderName) == false) $Request['sndnm'] = $SenderName;
        if (empty($Content) == false) $Request['content'] = $Content;
        if (empty($Subject) == false) $Request['subject'] = $Subject;
        if (empty($ReserveDT) == false) $Request['sndDT'] = $ReserveDT;
        if (empty($RequestNum) == false) $Request['requestNum'] = $RequestNum;

        if ($adsYN) $Request['adsYN'] = $adsYN;
        if ($SystemYN) $Request['systemYN'] = $SystemYN;

        $Request['msgs'] = $Messages;

        $postdata = json_encode($Request);
        return $this->executeCURL('/' . $MessageType, $corpnum, $UserID, true, null, $postdata)->receiptNum;
    }

    //문자 관련 URL함수
    public function GetURL($corpnum, $UserID, $TOGO)
    {
        $response = $this->executeCURL('/Message/?TG=' . $TOGO, $corpnum, $UserID);
        return $response->url;
    }

    //문자 전송내역 팝업 URL
    public function GetSentListURL($corpnum, $UserID)
    {
        $response = $this->executeCURL('/Message/?TG=BOX', $corpnum, $UserID);
        return $response->url;
    }

    //발신번호 관리 팝업 URL
    public function GetSenderNumberMgtURL($corpnum, $UserID)
    {
        $response = $this->executeCURL('/Message/?TG=SENDER', $corpnum, $UserID);
        return $response->url;
    }

    //문자 전송내역 조회
    public function Search($corpnum, $SDate, $EDate, $State = array(), $Item = array(), $ReserveYN = false, $SenderYN = false, $Page = null, $PerPage = null, $Order = null, $UserID = null, $QString = null)
    {
        if (is_null($SDate) || $SDate === "") {
            throw new PopbillException(-99999999, '시작일자가 입력되지 않았습니다.');
        }

        if (is_null($EDate) || $EDate === "") {
            throw new PopbillException(-99999999, '종료일자가 입력되지 않았습니다.');
        }

        $uri = '/Message/Search?SDate=' . $SDate;
        $uri .= '&EDate=' . $EDate;

        if (!is_null($State) || !empty($State)) {
            $uri .= '&State=' . implode(',', $State);
        }
        if (!is_null($Item) || !empty($Item)) {
            $uri .= '&Item=' . implode(',', $Item);
        }

        if ($ReserveYN) {
            $uri .= '&ReserveYN=1';
        }
        if ($SenderYN) {
            $uri .= '&SenderYN=1';
        }

        $uri .= '&Page=' . $Page;
        $uri .= '&PerPage=' . $PerPage;
        $uri .= '&Order=' . $Order;

        if (!is_null($QString) || !empty($QString)) {
            $uri .= '&QString=' . $QString;
        }

        $response = $this->executeCURL($uri, $corpnum, $UserID);

        $SearchList = new MsgSearchResult();
        $SearchList->fromJsonInfo($response);

        return $SearchList;
    }

    // 080 수신거부목록 조회
    public function GetAutoDenyList($corpnum)
    {
        return $this->executeCURL('/Message/Denied', $corpnum);
    }

    public function GetChargeInfo($corpnum, $MessageType, $UserID = null)
    {
        $uri = '/Message/ChargeInfo?Type=' . $MessageType;

        $response = $this->executeCURL($uri, $corpnum, $UserID);
        $ChargeInfo = new ChargeInfo();
        $ChargeInfo->fromJsonInfo($response);

        return $ChargeInfo;
    }

    // 발신번호 목록 조회
    public function GetSenderNumberList($corpnum, $UserID = null)
    {
        return $this->executeCURL('/Message/SenderNumber', $corpnum, $UserID);
    }

    // 문자전송결과
    public function GetStates($corpnum, $ReceiptNumList = array(), $UserID = null)
    {
        if (is_null($ReceiptNumList) || empty($ReceiptNumList)) {
            throw new PopbillException('접수번호가 입력되지 않았습니다.');
        }

        $postdata = json_encode($ReceiptNumList);
        $result = $this->executeCURL('/Message/States', $corpnum, $UserID, true, null, $postdata);
        $MsgInfoList = array();

        for ($i = 0; $i < Count($result); $i++) {
            $MsgInfo = new MessageBriefInfo();
            $MsgInfo->fromJsonInfo($result[$i]);
            $MsgInfoList[$i] = $MsgInfo;
        }

        return $MsgInfoList;
    }

}

class ENumMessageType
{
    const SMS = 'SMS';
    const LMS = 'LMS';
    const XMS = 'XMS';
    const MMS = 'MMS';
}

class MsgSearchResult
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
            $InfoObj = new MessageInfo();
            $InfoObj->fromJsonInfo($jsonInfo->list[$i]);
            $InfoList[$i] = $InfoObj;
        }
        $this->list = $InfoList;
    }
}


class MessageInfo
{
    public $state;
    public $result;
    public $subject;
    public $type;
    public $content;
    public $tranNet;
    public $sendNum;
    public $senderName;
    public $receiveNum;
    public $receiveName;
    public $reserveDT;
    public $sendDT;
    public $resultDT;
    public $sendResult;
    public $receiptDT;
    public $receiptNum;
    public $requestNum;

    function fromJsonInfo($jsonInfo)
    {
        isset($jsonInfo->state) ? $this->state = $jsonInfo->state : null;
        isset($jsonInfo->result) ? $this->result = $jsonInfo->result : null;
        isset($jsonInfo->subject) ? $this->subject = $jsonInfo->subject : null;
        isset($jsonInfo->tranNet) ? $this->tranNet = $jsonInfo->tranNet : null;
        isset($jsonInfo->type) ? $this->type = $jsonInfo->type : null;
        isset($jsonInfo->content) ? $this->content = $jsonInfo->content : null;
        isset($jsonInfo->sendNum) ? $this->sendNum = $jsonInfo->sendNum : null;
        isset($jsonInfo->senderName) ? $this->senderName = $jsonInfo->senderName : null;
        isset($jsonInfo->receiveNum) ? $this->receiveNum = $jsonInfo->receiveNum : null;
        isset($jsonInfo->receiveName) ? $this->receiveName = $jsonInfo->receiveName : null;
        isset($jsonInfo->reserveDT) ? $this->reserveDT = $jsonInfo->reserveDT : null;
        isset($jsonInfo->sendDT) ? $this->sendDT = $jsonInfo->sendDT : null;
        isset($jsonInfo->resultDT) ? $this->resultDT = $jsonInfo->resultDT : null;
        isset($jsonInfo->sendResult) ? $this->sendResult = $jsonInfo->sendResult : null;
        isset($jsonInfo->receiptDT) ? $this->receiptDT = $jsonInfo->receiptDT : null;
        isset($jsonInfo->receiptNum) ? $this->receiptNum = $jsonInfo->receiptNum : null;
        isset($jsonInfo->requestNum) ? $this->requestNum = $jsonInfo->requestNum : null;
    }
}

class MessageBriefInfo
{
    public $sn;
    public $rNum;
    public $stat;
    public $sDT;
    public $rDT;
    public $rlt;
    public $net;
    public $srt;

    function fromJsonInfo($jsonInfo)
    {
        isset($jsonInfo->sn) ? $this->sn = $jsonInfo->sn : null;
        isset($jsonInfo->rNum) ? $this->rNum = $jsonInfo->rNum : null;
        isset($jsonInfo->stat) ? $this->stat = $jsonInfo->stat : null;
        isset($jsonInfo->sDT) ? $this->sDT = $jsonInfo->sDT : null;
        isset($jsonInfo->rDT) ? $this->rDT = $jsonInfo->rDT : null;
        isset($jsonInfo->rlt) ? $this->rlt = $jsonInfo->rlt : null;
        isset($jsonInfo->net) ? $this->net = $jsonInfo->net : null;
        isset($jsonInfo->srt) ? $this->srt = $jsonInfo->srt : null;
    }
}

?>
