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
* Written : 2016-07-07
* Updated : 2019-10-24
*
* Thanks for your interest.
* We welcome any suggestions, feedbacks, blames or anything.
* ======================================================================================
*/
require_once 'popbill.php';

class HTCashbillService extends PopbillBase {

	public function __construct ( $LinkID, $SecretKey )
  {
    parent::__construct ( $LinkID, $SecretKey );
    $this->AddScope ( '141' );
  }

  public function GetChargeInfo ( $CorpNum, $UserID = null)
  {
    $response = $this->executeCURL('/HomeTax/Cashbill/ChargeInfo', $CorpNum, $UserID);

    $ChargeInfo = new ChargeInfo();
    $ChargeInfo->fromJsonInfo($response);

    return $ChargeInfo;
  }

  public function RequestJob ( $CorpNum, $CBType, $SDate, $EDate, $UserID = null ) {
    if ( empty($SDate) || ( $SDate === "" ) )	{
      throw new PopbillException('시작일자가 입력되지 않았습니다.');
    }

    if ( empty($EDate) || ( $EDate === "" ) )	{
      throw new PopbillException('종료일자가 입력되지 않았습니다.');
    }

    $uri = '/HomeTax/Cashbill/'.$CBType;
    $uri .= '?SDate='.$SDate.'&EDate='.$EDate;

    return $this->executeCURL($uri, $CorpNum, $UserID, true, "", "")->jobID;
  }

  public function GetJobState ( $CorpNum, $JobID, $UserID = null )
  {
    if ( strlen ( $JobID ) != 18 ) {
      throw new PopbillException ('작업아이디(JobID)가 올바르지 않습니다.');
    }

    $response = $this->executeCURL('/HomeTax/Cashbill/'.$JobID.'/State', $CorpNum, $UserID);

    $JobState = new JobState();
    $JobState->fromJsonInfo($response);

    return $JobState;
  }

  public function ListActiveJob ( $CorpNum, $UserID = null )
  {
    $result = $this->executeCURL('/HomeTax/Cashbill/JobList', $CorpNum, $UserID);

    $JobList = array();

		for ( $i = 0; $i < Count ( $result ) ;  $i++ ) {
			$JobState = new JobState();
			$JobState->fromJsonInfo($result[$i]);
			$JobList[$i] = $JobState;
		}

    return $JobList;
  }

  public function Search ( $CorpNum, $JobID, $TradeType, $TradeUsage, $Page, $PerPage, $Order, $UserID = null )
  {
    if ( strlen ( $JobID ) != 18 ) {
      throw new PopbillException ('작업아이디(JobID)가 올바르지 않습니다.');
    }

    $uri = '/HomeTax/Cashbill/'.$JobID;
    $uri .= '?TradeType=' . implode ( ',' , $TradeType );
    $uri .= '&TradeUsage=' . implode ( ',' , $TradeUsage );
    $uri .= '&Page=' . $Page;
    $uri .= '&PerPage=' . $PerPage;
    $uri .= '&Oder=' . $Order;

    $response = $this->executeCURL ( $uri, $CorpNum, $UserID );

    $SearchResult = new HTCashbillSearch();
    $SearchResult->fromJsonInfo ( $response ) ;

    return $SearchResult;
  }

  public function Summary ( $CorpNum, $JobID, $TradeType, $TradeUsage, $UserID = null )
  {
    if ( strlen ( $JobID ) != 18 ) {
      throw new PopbillException ('작업아이디(JobID)가 올바르지 않습니다');
    }

    $uri = '/HomeTax/Cashbill/' . $JobID . '/Summary';
    $uri .= '?TradeType=' . implode ( ',' , $TradeType );
    $uri .= '&TradeUsage=' . implode ( ',' , $TradeUsage );

    $response = $this->executeCURL ( $uri, $CorpNum, $UserID );

    $Summary = new HTCashbillSummary();
    $Summary->fromJsonInfo ( $response ) ;

    return $Summary;
  }

