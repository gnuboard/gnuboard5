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
 * Author : Kim Seongjun
 * Written : 2014-04-15
 * Contributor : Jeong YoHan (code@linkhubcorp.com)
 * Updated : 2025-01-13
 *
 * Thanks for your interest.
 * We welcome any suggestions, feedbacks, blames or anythings.
 * ======================================================================================
 */

require_once 'Linkhub/linkhub.auth.php';

class PopbillBase
{
    const ServiceID_REAL = 'POPBILL';
    const ServiceID_TEST = 'POPBILL_TEST';
    const ServiceURL_REAL = 'https://popbill.linkhub.co.kr';
    const ServiceURL_TEST = 'https://popbill-test.linkhub.co.kr';

    const ServiceURL_Static_REAL = 'https://static-popbill.linkhub.co.kr';
    const ServiceURL_Static_TEST = 'https://static-popbill-test.linkhub.co.kr';

    const ServiceURL_GA_REAL = 'https://ga-popbill.linkhub.co.kr';
    const ServiceURL_GA_TEST = 'https://ga-popbill-test.linkhub.co.kr';
    const Version = '1.0';

    private $Token_Table = array();
    private $Linkhub;
    private $IsTest = false;
    private $IPRestrictOnOff = true;
    private $UseStaticIP = false;
    private $UseGAIP = false;
    private $UseLocalTimeYN = true;

    private $scopes = array();
    private $__requestMode = LINKHUB_COMM_MODE;

    public function __construct($LinkID, $SecretKey)
    {
        $this->Linkhub = Linkhub::getInstance($LinkID, $SecretKey);
        $this->scopes[] = 'member';
    }

    public function IsTest($T)
    {
        $this->IsTest = $T;
    }

    public function IPRestrictOnOff($V)
    {
        $this->IPRestrictOnOff = $V;
    }

    public function UseStaticIP($V)
    {
        $this->UseStaticIP = $V;
    }

    public function UseGAIP($V)
    {
        $this->UseGAIP = $V;
    }

    public function UseLocalTimeYN($V)
    {
        $this->UseLocalTimeYN = $V;
    }

    protected function AddScope($scope)
    {
        $this->scopes[] = $scope;
    }

    private function getsession_Token($CorpNum)
    {
        $targetToken = null;

        if (array_key_exists($CorpNum, $this->Token_Table)) {
            $targetToken = $this->Token_Table[$CorpNum];
        }

        $Refresh = false;

        if (is_null($targetToken)) {
            $Refresh = true;
        } else {
            $Expiration = new DateTime($targetToken->expiration, new DateTimeZone("UTC"));

            $now = $this->Linkhub->getTime($this->UseStaticIP, $this->UseLocalTimeYN, $this->UseGAIP);
            $Refresh = $Expiration < $now;
        }

        if ($Refresh) {
            try {
                $targetToken = $this->Linkhub->getToken($this->IsTest ? PopbillBase::ServiceID_TEST : PopbillBase::ServiceID_REAL, $CorpNum, $this->scopes, $this->IPRestrictOnOff ? null : "*", $this->UseStaticIP, $this->UseLocalTimeYN, $this->UseGAIP);
            } catch (LinkhubException $le) {
                throw new PopbillException($le->getMessage(), $le->getCode());
            }
            $this->Token_Table[$CorpNum] = $targetToken;
        }
        return $targetToken->session_token;
    }

    // ID 중복 확인
    public function CheckID($ID)
    {
        if (is_null($ID) || empty($ID)) {
            throw new PopbillException('조회할 아이디가 입력되지 않았습니다.');
        }
        return $this->executeCURL('/IDCheck?ID=' . $ID);
    }

    // 담당자 추가
    public function RegistContact($CorpNum, $ContactInfo, $UserID = null)
    {
        $postdata = json_encode($ContactInfo);
        return $this->executeCURL('/IDs/New', $CorpNum, $UserID, true, null, $postdata);
    }

    // 담당자 정보 수정
    public function UpdateContact($CorpNum, $ContactInfo, $UserID)
    {
        $postdata = json_encode($ContactInfo);
        return $this->executeCURL('/IDs', $CorpNum, $UserID, true, null, $postdata);
    }

