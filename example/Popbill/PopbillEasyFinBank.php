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
* https://www.linkhub.co.kr
* Author : Jeong Yohan (code@linkhubcorp.com)
* Written : 2019-12-19
* Updated : 2021-12-23
*
* Thanks for your interest.
* We welcome any suggestions, feedbacks, blames or anything.
* ======================================================================================
*/
require_once 'popbill.php';

class EasyFinBankService extends PopbillBase {

	public function __construct ( $LinkID, $SecretKey )
  {
    parent::__construct ( $LinkID, $SecretKey );
    $this->AddScope ( '180' );
  }

  public function GetBankAccountMgtURL ( $CorpNum, $UserID = null )
  {
    return $this->executeCURL ( '/EasyFin/Bank?TG=BankAccount', $CorpNum, $UserID )->url;
  }

  public function RegistBankAccount($CorpNum, $BankAccountInfo, $UserID = null)
  {
      $uri = '/EasyFin/Bank/BankAccount/Regist';
      $uri .= '?UsePeriod=' . $BankAccountInfo->UsePeriod;

      $postdata = json_encode($BankAccountInfo);

      return $this->executeCURL($uri, $CorpNum, $UserID, true, null, $postdata);
  }

  public function UpdateBankAccount($CorpNum, $BankCode, $AccountNumber, $BankAccountInfo, $UserID = null)
  {
    if ( empty($BankCode) || strlen ( $BankCode ) != 4 ) {
      throw new PopbillException ('기관코드가 올바르지 않습니다.');
    }

    if ( empty($AccountNumber) || $AccountNumber === "" ) {
      throw new PopbillException ('계좌번호가 올바르지 않습니다.');
    }

    $uri = '/EasyFin/Bank/BankAccount/'.$BankCode.'/'.$AccountNumber.'/Update';

    $postdata = json_encode($BankAccountInfo);

    return $this->executeCURL($uri, $CorpNum, $UserID, true, null, $postdata);
  }

  public function CloseBankAccount($CorpNum, $BankCode, $AccountNumber, $CloseType, $UserID = null)
  {
    if ( empty($BankCode) || strlen ( $BankCode ) != 4 ) {
      throw new PopbillException ('기관코드가 올바르지 않습니다.');
    }

    if ( empty($AccountNumber) || $AccountNumber === "" ) {
      throw new PopbillException ('계좌번호가 올바르지 않습니다.');
    }

    if( $CloseType != "일반" && $CloseType != "중도"){
      throw new PopbillException ('정액제 해지유형이 올바르지 않습니다.');
    }

    $uri = '/EasyFin/Bank/BankAccount/Close';
    $uri .= '?BankCode=' . $BankCode;
    $uri .= '&AccountNumber=' . $AccountNumber;
    $uri .= '&CloseType=' . $CloseType;

    return $this->executeCURL($uri, $CorpNum, $UserID, true, null, null);
  }

  public function RevokeCloseBankAccount($CorpNum, $BankCode, $AccountNumber, $UserID = null)
  {
    if ( empty($BankCode) || strlen ( $BankCode ) != 4 ) {
      throw new PopbillException ('기관코드가 올바르지 않습니다.');
    }

    if ( empty($AccountNumber) || $AccountNumber === "" ) {
      throw new PopbillException ('계좌번호가 올바르지 않습니다.');
    }

    $uri = '/EasyFin/Bank/BankAccount/RevokeClose';
    $uri .= '?BankCode=' . $BankCode;
    $uri .= '&AccountNumber=' . $AccountNumber;

    return $this->executeCURL($uri, $CorpNum, $UserID, true, null, null);
  }

  public function DeleteBankAccount($CorpNum, $BankCode, $AccountNumber, $UserID = null)
  {
	if ( empty($BankCode) || strlen ( $BankCode ) != 4 ) {
        throw new PopbillException ('기관코드가 올바르지 않습니다.');
    }

    if ( empty($AccountNumber) || $AccountNumber === "" ) {
        throw new PopbillException ('계좌번호가 올바르지 않습니다.');
    }

    $uri = '/EasyFin/Bank/BankAccount/Delete';

    $postdata = '{"BankCode":' . '"' . $BankCode . '"' . ', "AccountNumber":' . '"' . $AccountNumber . '"' .'}';

    return $this->executeCURL($uri, $CorpNum, $UserID, true, null, $postdata);
  }

