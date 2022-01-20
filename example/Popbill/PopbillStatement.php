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
* Written : 2014-09-04
* Contributor : Jeong YoHan (code@linkhubcorp.com)
* Updated : 2021-12-23
*
* Thanks for your interest.
* We welcome any suggestions, feedbacks, blames or anything.
* ======================================================================================
*/
require_once 'popbill.php';

class StatementService extends PopbillBase {
  public function __construct($LinkID,$SecretKey) {
    parent::__construct($LinkID,$SecretKey);
    $this->AddScope('121');
    $this->AddScope('122');
    $this->AddScope('123');
	$this->AddScope('124');
	$this->AddScope('125');
	$this->AddScope('126');
  }

	#전자명세서 발행단가 확인
	public function GetUnitCost($CorpNum,$itemCode){
    	return $this->executeCURL('/Statement/'.$itemCode.'?cfg=UNITCOST',$CorpNum)->unitCost;
	}

	#문서문서번호 사용유무 확인
	public function CheckMgtKeyInuse($CorpNum, $itemCode, $MgtKey) {
    	if(is_null($MgtKey) || empty($MgtKey)) {
    		throw new PopbillException('문서번호가 입력되지 않았습니다.');
    	}
		try{
			$response = $this->executeCURL('/Statement/'.$itemCode."/".$MgtKey,$CorpNum);
			return is_null($response->itemKey) == false;
		}catch(PopbillException $pe){
			if($pe->getCode() == -12000004) {return false;}
		}
	}

	# 전자명세서 선팩스 전송
	public function FAXSend($CorpNum,$Statement,$SendNum,$ReceiveNum,$UserID = null){
		if(!is_null($SendNum) || !empty($SendNum)){
			$Statement->sendNum = $SendNum;
		}
		if(!is_null($ReceiveNum) || !empty($ReceiveNum)){
			$Statement->receiveNum = $ReceiveNum;
		}

		$postdata = json_encode($Statement);
    	return $this->executeCURL('/Statement',$CorpNum,$UserID,true,'FAX',$postdata)->receiptNum;
	}

	# 전자명세서 즉시발행
	public function RegistIssue($CorpNum,$Statement,$memo,$UserID = null, $EmailSubject = null){
		if(!is_null($memo) || !empty($memo)){
			$Statement->memo = $memo;
		}

    if(!is_null($EmailSubject) || !empty($EmailSubject)){
			$Statement->emailSubject = $EmailSubject;
		}

		$postdata = json_encode($Statement);
    return $this->executeCURL('/Statement',$CorpNum,$UserID,true,'ISSUE',$postdata);
	}

	# 전자명세서 임시저장
    public function Register($CorpNum, $Statement, $UserID = null) {
    	$postdata = json_encode($Statement);
    	return $this->executeCURL('/Statement',$CorpNum,$UserID,true,null,$postdata);
    }

	# 전자명세서 수정
	public function Update($CorpNum, $itemCode, $MgtKey, $Statement, $UserID = null) {
    	if(is_null($MgtKey) || empty($MgtKey)) {
    		throw new PopbillException('문서번호가 입력되지 않았습니다.');
    	}
		$postdata = json_encode($Statement);
		return $this->executeCURL('/Statement/'.$itemCode."/".$MgtKey,$CorpNum,$UserID,true,"PATCH",$postdata);
	}

	# 전자명세서 삭제
	public function Delete($CorpNum,$itemCode,$MgtKey,$UserID = null) {
    	if(is_null($MgtKey) || empty($MgtKey)) {
    		throw new PopbillException('문서번호가 입력되지 않았습니다.');
    	}
    	return $this->executeCURL('/Statement/'.$itemCode."/".$MgtKey, $CorpNum, $UserID,true,'DELETE','');
    }


	# 전자명세서 발행
	public function Issue($CorpNum, $itemCode, $MgtKey, $Memo, $UserID = null, $EmailSubject = null){
    	if(is_null($MgtKey) || empty($MgtKey)) {
    		throw new PopbillException('문서번호가 입력되지 않았습니다.');
    	}

		  $Request = new IssueRequest();
    	$Request->memo = $Memo;

      if(!is_null($EmailSubject) || !empty($EmailSubject)){
  			$Request->emailSubject = $EmailSubject;
  		}

    	$postdata = json_encode($Request);

		return $this->executeCURL('/Statement/'.$itemCode."/".$MgtKey, $CorpNum, $UserID, true, 'ISSUE',$postdata);
	}