    // 담당자 정보 확인
    public function GetContactInfo($CorpNum, $ContactID, $UserID = null)
    {
        $postdata = '{"id":' . '"' . $ContactID . '"}';
        return $this->executeCURL('/Contact', $CorpNum, $UserID, true, null, $postdata);
    }

    // 담당자 목록 조회
    public function ListContact($CorpNum, $UserID = null)
    {
        $ContactInfoList = array();

        $response = $this->executeCURL('/IDs', $CorpNum, $UserID);

        for ($i = 0; $i < Count($response); $i++) {
            $ContactInfo = new ContactInfo();
            $ContactInfo->fromJsonInfo($response[$i]);
            $ContactInfoList[$i] = $ContactInfo;
        }

        return $ContactInfoList;
    }

    // 회사정보 확인
    public function GetCorpInfo($CorpNum, $UserID = null)
    {
        $response = $this->executeCURL('/CorpInfo', $CorpNum, $UserID);

        $CorpInfo = new CorpInfo();
        $CorpInfo->fromJsonInfo($response);
        return $CorpInfo;
    }

    // 회사정보 수정
    public function UpdateCorpInfo($CorpNum, $CorpInfo, $UserID = null)
    {
        $postdata = json_encode($CorpInfo);
        return $this->executeCURL('/CorpInfo', $CorpNum, $UserID, true, null, $postdata);
    }

    //팝빌 연결 URL함수
    public function GetPopbillURL($CorpNum, $UserID, $TOGO)
    {
        $response = $this->executeCURL('/Member?TG=' . $TOGO, $CorpNum, $UserID);
        return $response->url;
    }

    //팝빌 로그인 URL
    public function GetAccessURL($CorpNum, $UserID)
    {
        $response = $this->executeCURL('/Member?TG=LOGIN', $CorpNum, $UserID);
        return $response->url;
    }

    // 연동회원 포인트 충전 팝업 URL
    public function GetChargeURL($CorpNum, $UserID)
    {
        $response = $this->executeCURL('/Member?TG=CHRG', $CorpNum, $UserID);
        return $response->url;
    }

    // 연동회원 포인트 결제내역 팝업 URL
    public function GetPaymentURL($CorpNum, $UserID)
    {
        $response = $this->executeCURL('/Member?TG=PAYMENT', $CorpNum, $UserID);
        return $response->url;
    }

    // 연동회원 포인트 사용내역 팝업 URL
    public function GetUseHistoryURL($CorpNum, $UserID)
    {
        $response = $this->executeCURL('/Member?TG=USEHISTORY', $CorpNum, $UserID);
        return $response->url;
    }

    //가입여부 확인
    public function CheckIsMember($CorpNum, $LinkID)
    {
        return $this->executeCURL('/Join?CorpNum=' . $CorpNum . '&LID=' . $LinkID);
    }

    //회원가입
    public function JoinMember($JoinForm)
    {
        $postdata = json_encode($JoinForm);
        return $this->executeCURL('/Join', null, null, true, null, $postdata);
    }

    // 연동회원 잔여포인트 확인
    public function GetBalance($CorpNum)
    {
        try {
            return $this->Linkhub->getBalance($this->getsession_Token($CorpNum), $this->IsTest ? PopbillBase::ServiceID_TEST : PopbillBase::ServiceID_REAL, $this->UseStaticIP, $this->UseGAIP);
        } catch (LinkhubException $le) {
            throw new PopbillException($le->getMessage(), $le->getCode());
        }
    }

    // 연동회원 포인트 사용내역 확인
    public function GetUseHistory($CorpNum, $SDate, $EDate, $Page = null, $PerPage = null, $Order = null, $UserID = null)
    {
        $uri  = '/UseHistory';
        $uri .= '?SDate='   . $SDate;
        $uri .= '&EDate='   . $EDate;
        $uri .= '&Page='    . $Page;
        $uri .= '&PerPage=' . $PerPage;
        $uri .= '&Order='   . $Order;
        $response = $this->executeCURL($uri, $CorpNum, $UserID);

        $UseHistoryResult = new UseHistoryResult();
        $UseHistoryResult->fromJsonInfo($response);

        return $UseHistoryResult;
    }

