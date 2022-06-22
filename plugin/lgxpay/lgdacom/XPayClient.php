<?php
class XPayClient
{
	public $ch = null;
	public $debug = false;
	public $bTest = false;
	public $error_msg = null;
	public $home_dir = null;
	public $mode = null;
	public $TX_ID = null;
	public $MID = null;
	public $Auth_Code = null;
	public $config = array();
	public $Post = array();
	public $response_json = null;
	public $response_array = array();
	public $response_code = null;
	public $response_msg = null;
	public $log_file  = null;
	public $err_label = array("FATAL","ERROR","WARN ","INFO ","DEBUG");
	public $INFO = array("LGD_TXID","LGD_AUTHCODE","LGD_MID","LGD_OID","LGD_TXNAME","LGD_PAYKEY","LGD_RESPCODE","LGD_RESPMSG");
	public $DEBUG = array("LGD_TXID","LGD_AUTHCODE","LGD_MID","LGD_TID","LGD_OID","LGD_PAYTYPE","LGD_PAYDATE","LGD_TXNAME","LGD_PAYKEY","LGD_RESPCODE","LGD_RESPMSG");

	function IsAcceptLog($ParamName,$LogLevel)
	{
	    if(LGD_LOG_DEBUG == $LogLevel)
	    {
	        if (in_array($ParamName,$this->DEBUG,true)) return true;
	    }
	    else if(LGD_LOG_INFO == $LogLevel)
	    {
	        if (in_array($ParamName,$this->INFO,true)) return true;
		}
	    return false;
	}