	# 전자명세서 발행취소
	public function CancelIssue($CorpNum, $itemCode, $MgtKey, $Memo, $UserID = null){
    	if(is_null($MgtKey) || empty($MgtKey)) {
    		throw new PopbillException('문서번호가 입력되지 않았습니다.');
    	}
		$Request = new MemoRequest();
		$Request->memo = $Memo;
		$postdata = json_encode($Request);

		return $this->executeCURL('/Statement/'.$itemCode."/".$MgtKey, $CorpNum, $UserID, true, 'CANCEL',$postdata);
	}

	# 전자명세서 첨부파일 추가
	public function AttachFile($CorpNum,$itemCode,$MgtKey,$FilePath,$UserID = null){
    	if(is_null($MgtKey) || empty($MgtKey)) {
    		throw new PopbillException('문서번호가 입력되지 않았습니다.');
    	}

		if (mb_detect_encoding($this->GetBasename($FilePath)) == 'CP949') {
            $FilePath = iconv('CP949', 'UTF-8', $FilePath);
        }

        $FileName = $this->GetBasename($FilePath);
        $postdata = array('Filedata' => '@' . $FilePath . ';filename=' . $FileName);

		return $this->executeCURL('/Statement/'.$itemCode.'/'.$MgtKey.'/Files',$CorpNum,$UserID, true, null, $postdata, true);
	}

	# 첨부파일 목록확인
	public function GetFiles($CorpNum,$itemCode,$MgtKey,$UserID = null){
		if(is_null($MgtKey) || empty($MgtKey)) {
    		throw new PopbillException('문서번호가 입력되지 않았습니다.');
    	}
		return $this->executeCURL('/Statement/'.$itemCode.'/'.$MgtKey.'/Files',$CorpNum,$UserID);
	}

	# 첨부파일 삭제
	public function DeleteFile($CorpNum,$itemCode,$MgtKey,$FileID,$UserID = null){
		if(is_null($MgtKey) || empty($MgtKey)) {
    		throw new PopbillException('문서번호가 입력되지 않았습니다.');
    	}
		return $this->executeCURL('/Statement/'.$itemCode.'/'.$MgtKey.'/Files/'.$FileID,$CorpNum,$UserID, true,'DELETE',null);
	}

	# 다량 전자명세서 상태,요약 정보확인
	public function GetInfo($CorpNum,$itemCode,$MgtKey,$UserID = null){
		if(is_null($MgtKey) || empty($MgtKey)) {
    		throw new PopbillException('문서번호가 입력되지 않았습니다.');
    	}
		$result = $this->executeCURL('/Statement/'.$itemCode.'/'.$MgtKey,$CorpNum,$UserID);

		$StatementInfo = new StatementInfo();
		$StatementInfo->fromJsonInfo($result);
		return $StatementInfo;
	}

	# 다량 전자명세서 상태,요약 정보확인
	public function GetInfos($CorpNum,$itemCode,$MgtKeyList,$UserID = null){
		if(is_null($MgtKeyList) || empty($MgtKeyList)) {
    		throw new PopbillException('문서번호배열이 입력되지 않았습니다.');
    	}
		$postdata = json_encode($MgtKeyList);
		$result = $this->executeCURL('/Statement/'.$itemCode, $CorpNum,null,true,null,$postdata);

		$StatementInfoList = array();

		for($i=0; $i<Count($result); $i++){
			$StmtInfoObj = new StatementInfo();
			$StmtInfoObj->fromJsonInfo($result[$i]);
			$StatementInfoList[$i] = $StmtInfoObj;
		}

		return $StatementInfoList;
	}

	# 이력 확인
	public function GetLogs($CorpNum,$itemCode,$MgtKey){
		if(is_null($MgtKey) || empty($MgtKey)) {
    		throw new PopbillException('문서번호가 입력되지 않았습니다.');
    	}
		$result = $this->executeCURL('/Statement/'.$itemCode.'/'.$MgtKey.'/Logs',$CorpNum);

		$StatementLogList = array();

		for($i=0; $i<Count($result); $i++){
			$StmtLog = new StatementLog();
			$StmtLog->fromJsonInfo($result[$i]);
			$StatementLogList[$i] = $StmtLog;
		}
		return $StatementLogList;
	}

