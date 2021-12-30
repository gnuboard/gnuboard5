<?php


/*____________________________________________________________

Copyright (C) 2016 NICE IT&T
*
* 해당 라이브러리는 수정하시는경우 승인및 취소에 문제가 발생할 수 있습니다.
* 임의로 수정된 코드에 대한 책임은 전적으로 수정자에게 있음을 알려드립니다.

*	@ description		: 주요 변수 설정.
*	@ name				: NicepayLiteCommon.php
*	@ auther			: NICEPAY I&T (tech@nicepay.co.kr)
*	@ date				: 
*	@ modify			
	
	
*____________________________________________________________
*/

/*GLOBAL*/
define("PROGRAM", "NicepayLite");
define("LIB_LANG", "PHP");
define("VERSION", "1.3.1");
define("BUILDDATE", "20180416");
define("AUTHOR", "naun_cjlee");
define("TID_LEN", 30);

define("NICEPAY_MODULE_CL","LITE"); // TX/LITE 구분
define("NICEPAY_MODULE_INFO","PHP_LITE_1.3.1"); // 모듈 상세 버전 정보

/*HTTP SERVER INFO*/
//----------------
//PRODUCTION
//----------------
define("HTTP_SERVER", "web.nicepay.co.kr");
define("HTTP_PORT", 80);

define("HTTP_SSL_SERVER", "web.nicepay.co.kr");
define("HTTP_SSL_PORT", 443);

/*TIMEOUT*/
define("TIMEOUT_CONNECT", 5);
define("TIMEOUT_READ", 25);

/*LOG LEVEL*/
define("CRITICAL", 1);
define("ERROR", 2);
define("NOTICE", 3);
define("INFO", 5);
define("DEBUG", 7);

/*HTTP CALL ERROR CODE*/

/*NicepayLite ERROR CODE*/
define("ERR_WRONG_HOME", "PL01");
define("ERR_OPENLOG", "PL02");
define("ERR_SSLCONN", "PL03");
define("ERR_CONN", "PL04");
define("READ_TIMEOUT_ERR", "PL05");
define("ERR_WRONG_ACTIONTYPE", "PL10");
define("ERR_WRONG_PARAMETER", "PL11");
define("ERR_MISSING_PARAMETER", "PL12");
define("ERR_MAKE_PLAINTEXT", "PL20");
define("ERR_FAIL_TRANSPORT", "PL30");
define("ERR_NO_RESPONSE", "PL40");

/*-----------------------------------------------------*/
/* Global Function                                     */
/*-----------------------------------------------------*/
function Base64Encode( $str )
{   
  return substr(chunk_split(base64_encode( $str ),64,"\n"),0,-1)."\n";
}   
function GetMicroTime()
{
    list($usec, $sec) = explode(" ", microtime());
    return (float)$usec + (float)$sec;
}
function SetTimestamp()
{
    $m = explode(' ',microtime());
    list($totalSeconds, $extraMilliseconds) = array($m[1], (int)round($m[0]*1000,3));
    return date("Y-m-d H:i:s", $totalSeconds) . ":$extraMilliseconds";
}
function SetTimestamp1()
{
    $m = explode(' ',microtime());
    list($totalSeconds, $extraMilliseconds) = array($m[1], (int)round($m[0]*10000,4));
    return date("ymdHis", $totalSeconds) . "$extraMilliseconds";
}

/**
 * 결제수단에 따른 TID 생성 로직
 * 생성해야할 결제수단은 PHP-TX모듈 기준을 따름
 */
function genTIDNew($mid, $payMethod) {
	$svcCd = "";
	$svcPrdtCd = "01";
	
	if("CARD" == $payMethod){
		$svcCd = "01";
	}else if("BANK" == $payMethod){
		$svcCd = "02";
	}else if("VBANK" == $payMethod){
		$svcCd = "03";
	}else if("CELLPHONE" == $payMethod){
		$svcCd = "05";
	}else if("CPBILL" == $payMethod){
		$svcCd = "06";
	}else if("VBANK_BULK" == $payMethod){
		$svcCd = "03";
	}else if("CASHRCPT" == $payMethod){
		$svcCd = "04";
	}else if("GIFT_SSG" == $payMethod){
		// SSG머니
		$svcCd = "21";
	}else if("SSG_BANK" == $payMethod){
		// SSG 은행직불
		$svcCd = "24";
	}else {
		// 여기에 명시하지 않은 결제수단은 TID 보내지 않고 서버에서 생성하도록 함 (기존 로직)
		return "";
	}
	
	return genTID($mid, $svcCd, $svcPrdtCd);
}

