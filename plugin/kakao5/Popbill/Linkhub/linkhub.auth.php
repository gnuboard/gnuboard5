<?php
/**
* =====================================================================================
* Class for develop interoperation with Linkhub APIs.
* Functionalities are authentication for Linkhub api products, and to support
* several base infomation(ex. Remain point).
*
* This module uses curl and openssl for HTTPS Request. So related modules must
* be installed and enabled.
*
* http://www.linkhub.co.kr
* Author : Kim Seongjun
* Contributor : Jeong Yohan (code@linkhubcorp.com)
* Contributor : Jeong Wooseok (code@linkhubcorp.com)
* Written : 2017-08-29
* Updated : 2024-10-15
*
* Thanks for your interest.
* We welcome any suggestions, feedbacks, blames or anythings.
*
* Update Log
* - 2017/08/29 GetPartnerURL API added
* - 2023/02/10 Request Header User-Agent added
* - 2023/08/02 AuthURL Setter added
* - 2023/08/11 ServiceURL Rename
* - 2024/09/26 Timeout added
* ======================================================================================
*/
class Linkhub
{
    const VERSION = '2.0';
    const ServiceURL = 'https://auth.linkhub.co.kr';
    const ServiceURL_Static = 'https://static-auth.linkhub.co.kr';
    const ServiceURL_GA = 'https://ga-auth.linkhub.co.kr';
    
    private $__LinkID;
    private $__SecretKey;
    private $__requestMode = LINKHUB_COMM_MODE;
    private $__ServiceURL;

    public function getSecretKey(){
        return $this->__SecretKey;
    }
    public function getLinkID(){
        return $this->__LinkID;
    }
    public function ServiceURL($V){
        $this->__ServiceURL = $V;
    }
    private static $singleton = null;
    public static function getInstance($LinkID,$secretKey)
    {
        if(is_null(Linkhub::$singleton)) {
            Linkhub::$singleton = new Linkhub();
        }
        Linkhub::$singleton->__LinkID = $LinkID;
        Linkhub::$singleton->__SecretKey = $secretKey;

        return Linkhub::$singleton;
    }
    public function gzdecode($data){
        return gzinflate(substr($data, 10, -8));
    }