  public function GetBankAccountInfo ( $CorpNum, $BankCode, $AccountNumber, $UserID = null)
  {
    if ( empty($BankCode) || strlen ( $BankCode ) != 4 ) {
      throw new PopbillException ('기관코드가 올바르지 않습니다.');
    }

    if ( empty($AccountNumber) || $AccountNumber === "" ) {
      throw new PopbillException ('계좌번호가 올바르지 않습니다.');
    }

    $response = $this->executeCURL('/EasyFin/Bank/BankAccount/'.$BankCode.'/'.$AccountNumber, $CorpNum, $UserID);

    $BankInfo = new EasyFinBankAccount();
    $BankInfo->fromJsonInfo($response);

    return $BankInfo;
  }

  public function ListBankAccount ( $CorpNum, $UserID = null )
  {
    $result = $this->executeCURL('/EasyFin/Bank/ListBankAccount', $CorpNum, $UserID);

    $BankAccountList = array();

		for ( $i = 0; $i < Count ( $result ) ;  $i++ ) {
			$BankAccountInfo = new EasyFinBankAccount();
			$BankAccountInfo->fromJsonInfo($result[$i]);
			$BankAccountList[$i] = $BankAccountInfo;
		}

    return $BankAccountList;
  }

  public function RequestJob ( $CorpNum, $BankCode, $AccountNumber, $SDate, $EDate, $UserID = null ) {

    if ( empty($BankCode) || $BankCode === "")	{
      throw new PopbillException('기관코드가 입력되지 않았습니다.');
    }

    if(empty($AccountNumber) || $AccountNumber === "")	{
      throw new PopbillException('계좌번호가 입력되지 않았습니다.');
    }

    if(empty($SDate) || $SDate === "")	{
      throw new PopbillException('조회 시작일자가 입력되지 않았습니다.');
    }

    if(empty($EDate) || $EDate === "")	{
      throw new PopbillException('조회 종료일자가 입력되지 않았습니다.');
    }

    $uri = '/EasyFin/Bank/BankAccount?BankCode='.$BankCode.'&AccountNumber='.$AccountNumber;
    $uri .= '&SDate='.$SDate.'&EDate='.$EDate;

    return $this->executeCURL($uri, $CorpNum, $UserID, true, "", "")->jobID;
  }

  public function GetJobState ( $CorpNum, $JobID, $UserID = null )
  {
    if ( empty($JobID) || strlen ( $JobID ) != 18 ) {
      throw new PopbillException ('작업아이디가 올바르지 않습니다.');
    }

    $response = $this->executeCURL('/EasyFin/Bank/'.$JobID.'/State', $CorpNum, $UserID);

    $JobState = new EasyFinBankJobState();
    $JobState->fromJsonInfo($response);

    return $JobState;
  }

  public function ListActiveJob ( $CorpNum, $UserID = null )
  {
    $result = $this->executeCURL('/EasyFin/Bank/JobList', $CorpNum, $UserID);

    $JobList = array();

		for ( $i = 0; $i < Count ( $result ) ;  $i++ ) {
			$JobState = new EasyFinBankJobState();
			$JobState->fromJsonInfo($result[$i]);
			$JobList[$i] = $JobState;
		}

    return $JobList;
  }

  public function Search ( $CorpNum, $JobID, $TradeType, $SearchString, $Page, $PerPage, $Order, $UserID = null)
  {
    if ( strlen ( $JobID ) != 18 ) {
      throw new PopbillException ('작업아이디(JobID)가 올바르지 않습니다.');
    }

    $uri = '/EasyFin/Bank/'.$JobID;
    $uri .= '?TradeType=' . implode ( ',' , $TradeType );

    if ( !empty( $SearchString ) ) {
      $uri .= '&SearchString=' . $SearchString;
    }

    $uri .= '&Page=' . $Page;
    $uri .= '&PerPage=' . $PerPage;
    $uri .= '&Order=' . $Order;

    $response = $this->executeCURL ( $uri, $CorpNum, $UserID );

    $SearchResult = new EasyFinBankSearchResult();
    $SearchResult->fromJsonInfo($response);

    return $SearchResult;
  }

  public function Summary ( $CorpNum, $JobID, $TradeType, $SearchString, $UserID = null )
  {
    if ( strlen ( $JobID ) != 18 ) {
      throw new PopbillException ('작업아이디(JobID)가 올바르지 않습니다.');
    }

    $uri = '/EasyFin/Bank/'.$JobID.'/Summary';
    $uri .= '?TradeType=' . implode ( ',' , $TradeType );

    if ( !empty( $SearchString ) ) {
      $uri .= '&SearchString=' . $SearchString;
    }

    $response = $this->executeCURL ( $uri, $CorpNum, $UserID );

    $SummaryResult = new EasyFinBankSummaryResult();
    $SummaryResult->fromJsonInfo($response);

    return $SummaryResult;
  }