	# 상세정보 확인
	public function GetDetailInfo($CorpNum,$itemCode,$MgtKey,$UserID = null){
		if(is_null($MgtKey) || empty($MgtKey)) {
    		throw new PopbillException('문서번호가 입력되지 않았습니다.');
    	}
		$result = $this->executeCURL('/Statement/'.$itemCode.'/'.$MgtKey.'?Detail',$CorpNum,$UserID);

		$StatementDetail = new Statement();
		$StatementDetail->fromJsonInfo($result);

		return $StatementDetail;
	}

	#알림메일 재전송
	public function SendEmail($CorpNum,$itemCode,$MgtKey,$receiver,$UserID = null){
		if(is_null($MgtKey) || empty($MgtKey)) {
    		throw new PopbillException('문서번호가 입력되지 않았습니다.');
    	}
    	$Request = array('receiver' => $receiver);
		$postdata = json_encode($Request);
		return $this->executeCURL('/Statement/'.$itemCode.'/'.$MgtKey,$CorpNum,$UserID,true,'EMAIL',$postdata);
	}

	#알림문자 재전송
	public function SendSMS($CorpNum,$itemCode,$MgtKey,$sender,$receiver,$contents,$UserID = null){
		if(is_null($MgtKey) || empty($MgtKey)) {
    		throw new PopbillException('문서번호가 입력되지 않았습니다.');
    	}

		$Request = array(
					'receiver' => $receiver,
					'sender' => $sender,
					'contents' => $contents
		);

		$postdata = json_encode($Request);
		return $this->executeCURL('/Statement/'.$itemCode.'/'.$MgtKey,$CorpNum,$UserID,true,'SMS',$postdata);
	}


	#전자명세서 팩스전송
	public function SendFAX($CorpNum,$itemCode,$MgtKey,$sender,$receiver,$UserID = null){
		if(is_null($MgtKey) || empty($MgtKey)) {
    		throw new PopbillException('문서번호가 입력되지 않았습니다.');
    	}

		$Request = array (
					'receiver' => $receiver,
					'sender' => $sender
		);

		$postdata = json_encode($Request);
		return $this->executeCURL('/Statement/'.$itemCode.'/'.$MgtKey,$CorpNum,$UserID,true,'FAX',$postdata);
	}

	#팝빌 전자명세서 연결 URL
	public function GetURL($CorpNum, $UserID, $TOGO) {
		return $this->executeCURL('/Statement?TG='.$TOGO, $CorpNum,$UserID)->url;
	}

	#전자명세서 보기 URL
	public function GetPopUpURL($CorpNum,$itemCode,$MgtKey,$UserID = null){
		if(is_null($MgtKey) || empty($MgtKey)) {
    		throw new PopbillException('문서번호가 입력되지 않았습니다.');
    	}
		return $this->executeCURL('/Statement/'.$itemCode.'/'.$MgtKey.'?TG=POPUP',$CorpNum,$UserID)->url;
	}

	#인쇄 URL 호출
	public function GetPrintURL($CorpNum,$itemCode,$MgtKey,$UserID = null){
		if(is_null($MgtKey) || empty($MgtKey)) {
    		throw new PopbillException('문서번호가 입력되지 않았습니다.');
    	}
		return $this->executeCURL('/Statement/'.$itemCode.'/'.$MgtKey.'?TG=PRINT',$CorpNum,$UserID)->url;
	}

  # 뷰 URL
  public function GetViewURL($CorpNum, $itemCode, $MgtKey, $UserID = null)
  {
      if (is_null($MgtKey) || empty($MgtKey)) {
          throw new PopbillException('문서번호가 입력되지 않았습니다.');
      }

      return $this->executeCURL('/Statement/'.$itemCode.'/'.$MgtKey.'?TG=VIEW', $CorpNum, $UserID)->url;
  }

	#인쇄 URL 호출(공급받는자용)
	public function GetEPrintURL($CorpNum,$itemCode,$MgtKey,$UserID = null){
		if(is_null($MgtKey) || empty($MgtKey)) {
    		throw new PopbillException('문서번호가 입력되지 않았습니다.');
    	}
		return $this->executeCURL('/Statement/'.$itemCode.'/'.$MgtKey.'?TG=EPRINT',$CorpNum,$UserID)->url;
	}