    // 연동회원 포인트 결제내역 확인
    public function GetPaymentHistory($CorpNum, $SDate, $EDate, $Page = null, $PerPage = null, $UserID = null)
    {
        $uri  = '/PaymentHistory';
        $uri .= '?SDate='   . $SDate;
        $uri .= '&EDate='   . $EDate;
        $uri .= '&Page='    . $Page;
        $uri .= '&PerPage=' . $PerPage;
        $response = $this->executeCURL($uri, $CorpNum, $UserID);

        $PaymentHistoryResult = new PaymentHistoryResult();
        $PaymentHistoryResult->fromJsonInfo($response);

        return $PaymentHistoryResult;
    }

    // 연동회원 포인트 환불내역 확인
    public function GetRefundHistory($CorpNum, $Page = null, $PerPage = null, $UserID = null)
    {
        $uri  = '/RefundHistory';
        $uri .= '?Page='    . $Page;
        $uri .= '&PerPage=' . $PerPage;
        $response = $this->executeCURL($uri, $CorpNum, $UserID);

        $RefundHistoryResult = new RefundHistoryResult();
        $RefundHistoryResult->fromJsonInfo($response);

        return $RefundHistoryResult;
    }

    // 연동회원 포인트 환불신청
    public function Refund($CorpNum, $RefundForm, $UserID = null)
    {
        $postdata = json_encode($RefundForm);

        return $this->executeCURL('/Refund', $CorpNum, $UserID, true, null, $postdata);
    }

    // 연동회원 무통장 입금신청
    public function PaymentRequest($CorpNum, $PaymentForm, $UserID = null)
    {
        $postdata = json_encode($PaymentForm);

        return $this->executeCURL('/Payment', $CorpNum, $UserID, true, null, $postdata);
    }

    // 연동회원 무통장 입금신청 정보확인
    public function GetSettleResult($CorpNum, $SettleCode, $UserID = null)
    {
        $uri  = '/Payment/' . $SettleCode;
        $response = $this->executeCURL($uri, $CorpNum, $UserID);

        $PaymentHistory = new PaymentHistory();
        $PaymentHistory->fromJsonInfo($response);

        return $PaymentHistory;
    }

    // 파트너 포인트충전 팝업 URL
    // - 2017/08/29 추가
    public function GetPartnerURL($CorpNum, $TOGO)
    {
        try {
            return $this->Linkhub->getPartnerURL($this->getsession_Token($CorpNum), $this->IsTest ? PopbillBase::ServiceID_TEST : PopbillBase::ServiceID_REAL, $TOGO, $this->UseStaticIP, $this->UseGAIP);
        } catch (LinkhubException $le) {
            throw new PopbillException($le->getMessage(), $le->getCode());
        }
    }

    // 파트너 잔여포인트 확인
    public function GetPartnerBalance($CorpNum)
    {
        try {
            return $this->Linkhub->getPartnerBalance($this->getsession_Token($CorpNum), $this->IsTest ? PopbillBase::ServiceID_TEST : PopbillBase::ServiceID_REAL, $this->UseStaticIP, $this->UseGAIP);
        } catch (LinkhubException $le) {
            throw new PopbillException($le->getMessage(), $le->getCode());
        }
    }

    // 회원 탈퇴
    public function QuitMember($CorpNum, $QuitReason, $UserID = null)
    {
        $postData = json_encode(array("quitReason" => $QuitReason));
        try {
            $response = $this->executeCURL('/QuitRequest', $CorpNum, $UserID, true, null, $postData);
            if($response->code == 1) {
                unset($this-> Token_Table[$CorpNum]);
            } 
        } catch (LinkhubException $le) {
            throw new PopbillException($le->getMessage(), $le->getCode());
        }
        return $response;
    }

    // 환불가능 포인트 조회
    public function GetRefundableBalance($CorpNum, $UserID = null)
    {
        return $this->executeCURL('/RefundPoint', $CorpNum, $UserID, false, null)->refundableBalance;
    }

    // 환불 신청 상태 조회
    public function GetRefundInfo($CorpNum, $RefundCode, $UserID = null)
    {
        if (is_null($RefundCode) || empty($RefundCode)) {
            throw new PopbillException('조회할 환불코드가 입력되지 않았습니다.');
        }
        return $this->executeCURL('/Refund/' . $RefundCode, $CorpNum, $UserID, false, null, null);
    }