  public function SaveMemo ( $CorpNum, $TID, $Memo, $UserID = null )
  {
    if(empty($TID) || $TID === "")	{
      throw new PopbillException('거래내역 아이디가 입력되지 않았습니다.');
    }

    $uri = '/EasyFin/Bank/SaveMemo';
    $uri .= '?TID=' . $TID;
    $uri .= '&Memo=' . $Memo;

    return $this->executeCURL($uri, $CorpNum, $UserID, true, "", "");

  }

  public function GetFlatRatePopUpURL ( $CorpNum, $UserID = null )
  {
    return $this->executeCURL ( '/EasyFin/Bank?TG=CHRG', $CorpNum, $UserID )->url;
  }

  public function GetFlatRateState ( $CorpNum, $BankCode, $AccountNumber, $UserID = null )
  {
    $response = $this->executeCURL ( '/EasyFin/Bank/Contract/'.$BankCode.'/'.$AccountNumber, $CorpNum, $UserID ) ;

    $FlatRateState = new EasyFinBankFlatRate();
    $FlatRateState->fromJsonInfo ( $response );

    return $FlatRateState;
  }

  public function GetChargeInfo ( $CorpNum, $UserID = null)
  {
    $response = $this->executeCURL('/EasyFin/Bank/ChargeInfo', $CorpNum, $UserID);

    $ChargeInfo = new ChargeInfo();
    $ChargeInfo->fromJsonInfo($response);

    return $ChargeInfo;
  }


}

class EasyFinBankSummaryResult
{
    public $count;
    public $cntAccIn;
    public $cntAccOut;
    public $totalAccIn;
    public $totalAccOut;

    public function fromJsonInfo($jsonInfo)
    {
        isset ($jsonInfo->count) ? $this->count = $jsonInfo->count : null;
        isset ($jsonInfo->cntAccIn) ? $this->cntAccIn = $jsonInfo->cntAccIn : null;
        isset ($jsonInfo->cntAccOut) ? $this->cntAccOut = $jsonInfo->cntAccOut : null;
        isset ($jsonInfo->totalAccIn) ? $this->totalAccIn = $jsonInfo->totalAccIn : null;
        isset ($jsonInfo->totalAccOut) ? $this->totalAccOut = $jsonInfo->totalAccOut : null;

    }
}

class EasyFinBankFlatRate
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

class EasyFinBankSearchResult
{
    public $code;
    public $message;
    public $total;
    public $perPage;
    public $pageNum;
    public $pageCount;
    public $lastScrapDT;
    public $list;

    public function fromJsonInfo($jsonInfo)
    {
        isset ($jsonInfo->code) ? $this->code = $jsonInfo->code : null;
        isset ($jsonInfo->message) ? $this->message = $jsonInfo->message : null;
        isset ($jsonInfo->total) ? $this->total = $jsonInfo->total : null;
        isset ($jsonInfo->perPage) ? $this->perPage = $jsonInfo->perPage : null;
        isset ($jsonInfo->pageNum) ? $this->pageNum = $jsonInfo->pageNum : null;
        isset ($jsonInfo->pageCount) ? $this->pageCount = $jsonInfo->pageCount : null;
        isset ($jsonInfo->lastScrapDT) ? $this->lastScrapDT = $jsonInfo->lastScrapDT : null;

        $SearchDetailList = array();
        for ($i = 0; $i < Count($jsonInfo->list); $i++) {
            $SearchDetail = new EasyFinBankSearchDetail();
            $SearchDetail->fromJsonInfo($jsonInfo->list[$i]);
            $SearchDetailList[$i] = $SearchDetail;
        }
        $this->list = $SearchDetailList;
    }
}

class EasyFinBankSearchDetail
{
    public $tid;
    public $trdate;
    public $trserial;
    public $trdt;
    public $accIn;
    public $accOut;
    public $balance;
    public $remark1;
    public $remark2;
    public $remark3;
    public $remark4;
    public $regDT;
    public $memo;

