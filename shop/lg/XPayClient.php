<?php
/**
 * @version 1.0
 * @package aurasoft.co.kr
 * @copyright &copy; 2008 aurasoft.co.kr
 * @author Jae Hak Jung <jhjung@aurasoft.co.kr>
 */

/**
 * Curl based HTTP Client
 * Simple but effective OOP wrapper around Curl php lib.
 * Contains common methods needed
 * for getting data from url, setting referrer, credentials,
 * sending post data, managing cookies, etc.
 *
 * Samle usage:
 * $curl = &new Curl_HTTP_Client();
 * $useragent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)";
 * $curl->set_user_agent($useragent);
 * $curl->store_cookies("/tmp/cookies.txt");
 * $post_data = array('login' => 'pera', 'password' => 'joe');
 * $html_data = $curl->send_post_data(http://www.foo.com/login.php, $post_data);
 */
class XPayClient
{
	/**
	 * Curl handler
	 * @access private
	 * @var resource
	 */
	var $ch;

	/**
	 * set debug to true in order to get usefull output
	 * @access private
	 * @var string
	 */
	var $debug = false;
	var	$bTest = false;

	/**
	 * Contain last error message if error occured
	 * @access private
	 * @var string
	 */
	var $error_msg;

	var $home_dir;
	var $mode;
	var $TX_ID;
	var	$MID;
	var $Auth_Code;

	var $config;

	var $Post = array();
	var	$response_json;
	var $response_array;
	var $response_code;
	var $response_msg;


	var	$log_file;

	var $err_label = array("FATAL", "ERROR", "WARN ", "INFO ", "DEBUG");


	/**
	 * Curl_HTTP_Client constructor
	 * @param boolean debug
	 * @access public
	 */
	function XPayClient($home_dir, $mode="real")
	{
		// for php4 JSON
		if ( !function_exists('json_decode') ){
		    function json_decode($content, $assoc=false){
		                require_once 'JSON.php';
		                if ( $assoc ){
		                    $json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
		        } else {
		                    $json = new Services_JSON;
		                }
		        return $json->decode($content);
		    }
		}

		define("LGD_USER_AGENT", "XPayClient (1.1/PHP)");

		define("LGD_LOG_FATAL",	0);
		define("LGD_LOG_ERROR",	1);
		define("LGD_LOG_WARN",	2);
		define("LGD_LOG_INFO",	3);
		define("LGD_LOG_DEBUG", 4);

		define("LGD_ERR_NO_HOME_DIR",		"10001");
		define("LGD_ERR_NO_MALL_CONFIG",	"10002");
		define("LGD_ERR_NO_LGDACOM_CONFIG",	"10003");
		define("LGD_ERR_NO_MID",			"10004");
		define("LGD_ERR_OUT_OF_MEMORY",		"10005");

		define("LGD_ERR_HTTP_URL",			"20001");
		define("LGD_ERR_RESOLVE_HOST",		"20002");
		define("LGD_ERR_RESOLVE_PROXY",		"20003");
		define("LGD_ERR_CONNECT",			"20004");
		define("LGD_ERR_WRITE",				"20005");
		define("LGD_ERR_READ",				"20006");
		define("LGD_ERR_SEND",				"20007");
		define("LGD_ERR_RECV",				"20008");
		define("LGD_ERR_TIMEDOUT",			"20009");

		define("LGD_ERR_SSL",				"20101");

		define("LGD_ERR_CURL",				"20201");

		define("LGD_ERR_JSON_DECODE",		"40001");

		session_start();

		$this->home_dir = $home_dir;

		// check directory and config files
		if (!file_exists($home_dir)) {
			$this->response_code = LGD_ERR_NO_HOME_DIR;
			$this->response_msg = "home_dir [".$home_dir."] does not exist";
			trigger_error($this->response_msg, E_USER_ERROR);
		}
		else if (!file_exists($home_dir."/conf/mall.conf")) {
			$this->response_code = LGD_ERR_NO_MALL_CONFIG;
			$this->response_msg = "config file [".$home_dir."/conf/mall.conf] does not exist";
			trigger_error($this->response_msg, E_USER_ERROR);
		}
		else if (!file_exists($home_dir."/conf/lgdacom.conf")) {
			$this->response_code = LGD_ERR_NO_LGDACOM_CONFIG;
			$this->response_msg = "config file [".$home_dir."/conf/lgdacom.conf] does not exist";
			trigger_error($this->response_msg, E_USER_ERROR);
		}

		$array1 = parse_ini_file($home_dir . "/conf/mall.conf");
		$array2 = parse_ini_file($home_dir . "/conf/lgdacom.conf");
		$this->config = $array1 + $array2;

        // log_dir 재설정
        $this->config["log_dir"] = $home_dir."/log";

		$this->log_file = $this->config["log_dir"] . "/log_" . date("Ymd") . ".log";
		// make log directory if does not exist
		if (!file_exists($this->config["log_dir"])) {
			mkdir($this->config["log_dir"], "0777", true);
		}

		$this->log("XPayClient initialize [".$home_dir."] [".$mode."]", LGD_LOG_INFO);
		foreach($this->config as $name => $value)
			$this->log("Config [".$name."] = [".$value."]", LGD_LOG_DEBUG);
		if (strtolower($mode) == "test") {
			$this->bTest = true;
			$this->debug = false;
		}
		$this->init();
	}