    protected function executeCURL($uri, $CorpNum = null, $userID = null, $isPost = false, $action = null, $postdata = null, $isMultiPart = false, $contentsType = null, $isBinary = false, $SubmitID = null)
    {
        if ($this->__requestMode != "STREAM") {

            $targetURL = $this->getTargetURL();

            $http = curl_init($targetURL . $uri);
            $header = array();

            $header[] = 'User-Agent: PHP5 POPBILL SDK';
            if (is_null($CorpNum) == false) {
                $header[] = 'Authorization: Bearer ' . $this->getsession_Token($CorpNum);
            }
            if (is_null($userID) == false) {
                $header[] = 'x-pb-userid: ' . $userID;
            }
            if (is_null($action) == false) {
                $header[] = 'X-HTTP-Method-Override: ' . $action;
                if ($action == 'BULKISSUE') {
                    $header[] = 'x-pb-message-digest: ' . base64_encode(hash('sha1', $postdata, true));
                    $header[] = 'x-pb-submit-id: ' . $SubmitID;
                }
            }

            if ($isMultiPart == false) {
                if (is_null($contentsType) == false) {
                    $header[] = 'Content-Type: ' . $contentsType;
                } else {
                    $header[] = 'Content-Type: Application/json';
                }
            } else {
                if ($isBinary) {
                    $boundary = md5(time());
                    $header[] = "Content-Type: multipart/form-data; boundary=" . $boundary;
                    $postbody = $this->binaryPostbody($boundary, $postdata);
                } else {
                    // PHP 5.6 이상 CURL 파일전송 처리
                    if ((version_compare(PHP_VERSION, '5.5') >= 0)) {
                        curl_setopt($http, CURLOPT_SAFE_UPLOAD, true);
                        foreach ($postdata as $key => $value) {
                            if (strpos($value, '@') === 0) {
                                $filename = ltrim($value, '@');
                                if ($key == 'Filedata') {
                                    $filename = substr($filename, 0, strpos($filename, ';filename'));
                                }
                				$displayName = substr($value, strpos($value, 'filename=') + strlen('filename='));
                                $postdata[$key] = new CURLFile($filename, null, $displayName);
                            }
                        } // end of foreach
                    }
                }
            }

            if ($isPost) {
                curl_setopt($http, CURLOPT_POST, 1);
                if ($isBinary) {
                    curl_setopt($http, CURLOPT_POSTFIELDS, $postbody);
                } else {
                    curl_setopt($http, CURLOPT_POSTFIELDS, $postdata);
                }
            }

            curl_setopt($http, CURLOPT_HTTPHEADER, $header);
            curl_setopt($http, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($http, CURLOPT_ENCODING, 'gzip,deflate');
            // Connection timeout 설정
            curl_setopt($http, CURLOPT_CONNECTTIMEOUT_MS, 10 * 1000);
            // 통합 timeout 설정 
            curl_setopt($http, CURLOPT_TIMEOUT_MS, 180 * 1000);

            $responseJson = curl_exec($http);

            // curl Error 추가
            if ($responseJson == false) {
                throw new PopbillException(curl_error($http));
            }

            $http_status = curl_getinfo($http, CURLINFO_HTTP_CODE);

            $is_gzip = 0 === mb_strpos($responseJson, "\x1f" . "\x8b" . "\x08");

            if ($is_gzip) {
                $responseJson = $this->Linkhub->gzdecode($responseJson);
            }

            $contentType = strtolower(curl_getinfo($http, CURLINFO_CONTENT_TYPE));

            curl_close($http);
            if ($http_status != 200) {
                throw new PopbillException($responseJson);
            }

            if (0 === mb_strpos($contentType, 'application/pdf')) {
                return $responseJson;
            }

            return json_decode($responseJson);
        } else {
            $header = array();

            $header[] = 'Accept-Encoding: gzip,deflate';
            $header[] = 'Connection: close';
            $header[] = 'User-Agent: PHP5 POPBILL SDK';
            if (is_null($CorpNum) == false) {
                $header[] = 'Authorization: Bearer ' . $this->getsession_Token($CorpNum);
            }
            if (is_null($userID) == false) {
                $header[] = 'x-pb-userid: ' . $userID;
            }
            if (is_null($action) == false) {
                $header[] = 'X-HTTP-Method-Override: ' . $action;
                if ($action == 'BULKISSUE') {
                    $header[] = 'x-pb-message-digest: ' . base64_encode(hash('sha1', $postdata, true));
                    $header[] = 'x-pb-submit-id: ' . $SubmitID;
                }
            }
            if ($isMultiPart == false) {
                if (is_null($contentsType) == false) {
                    $header[] = 'Content-Type: ' . $contentsType;
                } else {
                    $header[] = 'Content-Type: Application/json';
                }
                $postbody = $postdata;
            } else { //Process MultipartBody.
                $eol = "\r\n";
                $mime_boundary = md5(time());
                $header[] = "Content-Type: multipart/form-data; boundary=" . $mime_boundary . $eol;
                if ($isBinary) {
                    $postbody = $this->binaryPostbody($mime_boundary, $postdata);
                } else {
                    $postbody = '';
                    if (array_key_exists('form', $postdata)) {
                        $postbody .= '--' . $mime_boundary . $eol;
                        $postbody .= 'content-disposition: form-data; name="form"' . $eol;
                        $postbody .= 'content-type: Application/json;' . $eol . $eol;
                        $postbody .= $postdata['form'] . $eol;
                        foreach ($postdata as $key => $value) {
                            if (substr($key, 0, 4) == 'file') {
                                if (substr($value, 0, 1) == '@') {
                                    $value = substr($value, 1);
                                }
                                if (file_exists($value) == FALSE) {
                                    throw new PopbillException("전송할 파일이 존재하지 않습니다.", -99999999);
                                }
                				$displayName = substr($value, strpos($value, 'filename=') + strlen('filename='));
                                $fileContents = file_get_contents($value);
                                $postbody .= '--' . $mime_boundary . $eol;
                                $postbody .= "Content-Disposition: form-data; name=\"file\"; filename=\"" .$displayName . "\"" . $eol;
                                $postbody .= "Content-Type: Application/octet-stream" . $eol . $eol;
                                $postbody .= $fileContents . $eol;
                            }
                        }
                    }

                    if (array_key_exists('Filedata', $postdata)) {
                        $postbody .= '--' . $mime_boundary . $eol;
                        if (substr($postdata['Filedata'], 0, 1) == '@') {
                            $value = substr($postdata['Filedata'], 1);
                            $splitStr = explode(';', $value);
                            $path = $splitStr[0];
                            $fileName = substr($splitStr[1], 9);
                        }
                        if (file_exists($path) == FALSE) {
                            throw new PopbillException("전송할 파일이 존재하지 않습니다.", -99999999);
                        }
                        $fileContents = file_get_contents($path);
                        $postbody .= 'content-disposition: form-data; name="Filedata"; filename="' . $this->GetBasename($fileName) . '"' . $eol;
                        $postbody .= 'content-type: Application/octet-stream;' . $eol . $eol;
                        $postbody .= $fileContents . $eol;
                    }
                    $postbody .= '--' . $mime_boundary . '--' . $eol;
                }
            }

            $params = array(
                'http' => array(
                    'ignore_errors' => TRUE,
                    'protocol_version' => '1.0',
                    'method' => 'GET',
                    'timeout' => 180
                )
            );

            if ($isPost) {
                $params['http']['method'] = 'POST';
                $params['http']['content'] = $postbody;
            }


            if ($header !== null) {
                $head = "";
                foreach ($header as $h) {
                    $head = $head . $h . "\r\n";
                }
                $params['http']['header'] = substr($head, 0, -2);
            }

            $ctx = stream_context_create($params);

            $targetURL = $this->getTargetURL();

            $response = file_get_contents($targetURL . $uri, false, $ctx);

            $is_gzip = 0 === mb_strpos($response, "\x1f" . "\x8b" . "\x08");

            if ($is_gzip) {
                $response = $this->Linkhub->gzdecode($response);
            }

            if ($http_response_header[0] != "HTTP/1.1 200 OK") {
                throw new PopbillException($response);
            }

            foreach ($http_response_header as $k => $v) {
                $t = explode(':', $v, 2);
                if (preg_match('/^Content-Type:/i', $v, $out)) {
                    $contentType = trim($t[1]);
                    if (0 === mb_strpos($contentType, 'application/pdf')) {
                        return $response;
                    }
                }
            }

            return json_decode($response);
        }
    }
    // build multipart/formdata , multipart 폼데이터 만들기
    protected function binaryPostbody($mime_boundary, $postdata)
    {
        $postbody = '';
        $eol = "\r\n";
        $postbody .= "--" . $mime_boundary . $eol
            . 'Content-Disposition: form-data; name="form"' . $eol . $eol . $postdata['form'] . $eol;

        foreach ($postdata as $key => $value) {
            if (substr($key, 0, 4) == 'name') {
                $fileName = $value;
            }
            if (substr($key, 0, 4) == 'file') {
                $postbody .= "--" . $mime_boundary . $eol
                    . 'Content-Disposition: form-data; name="' . 'file' . '"; filename="' . $fileName . '"' . $eol
                    . 'Content-Type: Application/octetstream' . $eol . $eol;
                $postbody .= $value . $eol;
            }
        }
        $postbody .= "--" . $mime_boundary . "--" . $eol;

        return $postbody;
    }

    //파일명 추출
    protected function GetBasename($path)
    {
        $pattern = '/[^\/\\\\]*$/';
        if (preg_match($pattern, $path, $matches)) {
            return $matches[0];
        }
        throw new PopbillException("파일명 추출에 실패 하였습니다.", -99999999);
    }

    // 서비스 URL
    private function getTargetURL()
    {
        if ($this->UseGAIP) {
            return ($this->IsTest ? PopbillBase::ServiceURL_GA_TEST : PopbillBase::ServiceURL_GA_REAL);
        } else if ($this->UseStaticIP) {
            return ($this->IsTest ? PopbillBase::ServiceURL_Static_TEST : PopbillBase::ServiceURL_Static_REAL);
        } else {
            return ($this->IsTest ? PopbillBase::ServiceURL_TEST : PopbillBase::ServiceURL_REAL);
        }
    }

    public function isNullOrEmpty($value)
    {
        if(is_bool($value)) {
            return is_null($value) || $value === '';
        }
        return is_null($value) || empty($value);
    }

    public function isValidDate($date)
    {
        return preg_match('/(\d{4})(0[1-9]|1[012])(0[1-9]|[12][0-9]|3[01])/', $date);
    }

    public function isValidDT($datetime) 
    {
        return preg_match('/(\d{4})(0[1-9]|1[012])(0[1-9]|[12][0-9]|3[01])(0[0-9]|1[0-9]|2[0-3])([0-5][0-9])([0-5][0-9])/', $datetime);
    }
}

class JoinForm
{
    public $LinkID;
    public $CorpNum;
    public $CEOName;
    public $CorpName;
    public $Addr;
    public $ZipCode;
    public $BizType;
    public $BizClass;
    public $ContactName;
    public $ContactEmail;
    public $ContactTEL;
    public $contactHP;
    public $contactFAX;
    public $ID;
    public $PWD;
    public $Password;
}

class UseHistoryResult
{
    public $code;
    public $total;
    public $perPage;
    public $pageNum;
    public $pageCount;
    public $list;