	#다량 인쇄 URL호출
	public function GetMassPrintURL($CorpNum,$itemCode,$MgtKeyList,$UserID = null){
		if(is_null($MgtKeyList) || empty($MgtKeyList)) {
    		throw new PopbillException('문서번호배열이 입력되지 않았습니다.');
    	}
		$postdata = json_encode($MgtKeyList);
		return $this->executeCURL('/Statement/'.$itemCode.'?Print',$CorpNum,$UserID,true,'',$postdata)->url;
	}

	#메일 링크 URL 호출
	public function GetMailURL($CorpNum,$itemCode,$MgtKey,$UserID = null){
		if(is_null($MgtKey) || empty($MgtKey)) {
    		throw new PopbillException('문서번호가 입력되지 않았습니다.');
    	}
		return $this->executeCURL('/Statement/'.$itemCode.'/'.$MgtKey.'?TG=MAIL',$CorpNum,$UserID)->url;
	}

  //전자명세서 목록조회
  public function Search($CorpNum, $DType, $SDate, $EDate, $State = array(), $ItemCode = array(), $Page = null, $PerPage = null, $Order = null, $QString = null){
    if(is_null($DType) || empty($DType)) {
    		throw new PopbillException('조회일자 유형이 입력되지 않았습니다.');
  	}
    if(is_null($SDate) || empty($SDate)) {
    		throw new PopbillException('시작일자가 입력되지 않았습니다.');
  	}
    if(is_null($EDate) || empty($EDate)) {
    		throw new PopbillException('종료일자가 입력되지 않았습니다.');
  	}

    $uri = '/Statement/Search?DType=' . $DType;
    $uri .= '&SDate=' . $SDate;
    $uri .= '&EDate=' . $EDate;

    if( !is_null( $State ) || !empty( $State ) ){
			$uri .= '&State=' . implode(',',$State);
		}

    if( !is_null( $ItemCode ) || !empty( $ItemCode ) ){
			$uri .= '&ItemCode=' . implode(',',$ItemCode);
		}

    if(!is_null($QString) || !empty($QString)){
			$uri .= '&QString=' . $QString;
		}

    $uri .= '&Page=' . $Page;
    $uri .= '&PerPage=' . $PerPage;
    $uri .= '&Order=' . $Order;

    $response = $this->executeCURL($uri,$CorpNum,"");

		$SearchList = new DocSearchResult();
		$SearchList->fromJsonInfo($response);
		return $SearchList;
  }

  //팝빌 인감 및 첨부문서 등록 URL
  public function GetSealURL($CorpNum, $UserID = null) {

    $response = $this->executeCURL('/?TG=SEAL', $CorpNum, $UserID);
    return $response->url;
  }

  // 전자명세서 첨부
  public function AttachStatement ( $CorpNum, $ItemCode, $MgtKey, $SubItemCode, $SubMgtKey, $UserID = null ) {
    $uri = '/Statement/' . $ItemCode . '/' . $MgtKey . '/AttachStmt';

    $Request = new StmtRequest();
  	$Request->ItemCode = $SubItemCode;
		$Request->MgtKey= $SubMgtKey;
   	$postdata = json_encode($Request);

    return $this->executeCURL($uri, $CorpNum, $UserID, true, "", $postdata);
  }

  // 전자명세서 첨부해제
  public function DetachStatement ( $CorpNum, $ItemCode, $MgtKey, $SubItemCode, $SubMgtKey, $UserID = null ) {
    $uri = '/Statement/' . $ItemCode . '/' . $MgtKey . '/DetachStmt';

    $Request = new StmtRequest();
  	$Request->ItemCode = $SubItemCode;
		$Request->MgtKey= $SubMgtKey;
   	$postdata = json_encode($Request);

    return $this->executeCURL($uri, $CorpNum, $UserID, true, "", $postdata);
  }

  // 과금정보 확인
  public function GetChargeInfo ( $CorpNum, $ItemCode, $UserID = null){
    $uri = '/Statement/ChargeInfo/'.$ItemCode;

    $response = $this->executeCURL($uri, $CorpNum, $UserID);
    $ChargeInfo = new ChargeInfo();
    $ChargeInfo->fromJsonInfo($response);

    return $ChargeInfo;
  }