function genTID($mid,$svcCd,$svcPrdtCd){	
	$buffer = "";	
	$nanotime = microtime(true);
		
	$nanoString = str_replace(".","",$nanotime);
	
	$nanoStrLength = strlen($nanoString);
	
	$yyyyMMddHHmmss = date("YmdHis");

	$appendNanoStr = substr($nanoString,10,1);
	
	$buffer = $mid;
	$buffer .= $svcCd;
	$buffer .= $svcPrdtCd;
	
	$buffer .= substr($yyyyMMddHHmmss,2,strlen($yyyyMMddHHmmss));
	$buffer .= $appendNanoStr;
	
	$buffer .= rand(0, 9);
	$buffer .= rand(0, 9);	
	$buffer .= rand(0, 9);
	return $buffer;
}

function getLibInfo() {
	return PROGRAM."-".LIB_LANG."-".VERSION."-".BUILDDATE."-".AUTHOR;
}

/**
 * 20180411 가맹점 모듈 정보
 * 
 * @return string
 */
function getNicepayModuleInfo() {
	return NICEPAY_MODULE_CL."^".NICEPAY_MODULE_INFO;
}

/**
 * AES 암호화 (지원버전: PHP 4 >= 4.0.2, PHP 5, DEPRECATED PHP 7.1.0)
 * AES/ECB/PKCS5padding, 암호화후 Base64 인코딩/디코딩
 */
function aesEncrypt($data, $key){
     $block = mcrypt_get_block_size('rijndael_128', 'ecb');
     $pad = $block - (strlen($data) % $block);
     $data .= str_repeat(chr($pad), $pad); 
     return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, substr($key, 0, 16), $data, MCRYPT_MODE_ECB));
}

/**
 * AES 복호화 (지원버전: PHP 4 >= 4.0.2, PHP 5, DEPRECATED PHP 7.1.0)
 * AES/ECB/PKCS5padding, Base64 인코딩/디코딩후 복호화
 */
function aesDecrypt($data, $key){
     $data = base64_decode($data);
     $data = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, substr($key, 0, 16), $data, MCRYPT_MODE_ECB);
     $block = mcrypt_get_block_size('rijndael_128', 'ecb');
     $pad = ord($data[($len = strlen($data)) - 1]);
     $len = strlen($data);
     $pad = ord($data[$len-1]);
     return substr($data, 0, strlen($data) - $pad);
}

/**
 * AES 암호화 (지원버전: PHP 5 >= 5.3.0, PHP 7)
 * http://php.net/manual/en/function.openssl-encrypt.php
 */
function aesEncryptSSL($data, $key)
{
	$iv = openssl_random_pseudo_bytes(16);
	$endata = @openssl_encrypt($data, "AES-128-ECB", $key, true, $iv);
	return base64_encode($endata);
}

/**
 * AES 복호화 (지원버전: PHP 5 >= 5.3.0, PHP 7)
 * http://php.net/manual/en/function.openssl-decrypt.php
 */
function aesDecryptSSL($data, $key)
{
	$iv = openssl_random_pseudo_bytes(16);	
	
	$data = base64_decode($data);
	$decData = @openssl_decrypt($data, "AES-128-ECB", $key, OPENSSL_RAW_DATA, $iv);
	return $decData;
}

function getIfEmptyDefault($str, $default)
{
	if ($str == null || $str == "") return $default;
	
	return $str;
}

function has_hangul($str) {
	$cnt = strlen($str);
	for($i=0; $i<$cnt; $i++) {
		$char = ord($str[$i]);
		if($char >= 0xa1 && $char <= 0xfe) {
			return true;
		}
	}
	return false;
}

/*-----------------------------------------------------*/
/* Http Proxy Class		                               */
/* HTTP												   */ 
/* HTTPS( PHP5.1.4 & OpenSSL 필요)               	   */
/*-----------------------------------------------------*/
class HttpClient 
{
    var $sock=0;
    var $host;
    var $port;
    var $ssl;
    var $status;
    var $headers="";
    var $body="";
    var $reqeust;
	var $errorcode;
	var $errormsg;

    function __construct($ssl, $ReqHost, $ReqPort) 
	{
    	if($ReqHost != null && $ReqHost != "") {
    		$this->host = $ReqHost;
    		
    		if( $ssl == "true" )
    		{
    			$this->port = HTTP_SSL_PORT;
    			$this->ssl = "ssl://";
    		}
    		else
    			{
    			$this->port = HTTP_PORT;
    		}
    		
    		// 별도 포트를 사용하는 서버를 위한 처리 추가
    		if ($ReqPort != null && $ReqPort != "") {
    			$this->port = $ReqPort;
    		}
    	} else {
    		if( $ssl == "true" )
    		{
    			$this->host = HTTP_SSL_SERVER;
    			$this->port = HTTP_SSL_PORT;
    			$this->ssl = "ssl://";
    		}
    		else
    		{
    			$this->host = HTTP_SERVER;
    			$this->port = HTTP_PORT;
    		}
    	}
    }