    public function fromJsonInfo($jsonInfo)
    {
        isset($jsonInfo->code) ? $this->code = $jsonInfo->code : null;
        isset($jsonInfo->total) ? $this->total = $jsonInfo->total : null;
        isset($jsonInfo->perPage) ? $this->perPage = $jsonInfo->perPage : null;
        isset($jsonInfo->pageNum) ? $this->pageNum = $jsonInfo->pageNum : null;
        isset($jsonInfo->pageCount) ? $this->pageCount = $jsonInfo->pageCount : null;

        $HistoryList = array();

        for ($i = 0; $i < Count($jsonInfo->list); $i++) {
            $HistoryObj = new UseHistory();
            $HistoryObj->fromJsonInfo($jsonInfo->list[$i]);
            $HistoryList[$i] = $HistoryObj;
        }
        $this->list = $HistoryList;
    }
}

class UseHistory
{
    public $itemCode;
    public $txType;
    public $txPoint;
    public $balance;
    public $txDT;
    public $userID;
    public $userName;

    public function fromJsonInfo($jsonInfo)
    {
        isset($jsonInfo->itemCode) ? $this->itemCode = $jsonInfo->itemCode : null;
        isset($jsonInfo->txType) ? $this->txType = $jsonInfo->txType : null;
        isset($jsonInfo->txPoint) ? $this->txPoint = $jsonInfo->txPoint : null;
        isset($jsonInfo->balance) ? $this->balance = $jsonInfo->balance : null;
        isset($jsonInfo->txDT) ? $this->txDT = $jsonInfo->txDT : null;
        isset($jsonInfo->userID) ? $this->userID = $jsonInfo->userID : null;
        isset($jsonInfo->userName) ? $this->userName = $jsonInfo->userName : null;
    }
}

class PaymentHistoryResult
{
    public $code;
    public $total;
    public $perPage;
    public $pageNum;
    public $pageCount;
    public $list;