	public function __construct($home_dir,$mode="real")
	{
		if(!function_exists('json_decode'))
		{
			function json_decode($content,$assoc=false)
			{
				require_once 'JSON.php';
				if ( $assoc )
				{
					$json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
				}
				else 
				{
					$json = new Services_JSON;
				}
				return $json->decode($content);
			}
		}
		define("LGD_USER_AGENT","XPayClient (4.0.0.0/PHP)");
		define("LGD_LOG_FATAL",0);
		define("LGD_LOG_ERROR",1);
		define("LGD_LOG_WARN",2);
		define("LGD_LOG_INFO",3);
		define("LGD_LOG_DEBUG",4);
		define("LGD_ERR_NO_HOME_DIR","10001");
		define("LGD_ERR_NO_MALL_CONFIG","10002");
		define("LGD_ERR_NO_LGDACOM_CONFIG","10003");
		define("LGD_ERR_NO_MID","10004");
		define("LGD_ERR_OUT_OF_MEMORY","10005");
		define("LGD_ERR_NO_SECURE_PROTOCOLS","10007");
		define("LGD_ERR_HTTP_URL","20001");
		define("LGD_ERR_RESOLVE_HOST","20002");
		define("LGD_ERR_RESOLVE_PROXY","20003");
		define("LGD_ERR_CONNECT","20004");
		define("LGD_ERR_WRITE","20005");
		define("LGD_ERR_READ","20006");
		define("LGD_ERR_SEND","20007");
		define("LGD_ERR_RECV","20008");
		define("LGD_ERR_TIMEDOUT","20009");
		define("LGD_ERR_SSL","20101");
		define("LGD_ERR_CURL","20201");
		define("LGD_ERR_JSON_DECODE","40001");
		if(!isset($_SESSION))
		{
			session_start();
		}
		$this->home_dir = $home_dir;
		if (!file_exists($home_dir)) 
		{
			$this->response_code = LGD_ERR_NO_HOME_DIR;
			$this->response_msg = "home_dir [".$home_dir."] does not exist";
			trigger_error($this->response_msg, E_USER_ERROR);	
		}
		else if (!file_exists($home_dir."/conf/mall.conf")) 
		{
			$this->response_code = LGD_ERR_NO_MALL_CONFIG;
			$this->response_msg = "config file [".$home_dir."/conf/mall.conf] does not exist";
			trigger_error($this->response_msg, E_USER_ERROR);	
		}
		else if (!file_exists($home_dir."/conf/lgdacom.conf")) 
		{
			$this->response_code = LGD_ERR_NO_LGDACOM_CONFIG;
			$this->response_msg = "config file [".$home_dir."/conf/lgdacom.conf] does not exist";
			trigger_error($this->response_msg, E_USER_ERROR);	
		}
		$array1 = parse_ini_file($home_dir . "/conf/mall.conf");
		foreach($array1 as $name => $value)
		{
			$tempValue = $name;
			if( strpos($tempValue,"MID_") !== FALSE )
			{
				$tempValue = substr($tempValue,4,strlen($tempValue));
				$name = $tempValue;
				$temparray = array($name => $value);
				$array1 += $temparray;
				break;
			}
		}
		$array2 = parse_ini_file($home_dir . "/conf/lgdacom.conf");
		$this->config = $array1 + $array2;

        // log_dir 재설정
        $this->config["log_dir"] = $home_dir."/log";
        $random_str = isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'].$_SERVER['DOCUMENT_ROOT'].date("Ymd") : $_SERVER['DOCUMENT_ROOT'].date("Ymd");

		$this->log_file = $this->config["log_dir"] . "/log_" . date("Ymd") . '_' . substr(md5($random_str), 0, 12) . ".log";
		if (!file_exists($this->config["log_dir"])) 
		{
			mkdir($this->config["log_dir"], "0777", true);
		}
		$this->log("XPayClient initialize [".$home_dir."] [".$mode."]", LGD_LOG_INFO);
		if (strtolower($mode) == "test") 
		{
			$this->bTest = true;
			$this->debug = false;
		}
		$this->init();
	}
	function array_push_associative(&$arr) 
	{
		$args = func_get_args();
		foreach ($args as $arg) 
		{
			if (is_array($arg)) 
			{
				foreach ($arg as $key => $value) 
				{
					$arr[$key] = $value;
					$ret++;
				}
			}
			else
			{
				$arr[$arg] = "";
			}
		}
		return $ret;
	}
	function init()
	{
		$this->ch = curl_init();
		curl_setopt($this->ch, CURLOPT_USERAGENT, LGD_USER_AGENT);
		curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($this->ch, CURLOPT_ENCODING , 'gzip, deflate');
		if ($this->config["verify_cert"] == 0) 
		{
			curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
			$this->log("Do not verify server Certificate", LGD_LOG_WARN);
		}
		if ($this->config["verify_host"] == 0) 
		{
			curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 0);
			$this->log("Do not verify host Domain", LGD_LOG_WARN);
		}
		// TLS 설정
		//; 512 (TLS1.1) , 2048 (TLS1.2) , 2560 (TLS1.0)
		//CURL_SSLVERSION_DEFAULT (0), CURL_SSLVERSION_TLSv1 (1), CURL_SSLVERSION_SSLv2 (2), 
		//CURL_SSLVERSION_SSLv3 (3), CURL_SSLVERSION_TLSv1_0 (4), CURL_SSLVERSION_TLSv1_1 (5) or CURL_SSLVERSION_TLSv1_2 (6). 
		if( $this->config['default_secure_protocols'] == "512" ) 
		{
			curl_setopt($this->ch, CURLOPT_SSLVERSION , 5 );
			//echo "default_secure_protocols = " . $this->config['default_secure_protocols'] . "<br>";
		}
		else if ( $this->config['default_secure_protocols'] == "2048" ) 
		{
			curl_setopt($this->ch, CURLOPT_SSLVERSION , 6 );
			//echo "default_secure_protocols = " . $this->config['default_secure_protocols'] . "<br>";
		}
		else if ( $this->config['default_secure_protocols'] == "2560" ) 
		{
			curl_setopt($this->ch, CURLOPT_SSLVERSION , 4 );
			//echo "default_secure_protocols = " . $this->config['default_secure_protocols'] . "<br>";
		}
		else 
		{
			curl_setopt($this->ch, CURLOPT_SSLVERSION , 0);
			//echo "default_secure_protocols = " . $this->config['default_secure_protocols'] . "<br>";
		}
		curl_setopt($this->ch, CURLOPT_CAINFO, $this->home_dir."/conf/ca-bundle.crt");
	}
	function Init_TX($MID)
	{
		if ($this->config[$MID] == null) 
		{
			$this->response_code = LGD_ERR_NO_MID;
			$this->response_msg = "Key for MID [".$MID."] does not exist in mall.conf";
			$this->log($this->response_msg, LGD_LOG_FATAL);
			return false;
		}
		$this->TX_ID = $this->Gen_TX_ID($MID);
		$this->MID = $MID;
		$this->Auth_Code = $this->Gen_Auth_Code($this->TX_ID, $MID);
		$this->Post = array("LGD_TXID" => $this->TX_ID,"LGD_AUTHCODE" => $this->Auth_Code,"LGD_MID" => $MID);
		return true;
	}
	function GenerateGUID()
	{
		if (function_exists('com_create_guid'))
		{
			return com_create_guid();
		}
		else
		{
			mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
			$charid = strtoupper(md5(uniqid(rand(), true)));
			$hyphen = chr(45);
			//$uuid = chr(123).substr($charid, 0, 8).$hyphen.substr($charid, 8, 4).$hyphen.substr($charid,12, 4).$hyphen.substr($charid,16, 4).$hyphen.substr($charid,20,12).chr(125);
			$uuid = $charid;
			return $uuid;
		}
	}
	function Get_Unique()
	{
		if(isset($_SESSION['tx_counter'])) $_SESSION['tx_counter'] = $_SESSION['tx_counter'] + 1;
		else $_SESSION['tx_counter'] = 1;
		return session_id().$_SESSION['tx_counter'].$this->GenerateGUID();
	}
	function Gen_TX_ID($MID)
	{
		$now = date("YmdHis");
        $server_id = isset($this->config['server_id']) ? $this->config['server_id'] : '';
		$header = $MID . "-" . $server_id . $now;
		$tx_id = $header . sha1($header.$this->Get_Unique());
		return $tx_id;
	}
	function Gen_Auth_Code($tx_id, $MID)
	{
		$auth_code = sha1($tx_id . $this->config[$MID]);
		return $auth_code;
	}
	function Set($name, $value)
	{
		$this->Post[$name] = $value;
	}
	function Check_Auto_Rollback($errno)
	{
		//CURLE_OPERATION_TIMEDOUT 
		if ($errno == 28) return true;
		else return false;
	}
	function set_curl_error()
	{
		switch (curl_errno($this->ch)) 
		{
		case 1: // CURLE_UNSUPPORTED_PROTOCOL:
		case 3: // CURLE_URL_MALFORMAT:
			$this->response_code = LGD_ERR_HTTP_URL;
			$this->response_msg = "URL error";
			break;
		case 6: // CURLE_COULDNT_RESOLVE_HOST:
			$this->response_code = LGD_ERR_RESOLVE_HOST;
			$this->response_msg = "Resolve host error";
			break;
		case 5: // CURLE_COULDNT_RESOLVE_PROXY:
			$this->response_code = LGD_ERR_RESOLVE_PROXY;
			$this->response_msg = "Resolve proxy error";
			break;
		case 7: // CURLE_COULDNT_CONNECT:
			$this->response_code = LGD_ERR_CONNECT;
			$this->response_msg = "Could not connect error";
			break;
		case 23: // CURLE_WRITE_ERROR:
			$this->response_code = LGD_ERR_WRITE;
			$this->response_msg = "Write error";
			break;
		case 26: // CURLE_READ_ERROR:
			$this->response_code = LGD_ERR_READ;
			$this->response_msg = "Read error";
			break;
		case 55: // CURLE_SEND_ERROR:
			$this->response_code = LGD_ERR_SEND;
			$this->response_msg = "Send error";
			break;
		case 56: // CURLE_RECV_ERROR:
			$this->response_code = LGD_ERR_RECV;
			$this->response_msg = "Recv error";
			break;
		case 27: // CURLE_OUT_OF_MEMORY:
			$this->response_code = LGD_ERR_OUT_OF_MEMORY;
			$this->response_msg = "Out of memory error";
			break;
		case 28: // CURLE_OPERATION_TIMEDOUT :
			$this->response_code = LGD_ERR_TIMEDOUT;
			$this->response_msg = "Timeout error";
			break;
		case 35: // CURLE_SSL_CONNECT_ERROR:
		case 51: // CURLE_PEER_FAILED_VERIFICATION:
		case 53: // CURLE_SSL_ENGINE_NOTFOUND:
		case 54: // CURLE_SSL_ENGINE_SETFAILED:
		case 58: // CURLE_SSL_CERTPROBLEM:
		case 59: // CURLE_SSL_CIPHER:
		case 60: // CURLE_SSL_CACERT:
		case 64: // CURLE_USE_SSL_FAILED:
		case 66: // CURLE_SSL_ENGINE_INITFAILED:
		case 77: // CURLE_SSL_CACERT_BADFILE:
		case 80: // CURLE_SSL_SHUTDOWN_FAILED:
		case 82: // CURLE_SSL_CRL_BADFILE:
		case 83: // CURLE_SSL_ISSUER_ERROR:
			$this->response_code = LGD_ERR_SSL;
			$this->response_msg = "SSL error";
			break;
		default:
			$this->response_code = LGD_ERR_CURL;
			$this->response_msg = "CURL error";
			break;
		}
		$this->response_msg .= "; cURL error code = ".curl_errno($this->ch)." msg = ".curl_error($this->ch);
	}
	function TX($bRollbackOnError = true)
	{
		$bTX = true;
		$bRollback = false;
		$strRollbackReason = "";
		$bReporting = false;
		$bCheckURL = false;
		$strReportStatus = "";
		$strReportMsg = "";
		if ($bRollbackOnError) 
		{
			if (isset($this->config['auto_rollback']) && $this->config['auto_rollback'] == 0) $bRollbackOnError = false;
		}
		
		if ($this->bTest) $url = isset($this->config['test_url']) ? $this->config['test_url'] : '';
		else $url = isset($this->config['url']) ? $this->config['url'] : '';
		
		$Protocol = parse_url($url, PHP_URL_SCHEME);
		if($Protocol == "") 
		{
		    $url = "https://" . $url;
		    $bCheckURL = true;
		}
		else if($Protocol == "http") 
		{
		    $bCheckURL = false;
		}
		else if($Protocol == "https")
		{
		    $bCheckURL = true;
		}
		if($bCheckURL == true)
		{
            $pay_timeout = isset($this->config['timeout']) ? (int) $this->config['timeout'] : 0;
		    $result = $this->send_post_data($url, $this->Post, null, $pay_timeout);
		}
		else
		{
		    $result = false;
		    $bRollback = false;
		    $bReporting = false;
		    $this->response_code = LGD_ERR_HTTP_URL;
		    $this->response_msg = "http protocol not supported.";
		    $this->log("TX failed: res code = ".$this->response_code."; msg = ". $this->response_msg, LGD_LOG_FATAL);
		}
		if ($result == false) 
		{
		    if($bCheckURL == true)
		    {
    			$bTX = false;
    			$this->set_curl_error();
    			$this->log("TX failed: res code = ".$this->response_code."; msg = ". $this->response_msg, LGD_LOG_FATAL);
    			if ($bRollbackOnError && $this->Check_Auto_Rollback(curl_errno($this->ch))) 
    			{
    				$bRollback = true;
    				$strRollbackReason = "Timeout";
    			}
		    }
		    else
		    {
		        $bTX = false;
		    }
		}
		else 
		{
			$http_res_code = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
			if ($http_res_code < 200 || $http_res_code >= 300) 
			{
				// http response error
				$bTX = false;
				$this->response_code = "".(30000 + $http_res_code);
				$this->response_msg = "HTTP response code = ".$http_res_code;
				$this->log("TX failed: res code = ".$this->response_code."; msg = ". $this->response_msg, LGD_LOG_FATAL);
				// report
				$bReporting = true;
				$strReportStatus = "HTTP response ".$http_res_code;
				$strReportMsg = $result;
				if ($bRollbackOnError && $http_res_code >= 500) 
				{
					// rollback
					$bRollback = true;
					$strRollbackReason = "HTTP ".$http_res_code;
				}
			}
			else 
			{
				$this->response_json = $result;
				$this->response_array = json_decode($this->response_json, true);
				if (($this->response_array == false) || (strlen($this->response_array["LGD_RESPCODE"]) == 0)) 
				{
					// JSON decode failed
					$bTX = false;
					$bReporting = true;
					$strReportStatus = "JSON decode fail";
					$strReportMsg = $result;
					if ($bRollbackOnError) 
					{
						$bRollback = true;
						$strRollbackReason = "JSON decode fail";
					}
					$this->log("JSON Decode failed", LGD_LOG_ERROR);
					$this->response_array = array();
					$this->response_code = LGD_ERR_JSON_DECODE;
					$this->response_msg = "JSON Decode Failed";
				}
				else 
				{
					$this->response_code = $this->response_array["LGD_RESPCODE"];
					if ($this->config['output_UTF8'] == 1) $this->response_msg = $this->response_array["LGD_RESPMSG"];
					else $this->response_msg = iconv("utf-8", "euc-kr", $this->response_array["LGD_RESPMSG"]);
					$this->log("Response Code=[".$this->response_code."], Msg=[".iconv("utf-8", "euc-kr", $this->response_array["LGD_RESPMSG"])."], Count=".$this->Response_Count(), LGD_LOG_INFO);
					$keys = $this->Response_Names();
					for ($i = 0; $i < $this->Response_Count(); $i++) 
					{
						foreach($keys as $name) 
						{
						    if($this->IsAcceptLog($name,LGD_LOG_DEBUG))
						    {
								$this->log("Response (".$name.", ".$i.") = ".$this->Response($name, $i), LGD_LOG_DEBUG);
							}
						}
					}
				}
			}
		}
		if ($bRollback) 
		{
			// try auto-rollback
			$tx_id = $this->TX_ID;
			$code = $this->response_code;
			$msg = $this->response_msg;
			$this->init();
			$this->Rollback($strRollbackReason);
			// restore previous info
			$this->TX_ID = $tx_id;
			$this->response_code = $code;
			$this->response_msg = $msg;
		}
		if ($bReporting) 
		{
			$this->Report($strReportStatus, $strReportMsg);
		}
		return $bTX;
	}
	function Report_TX()
	{
		$url = $this->config['aux_url'];
		$result = $this->send_post_data($url, $this->Post, null, $this->config['timeout']);
		if ($result == false) 
		{
			set_curl_error();
			$this->log("Reporting failed: res code = ".$this->response_code."; msg = ". $this->response_msg, LGD_LOG_ERROR);
			return false;
		}
		$http_res_code = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
		if ($http_res_code < 200 || $http_res_code >= 300) 
		{
			// http response error
			$this->log("Reporting failed: HTTP response code = ".$http_res_code, LGD_LOG_ERROR);
			return false;
		}
		$response_array = json_decode($result, true);
		if ($response_array == false) 
		{
			// JSON decode failed
			$this->log("Report JSON Decode failed", LGD_LOG_ERROR);
			return false;
		}
		$response_code = $response_array["LGD_RESPCODE"];
		$response_msg = iconv("utf-8", "euc-kr", $response_array["LGD_RESPMSG"]);
		$this->log("Report Response Code=[".$response_code."], Msg=[".$response_msg."]", LGD_LOG_INFO);
		return true;
	}
	function Patch_TX($filename)
	{
		$url = $this->config['aux_url'];
		$tmp_file = tempnam($this->home_dir."/conf", "tmp");
		$fp = fopen($tmp_file, "w");
		$result = $this->post_into_file($url, $this->Post, $fp, null, $this->config['timeout']);
		fclose($fp);
		if ($result == false) 
		{
			unlink($tmp_file);
			$this->log("Patch failed: file = ".$filename, LGD_LOG_ERROR);
			return false;
		}
		else 
		{
			$this->log("Patch success: file = ".$filename, LGD_LOG_INFO);
			$type = curl_getinfo($this->ch, CURLINFO_CONTENT_TYPE);
			if (strncasecmp($type, "text/plain", 10) == 0) 
			{
				$this->log("Patch success: Content-Type = ".$type, LGD_LOG_INFO);
				copy($tmp_file, $this->home_dir."/conf/".$filename);
				unlink($tmp_file);
				return true;			
			}
			else 
			{
				// error
				$fp = fopen($tmp_file, "r");
				$content = fread($fp, 512);
				$this->Report("Patch error : ".$filename, $content);
				fclose($fp);
				unlink($tmp_file);				
			}
			return false;
		}
	}
	function Rollback($reason)
	{
		$RBTX = $this->TX_ID;
		$this->Init_TX($this->MID);
		$this->Set("LGD_TXNAME", "Rollback");
		$this->Set("LGD_RB_TXID", $RBTX);
		$this->Set("LGD_RB_REASON", $reason);
		if ($this->TX(false) == false) return false;
		if ($this->response_code == "0000") return true;
		return false;
	}
	function Report($status, $msg)
	{
		if ($this->config['report_error'] != 1) return false;
		$this->Init_TX($this->MID);
		$this->Set("LGD_TXNAME", "Report");
		$this->Set("LGD_STATUS", $status);
		$this->Set("LGD_MSG", $msg);
		return $this->Report_TX();
	}
	function Patch($filename)
	{
		$this->Init_TX($this->MID);
		$this->Set("LGD_TXNAME", "Patch");
		$this->Set("LGD_FILE", $filename);
		return $this->Patch_TX($filename);
	}
	function Response_Json()
	{
		return ($this->response_json);
	}
	function Response_Count()
	{
		if ($this->response_array["LGD_RESPONSE"] == null) return 0;
		return count($this->response_array["LGD_RESPONSE"]);
	}
	function Response_Code()
	{
		return ($this->response_code);
	}
	function Response_Msg()
	{
		return ($this->response_msg);
	}
	function Response_Names()
	{
		if ($this->Response_Count() == 0) return null;
		return array_keys($this->response_array["LGD_RESPONSE"][0]);
	}
	function Response($name, $index=0)
	{
        $response_val = isset($this->response_array["LGD_RESPONSE"][$index][$name]) ? $this->response_array["LGD_RESPONSE"][$index][$name] : '';

		if ($this->config['output_UTF8'] == 1) return $response_val;
		else return (iconv("utf-8", "euc-kr", $response_val));
	}
	function Log($msg, $level=LGD_LOG_FATAL)
	{
        if( !(defined('LGD_LOG_SAVE') && LGD_LOG_SAVE) ){
            return;
        }
		if ($level > $this->config["log_level"]) return;
		$err_msg = date("Y-m-d H:i:s")." [".$this->err_label[$level]."] [".$this->TX_ID."] ".$msg."\n";
		error_log($err_msg, 3, $this->log_file);
	}
	function set_credentials($username,$password)
	{
		curl_setopt($this->ch, CURLOPT_USERPWD, "$username:$password");
	}
	function set_referrer($referrer_url)
	{
		curl_setopt($this->ch, CURLOPT_REFERER, $referrer_url);
	}
	function set_user_agent($useragent)
	{
		curl_setopt($this->ch, CURLOPT_USERAGENT, $useragent);
	}
	function include_response_headers($value)
	{
		curl_setopt($this->ch, CURLOPT_HEADER, $value);
	}
	function set_proxy($proxy)
	{
		curl_setopt($this->ch, CURLOPT_PROXY, $proxy);
	}
	function send_post_data($url, $postdata, $ip=null, $timeout=10)
	{
		curl_setopt($this->ch, CURLOPT_URL, $url);
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
		if ($ip)
		{
			if($this->debug)
			{
				echo "Binding to ip $ip\n";
			}
			curl_setopt($this->ch, CURLOPT_INTERFACE, $ip);
		}
		curl_setopt($this->ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($this->ch, CURLOPT_POST, true);
		$post_array = array();
		if(is_array($postdata))
		{		
			foreach($postdata as $key=>$value)
			{
				$post_array[] = urlencode($key) . "=" . urlencode($value);
				if($this->IsAcceptLog($key,LGD_LOG_DEBUG))
				{
					$this->log("Post [".$key."] = [".$value."]", LGD_LOG_DEBUG);
				}
			}
			$post_string = implode("&",$post_array);
		}
		else 
		{
			$post_string = $postdata;
		}
		curl_setopt($this->ch, CURLOPT_POSTFIELDS, $post_string);
		$result = curl_exec($this->ch);
		if(curl_errno($this->ch))
		{
			if($this->debug)
			{
				echo "Error Occured in Curl\n";
				echo "Error number: " .curl_errno($this->ch) ."\n";
				echo "Error message: " .curl_error($this->ch)."\n";
			}
			return false;
		}
		else
		{
			return $result;
		}
	}
	function fetch_url($url, $ip=null, $timeout=5)
	{
		curl_setopt($this->ch, CURLOPT_URL,$url);
		curl_setopt($this->ch, CURLOPT_HTTPGET,true);
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER,true);
		if($ip)
		{
			if($this->debug)
			{
				echo "Binding to ip $ip\n";
			}
			curl_setopt($this->ch,CURLOPT_INTERFACE,$ip);
		}
		curl_setopt($this->ch, CURLOPT_TIMEOUT, $timeout);
		$result = curl_exec($this->ch);
		if(curl_errno($this->ch))
		{
			if($this->debug)
			{
				echo "Error Occured in Curl\n";
				echo "Error number: " .curl_errno($this->ch) ."\n";
				echo "Error message: " .curl_error($this->ch)."\n";
			}
			return false;
		}
		else
		{
			return $result;
		}
	}
	function fetch_into_file($url, $fp, $ip=null, $timeout=5)
	{
		curl_setopt($this->ch, CURLOPT_URL, $url);
		curl_setopt($this->ch, CURLOPT_HTTPGET, true);
		curl_setopt($this->ch, CURLOPT_FILE, $fp);
		if($ip)
		{
			if($this->debug)
			{
				echo "Binding to ip $ip\n";
			}
			curl_setopt($this->ch, CURLOPT_INTERFACE, $ip);
		}
		curl_setopt($this->ch, CURLOPT_TIMEOUT, $timeout);
		$result = curl_exec($this->ch);
		if(curl_errno($this->ch))
		{
			if($this->debug)
			{
				echo "Error Occured in Curl\n";
				echo "Error number: " .curl_errno($this->ch) ."\n";
				echo "Error message: " .curl_error($this->ch)."\n";
			}
			return false;
		}
		else
		{
			return true;
		}
	}
	function post_into_file($url, $postdata, $fp, $ip=null, $timeout=10)
	{
		curl_setopt($this->ch, CURLOPT_URL, $url);
		curl_setopt($this->ch, CURLOPT_FILE, $fp);
		if ($ip)
		{
			if($this->debug)
			{
				echo "Binding to ip $ip\n";
			}
			curl_setopt($this->ch, CURLOPT_INTERFACE, $ip);
		}
		curl_setopt($this->ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($this->ch, CURLOPT_POST, true);
		$post_array = array();
		if(is_array($postdata))
		{		
			foreach($postdata as $key=>$value)
			{
				$post_array[] = urlencode($key) . "=" . urlencode($value);
				if(IsAcceptLog($key,LGD_LOG_DEBUG))
				{
					$this->log("Post [".$key."] = [".$value."]", LGD_LOG_DEBUG);
				}
			}
			$post_string = implode("&",$post_array);
			if($this->debug)
			{
				echo "Url: $url\nPost String: $post_string\n";
			}
		}
		else 
		{
			$post_string = $postdata;
		}
		curl_setopt($this->ch, CURLOPT_POSTFIELDS, $post_string);
		$result = curl_exec($this->ch);
		if(curl_errno($this->ch))
		{
			if($this->debug)
			{
				echo "Error Occured in Curl\n";
				echo "Error number: " .curl_errno($this->ch) ."\n";
				echo "Error message: " .curl_error($this->ch)."\n";
			}
			return false;
		}
		else
		{
			return $result;
		}
	}
	function send_multipart_post_data($url, $postdata, $file_field_array=array(), $ip=null, $timeout=30)
	{
		curl_setopt($this->ch, CURLOPT_URL, $url);
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
		if($ip)
		{
			if($this->debug)
			{
				echo "Binding to ip $ip\n";
			}
			curl_setopt($this->ch,CURLOPT_INTERFACE,$ip);
		}
		curl_setopt($this->ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($this->ch, CURLOPT_POST, true);
		$headers = array("Expect: ");
		curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers);
		$result_post = array();
		$post_array = array();
		$post_string_array = array();
		if(!is_array($postdata))
		{
			return false;
		}
		foreach($postdata as $key=>$value)
		{
			$post_array[$key] = $value;
			$post_string_array[] = urlencode($key)."=".urlencode($value);
		}
		$post_string = implode("&",$post_string_array);
		if($this->debug)
		{
			echo "Post String: $post_string\n";
		}
		if(!empty($file_field_array))
		{
			foreach($file_field_array as $var_name => $var_value)
			{
				if(strpos(PHP_OS, "WIN") !== false) $var_value = str_replace("/", "\\", $var_value); // win hack
				$file_field_array[$var_name] = "@".$var_value;
			}
		}
		$result_post = array_merge($post_array, $file_field_array);
		curl_setopt($this->ch, CURLOPT_POSTFIELDS, $result_post);
		$result = curl_exec($this->ch);
		if(curl_errno($this->ch))
		{
			if($this->debug)
			{
				echo "Error Occured in Curl\n";
				echo "Error number: " .curl_errno($this->ch) ."\n";
				echo "Error message: " .curl_error($this->ch)."\n";
			}
			return false;
		}
		else
		{
			return $result;
		}
	}
	function store_cookies($cookie_file)
	{
		curl_setopt ($this->ch, CURLOPT_COOKIEJAR, $cookie_file);
		curl_setopt ($this->ch, CURLOPT_COOKIEFILE, $cookie_file);
	}
	function set_cookie($cookie)
	{		
		curl_setopt ($this->ch, CURLOPT_COOKIE, $cookie);
	}
	function get_effective_url()
	{
		return curl_getinfo($this->ch, CURLINFO_EFFECTIVE_URL);
	}
	function get_http_response_code()
	{
		return curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
	}
	function get_error_msg()
	{
		$err = "Error number: " .curl_errno($this->ch) ."\n";
		$err .="Error message: " .curl_error($this->ch)."\n";
		return $err;
	}
	function close()
	{
		curl_close($this->ch);
	}

    function GetTimeStamp()
	{
		$Result = "";
		$Result = date("YmdHis");
		return $Result;
	}

	function GetHashData($LGD_MID,$LGD_OID,$LGD_AMOUNT,$LGD_TIMESTAMP)
	{
		$LGD_HASHDATA = "";
		$LGD_HASHDATA = md5($LGD_MID.$LGD_OID.$LGD_AMOUNT.$LGD_TIMESTAMP.$this->config[$LGD_MID]);
		return $LGD_HASHDATA;
	}

	function GetHashDataOpenpay($LGD_VENDERNO,$LGD_MID,$LGD_OPENPAY_MER_UID,$LGD_OPENPAY_TOKEN,$LGD_TIMESTAMP)
	{
		$LGD_HASHDATA = "";			
		$InputData = "";
		$InputData = $LGD_MID . $LGD_VENDERNO . $LGD_OPENPAY_MER_UID;
		if(empty($LGD_OPENPAY_TOKEN)) $InputData = $InputData . $LGD_TIMESTAMP .$this->config[$LGD_MID];
		else $InputData = $InputData . $LGD_OPENPAY_TOKEN . $LGD_TIMESTAMP .$this->config[$LGD_MID];
		$LGD_HASHDATA = hash('sha512',$InputData);
		return $LGD_HASHDATA;
	}
	
	function GetHashDataCas($LGD_MID,$LGD_OID,$LGD_AMOUNT,$LGD_RESPCODE,$LGD_TIMESTAMP)
	{
	    $LGD_HASHDATA = "";
	    $LGD_HASHDATA = md5($LGD_MID.$LGD_OID.$LGD_AMOUNT.$LGD_RESPCODE.$LGD_TIMESTAMP.$this->config[$LGD_MID]);
	    return $LGD_HASHDATA;
	}
		
	/**
	@brief MertKey Bytes 변환
	@param MertKey MertKey
	@date 2014.04.03
	@author SangmanPark (sangman.park@gmail.com)
	@remark MertKey 를 Bytes 로 변환한다.
	*/
	function StringToHex($MertKey)
	{
		$szKey = array();
		$szMertKey = str_split($MertKey,2);
		for ($i = 0 ; $i < 16 ; $i++)
		{
			$szKey[$i] = Hex2Bin ($szMertKey[$i]);
		}
		$szKeyString = implode("", $szKey);

		//for ($i = 0 ; $i < 16 ; $i++)
		//{
		//	echo Bin2Hex($szKeyString[$i])."<BR>";
		//}
		
		return $szKeyString;
	}

	/**
	@brief Micro time 
	@param MertKey MertKey
	@date 2014.04.03
	@author SangmanPark (sangman.park@gmail.com)
	@remark Micro Time 을 구한다.
	*/
	function getMicrotime()
	{
		if (version_compare(PHP_VERSION, '5.0.0', '<'))
		{
			return array_sum(explode(' ', microtime()));
		}

		return microtime(true);
	}

	/**
	@brief Encrypt And Encode
	@param MertKey MertKey
	@param PlainBuffer 원문
	@date 2014.04.03
	@author SangmanPark (sangman.park@gmail.com)
	@remark AES128/ECB/PKCS#5 로 암호화 한후 Base64 Encoding 한다.
	*/
	function EncryptAndEncode($MertKey,$PlainBuffer)
    {
		echo "EncryptAndEncode Start<BR>";	
		$microsecond = $this->getMicrotime();
		$PlainBufferTemp = sprintf("%lf", $microsecond);
		$PlainBufferTemp = substr($PlainBufferTemp,-3);
		$LgdValue = ":LGD:".$PlainBufferTemp;
		$PlainBuffer .= $LgdValue;
		$key = $this->StringToHex($MertKey);
		$EncrytBuffer = mcrypt_ecb(MCRYPT_RIJNDAEL_128,$key,$PlainBuffer,MCRYPT_ENCRYPT);
		$EncryptAndEncodeBuffer = base64_encode($EncrytBuffer); 
		$EncryptReturnValue = $EncryptAndEncodeBuffer;
		echo "EncryptReturnValue = " . $EncryptReturnValue . "<BR>";
		echo "EncryptAndEncode Complete<BR>";
        return $EncryptReturnValue;
	}

	/**
	@brief Decode And Decrypt
	@param MertKey MertKey
	@param EncryptAndEncodeBuffer Encrypt And Encode String
	@date 2014.04.03
	@author SangmanPark (sangman.park@gmail.com)
	@remark Base64 Decoding 한후 AES128/ECB/PKCS#5 로 복호화 한다.
	*/
    function DecodeAndDecrypt($MertKey,$EncryptAndEncodeBuffer)
    {
		echo "DecodeAndDecrypt Start<BR>";
		$key = $this->StringToHex($MertKey);
		$DecodeBuffer = base64_decode($EncryptAndEncodeBuffer);  
		$DecryptBuffer = mcrypt_ecb(MCRYPT_RIJNDAEL_128,$key,$DecodeBuffer,MCRYPT_DECRYPT);
		$UnPaddString = $this->pkcs5_unpad($DecryptBuffer);
		echo "UnPaddString : ".$UnPaddString . "<BR>";
		echo "DecodeAndDecrypt Complete<BR>";
		$ConvertCharset = iconv("euc-kr", "utf-8", $UnPaddString);	
		return $ConvertCharset;

    }

	/**
	* PKCS5 패딩추가
	* @param string $text
	* @param int $blocksize
	* @return string
	*/
	function pkcs5_pad($text, $blocksize) 
	{ 
		$pad = $blocksize - (strlen($text) % $blocksize); 
		return $text . str_repeat(chr($pad), $pad); 
	} 
	
	/**
	* PKCS5 패딩제거
	* @param string $text
	* @return boolean|string
	*/
	function pkcs5_unpad($text) 
	{ 
		$pad = ord($text[strlen($text)-1]); 
		if ($pad > strlen($text)) return false; 
		if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) return false; 
		return substr($text, 0, -1 * $pad); 
	}
}