  public function GetFlatRatePopUpURL ( $CorpNum, $UserID = null )
  {
    return $this->executeCURL ( '/HomeTax/Cashbill?TG=CHRG', $CorpNum, $UserID )->url;
  }

  public function GetFlatRateState ( $CorpNum, $UserID = null )
  {
    $response = $this->executeCURL ( '/HomeTax/Cashbill/Contract', $CorpNum, $UserID ) ;

    $FlatRateState = new FlatRate();
    $FlatRateState->fromJsonInfo ( $response );

    return $FlatRateState;
  }

  public function GetCertificatePopUpURL ( $CorpNum, $UserID = null)
  {
    return $this->executeCURL ( '/HomeTax/Cashbill?TG=CERT', $CorpNum, $UserID )->url;
  }

  public function GetCertificateExpireDate ( $CorpNum )
  {
    return $this->executeCURL ( '/HomeTax/Cashbill/CertInfo', $CorpNum )->certificateExpiration;
  }

	// 홈택스 공인인증서 로그인 테스트
	public function CheckCertValidation($CorpNum, $UserID = null){
		if(is_null($CorpNum) || empty($CorpNum)) {
      throw new PopbillException('연동회원 사업자번호가 입력되지 않았습니다.');
    }
		return $this->executeCURL('/HomeTax/Cashbill/CertCheck', $CorpNum, $UserID);
	}

	// 부서사용자 계정등록
	public function RegistDeptUser($CorpNum, $deptUserID, $deptUserPWD, $UserID = null){
		if(is_null($CorpNum) || empty($CorpNum)) {
			throw new PopbillException('연동회원 사업자번호가 입력되지 않았습니다.');
		}
		if(is_null($deptUserID) || empty($deptUserID)) {
			throw new PopbillException('홈택스 부서사용자 계정 아이디가 입력되지 않았습니다.');
		}
		if(is_null($deptUserPWD) || empty($deptUserPWD)) {
			throw new PopbillException('홈택스 부서사용자 계정 비밀번호가 입력되지 않았습니다.');
		}

		$Request = new RegistDeptUserRequest();
		$Request->id = $deptUserID;
		$Request->pwd = $deptUserPWD;
		$postdata = json_encode($Request);

		return $this->executeCURL('/HomeTax/Cashbill/DeptUser', $CorpNum, $UserID, true, null, $postdata);
	}

	// 부서사용자 등록정보 확인
	public function CheckDeptUser($CorpNum, $UserID = null){
		if(is_null($CorpNum) || empty($CorpNum)) {
			throw new PopbillException('연동회원 사업자번호가 입력되지 않았습니다.');
		}
		return $this->executeCURL('/HomeTax/Cashbill/DeptUser', $CorpNum, $UserID);
	}

	// 부서사용자 로그인 테스트
	public function CheckLoginDeptUser($CorpNum, $UserID = null){
		if(is_null($CorpNum) || empty($CorpNum)) {
			throw new PopbillException('연동회원 사업자번호가 입력되지 않았습니다.');
		}
		return $this->executeCURL('/HomeTax/Cashbill/DeptUser/Check', $CorpNum, $UserID);
	}

	// 부서사용자 등록정보 삭제
	public function DeleteDeptUser($CorpNum, $UserID = null){
		if(is_null($CorpNum) || empty($CorpNum)) {
			throw new PopbillException('연동회원 사업자번호가 입력되지 않았습니다.');
		}
		return $this->executeCURL('/HomeTax/Cashbill/DeptUser', $CorpNum, $UserID, true, 'DELETE', null);
	}
}

class FlatRate
{
    public $referenceID;
    public $contractDT;
    public $useEndDate;
    public $baseDate;
    public $state;
    public $closeRequestYN;
    public $useRestrictYN;
    public $closeOnExpired;
    public $unPaidYN;