	/**
	 * Init Curl session
	 * @access public
	 */
	function init()
	{
		// initialize curl handle
		$this->ch = curl_init();

		//set various options

		// set user agent string for sending client version string
		curl_setopt($this->ch, CURLOPT_USERAGENT, LGD_USER_AGENT);

		//set error in case http return code bigger than 300
		//curl_setopt($this->ch, CURLOPT_FAILONERROR, true);

		// allow redirects
		curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, true);

		// use gzip if possible
		curl_setopt($this->ch, CURLOPT_ENCODING , 'gzip, deflate');

		// do not veryfy ssl
		// this is important for windows
		// as well for being able to access pages with non valid cert
		if ($this->config["verify_cert"] == 0) {
			curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
			$this->log("Do not verify server Certificate", LGD_LOG_WARN);
		}

		// do not verify host name
		//
		if ($this->config["verify_host"] == 0) {
			curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 0);
			$this->log("Do not verify host Domain", LGD_LOG_WARN);
		}
		curl_setopt($this->ch, CURLOPT_CAINFO, $this->home_dir."/conf/ca-bundle.crt");

	}

	function Init_TX($MID)
	{
		if ($this->config[$MID] == null) {
			$this->response_code = LGD_ERR_NO_MID;
			$this->response_msg = "Key for MID [".$MID."] does not exist in mall.conf";
			$this->log($this->response_msg, LGD_LOG_FATAL);
			return false;
		}

		$this->TX_ID = $this->Gen_TX_ID($MID);
		$this->MID = $MID;
		$this->Auth_Code = $this->Gen_Auth_Code($this->TX_ID, $MID);

		$this->Post = array("LGD_TXID" => $this->TX_ID,
							"LGD_AUTHCODE" => $this->Auth_Code,
							"LGD_MID" => $MID);

		return true;
	}

	function Get_Unique()
	{
		if(isset($_SESSION['tx_counter']))
			$_SESSION['tx_counter'] = $_SESSION['tx_counter'] + 1;
		else
			$_SESSION['tx_counter'] = 1;
//		$this->log("session id = ".session_id().$_SESSION['tx_counter'], LGD_LOG_FATAL);
		return session_id().$_SESSION['tx_counter'];
	}

	function Gen_TX_ID($MID)
	{
		$now = date("YmdHis");
		$header = $MID . "-" . $this->config['server_id'] . $now;
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
		if ($errno == 28)	//CURLE_OPERATION_TIMEDOUT
			return true;

		return false;
	}

	function set_curl_error()
	{
		switch (curl_errno($this->ch)) {
			case 1:		// CURLE_UNSUPPORTED_PROTOCOL:
			case 3:		// CURLE_URL_MALFORMAT:
				$this->response_code = LGD_ERR_HTTP_URL;
				$this->response_msg = "URL error";
				break;

			case 6:		// CURLE_COULDNT_RESOLVE_HOST:
				$this->response_code = LGD_ERR_RESOLVE_HOST;
				$this->response_msg = "Resolve host error";
				break;

			case 5:		// CURLE_COULDNT_RESOLVE_PROXY:
				$this->response_code = LGD_ERR_RESOLVE_PROXY;
				$this->response_msg = "Resolve proxy error";
				break;

			case 7:		// CURLE_COULDNT_CONNECT:
				$this->response_code = LGD_ERR_CONNECT;
				$this->response_msg = "Could not connect error";
				break;

			case 23:	// CURLE_WRITE_ERROR:
				$this->response_code = LGD_ERR_WRITE;
				$this->response_msg = "Write error";
				break;

			case 26:	// CURLE_READ_ERROR:
				$this->response_code = LGD_ERR_READ;
				$this->response_msg = "Read error";
				break;

			case 55:	// CURLE_SEND_ERROR:
				$this->response_code = LGD_ERR_SEND;
				$this->response_msg = "Send error";
				break;

			case 56:	// CURLE_RECV_ERROR:
				$this->response_code = LGD_ERR_RECV;
				$this->response_msg = "Recv error";
				break;

			case 27:	// CURLE_OUT_OF_MEMORY:
				$this->response_code = LGD_ERR_OUT_OF_MEMORY;
				$this->response_msg = "Out of memory error";
				break;

			case 28:	// CURLE_OPERATION_TIMEDOUT :
				$this->response_code = LGD_ERR_TIMEDOUT;
				$this->response_msg = "Timeout error";
				break;

			case 35:	// CURLE_SSL_CONNECT_ERROR:
			case 51:	// CURLE_PEER_FAILED_VERIFICATION:
			case 53:	// CURLE_SSL_ENGINE_NOTFOUND:
			case 54:	// CURLE_SSL_ENGINE_SETFAILED:
			case 58:	// CURLE_SSL_CERTPROBLEM:
			case 59:	// CURLE_SSL_CIPHER:
			case 60:	// CURLE_SSL_CACERT:
			case 64:	// CURLE_USE_SSL_FAILED:
			case 66:	// CURLE_SSL_ENGINE_INITFAILED:
			case 77:	// CURLE_SSL_CACERT_BADFILE:
			case 80:	// CURLE_SSL_SHUTDOWN_FAILED:
			case 82:	// CURLE_SSL_CRL_BADFILE:
			case 83:	// CURLE_SSL_ISSUER_ERROR:
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
		$strReportStatus = "";
		$strReportMsg = "";

		if ($bRollbackOnError) {
			if ($this->config['auto_rollback'] == 0)
				$bRollbackOnError = false;
		}

		if ($this->bTest)
			$url = $this->config['test_url'];
		else
			$url = $this->config['url'];

		$result = $this->send_post_data($url, $this->Post, null, $this->config['timeout']);
		if ($result == false) {
			$bTX = false;
			$this->set_curl_error();

			$this->log("TX failed: res code = ".$this->response_code."; msg = ". $this->response_msg, LGD_LOG_FATAL);

			if ($bRollbackOnError && $this->Check_Auto_Rollback(curl_errno($this->ch))) {
				$bRollback = true;
				$strRollbackReason = "Timeout";
			}
		}
		else {
			$http_res_code = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
			if ($http_res_code < 200 || $http_res_code >= 300) {
				// http response error
				$bTX = false;
				$this->response_code = "".(30000 + $http_res_code);
				$this->response_msg = "HTTP response code = ".$http_res_code;
				$this->log("TX failed: res code = ".$this->response_code."; msg = ". $this->response_msg, LGD_LOG_FATAL);

				// report
				$bReporting = true;
				$strReportStatus = "HTTP response ".$http_res_code;
				$strReportMsg = $result;
				if ($bRollbackOnError && $http_res_code >= 500) {
					// rollback
					$bRollback = true;
					$strRollbackReason = "HTTP ".$http_res_code;
				}

			}
			else {
				$this->log("Result = [".$result."]", LGD_LOG_DEBUG);
				$this->response_json = $result;

				$this->response_array = json_decode($this->response_json, true);

				if (($this->response_array == false) || (strlen($this->response_array["LGD_RESPCODE"]) == 0)) {
					// JSON decode failed
					$bTX = false;

					$bReporting = true;
					$strReportStatus = "JSON decode fail";
					$strReportMsg = $result;

					if ($bRollbackOnError) {
						$bRollback = true;
						$strRollbackReason = "JSON decode fail";
					}
					$this->log("JSON Decode failed", LGD_LOG_ERROR);

					$this->response_array = array();
					$this->response_code = LGD_ERR_JSON_DECODE;
					$this->response_msg = "JSON Decode Failed";
				}
				else {
					$this->response_code = $this->response_array["LGD_RESPCODE"];
					if ($this->config['output_UTF8'] == 1)
						$this->response_msg = $this->response_array["LGD_RESPMSG"];
					else
						$this->response_msg = iconv("utf-8", "euc-kr", $this->response_array["LGD_RESPMSG"]);

					$this->log("Response Code=[".$this->response_code."], Msg=[".iconv("utf-8", "euc-kr", $this->response_array["LGD_RESPMSG"])."], Count=".$this->Response_Count(), LGD_LOG_INFO);
					$keys = $this->Response_Names();
					for ($i = 0; $i < $this->Response_Count(); $i++) {
						foreach($keys as $name) {
							$this->log("Response (".$name.", ".$i.") = ".$this->Response($name, $i), LGD_LOG_DEBUG);
						}
					}
				}
			}
		}

		if ($bRollback) {
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

		if ($bReporting) {
			$this->Report($strReportStatus, $strReportMsg);
		}
		return $bTX;
	}

	function Report_TX()
	{
		$url = $this->config['aux_url'];

		$result = $this->send_post_data($url, $this->Post, null, $this->config['timeout']);
		if ($result == false) {

			set_curl_error();
			$this->log("Reporting failed: res code = ".$this->response_code."; msg = ". $this->response_msg, LGD_LOG_ERROR);

			return false;
		}

		$http_res_code = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
		if ($http_res_code < 200 || $http_res_code >= 300) {
			// http response error
			$this->log("Reporting failed: HTTP response code = ".$http_res_code, LGD_LOG_ERROR);
			return false;
		}


		$this->log("Reporting result = [".$result."]", LGD_LOG_DEBUG);

		$response_array = json_decode($result, true);

		if ($response_array == false) {
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

		if ($result == false) {
			unlink($tmp_file);
			$this->log("Patch failed: file = ".$filename, LGD_LOG_ERROR);

			return false;
		}
		else {
			$this->log("Patch success: file = ".$filename, LGD_LOG_INFO);
			$type = curl_getinfo($this->ch, CURLINFO_CONTENT_TYPE);
			if (strncasecmp($type, "text/plain", 10) == 0) {
				$this->log("Patch success: Content-Type = ".$type, LGD_LOG_INFO);
				copy($tmp_file, $this->home_dir."/conf/".$filename);
				unlink($tmp_file);
				return true;
			}
			else {
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
		if ($this->TX(false) == false)
			return false;

		if ($this->response_code == "0000")
			return true;
		return false;
	}

	function Report($status, $msg)
	{
		if ($this->config['report_error'] != 1)
			return false;

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
		if ($this->response_array["LGD_RESPONSE"] == null)
			return 0;
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
		if ($this->Response_Count() == 0)
			return null;
		return array_keys($this->response_array["LGD_RESPONSE"][0]);
	}

	function Response($name, $index=0)
	{
		if ($this->config['output_UTF8'] == 1)
			return ($this->response_array["LGD_RESPONSE"][$index][$name]);
		else
			return (iconv("utf-8", "euc-kr", $this->response_array["LGD_RESPONSE"][$index][$name]));
	}


	function Log($msg, $level=LGD_LOG_FATAL)
	{
		if ($level > $this->config["log_level"])
			return;
		$err_msg = date("Y-m-d H:i:s")." [".$this->err_label[$level]."] [".$this->TX_ID."] ".$msg."\n";
		error_log($err_msg, 3, $this->log_file);
	}

	/**
	 * Set username/pass for basic http auth
	 * @param string user
	 * @param string pass
	 * @access public
	 */
	function set_credentials($username,$password)
	{
		curl_setopt($this->ch, CURLOPT_USERPWD, "$username:$password");
	}

	/**
	 * Set referrer
	 * @param string referrer url
	 * @access public
	 */
	function set_referrer($referrer_url)
	{
		curl_setopt($this->ch, CURLOPT_REFERER, $referrer_url);
	}

	/**
	 * Set client's useragent
	 * @param string user agent
	 * @access public
	 */
	function set_user_agent($useragent)
	{
		curl_setopt($this->ch, CURLOPT_USERAGENT, $useragent);
	}

	/**
	 * Set to receive output headers in all output functions
	 * @param boolean true to include all response headers with output, false otherwise
	 * @access public
	 */
	function include_response_headers($value)
	{
		curl_setopt($this->ch, CURLOPT_HEADER, $value);
	}


	/**
	 * Set proxy to use for each curl request
	 * @param string proxy
	 * @access public
	 */
	function set_proxy($proxy)
	{
		curl_setopt($this->ch, CURLOPT_PROXY, $proxy);
	}



	/**
	 * Send post data to target URL
	 * return data returned from url or false if error occured
	 * @param string url
	 * @param mixed post data (assoc array ie. $foo['post_var_name'] = $value or as string like var=val1&var2=val2)
	 * @param string ip address to bind (default null)
	 * @param int timeout in sec for complete curl operation (default 10)
	 * @return string data
	 * @access public
	 */
	function send_post_data($url, $postdata, $ip=null, $timeout=10)
	{
		//set various curl options first

		// set url to post to
		curl_setopt($this->ch, CURLOPT_URL, $url);

		// return into a variable rather than displaying it
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);

		//bind to specific ip address if it is sent trough arguments
		if ($ip)
		{
			if($this->debug)
			{
				echo "Binding to ip $ip\n";
			}
			curl_setopt($this->ch, CURLOPT_INTERFACE, $ip);
		}

		//set curl function timeout to $timeout
		curl_setopt($this->ch, CURLOPT_TIMEOUT, $timeout);

		//set method to post
		curl_setopt($this->ch, CURLOPT_POST, true);


		//generate post string
		$post_array = array();
		if(is_array($postdata))
		{
			foreach($postdata as $key=>$value)
			{
				$post_array[] = urlencode($key) . "=" . urlencode($value);
				$this->log("Post [".$key."] = [".$value."]", LGD_LOG_DEBUG);
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

		$this->log("post_string = ".$post_string, LGD_LOG_DEBUG);
		// set post string
		curl_setopt($this->ch, CURLOPT_POSTFIELDS, $post_string);


		//and finally send curl request
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

	/**
	 * fetch data from target URL
	 * return data returned from url or false if error occured
	 * @param string url
	 * @param string ip address to bind (default null)
	 * @param int timeout in sec for complete curl operation (default 5)
	 * @return string data
	 * @access public
	 */
	function fetch_url($url, $ip=null, $timeout=5)
	{
		// set url to post to
		curl_setopt($this->ch, CURLOPT_URL,$url);

		//set method to get
		curl_setopt($this->ch, CURLOPT_HTTPGET,true);

		// return into a variable rather than displaying it
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER,true);

		//bind to specific ip address if it is sent trough arguments
		if($ip)
		{
			if($this->debug)
			{
				echo "Binding to ip $ip\n";
			}
			curl_setopt($this->ch,CURLOPT_INTERFACE,$ip);
		}

		//set curl function timeout to $timeout
		curl_setopt($this->ch, CURLOPT_TIMEOUT, $timeout);

		//and finally send curl request
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

	/**
	 * Fetch data from target URL
	 * and store it directly to file
	 * @param string url
	 * @param resource value stream resource(ie. fopen)
	 * @param string ip address to bind (default null)
	 * @param int timeout in sec for complete curl operation (default 5)
	 * @return boolean true on success false othervise
	 * @access public
	 */
	function fetch_into_file($url, $fp, $ip=null, $timeout=5)
	{
		// set url to post to
		curl_setopt($this->ch, CURLOPT_URL, $url);

		//set method to get
		curl_setopt($this->ch, CURLOPT_HTTPGET, true);

		// store data into file rather than displaying it
		curl_setopt($this->ch, CURLOPT_FILE, $fp);

		//bind to specific ip address if it is sent trough arguments
		if($ip)
		{
			if($this->debug)
			{
				echo "Binding to ip $ip\n";
			}
			curl_setopt($this->ch, CURLOPT_INTERFACE, $ip);
		}

		//set curl function timeout to $timeout
		curl_setopt($this->ch, CURLOPT_TIMEOUT, $timeout);

		//and finally send curl request
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
	/**
	 * Send post data to target URL
	 * return data returned from url or false if error occured
	 * @param string url
	 * @param mixed post data (assoc array ie. $foo['post_var_name'] = $value or as string like var=val1&var2=val2)
	 * @param string ip address to bind (default null)
	 * @param int timeout in sec for complete curl operation (default 10)
	 * @return string data
	 * @access public
	 */
	function post_into_file($url, $postdata, $fp, $ip=null, $timeout=10)
	{
		//set various curl options first

		// set url to post to
		curl_setopt($this->ch, CURLOPT_URL, $url);

		// store data into file rather than displaying it
		curl_setopt($this->ch, CURLOPT_FILE, $fp);


		//bind to specific ip address if it is sent trough arguments
		if ($ip)
		{
			if($this->debug)
			{
				echo "Binding to ip $ip\n";
			}
			curl_setopt($this->ch, CURLOPT_INTERFACE, $ip);
		}

		//set curl function timeout to $timeout
		curl_setopt($this->ch, CURLOPT_TIMEOUT, $timeout);

		//set method to post
		curl_setopt($this->ch, CURLOPT_POST, true);


		//generate post string
		$post_array = array();
		if(is_array($postdata))
		{
			foreach($postdata as $key=>$value)
			{
				$post_array[] = urlencode($key) . "=" . urlencode($value);
				$this->log("Post [".$key."] = [".$value."]", LGD_LOG_DEBUG);
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

		// set post string
		curl_setopt($this->ch, CURLOPT_POSTFIELDS, $post_string);


		//and finally send curl request
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

	/**
	 * Send multipart post data to the target URL
	 * return data returned from url or false if error occured
	 * (contribution by vule nikolic, vule@dinke.net)
	 * @param string url
	 * @param array assoc post data array ie. $foo['post_var_name'] = $value
	 * @param array assoc $file_field_array, contains file_field name = value - path pairs
	 * @param string ip address to bind (default null)
	 * @param int timeout in sec for complete curl operation (default 30 sec)
	 * @return string data
	 * @access public
	 */
	function send_multipart_post_data($url, $postdata, $file_field_array=array(), $ip=null, $timeout=30)
	{
		//set various curl options first

		// set url to post to
		curl_setopt($this->ch, CURLOPT_URL, $url);

		// return into a variable rather than displaying it
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);

		//bind to specific ip address if it is sent trough arguments
		if($ip)
		{
			if($this->debug)
			{
				echo "Binding to ip $ip\n";
			}
			curl_setopt($this->ch,CURLOPT_INTERFACE,$ip);
		}

		//set curl function timeout to $timeout
		curl_setopt($this->ch, CURLOPT_TIMEOUT, $timeout);

		//set method to post
		curl_setopt($this->ch, CURLOPT_POST, true);

		// disable Expect header
		// hack to make it working
		$headers = array("Expect: ");
		curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers);

		// initialize result post array
		$result_post = array();

		//generate post string
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

		// set post string
		//curl_setopt($this->ch, CURLOPT_POSTFIELDS, $post_string);


		// set multipart form data - file array field-value pairs
		if(!empty($file_field_array))
		{
			foreach($file_field_array as $var_name => $var_value)
			{
				if(strpos(PHP_OS, "WIN") !== false) $var_value = str_replace("/", "\\", $var_value); // win hack
				$file_field_array[$var_name] = "@".$var_value;
			}
		}

		// set post data
		$result_post = array_merge($post_array, $file_field_array);
		curl_setopt($this->ch, CURLOPT_POSTFIELDS, $result_post);


		//and finally send curl request
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

	/**
	 * Set file location where cookie data will be stored and send on each new request
	 * @param string absolute path to cookie file (must be in writable dir)
	 * @access public
	 */
	function store_cookies($cookie_file)
	{
		// use cookies on each request (cookies stored in $cookie_file)
		curl_setopt ($this->ch, CURLOPT_COOKIEJAR, $cookie_file);
		curl_setopt ($this->ch, CURLOPT_COOKIEFILE, $cookie_file);
	}

	/**
	 * Set custom cookie
	 * @param string cookie
	 * @access public
	 */
	function set_cookie($cookie)
	{
		curl_setopt ($this->ch, CURLOPT_COOKIE, $cookie);
	}

	/**
	 * Get last URL info
	 * usefull when original url was redirected to other location
	 * @access public
	 * @return string url
	 */
	function get_effective_url()
	{
		return curl_getinfo($this->ch, CURLINFO_EFFECTIVE_URL);
	}

	/**
	 * Get http response code
	 * @access public
	 * @return int
	 */
	function get_http_response_code()
	{
		return curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
	}

	/**
	 * Return last error message and error number
	 * @return string error msg
	 * @access public
	 */
	function get_error_msg()
	{
		$err = "Error number: " .curl_errno($this->ch) ."\n";
		$err .="Error message: " .curl_error($this->ch)."\n";

		return $err;
	}

	/**
	 * Close curl session and free resource
	 * Usually no need to call this function directly
	 * in case you do you have to call init() to recreate curl
	 * @access public
	 */
	function close()
	{
		//close curl session and free up resources
		curl_close($this->ch);
	}
}
?>