    private function executeCURL($url,$header = array(),$isPost = false, $postdata = null) {
        $base_header = array();
        $base_header[] = 'Accept-Encoding: gzip,deflate';
        $base_header[] = 'User-Agent: PHP5 LINKHUB SDK';
        $arr_header = $header + $base_header;

        if($this->__requestMode != "STREAM") {
            $http = curl_init($url);

            if($isPost) {
                curl_setopt($http, CURLOPT_POST,1);
                curl_setopt($http, CURLOPT_POSTFIELDS, $postdata);
            }
            curl_setopt($http, CURLOPT_HTTPHEADER,$arr_header);
            curl_setopt($http, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($http, CURLOPT_ENCODING, 'gzip,deflate');
            // Connection timeout 설정
            curl_setopt($http, CURLOPT_CONNECTTIMEOUT_MS, 10 * 1000);
            // 통합 timeout 설정
            curl_setopt($http, CURLOPT_TIMEOUT_MS, 180 * 1000);

            $responseJson = curl_exec($http);

            // curl Error 추가
            if ($responseJson == false) {
                throw new LinkhubException(curl_error($http));
            }

            $http_status = curl_getinfo($http, CURLINFO_HTTP_CODE);

            curl_close($http);

            $is_gzip = 0 === mb_strpos($responseJson, "\x1f" . "\x8b" . "\x08");

            if ($is_gzip) {
                $responseJson = $this->gzdecode($responseJson);
            }

            if($http_status != 200) {
                throw new LinkhubException($responseJson);
            }

            return json_decode($responseJson);

        }
        else {
            if($isPost) {
                $params = array('http' => array(
                     'ignore_errors' => TRUE,
                     'method' => 'POST',
                     'protocol_version' => '1.0',
                     'content' => $postdata,
                     'timeout' => 180
                    ));
            } else {
                $params = array('http' => array(
                     'ignore_errors' => TRUE,
                     'method' => 'GET',
                     'protocol_version' => '1.0',
                     'timeout' => 180
                    ));
            }
            if ($arr_header !== null) {
                $head = "";
                foreach($arr_header as $h) {
                    $head = $head . $h . "\r\n";
                }
                $params['http']['header'] = substr($head,0,-2);
            }
            $ctx = stream_context_create($params);
            $response = file_get_contents($url, false, $ctx);

            $is_gzip = 0 === mb_strpos($response , "\x1f" . "\x8b" . "\x08");
            if($is_gzip){
                $response = $this->gzdecode($response);
            }

            if ($http_response_header[0] != "HTTP/1.1 200 OK") {
                throw new LinkhubException($response);
            }

            return json_decode($response);
        }
    }

    public function getTime($useStaticIP = false, $useLocalTimeYN = true, $useGAIP = false) {
        if($useLocalTimeYN) {
            $replace_search = array("@","#");
            $replace_target = array("T","Z");

            $date = new DateTime('now', new DateTimeZone('UTC'));

            return str_replace($replace_search, $replace_target, $date->format('Y-m-d@H:i:s#'));
        }
        if($this->__requestMode != "STREAM") {
            $targetURL = $this->getTargetURL($useStaticIP, $useGAIP);

            $http = curl_init($targetURL.'/Time');

            curl_setopt($http, CURLOPT_RETURNTRANSFER, TRUE);
            // Read timeout 설정 
            curl_setopt($http, CURLOPT_TIMEOUT_MS, 180 * 1000);
            // Connection timeout 설정
            curl_setopt($http, CURLOPT_CONNECTTIMEOUT_MS, 10 * 1000);

            $response = curl_exec($http);

            // curl Error 추가
            if ($response == false) {
                throw new LinkhubException(curl_error($http));
            }

            $http_status = curl_getinfo($http, CURLINFO_HTTP_CODE);

            curl_close($http);

            if($http_status != 200) {
                throw new LinkhubException($response);
            }
            return $response;

        } else {
            $header = array();
            $header[] = 'Connection: close';
            $params = array('http' => array(
                 'ignore_errors' => TRUE,
                 'protocol_version' => '1.0',
                 'method' => 'GET',
                 'timeout' => 180
            ));
            if ($header !== null) {
                $head = "";
                foreach($header as $h) {
                    $head = $head . $h . "\r\n";
                }
                $params['http']['header'] = substr($head,0,-2);
            }

            $ctx = stream_context_create($params);

            $targetURL = $this->getTargetURL($useStaticIP, $useGAIP);

            $response = (file_get_contents( $targetURL.'/Time', false, $ctx));

            if ($http_response_header[0] != "HTTP/1.1 200 OK") {
                throw new LinkhubException($response);
            }
            return $response;
        }
    }

    public function getToken($ServiceID, $access_id, array $scope = array() , $forwardIP = null, $useStaticIP = false, $useLocalTimeYN = true, $useGAIP = false)
    {
        $xDate = $this->getTime($useStaticIP, $useLocalTimeYN, $useGAIP);

        $uri = '/' . $ServiceID . '/Token';
        $header = array();

        $TokenRequest = new TokenRequest();
        $TokenRequest->access_id = $access_id;
        $TokenRequest->scope = $scope;

        $postdata = json_encode($TokenRequest);

        $digestTarget = 'POST'.chr(10);
        $digestTarget = $digestTarget.base64_encode(hash('sha256',$postdata,true)).chr(10);
        $digestTarget = $digestTarget.$xDate.chr(10);
        if(!(is_null($forwardIP) || $forwardIP == '')) {
            $digestTarget = $digestTarget.$forwardIP.chr(10);
        }
        $digestTarget = $digestTarget.Linkhub::VERSION.chr(10);
        $digestTarget = $digestTarget.$uri;

        $digest = base64_encode(hash_hmac('sha256',$digestTarget,base64_decode(strtr($this->__SecretKey, '-_', '+/')),true));

        $header[] = 'x-lh-date: '.$xDate;
        $header[] = 'x-lh-version: '.Linkhub::VERSION;
        if(!(is_null($forwardIP) || $forwardIP == '')) {
            $header[] = 'x-lh-forwarded: '.$forwardIP;
        }

        $header[] = 'Authorization: LINKHUB '.$this->__LinkID.' '.$digest;
        $header[] = 'Content-Type: Application/json';
        $header[] = 'Connection: close';

        $targetURL = $this->getTargetURL($useStaticIP, $useGAIP);

        return $this->executeCURL($targetURL.$uri , $header,true,$postdata);
	}


    public function getBalance($bearerToken, $ServiceID, $useStaticIP = false, $useGAIP = false)
    {
        $header = array();
        $header[] = 'Authorization: Bearer '.$bearerToken;
        $header[] = 'Connection: close';

        $targetURL = $this->getTargetURL($useStaticIP, $useGAIP);
        $uri = '/'.$ServiceID.'/Point';

        $response = $this->executeCURL($targetURL.$uri,$header);
        return $response->remainPoint;

    }

    public function getPartnerBalance($bearerToken, $ServiceID, $useStaticIP = false, $useGAIP = false)
    {
        $header = array();
        $header[] = 'Authorization: Bearer '.$bearerToken;
        $header[] = 'Connection: close';

        $targetURL = $this->getTargetURL($useStaticIP, $useGAIP);
        $uri = '/'.$ServiceID.'/PartnerPoint';

        $response = $this->executeCURL($targetURL.$uri,$header);
        return $response->remainPoint;
        }

    /*
    * 파트너 포인트 충전 팝업 URL 추가 (2017/08/29)
    */
    public function getPartnerURL($bearerToken, $ServiceID, $TOGO, $useStaticIP = false, $useGAIP = false)
    {
        $header = array();
        $header[] = 'Authorization: Bearer '.$bearerToken;
        $header[] = 'Connection: close';

        $targetURL = $this->getTargetURL($useStaticIP, $useGAIP);
        $uri = '/'.$ServiceID.'/URL?TG='.$TOGO;

        $response = $this->executeCURL($targetURL.$uri, $header);
        return $response->url;
    }

    private function getTargetURL($useStaticIP, $useGAIP){
        if(isset($this->__ServiceURL)) {
            return $this->__ServiceURL;
        }
        
        if($useGAIP){
            return Linkhub::ServiceURL_GA;
        } else if($useStaticIP){
            return Linkhub::ServiceURL_Static;
        } else {
            return Linkhub::ServiceURL;
        }
    }
}

class TokenRequest
{
    public $access_id;
    public $scope;
}

class LinkhubException extends Exception
{
    public function __construct($response, Exception $previous = null) {
        $Err = json_decode($response);
        if(is_null($Err)) {
            parent::__construct($response, -99999999);
        }
        else {
            parent::__construct($Err->message, $Err->code);
        }
    }

    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}

?>