    public function fromJsonInfo($jsonInfo)
    {
        isset ($jsonInfo->referenceID) ? $this->referenceID = $jsonInfo->referenceID : null;
        isset ($jsonInfo->contractDT) ? $this->contractDT = $jsonInfo->contractDT : null;
        isset ($jsonInfo->useEndDate) ? $this->useEndDate = $jsonInfo->useEndDate : null;
        isset ($jsonInfo->baseDate) ? $this->baseDate = $jsonInfo->baseDate : null;
        isset ($jsonInfo->state) ? $this->state = $jsonInfo->state : null;
        isset ($jsonInfo->closeRequestYN) ? $this->closeRequestYN = $jsonInfo->closeRequestYN : null;
        isset ($jsonInfo->useRestrictYN) ? $this->useRestrictYN = $jsonInfo->useRestrictYN : null;
        isset ($jsonInfo->closeOnExpired) ? $this->closeOnExpired = $jsonInfo->closeOnExpired : null;
        isset ($jsonInfo->unPaidYN) ? $this->unPaidYN = $jsonInfo->unPaidYN : null;
    }
}

class HTCashbillSummary
{
    public $count;
    public $supplyCostTotal;
    public $taxTotal;
    public $serviceFeeTotal;
    public $amountTotal;

    public function fromJsonInfo($jsonInfo)
    {
        isset ($jsonInfo->count) ? $this->count = $jsonInfo->count : null;
        isset ($jsonInfo->supplyCostTotal) ? $this->supplyCostTotal = $jsonInfo->supplyCostTotal : null;
        isset ($jsonInfo->taxTotal) ? $this->taxTotal = $jsonInfo->taxTotal : null;
        isset ($jsonInfo->serviceFeeTotal) ? $this->serviceFeeTotal = $jsonInfo->serviceFeeTotal : null;
        isset ($jsonInfo->amountTotal) ? $this->amountTotal = $jsonInfo->amountTotal : null;
    }
}

class HTCashbill
{
    public $ntsconfirmNum;
    public $tradeDate;
    public $tradeDT;
    public $tradeUsage;
    public $tradeType;
    public $supplyCost;
    public $tax;
    public $serviceFee;
    public $totalAmount;

    public $franchiseCorpNum;
    public $franchiseCorpName;
    public $franchiseCorpType;

    public $identityNum;
    public $identityNumType;
    public $customerName;
    public $cardOwnerName;
    public $deductionType;
    /*
    * 매출/매입 구분 필드 추가 (2017/08/29)
    */
    public $invoiceType;

    public function fromJsonInfo($jsonInfo)
    {
        isset ($jsonInfo->ntsconfirmNum) ? $this->ntsconfirmNum = $jsonInfo->ntsconfirmNum : null;
        isset ($jsonInfo->tradeDate) ? $this->tradeDate = $jsonInfo->tradeDate : null;
        isset ($jsonInfo->tradeDT) ? $this->tradeDT = $jsonInfo->tradeDT : null;
        isset ($jsonInfo->tradeUsage) ? $this->tradeUsage = $jsonInfo->tradeUsage : null;
        isset ($jsonInfo->tradeType) ? $this->tradeType = $jsonInfo->tradeType : null;
        isset ($jsonInfo->supplyCost) ? $this->supplyCost = $jsonInfo->supplyCost : null;
        isset ($jsonInfo->tax) ? $this->tax = $jsonInfo->tax : null;
        isset ($jsonInfo->serviceFee) ? $this->serviceFee = $jsonInfo->serviceFee : null;
        isset ($jsonInfo->totalAmount) ? $this->totalAmount = $jsonInfo->totalAmount : null;

        isset ($jsonInfo->franchiseCorpNum) ? $this->franchiseCorpNum = $jsonInfo->franchiseCorpNum : null;
        isset ($jsonInfo->franchiseCorpName) ? $this->franchiseCorpName = $jsonInfo->franchiseCorpName : null;
        isset ($jsonInfo->franchiseCorpType) ? $this->franchiseCorpType = $jsonInfo->franchiseCorpType : null;

        isset ($jsonInfo->identityNum) ? $this->identityNum = $jsonInfo->identityNum : null;
        isset ($jsonInfo->identityNumType) ? $this->identityNumType = $jsonInfo->identityNumType : null;
        isset ($jsonInfo->customerName) ? $this->customerName = $jsonInfo->customerName : null;
        isset ($jsonInfo->cardOwnerName) ? $this->cardOwnerName = $jsonInfo->cardOwnerName : null;
        isset ($jsonInfo->deductionType) ? $this->deductionType = $jsonInfo->deductionType : null;

        isset ($jsonInfo->invoiceType) ? $this->invoiceType = $jsonInfo->invoiceType : null;
    }
}