    public function fromJsonInfo($jsonInfo)
    {
        isset($jsonInfo->code) ? $this->code = $jsonInfo->code : null;
        isset($jsonInfo->total) ? $this->total = $jsonInfo->total : null;
        isset($jsonInfo->perPage) ? $this->perPage = $jsonInfo->perPage : null;
        isset($jsonInfo->pageNum) ? $this->pageNum = $jsonInfo->pageNum : null;
        isset($jsonInfo->pageCount) ? $this->pageCount = $jsonInfo->pageCount : null;

        $HistoryList = array();

        for ($i = 0; $i < Count($jsonInfo->list); $i++) {
            $HistoryObj = new PaymentHistory();
            $HistoryObj->fromJsonInfo($jsonInfo->list[$i]);
            $HistoryList[$i] = $HistoryObj;
        }
        $this->list = $HistoryList;
    }
}

class PaymentHistory
{
    public $productType;
    public $productName;
    public $settleType;
    public $settlerName;
    public $settlerEmail;
    public $settleCost;
    public $settlePoint;
    public $settleState;
    public $regDT;
    public $stateDT;

    public function fromJsonInfo($jsonInfo)
    {
        isset($jsonInfo->productType) ? $this->productType = $jsonInfo->productType : null;
        isset($jsonInfo->productName) ? $this->productName = $jsonInfo->productName : null;
        isset($jsonInfo->settleType) ? $this->settleType = $jsonInfo->settleType : null;
        isset($jsonInfo->settlerName) ? $this->settlerName = $jsonInfo->settlerName : null;
        isset($jsonInfo->settlerEmail) ? $this->settlerEmail = $jsonInfo->settlerEmail : null;
        isset($jsonInfo->settleCost) ? $this->settleCost = $jsonInfo->settleCost : null;
        isset($jsonInfo->settlePoint) ? $this->settlePoint = $jsonInfo->settlePoint : null;
        isset($jsonInfo->settleState) ? $this->settleState = $jsonInfo->settleState : null;
        isset($jsonInfo->regDT) ? $this->regDT = $jsonInfo->regDT : null;
        isset($jsonInfo->stateDT) ? $this->stateDT = $jsonInfo->stateDT : null;
    }
}

class RefundHistoryResult
{
    public $code;
    public $total;
    public $perPage;
    public $pageNum;
    public $pageCount;
    public $list;