  // 전자명세서 관련 메일전송 항목에 대한 전송여부 목록 반환
  public function ListEmailConfig($CorpNum, $UserID = null) {
		$EmailSendConfigList = array();

		$result = $this->executeCURL('/Statement/EmailSendConfig', $CorpNum, $UserID);

		for($i=0; $i<Count($result); $i++){
			$EmailSendConfig = new EmailSendConfig();
			$EmailSendConfig->fromJsonInfo($result[$i]);
			$EmailSendConfigList[$i] = $EmailSendConfig;
		}
		return $EmailSendConfigList;
  }

  // 전자명세서 관련 메일전송 항목에 대한 전송여부를 수정
	public function UpdateEmailConfig($corpNum, $emailType, $sendYN, $userID = null) {
		$sendYNString = $sendYN ? 'True' : 'False';
		$uri = '/Statement/EmailSendConfig?EmailType='.$emailType.'&SendYN='.$sendYNString;

		return $result = $this->executeCURL($uri, $corpNum, $userID, true);
	}
}

class Statement
{
    public $sendNum;
    public $receiveNum;
    public $memo;
    public $emailSubject;

    public $itemCode;
    public $mgtKey;
    public $invoiceNum;
    public $formCode;
    public $writeDate;
    public $taxType;

    public $senderCorpNum;
    public $senderTaxRegID;
    public $senderCorpName;
    public $senderCEOName;
    public $senderAddr;
    public $senderBizClass;
    public $senderBizType;
    public $senderContactName;
    public $senderDeptName;
    public $senderTEL;
    public $senderHP;
    public $senderEmail;
    public $senderFAX;

    public $receiverCorpNum;
    public $receiverTaxRegID;
    public $receiverCorpName;
    public $receiverCEOName;
    public $receiverAddr;
    public $receiverBizClass;
    public $receiverBizType;
    public $receiverContactName;
    public $receiverDeptName;
    public $receiverTEL;
    public $receiverHP;
    public $receiverEmail;
    public $receiverFAX;

    public $taxTotal;
    public $supplyCostTotal;
    public $totalAmount;
    public $purposeType;
    public $serialNum;
    public $remark1;
    public $remark2;
    public $remark3;
    public $businessLicenseYN;
    public $bankBookYN;
    public $faxsendYN;
    public $smssendYN;
    public $autoacceptYN;

    public $detailList;
    public $propertyBag;

