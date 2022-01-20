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
* Author : Jeong Yohan (code@linkhubcorp.com)
* Written : 2015-07-10
* Updated : 2021-07-21
*
* Thanks for your interest.
* We welcome any suggestions, feedbacks, blames or anything.
* ======================================================================================
*/
require_once 'popbill.php';

class ClosedownService extends PopbillBase {

	public function __construct($LinkID,$SecretKey) {
    	parent::__construct($LinkID,$SecretKey);
    	$this->AddScope('170');
    }

    //휴폐업조회 - 단건
    public function CheckCorpNum($MemberCorpNum, $CheckCorpNum) {
    	if(is_null($MemberCorpNum) || empty($MemberCorpNum)) {
    		throw new PopbillException('팝빌회원 사업자번호가 입력되지 않았습니다.');
    	}

		  if(is_null($CheckCorpNum) || empty($CheckCorpNum)) {
    		throw new PopbillException('조회할 사업자번호가 입력되지 않았습니다.');
    	}

    	$result = $this->executeCURL('/CloseDown?CN='.$CheckCorpNum, $MemberCorpNum);

  		$CorpState = new CorpState();
  		$CorpState->fromJsonInfo($result);
  		return $CorpState;

    }

	//휴폐업조회 - 대량
	public function CheckCorpNums($MemberCorpNum, $CheckCorpNumList){
		if(is_null($MemberCorpNum) || empty($MemberCorpNum)) {
    		throw new PopbillException('팝빌회원 사업자번호가 입력되지 않았습니다.');
    }

		if(is_null($CheckCorpNumList) || empty($CheckCorpNumList)) {
    		throw new PopbillException('조회할 사업자번호 배열이 입력되지 않았습니다.');
    }

		$postData = json_encode($CheckCorpNumList);

		$result = $this->executeCURL('/CloseDown', $MemberCorpNum, null, true, null, $postData);

		$CorpStateList = array();

		for($i = 0; $i < Count($result); $i++) {
			$CorpState = new CorpState();
			$CorpState->fromJsonInfo($result[$i]);
			$CorpStateList[$i] = $CorpState;
		}

		return $CorpStateList;
	}

  //조회단가 확인
  public function GetUnitCost($CorpNum) {
	  return $this->executeCURL('/CloseDown/UnitCost', $CorpNum)->unitCost;
  }

  public function GetChargeInfo ( $CorpNum, $UserID = null) {
    $uri = '/CloseDown/ChargeInfo';

    $response = $this->executeCURL($uri, $CorpNum, $UserID);
    $ChargeInfo = new ChargeInfo();
    $ChargeInfo->fromJsonInfo($response);

    return $ChargeInfo;
  }
}

class CorpState
{
    public $corpNum;
    public $state;
    public $type;
    public $taxType;
    public $stateDate;
    public $checkDate;
    public $typeDate;

    function fromJsonInfo($jsonInfo)
    {
        isset($jsonInfo->corpNum) ? $this->corpNum = $jsonInfo->corpNum : null;
        isset($jsonInfo->state) ? $this->state = $jsonInfo->state : null;
        isset($jsonInfo->type) ? $this->type = $jsonInfo->type : null;
        isset($jsonInfo->taxType) ? $this->taxType = $jsonInfo->taxType : null;
        isset($jsonInfo->stateDate) ? $this->stateDate = $jsonInfo->stateDate : null;
        isset($jsonInfo->checkDate) ? $this->checkDate = $jsonInfo->checkDate : null;
        isset($jsonInfo->typeDate) ? $this->typeDate = $jsonInfo->typeDate : null;
    }
}

?>