    public function fromJsonInfo($jsonInfo)
    {
        isset($jsonInfo->code) ? $this->code = $jsonInfo->code : null;
        isset($jsonInfo->total) ? $this->total = $jsonInfo->total : null;
        isset($jsonInfo->perPage) ? $this->perPage = $jsonInfo->perPage : null;
        isset($jsonInfo->pageNum) ? $this->pageNum = $jsonInfo->pageNum : null;
        isset($jsonInfo->pageCount) ? $this->pageCount = $jsonInfo->pageCount : null;

        $HistoryList = array();

        for ($i = 0; $i < Count($jsonInfo->list); $i++) {
            $HistoryObj = new RefundHistory();
            $HistoryObj->fromJsonInfo($jsonInfo->list[$i]);
            $HistoryList[$i] = $HistoryObj;
        }
        $this->list = $HistoryList;
    }
}

class RefundHistory
{
    public $reqDT;
    public $requestPoint;
    public $accountBank;
    public $accountNum;
    public $accountName;
    public $state;
    public $reason;

    public function fromJsonInfo($jsonInfo)
    {
        isset($jsonInfo->reqDT) ? $this->reqDT = $jsonInfo->reqDT : null;
        isset($jsonInfo->requestPoint) ? $this->requestPoint = $jsonInfo->requestPoint : null;
        isset($jsonInfo->accountBank) ? $this->accountBank = $jsonInfo->accountBank : null;
        isset($jsonInfo->accountNum) ? $this->accountNum = $jsonInfo->accountNum : null;
        isset($jsonInfo->accountName) ? $this->accountName = $jsonInfo->accountName : null;
        isset($jsonInfo->state) ? $this->state = $jsonInfo->state : null;
        isset($jsonInfo->reason) ? $this->reason = $jsonInfo->reason : null;
    }
}

class PaymentForm
{
    public $settlerName;
    public $settlerEmail;
    public $notifyHP;
    public $paymentName;
    public $settleCost;
}

class RefundForm
{
    public $contactname;
    public $tel;
    public $requestpoint;
    public $accountbank;
    public $accountnum;
    public $accountname;
    public $reason;
}

class CorpInfo
{
    public $ceoname;
    public $corpName;
    public $addr;
    public $bizType;
    public $bizClass;