    function fromJsonInfo($jsonInfo)
    {
        isset($jsonInfo->itemCode) ? ($this->itemCode = $jsonInfo->itemCode) : null;
        isset($jsonInfo->mgtKey) ? ($this->mgtKey = $jsonInfo->mgtKey) : null;
        isset($jsonInfo->invoiceNum) ? ($this->invoiceNum = $jsonInfo->invoiceNum) : null;
        isset($jsonInfo->formCode) ? ($this->formCode = $jsonInfo->formCode) : null;
        isset($jsonInfo->writeDate) ? ($this->writeDate = $jsonInfo->writeDate) : null;
        isset($jsonInfo->taxType) ? ($this->taxType = $jsonInfo->taxType) : null;

        isset($jsonInfo->senderCorpNum) ? ($this->senderCorpNum = $jsonInfo->senderCorpNum) : null;
        isset($jsonInfo->senderTaxRegID) ? ($this->senderTaxRegID = $jsonInfo->senderTaxRegID) : null;
        isset($jsonInfo->senderCorpName) ? ($this->senderCorpName = $jsonInfo->senderCorpName) : null;
        isset($jsonInfo->senderCEOName) ? ($this->senderCEOName = $jsonInfo->senderCEOName) : null;
        isset($jsonInfo->senderAddr) ? ($this->senderAddr = $jsonInfo->senderAddr) : null;
        isset($jsonInfo->senderBizClass) ? ($this->senderBizClass = $jsonInfo->senderBizClass) : null;
        isset($jsonInfo->senderBizType) ? ($this->senderBizType = $jsonInfo->senderBizType) : null;
        isset($jsonInfo->senderContactName) ? ($this->senderContactName = $jsonInfo->senderContactName) : null;
        isset($jsonInfo->senderDeptName) ? ($this->senderDeptName = $jsonInfo->senderDeptName) : null;
        isset($jsonInfo->senderTEL) ? ($this->senderTEL = $jsonInfo->senderTEL) : null;
        isset($jsonInfo->senderHP) ? ($this->senderHP = $jsonInfo->senderHP) : null;
        isset($jsonInfo->senderEmail) ? ($this->senderEmail = $jsonInfo->senderEmail) : null;
        isset($jsonInfo->senderFAX) ? ($this->senderFAX = $jsonInfo->senderFAX) : null;

        isset($jsonInfo->receiverCorpNum) ? ($this->receiverCorpNum = $jsonInfo->receiverCorpNum) : null;
        isset($jsonInfo->receiverTaxRegID) ? ($this->receiverTaxRegID = $jsonInfo->receiverTaxRegID) : null;
        isset($jsonInfo->receiverCorpName) ? ($this->receiverCorpName = $jsonInfo->receiverCorpName) : null;
        isset($jsonInfo->receiverCEOName) ? ($this->receiverCEOName = $jsonInfo->receiverCEOName) : null;
        isset($jsonInfo->receiverAddr) ? ($this->receiverAddr = $jsonInfo->receiverAddr) : null;
        isset($jsonInfo->receiverBizClass) ? ($this->receiverBizClass = $jsonInfo->receiverBizClass) : null;
        isset($jsonInfo->receiverBizType) ? ($this->receiverBizType = $jsonInfo->receiverBizType) : null;
        isset($jsonInfo->receiverContactName) ? ($this->receiverContactName = $jsonInfo->receiverContactName) : null;
        isset($jsonInfo->receiverDeptName) ? ($this->receiverDeptName = $jsonInfo->receiverDeptName) : null;
        isset($jsonInfo->receiverTEL) ? ($this->receiverTEL = $jsonInfo->receiverTEL) : null;
        isset($jsonInfo->receiverHP) ? ($this->receiverHP = $jsonInfo->receiverHP) : null;

        isset($jsonInfo->receiverEmail) ? ($this->receiverEmail = $jsonInfo->receiverEmail) : null;
        isset($jsonInfo->receiverFAX) ? ($this->receiverFAX = $jsonInfo->receiverFAX) : null;
        isset($jsonInfo->taxTotal) ? ($this->taxTotal = $jsonInfo->taxTotal) : null;
        isset($jsonInfo->supplyCostTotal) ? ($this->supplyCostTotal = $jsonInfo->supplyCostTotal) : null;
        isset($jsonInfo->totalAmount) ? ($this->totalAmount = $jsonInfo->totalAmount) : null;
        isset($jsonInfo->purposeType) ? ($this->purposeType = $jsonInfo->purposeType) : null;
        isset($jsonInfo->serialNum) ? ($this->serialNum = $jsonInfo->serialNum) : null;

        isset($jsonInfo->remark1) ? ($this->remark1 = $jsonInfo->remark1) : null;
        isset($jsonInfo->remark2) ? ($this->remark2 = $jsonInfo->remark2) : null;
        isset($jsonInfo->remark3) ? ($this->remark3 = $jsonInfo->remark3) : null;
        isset($jsonInfo->businessLicenseYN) ? ($this->businessLicenseYN = $jsonInfo->businessLicenseYN) : null;
        isset($jsonInfo->bankBookYN) ? ($this->bankBookYN = $jsonInfo->bankBookYN) : null;

        isset($jsonInfo->faxsendYN) ? ($this->faxsendYN = $jsonInfo->faxsendYN) : null;
        isset($jsonInfo->bankBookYN) ? ($this->bankBookYN = $jsonInfo->bankBookYN) : null;
        isset($jsonInfo->smssendYN) ? ($this->smssendYN = $jsonInfo->smssendYN) : null;
        isset($jsonInfo->autoacceptYN) ? ($this->autoacceptYN = $jsonInfo->autoacceptYN) : null;

        if (!is_null($jsonInfo->detailList)) {
            $StatementDetailList = array();
            for ($i = 0; $i < Count($jsonInfo->detailList); $i++) {
                $StatementDetail = new StatementDetail();
                $StatementDetail->fromJsonInfo($jsonInfo->detailList[$i]);
                $StatementDetailList[$i] = $StatementDetail;
            }

            $this->detailList = $StatementDetailList;
        }

        isset($jsonInfo->propertyBag) ? ($this->propertyBag = $jsonInfo->propertyBag) : null;

    }
}