class HTCashbillSearch
{
    public $code;
    public $message;
    public $total;
    public $perPage;
    public $pageNum;
    public $pageCount;
    public $list;

    public function fromJsonInfo($jsonInfo)
    {
        isset ($jsonInfo->code) ? $this->code = $jsonInfo->code : null;
        isset ($jsonInfo->message) ? $this->message = $jsonInfo->message : null;
        isset ($jsonInfo->total) ? $this->total = $jsonInfo->total : null;
        isset ($jsonInfo->perPage) ? $this->perPage = $jsonInfo->perPage : null;
        isset ($jsonInfo->pageNum) ? $this->pageNum = $jsonInfo->pageNum : null;
        isset ($jsonInfo->pageCount) ? $this->pageCount = $jsonInfo->pageCount : null;

        $CashbillInfoList = array();
        for ($i = 0; $i < Count($jsonInfo->list); $i++) {
            $CashbillInfo = new HTCashbill();
            $CashbillInfo->fromJsonInfo($jsonInfo->list[$i]);
            $CashbillInfoList[$i] = $CashbillInfo;
        }
        $this->list = $CashbillInfoList;
    }
}

class JobState
{
    public $jobID;
    public $jobState;
    public $queryType;
    public $queryDateType;
    public $queryStDate;
    public $queryEnDate;
    public $errorCode;
    public $errorReason;
    public $jobStartDT;
    public $jobEndDT;
    public $collectCount;
    public $regDT;

    public function fromJsonInfo($jsonInfo)
    {
        isset($jsonInfo->jobID) ? $this->jobID = $jsonInfo->jobID : null;
        isset($jsonInfo->jobState) ? $this->jobState = $jsonInfo->jobState : null;
        isset($jsonInfo->queryType) ? $this->queryType = $jsonInfo->queryType : null;
        isset($jsonInfo->queryDateType) ? $this->queryDateType = $jsonInfo->queryDateType : null;
        isset($jsonInfo->queryStDate) ? $this->queryStDate = $jsonInfo->queryStDate : null;
        isset($jsonInfo->queryEnDate) ? $this->queryEnDate = $jsonInfo->queryEnDate : null;
        isset($jsonInfo->errorCode) ? $this->errorCode = $jsonInfo->errorCode : null;
        isset($jsonInfo->errorReason) ? $this->errorReason = $jsonInfo->errorReason : null;
        isset($jsonInfo->jobStartDT) ? $this->jobStartDT = $jsonInfo->jobStartDT : null;
        isset($jsonInfo->jobEndDT) ? $this->jobEndDT = $jsonInfo->jobEndDT : null;
        isset($jsonInfo->collectCount) ? $this->collectCount = $jsonInfo->collectCount : null;
        isset($jsonInfo->regDT) ? $this->regDT = $jsonInfo->regDT : null;
    }
}

class KeyType
{
    const SELL = 'SELL';
    const BUY = 'BUY';
}

class RegistDeptUserRequest
{
    public $id;
    public $pwd;

    public function fromJsonInfo($jsonInfo)
    {
        isset ($jsonInfo->id) ? $this->id = $jsonInfo->id : null;
        isset ($jsonInfo->pwd) ? $this->pwd = $jsonInfo->pwd : null;
    }
}

?>