	function HttpConnect($NICELog)
	{
	    $NICELog->WriteLog("Connect to ".$this->ssl.$this->host.":".$this->port );
        if (!$this->sock = @fsockopen( $this->ssl.$this->host, $this->port, $errno, $errstr, TIMEOUT_CONNECT)) 
		{
			$this->errorcode = $errno;
            switch($errno) 
			{
                case -3:
                    $this->errormsg = 'Socket creation failed (-3)';
                case -4:
                    $this->errormsg = 'DNS lookup failure (-4)';
                case -5:
                    $this->errormsg = 'Connection refused or timed out (-5)';
                default:
                    $this->errormsg = 'Connection failed ('.$errno.')';
                $this->errormsg .= ' '.$errstr;
            }
			return false;
        }
		$NICELog->WriteLog($this->ssl.$this->host.":".$this->port." Server Connect OK" );
		return true;
	}
	
	function HttpRequest($uri, $data, $NICELog)
	{
    	$this->headers="";
    	$this->body="";

		$postdata = $this->buildQueryString($data);

		/*Write*/
		$request  = "POST ".$uri." HTTP/1.0\r\n";
		$request .= "Connection: close\r\n";
		$request .= "Host: ".$this->host."\r\n";
		$request .= "Content-type: application/x-www-form-urlencoded\r\n";
		$request .= "Content-length: ".strlen($postdata)."\r\n";
		$request .= "Accept: */*\r\n";
		$request .= "\r\n";
		$request .= $postdata."\r\n";
		$request .= "\r\n";
		fwrite($this->sock, $request);

		$NICELog->WriteLog("MSG_TO_SVR::[".$uri."]" );

		/*Read*/
		stream_set_blocking($this->sock, FALSE ); 

		$atStart = true;
		$IsHeader = true;
		$timeout = false;
		$start_time= time();
		while ( !feof($this->sock) && !$timeout )
		{
			$line = fgets($this->sock, 4096);
			$diff=time()-$start_time;
			if( $diff >= TIMEOUT_READ )
			{
				$timeout = true;
			}
			if( $IsHeader )
			{
				if( $line == "" ) //for stream_set_blocking
				{
					continue;
				}
				if( substr( $line, 0, 2 ) == "\r\n" )  //end of header
				{
					$IsHeader = false;
					continue;
				}
  				$this->headers .= $line;
            	if ($atStart) 
				{
                	$atStart = false;
                	if (!preg_match('/HTTP\/(\\d\\.\\d)\\s*(\\d+)\\s*(.*)/', $line, $m)) 
					{
                    	$this->errormsg = "Status code line invalid: ".htmlentities($line);
						fclose( $this->sock );
                    	return false;
                	}
                	$http_version = $m[1];
                	$this->status = $m[2];
                	$status_string = $m[3];
                	continue;
				}
            }
			else
			{
  				$this->body .= $line;
			}
		}
		fclose( $this->sock );

		if( $timeout )
		{
			$this->errorcode = READ_TIMEOUT_ERR;
            $this->errormsg = "Socket Timeout(".$diff."SEC)";
			$NICELog->WriteLog($this->errormsg );
			return false;
		}

		return true;
	}

    function buildQueryString($data) 
	{
        $querystring = '';
        if (is_array($data)) 
		{
            foreach ($data as $key => $val) 
			{
                if (is_array($val)) 
				{
                    foreach ($val as $val2) 
					{
												if( $key != "key" )
                        	$querystring .= urlencode($key).'='.urlencode($val2).'&';
                    }
                } 
				else 
				{
										if( $key != "key" )
                    	$querystring .= urlencode($key).'='.urlencode($val).'&';
                }
            }
            $querystring = substr($querystring, 0, -1);
        } 
		else 
		{
            $querystring = $data;
        }
        return $querystring;
    }
	function NetCancel()
	{
		return true;
	}
    function getStatus() 
	{
        return $this->status;
    }
    function getBody() 
	{
        return $this->body;
    }
    function getHeaders() 
	{
        return $this->headers;
    }
    function getErrorMsg() 
	{
        return $this->errormsg;
    }
    function getErrorCode() 
	{
        return $this->errorcode;
    }
}

?>