class StatementDetail
{
    public $serialNum;
    public $purchaseDT;
    public $itemName;
    public $spec;
    public $unit;
    public $qty;
    public $unitCost;
    public $supplyCost;
    public $tax;
    public $remark;
    public $spare1;
    public $spare2;
    public $spare3;
    public $spare4;
    public $spare5;
    public $spare6;
    public $spare7;
    public $spare8;
    public $spare9;
    public $spare10;
    public $spare11;
    public $spare12;
    public $spare13;
    public $spare14;
    public $spare15;
    public $spare16;
    public $spare17;
    public $spare18;
    public $spare19;
    public $spare20;

    function fromJsonInfo($jsonInfo)
    {
        isset($jsonInfo->serialNum) ? ($this->serialNum = $jsonInfo->serialNum) : null;
        isset($jsonInfo->purchaseDT) ? ($this->purchaseDT = $jsonInfo->purchaseDT) : null;
        isset($jsonInfo->itemName) ? ($this->itemName = $jsonInfo->itemName) : null;
        isset($jsonInfo->spec) ? ($this->spec = $jsonInfo->spec) : null;
        isset($jsonInfo->unit) ? ($this->unit = $jsonInfo->unit) : null;
        isset($jsonInfo->qty) ? ($this->qty = $jsonInfo->qty) : null;
        isset($jsonInfo->unitCost) ? ($this->unitCost = $jsonInfo->unitCost) : null;
        isset($jsonInfo->supplyCost) ? ($this->supplyCost = $jsonInfo->supplyCost) : null;
        isset($jsonInfo->tax) ? ($this->tax = $jsonInfo->tax) : null;
        isset($jsonInfo->remark) ? ($this->remark = $jsonInfo->remark) : null;
        isset($jsonInfo->spare1) ? ($this->spare1 = $jsonInfo->spare1) : null;
        isset($jsonInfo->spare2) ? ($this->spare2 = $jsonInfo->spare2) : null;
        isset($jsonInfo->spare3) ? ($this->spare3 = $jsonInfo->spare3) : null;
        isset($jsonInfo->spare4) ? ($this->spare4 = $jsonInfo->spare4) : null;
        isset($jsonInfo->spare5) ? ($this->spare5 = $jsonInfo->spare5) : null;

        isset($jsonInfo->spare6) ? ($this->spare6 = $jsonInfo->spare6) : null;
        isset($jsonInfo->spare7) ? ($this->spare7 = $jsonInfo->spare7) : null;
        isset($jsonInfo->spare8) ? ($this->spare8 = $jsonInfo->spare8) : null;
        isset($jsonInfo->spare9) ? ($this->spare9 = $jsonInfo->spare9) : null;
        isset($jsonInfo->spare10) ? ($this->spare10 = $jsonInfo->spare10) : null;

        isset($jsonInfo->spare11) ? ($this->spare11 = $jsonInfo->spare11) : null;
        isset($jsonInfo->spare12) ? ($this->spare12 = $jsonInfo->spare12) : null;
        isset($jsonInfo->spare13) ? ($this->spare13 = $jsonInfo->spare13) : null;
        isset($jsonInfo->spare14) ? ($this->spare14 = $jsonInfo->spare14) : null;
        isset($jsonInfo->spare15) ? ($this->spare15 = $jsonInfo->spare15) : null;

        isset($jsonInfo->spare16) ? ($this->spare16 = $jsonInfo->spare16) : null;
        isset($jsonInfo->spare17) ? ($this->spare17 = $jsonInfo->spare17) : null;
        isset($jsonInfo->spare18) ? ($this->spare18 = $jsonInfo->spare18) : null;
        isset($jsonInfo->spare19) ? ($this->spare19 = $jsonInfo->spare19) : null;
        isset($jsonInfo->spare20) ? ($this->spare20 = $jsonInfo->spare20) : null;

    }
}


class StatementInfo
{

    public $itemKey;
    public $mgtKey;
    public $itemCode;
    public $stateCode;
    public $taxType;
    public $purposeType;
    public $writeDate;
    public $senderCorpName;
    public $senderCorpNum;
    public $senderPrintYN;
    public $receiverCorpName;
    public $receiverCorpNum;
    public $receiverPrintYN;
    public $supplyCostTotal;
    public $taxTotal;
    public $issueDT;
    public $stateDT;
    public $openYN;
    public $openDT;
    public $stateMemo;
    public $regDT;