    public function fromJsonInfo($jsonInfo)
    {
        isset($jsonInfo->ceoname) ? $this->ceoname = $jsonInfo->ceoname : null;
        isset($jsonInfo->corpName) ? $this->corpName = $jsonInfo->corpName : null;
        isset($jsonInfo->addr) ? $this->addr = $jsonInfo->addr : null;
        isset($jsonInfo->bizType) ? $this->bizType = $jsonInfo->bizType : null;
        isset($jsonInfo->bizClass) ? $this->bizClass = $jsonInfo->bizClass : null;
    }
}

class ContactInfo
{
    public $id;
    public $pwd;
    public $Password;
    public $email;
    public $hp;
    public $personName;
    public $searchAllAllowYN;
    public $searchRole;
    public $tel;
    public $fax;
    public $mgrYN;
    public $regDT;
    public $state;

    public function fromJsonInfo($jsonInfo)
    {
        isset($jsonInfo->id) ? $this->id = $jsonInfo->id : null;
        isset($jsonInfo->email) ? $this->email = $jsonInfo->email : null;
        isset($jsonInfo->hp) ? $this->hp = $jsonInfo->hp : null;
        isset($jsonInfo->personName) ? $this->personName = $jsonInfo->personName : null;
        isset($jsonInfo->searchAllAllowYN) ? $this->searchAllAllowYN = $jsonInfo->searchAllAllowYN : null;
        isset($jsonInfo->searchRole) ? $this->searchRole = $jsonInfo->searchRole : null;
        isset($jsonInfo->tel) ? $this->tel = $jsonInfo->tel : null;
        isset($jsonInfo->fax) ? $this->fax = $jsonInfo->fax : null;
        isset($jsonInfo->mgrYN) ? $this->mgrYN = $jsonInfo->mgrYN : null;
        isset($jsonInfo->regDT) ? $this->regDT = $jsonInfo->regDT : null;
        isset($jsonInfo->state) ? $this->state = $jsonInfo->state : null;
    }
}

class ChargeInfo
{
    public $unitCost;
    public $chargeMethod;
    public $rateSystem;

    public function fromJsonInfo($jsonInfo)
    {
        isset($jsonInfo->unitCost) ? $this->unitCost = $jsonInfo->unitCost : null;
        isset($jsonInfo->chargeMethod) ? $this->chargeMethod = $jsonInfo->chargeMethod : null;
        isset($jsonInfo->rateSystem) ? $this->rateSystem = $jsonInfo->rateSystem : null;
    }
}

class PopbillException extends Exception
{
    public function __construct($response, $code = -99999999, Exception $previous = null)
    {
        $Err = json_decode($response);
        if (is_null($Err)) {
            parent::__construct($response, $code);
        } else {
            parent::__construct($Err->message, $Err->code);
        }
    }

    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}