    public function fromJsonInfo($jsonInfo)
    {
        isset ($jsonInfo->tid) ? $this->tid = $jsonInfo->tid : null;
        isset ($jsonInfo->trdate) ? $this->trdate = $jsonInfo->trdate : null;
        isset ($jsonInfo->trserial) ? $this->trserial = $jsonInfo->trserial : null;
        isset ($jsonInfo->trdt) ? $this->trdt = $jsonInfo->trdt : null;
        isset ($jsonInfo->accIn) ? $this->accIn = $jsonInfo->accIn : null;
        isset ($jsonInfo->accOut) ? $this->accOut = $jsonInfo->accOut : null;
        isset ($jsonInfo->balance) ? $this->balance = $jsonInfo->balance : null;
        isset ($jsonInfo->remark1) ? $this->remark1 = $jsonInfo->remark1 : null;
        isset ($jsonInfo->remark2) ? $this->remark2 = $jsonInfo->remark2 : null;
        isset ($jsonInfo->remark3) ? $this->remark3 = $jsonInfo->remark3 : null;
        isset ($jsonInfo->remark4) ? $this->remark4 = $jsonInfo->remark4 : null;
        isset ($jsonInfo->regDT) ? $this->regDT = $jsonInfo->regDT : null;
        isset ($jsonInfo->memo) ? $this->memo = $jsonInfo->memo : null;
    }
}

class EasyFinBankJobState
{
    public $jobID;
    public $jobState;
    public $startDate;
    public $endDate;
    public $errorCode;
    public $errorReason;
    public $jobStartDT;
    public $jobEndDT;
    public $regDT;

    public function fromJsonInfo($jsonInfo)
    {
        isset($jsonInfo->jobID) ? $this->jobID = $jsonInfo->jobID : null;
        isset($jsonInfo->jobState) ? $this->jobState = $jsonInfo->jobState : null;
        isset($jsonInfo->startDate) ? $this->startDate = $jsonInfo->startDate : null;
        isset($jsonInfo->endDate) ? $this->endDate = $jsonInfo->endDate : null;
        isset($jsonInfo->errorCode) ? $this->errorCode = $jsonInfo->errorCode : null;
        isset($jsonInfo->errorReason) ? $this->errorReason = $jsonInfo->errorReason : null;
        isset($jsonInfo->jobStartDT) ? $this->jobStartDT = $jsonInfo->jobStartDT : null;
        isset($jsonInfo->jobEndDT) ? $this->jobEndDT = $jsonInfo->jobEndDT : null;
        isset($jsonInfo->regDT) ? $this->regDT = $jsonInfo->regDT : null;
    }
}

class EasyFinBankAccountForm
{
  public $BankCode;
  public $AccountNumber;
  public $AccountPWD;
  public $AccountType;
  public $IdentityNumber;
  public $AccountName;
  public $BankID;
  public $FastID;
  public $FastPWD;
  public $UsePeriod;
  public $Memo;
}

class UpdateEasyFinBankAccountForm
{
  public $AccountPWD;
  public $AccountName;
  public $BankID;
  public $FastID;
  public $FastPWD;
  public $Memo;
}


class EasyFinBankAccount
{
  public $bankCode;
  public $accountNumber;
  public $accountName;
  public $accountType;
  public $state;
  public $regDT;
  public $memo;

  public $contractDT;
  public $baseDate;
  public $useEndDate;
  public $contractState;
  public $closeRequestYN;
  public $useRestrictYN;
  public $closeOnExpired;
  public $unPaidYN;

  public function fromJsonInfo($jsonInfo)
  {
    isset($jsonInfo->bankCode) ? $this->bankCode = $jsonInfo->bankCode : null;
    isset($jsonInfo->accountNumber) ? $this->accountNumber = $jsonInfo->accountNumber : null;
    isset($jsonInfo->accountName) ? $this->accountName = $jsonInfo->accountName : null;
    isset($jsonInfo->accountType) ? $this->accountType = $jsonInfo->accountType : null;
    isset($jsonInfo->state) ? $this->state = $jsonInfo->state : null;
    isset($jsonInfo->regDT) ? $this->regDT = $jsonInfo->regDT : null;
    isset($jsonInfo->memo) ? $this->memo = $jsonInfo->memo : null;

    isset($jsonInfo->contractDT) ? $this->contractDT = $jsonInfo->contractDT : null;
    isset($jsonInfo->baseDate) ? $this->baseDate = $jsonInfo->baseDate : null;
    isset($jsonInfo->useEndDate) ? $this->useEndDate = $jsonInfo->useEndDate : null;
    isset($jsonInfo->contractState) ? $this->contractState = $jsonInfo->contractState : null;
    isset($jsonInfo->closeRequestYN) ? $this->closeRequestYN = $jsonInfo->closeRequestYN : null;
    isset($jsonInfo->useRestrictYN) ? $this->useRestrictYN = $jsonInfo->useRestrictYN : null;
    isset($jsonInfo->closeOnExpired) ? $this->closeOnExpired = $jsonInfo->closeOnExpired : null;
    isset($jsonInfo->unPaidYN) ? $this->unPaidYN = $jsonInfo->unPaidYN : null;
  }
}

?>