    function fromJsonInfo($jsonInfo)
    {
        isset($jsonInfo->itemKey) ? ($this->itemKey = $jsonInfo->itemKey) : null;
        isset($jsonInfo->mgtKey) ? ($this->mgtKey = $jsonInfo->mgtKey) : null;
        isset($jsonInfo->itemCode) ? ($this->itemCode = $jsonInfo->itemCode) : null;
        isset($jsonInfo->stateCode) ? ($this->stateCode = $jsonInfo->stateCode) : null;
        isset($jsonInfo->taxType) ? ($this->taxType = $jsonInfo->taxType) : null;
        isset($jsonInfo->purposeType) ? ($this->purposeType = $jsonInfo->purposeType) : null;
        isset($jsonInfo->writeDate) ? ($this->writeDate = $jsonInfo->writeDate) : null;
        isset($jsonInfo->senderCorpName) ? ($this->senderCorpName = $jsonInfo->senderCorpName) : null;
        isset($jsonInfo->senderCorpNum) ? ($this->senderCorpNum = $jsonInfo->senderCorpNum) : null;
        isset($jsonInfo->senderPrintYN) ? ($this->senderPrintYN = $jsonInfo->senderPrintYN) : null;
        isset($jsonInfo->receiverCorpName) ? ($this->receiverCorpName = $jsonInfo->receiverCorpName) : null;
        isset($jsonInfo->receiverCorpNum) ? ($this->receiverCorpNum = $jsonInfo->receiverCorpNum) : null;
        isset($jsonInfo->receiverPrintYN) ? ($this->receiverPrintYN = $jsonInfo->receiverPrintYN) : null;
        isset($jsonInfo->supplyCostTotal) ? ($this->supplyCostTotal = $jsonInfo->supplyCostTotal) : null;
        isset($jsonInfo->taxTotal) ? ($this->taxTotal = $jsonInfo->taxTotal) : null;
        isset($jsonInfo->issueDT) ? ($this->issueDT = $jsonInfo->issueDT) : null;
        isset($jsonInfo->stateDT) ? ($this->stateDT = $jsonInfo->stateDT) : null;
        isset($jsonInfo->openYN) ? ($this->openYN = $jsonInfo->openYN) : null;
        isset($jsonInfo->openDT) ? ($this->openDT = $jsonInfo->openDT) : null;
        isset($jsonInfo->stateMemo) ? ($this->stateMemo = $jsonInfo->stateMemo) : null;
        isset($jsonInfo->regDT) ? ($this->regDT = $jsonInfo->regDT) : null;
    }
}

class StatementLog
{
    public $docLogType;
    public $log;
    public $procType;
    public $procCorpName;
    public $procMemo;
    public $regDT;
    public $ip;

    function fromJsonInfo($jsonInfo)
    {
        isset($jsonInfo->docLogType) ? ($this->docLogType = $jsonInfo->docLogType) : null;
        isset($jsonInfo->log) ? ($this->log = $jsonInfo->log) : null;
        isset($jsonInfo->procType) ? ($this->procType = $jsonInfo->procType) : null;
        isset($jsonInfo->procCorpName) ? ($this->procCorpName = $jsonInfo->procCorpName) : null;
        isset($jsonInfo->procMemo) ? ($this->procMemo = $jsonInfo->procMemo) : null;
        isset($jsonInfo->regDT) ? ($this->regDT = $jsonInfo->regDT) : null;
        isset($jsonInfo->ip) ? ($this->ip = $jsonInfo->ip) : null;
    }
}

class MemoRequest
{
    public $memo;
}

class IssueRequest
{
    public $memo;
    public $emailSubject;
}

class DocSearchResult
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
        isset($jsonInfo->pageNum) ? $this->pageNum = $jsonInfo->pageNum : null;
        isset($jsonInfo->pageCount) ? $this->pageCount = $jsonInfo->pageCount : null;
        isset($jsonInfo->message) ? $this->message = $jsonInfo->message : null;

        $InfoList = array();

        for ($i = 0; $i < Count($jsonInfo->list); $i++) {
            $InfoObj = new StatementInfo();
            $InfoObj->fromJsonInfo($jsonInfo->list[$i]);
            $InfoList[$i] = $InfoObj;
        }

        $this->list = $InfoList;
    }
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
