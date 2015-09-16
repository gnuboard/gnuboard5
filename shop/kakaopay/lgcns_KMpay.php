<?php
/**
 * 2014.12.02 1) 불필요한 로깅 삭제, 2) check key array
 */
// error_reporting(E_ALL);
// ini_set("display_errors", 1);
    class kmpayFunc {
        private $LogPath = "";
        private $phpVersion = "";
        public function kmpayFunc($LogDir) {
    	    if (substr($LogDir, strlen($LogDir) - 1) == "/") {
    	        $LogDir = substr($LogDir, 0, strlen($LogDir) - 1);
    	    }
    	    @mkdir($LogDir);
    	    $this->LogPath = $LogDir."/";
    	}
    	public function setPhpVersion($version) {
    		$this->phpVersion = $version;
    	}
        public function parameterEncrypt($key, $plainText) {
            try {
        		$encryptText = "";
        		$iv = "";
        		if ($key == null || $plainText == null || $key == "" || $plainText == "" || strlen($key) < 16) {
        			return "";
        		} else {
        			$iv = substr($key, 0, 16);
        		    $encryptText = $this->AESCBCPKCS5($plainText, $key, $iv, "enc", "yes");
        		}
        		return $encryptText;
        	} catch (Exception $ex) {
        	    $this->writeLog("parameterEncrypt() Exception Code ".$ex->getCode()." : ".$ex->getMessage()." in ".$ex->getFile()." on line ".$ex->getLine());
        	    return "_FAIL_";
        	}
    	}
    	public function parameterDecrypt($key, $EncryptText) {
    	    try {
        		$decryptText = "";
        		$iv = "";
        		if ($key == null || $EncryptText == null || $key == "" || $EncryptText == "" || strlen($key) < 16) {
        			return "1";
        		} else {
        			$iv = substr($key, 0, 16);
        		    $decryptText = $this->AESCBCPKCS5($EncryptText, $key, $iv, "dec", "yes");
        		}
        		return $decryptText;
        	} catch (Exception $ex) {
        	    $this->writeLog("parameterDecrypt() Exception Code ".$ex->getCode()." : ".$ex->getMessage()." in ".$ex->getFile()." on line ".$ex->getLine());
        	    return "_FAIL_";
        	}
    	}
        public function PKCS5Pad($text, $blocksize = 16) {
            try {
                $pad = $blocksize - (strlen($text) % $blocksize);
                return $text.str_repeat(chr($pad), $pad);
            } catch (Exception $ex) {
        	    $this->writeLog("PKCS5Pad() Exception Code ".$ex->getCode()." : ".$ex->getMessage()." in ".$ex->getFile()." on line ".$ex->getLine());
        	    return "_FAIL_";
        	}
        }
        public function PKCS5UnPad($text) {
            try {
                $pad = ord($text{strlen($text)-1});
                if ($pad > strlen($text)) return $text;
                if (!strspn($text, chr($pad), strlen($text) - $pad)) return $text;
                return substr($text, 0, -1 * $pad);
            } catch (Exception $ex) {
        	    $this->writeLog("PKCS5UnPad() Exception Code ".$ex->getCode()." : ".$ex->getMessage()." in ".$ex->getFile()." on line ".$ex->getLine());
        	    return "_FAIL_";
        	}
        }
        public function encrypt($iv, $key, $str) {
            try {
                $td = mcrypt_module_open("rijndael-128", "", "cbc", "");
                @mcrypt_generic_init($td, $key, $iv);
                $encrypted = @mcrypt_generic($td, $this->PKCS5Pad($str));
                mcrypt_generic_deinit($td);
                mcrypt_module_close($td);
                return $encrypted;
            } catch (Exception $ex) {
        	    $this->writeLog("encrypt() Exception Code ".$ex->getCode()." : ".$ex->getMessage()." in ".$ex->getFile()." on line ".$ex->getLine());
        	    return "_FAIL_";
        	}
        }
        public function decrypt($iv, $key, $code) {
            try {
                $td = mcrypt_module_open("rijndael-128", "", "cbc", "");
                @mcrypt_generic_init($td, $key, $iv);
                $decrypted = @mdecrypt_generic($td, $code);
                mcrypt_generic_deinit($td);
                mcrypt_module_close($td);
                return $this->PKCS5UnPad($decrypted);
            } catch (Exception $ex) {
        	    $this->writeLog("decrypt() Exception Code ".$ex->getCode()." : ".$ex->getMessage()." in ".$ex->getFile()." on line ".$ex->getLine());
        	    return "_FAIL_";
        	}
        }
        public function AESCBCPKCS5($source_data, $key, $iv, $mode="enc", $base64="yes") {
            try {
                if ($mode == "dec") {
                    if ($base64 == "yes") return $this->decrypt($iv, $key, base64_decode($source_data));
                    else return $this->decrypt($iv, $key, $source_data);
                }
                else {
                    if ($base64 == "yes") return base64_encode($this->encrypt($iv, $key, $source_data));
                    else return $this->encrypt($iv, $key, $source_data);
                }
            } catch (Exception $ex) {
        	    $this->writeLog("AESCBCPKCS5() Exception Code ".$ex->getCode()." : ".$ex->getMessage()." in ".$ex->getFile()." on line ".$ex->getLine());
        	    return "_FAIL_";
        	}
        }
        public function connMPayDLP($urlStr, $mid, $encryptStr) {
            try {
                // php에 cURL 모듈 설치 필요(리눅스 - curl.so, 윈도우 - php_curl.dll 확장모듈 필요)
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $urlStr); //접속할 URL 주소
                //curl_setopt($ch, CURLOPT_PORT, 12443); //접속할 port, 주소에 있으므로 설정하지 않음
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // 인증서 체크같은데 true 시 안되는 경우가 많다.
                //curl_setopt($ch, CURLOPT_SSLVERSION, 3); // SSL 버젼 (https 접속시에 필요, 기본값으로 해야하므로 설정하지 않음)
                curl_setopt($ch, CURLOPT_HEADER, 0); // 헤더 출력 여부
                curl_setopt($ch, CURLOPT_POST, 1); // Post Get 접속 여부
                curl_setopt($ch, CURLOPT_POSTFIELDS, array("k" => $mid, "v" => $encryptStr)); // Post 값 Get 방식처럼적는다.
                curl_setopt($ch, CURLOPT_TIMEOUT, 30); // TimeOut 값
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // 결과값을 받을것인지
                curl_setopt($ch, CURLOPT_USERAGENT, $this->phpVersion); // 버전
                $result = curl_exec($ch);
                $errcode = curl_error($ch);
                if ($errcode != "") $result = $errcode;
                //$errcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                //if ($errcode != 200) $result = $errcode;
                curl_close($ch);
                return $result;
            } catch (Exception $ex) {
        	    $this->writeLog("connMPayDLP() Exception Code ".$ex->getCode()." : ".$ex->getMessage()." in ".$ex->getFile()." on line ".$ex->getLine());
        	    return "_FAIL_";
        	}
        }
        public function writeLog($strLogText) {
            $log_string = "";
    	    if (is_array($strLogText)) {
    	        $log_string = "[".date("Y/m/d H:i:s")."] \r\n";
    	        foreach (array_keys($strLogText) as $key) {
                    if($key == 'MERCHANT_ID')
                        continue;

                    $log_string = $log_string."                      [".$key."] => ".$strLogText[$key]."\r\n";
                }
            } else {
                $log_string = "[".date("Y/m/d H:i:s")."] ".$strLogText."\r\n";
            }
            $log_filenm = $this->LogPath.date("Ymd")."_KMpay.log";
            $log_file = fopen($log_filenm, "a");
            if($log_file == false) return;
            flock($log_file, LOCK_EX);
            //fwrite($log_file, $log_string);
            fputs($log_file, $log_string);
            fflush($log_file);
            flock($log_file, LOCK_UN);
            fclose($log_file);
        }
    }
    class JsonString {
        private $LogPath = "";
        private $strValues = array();
        public function JsonString($LogDir) {
    	    if (substr($LogDir, strlen($LogDir) - 1) == "/") {
    	        $LogDir = substr($LogDir, 0, strlen($LogDir) - 1);
    	    }
    	    @mkdir($LogDir);
    	    $this->LogPath = $LogDir."/";
    	}
        public function setValue($key, $value) {
            try {
                $this->strValues[$key] = $value;
                return "_TRUE_";
            } catch (Exception $ex) {
        	    $this->writeLog("setValue() Exception Code ".$ex->getCode()." : ".$ex->getMessage()." in ".$ex->getFile()." on line ".$ex->getLine());
        	    return "_FAIL_";
        	}
        }
        public function getValue($key) {
            try {
                if (!in_array($key, array_keys($this->strValues))) return "";
                return $this->strValues[$key];
            } catch (Exception $ex) {
        	    $this->writeLog("getValue() Exception Code ".$ex->getCode()." : ".$ex->getMessage()." in ".$ex->getFile()." on line ".$ex->getLine());
        	    return "_FAIL_";
        	}
        }
        public function getArrayValue() {
            try {
                return $this->strValues;
            } catch (Exception $ex) {
        	    $this->writeLog("getArrayValue() Exception Code ".$ex->getCode()." : ".$ex->getMessage()." in ".$ex->getFile()." on line ".$ex->getLine());
        	    return "_FAIL_";
        	}
        }
        public function setJsonString($strJsonString) {
            try {
                $strJsonString = substr($strJsonString, 2, strlen($strJsonString) - 4);
                $strItems = explode("\",\"", $strJsonString);
                foreach ($strItems as $strItem) {
                    $strValue = explode("\":\"", $strItem);
                    $this->setValue($strValue[0], $strValue[1]);
                }
                return "_TRUE_";
            } catch (Exception $ex) {
        	    $this->writeLog("setJsonString() Exception Code ".$ex->getCode()." : ".$ex->getMessage()." in ".$ex->getFile()." on line ".$ex->getLine());
        	    return "_FAIL_";
        	}
        }
        public function getJsonString() {
            try {
                $strJsonString = "{";
                foreach (array_keys($this->strValues) as $key) {
                    $strJsonString = $strJsonString."\"".$key."\":";
                    // 2014.11.25 str_replace 추가
                    $strJsonString = $strJsonString."\"". str_replace(array("\\", "\""), array("\\\\", "\\\""), $this->strValues[$key])."\",";
                }
                $strJsonString = substr($strJsonString, 0, strlen($strJsonString)-1)."}";
                return $strJsonString;
            } catch (Exception $ex) {
        	    $this->writeLog("getJsonString() Exception Code ".$ex->getCode()." : ".$ex->getMessage()." in ".$ex->getFile()." on line ".$ex->getLine());
        	    return "_FAIL_";
        	}
        }
        public function writeLog($strLogText) {
    	    $log_string = "";
    	    if (is_array($strLogText)) {
    	        $log_string = "[".date("Y/m/d H:i:s")."] \r\n";
    	        foreach (array_keys($strLogText) as $key) {
                    $log_string = $log_string."                      [".$key."] => ".$strLogText[$key]."\r\n";
                }
            } else {
                $log_string = "[".date("Y/m/d H:i:s")."] ".$strLogText."\r\n";
            }
            $log_filenm = $this->LogPath.date("Ymd")."_KMpayLog.log";
            $log_file = fopen($log_filenm, "a");
            if($log_file == false) return;
            flock($log_file, LOCK_EX);
            //fwrite($log_file, $log_string);
            fputs($log_file, $log_string);
            fflush($log_file);
            flock($log_file, LOCK_UN);
            fclose($log_file);
        }
    }

    class KMPayDataValidator {
    	public $resultValid = "";
    	public function KMPayDataValidator($value) {
    		$this->resultValid = $this->validator($value);
    	}
    	// 2014.12.02 추가 (check key in array)
    	private function getValueFromArray($arr, $key) {
    		if(array_key_exists($key, $arr)) {
    			return $arr[$key];
    		} else {
    			return "";
    		}
    	}
    	// 2014.12.02 수정 (getValueFromArray 사용)
    	private function validator($value) {
    		//필수정보
    		$prType = $this->getValueFromArray($value, "PR_TYPE");
    		$merchantID = $this->getValueFromArray($value, "MERCHANT_ID");
    		$channelType = $this->getValueFromArray($value, "channelType");
    		$merchantTxnNum = $this->getValueFromArray($value, "MERCHANT_TXN_NUM");
    		$productName = $this->getValueFromArray($value, "PRODUCT_NAME");
    		$amount = $this->getValueFromArray($value, "AMOUNT");
    		$currency = $this->getValueFromArray($value, "CURRENCY");
    		$returnUrl = $this->getValueFromArray($value, "RETURN_URL");

    		//추가정보
    		$cardMerchantNum = $this->getValueFromArray($value, "CARD_MERCHANT_NUM");
    		$supplyAmt = $this->getValueFromArray($value, "SUPPLY_AMT");
    		$goodsVat = $this->getValueFromArray($value, "GOODS_VAT");
    		$serviceAmt = $this->getValueFromArray($value, "SERVICE_AMT");
    		$cancelTime = $this->getValueFromArray($value, "CANCEL_TIME");
    		$fixedInt = $this->getValueFromArray($value, "FIXED_INT");
    		$certifiedFlag = $this->getValueFromArray($value, "CERTIFIED_FLAG");
    		$offerPeriodFlag = $this->getValueFromArray($value, "OFFER_PERIOD_FLAG");
    		$offerPeriod = $this->getValueFromArray($value, "OFFER_PERIOD");


    		if (strlen($certifiedFlag) == 0) {
    			$certifiedFlag = "N";
    		}
    		if (strlen($supplyAmt) == 0) {
    			$supplyAmt = "0";
    		}
    		if (strlen($goodsVat) == 0) {
    			$goodsVat = "0";
    		}
    		if (strlen($cancelTime) == 0) {
    			$cancelTime = "1440";
    		}

    		//필수
    		if (strlen($prType) == 0) {
    			return "USER_ERROR_CODE,804,결제요청타입은 필수입력사항 입니다.";
    		}
    		else if ($prType != "MPM" && $prType != "WPM") {
    			return "USER_ERROR_CODE,805,잘못된 결제요청타입 입니다.";
    		}

    		if (strlen($merchantID) == 0) {
    			return "USER_ERROR_CODE,806,가맹점 ID 필수입력사항 입니다.";
    		}
    		else if (strlen($merchantID) > 38) {
    			return "USER_ERROR_CODE,808,가맹점 ID의 제한 길이가 초과 되었습니다.";
    		}

    		if (strlen($merchantTxnNum) == 0) {
    			return "USER_ERROR_CODE,823,가맹점 거래번호는 필수입력사항 입니다.";
    		}
    		else if (strlen($merchantTxnNum) > 40) {
    			return "USER_ERROR_CODE,824,가맹점 거래번호의 제한 길이가 초과 되었습니다.";
    		}

    		if (strlen($productName) == 0) {
    			return "USER_ERROR_CODE,809,상품명은 필수입력사항 입니다.";
    		}
    		else if (strlen($productName) > 200) {
    			return "USER_ERROR_CODE,810,상품명은 영문 200자 이내입니다.";
    		}

    		if (strlen($amount) == 0) {
    			return "USER_ERROR_CODE,811,상품금액은 필수입력사항 입니다.";
    		}
    		else if (!is_numeric($amount)){
    			return "USER_ERROR_CODE,812,상품금액은 숫자형입니다.";
    		}

    		if (strlen($currency) == 0) {
    			return "USER_ERROR_CODE,813,거래통화는 필수입력사항 입니다.";
    		}

    		if ($certifiedFlag == "CN") {
    			//웹결제에서는 필수체크 안함
    		}
    		else if (strlen($certifiedFlag) == 0) {
    			return "USER_ERROR_CODE,830,결제승인결과전송URL은 필수입력사항 입니다.";
    		}

    		if (strlen($cardMerchantNum) > 0 && !is_numeric($cardMerchantNum)) {
    			return "USER_ERROR_CODE,814,카드 가맹점 번호는 숫자형입니다.";
    		}

    		if (strlen($supplyAmt) > 0 && !is_numeric($supplyAmt)) {
    			return "USER_ERROR_CODE,815,공급가액은 숫자형입니다.";
    		}

    		if (strlen($goodsVat) > 0 && !is_numeric($goodsVat)) {
    			return "USER_ERROR_CODE,816,부가세는 숫자형입니다.";
    		}

    		if (strlen($serviceAmt) > 0 && !is_numeric($serviceAmt)) {
    			return "USER_ERROR_CODE,817,봉사료는 숫자형입니다.";
    		}

    		if (strlen($cancelTime) > 0 && !is_numeric($cancelTime)) {
    			return "USER_ERROR_CODE,818,결제취소시간(분)은 숫자형입니다.";
    		}

    		if (strlen($fixedInt) == 0) {
    			// 정상
    		}
    		else if (!is_numeric($fixedInt)) {
    			return "USER_ERROR_CODE,820,고정할부개월이 잘못되었습니다.";
    		}
    		else if (!((0 <= intval($fixedInt) && intval($fixedInt) <= 24) ||  $fixedInt == "36")) {
    			return "USER_ERROR_CODE,820,고정할부개월이 잘못되었습니다.";
    		}

    		if ($certifiedFlag != "N" && $certifiedFlag != "CN") {
    			return "USER_ERROR_CODE,831,가맹점 인증 구분값은 N 혹은 CN 입니다";
    		}

    		return "";
    	}
    }
?>