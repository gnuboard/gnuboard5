<?php
// 버전 1.0 : 2014/11/06  문석호, 최초 작성
/**
 * 2014.12.02 : 1) 로깅 시 주요 정보 마스킹 처리, 2) PayMethod key check
 */
    class CnsPayWebConnector {
        private $LogPath = "";
        private $ActionUrl = "";
    	private $cancelUrl = "";
    	private $phpVersion = "";
    	private $encodeKey = "";
    	private $requestData = array();
    	private $resultData = array();
    	public function CnsActionUrl($url) {
    		$this->ActionUrl = $url;
    	}
    	public function CnsPayVersion($ver) {
    		$this->phpVersion = $ver;
    	}
    	public function CnsPayWebConnector($LogDir) {
    	    $this->cancelUrl = $this->ActionUrl."/lite/cancelProcess.jsp";
    	    if (substr($LogDir, strlen($LogDir) - 1) == "/") {
    	        $LogDir = substr($LogDir, 0, strlen($LogDir) - 1);
    	    }
    	    @mkdir($LogDir);
    	    $this->LogPath = $LogDir."/";
    	}
    	public function setRequestData($request) {
    	    try {
        		foreach (array_keys($request) as $key) {
                    if(is_array($request[$key]))
                        continue;

                    $this->requestData[$key] = iconv("UTF-8", "EUC-KR", $request[$key]);
                }
                return "_TRUE_";
            } catch (Exception $ex) {
        	    $this->writeLog("setRequestData() Exception Code ".$ex->getCode()." : ".$ex->getMessage()." in ".$ex->getFile()." on line ".$ex->getLine());
        	    return "_FAIL_";
        	}
        }
        public function addRequestData($key, $value) {
    	    try {
    	        $this->requestData[$key] = $value;
    	        return "_TRUE_";
    	    } catch (Exception $ex) {
        	    $this->writeLog("addRequestData() Exception Code ".$ex->getCode()." : ".$ex->getMessage()." in ".$ex->getFile()." on line ".$ex->getLine());
        	    return "_FAIL_";
        	}
    	}
        public function getResultData($key) {
            try {
        		if (!in_array($key, array_keys($this->resultData))) {
        			return "";
        		} else if ($key == "Amt") {
        			if ($this->resultData[$key] != null && $this->resultData[$key] != "null" && $this->resultData[$key] != "") {
        				return $this->resultData[$key];
        			} else {
        			    return "0";
        			}
        		}
        		return $this->resultData[$key];
        	} catch (Exception $ex) {
        	    $this->writeLog("getResultData() Exception Code ".$ex->getCode()." : ".$ex->getMessage()." in ".$ex->getFile()." on line ".$ex->getLine());
        	    return "_FAIL_";
        	}
    	}

    	// 2014.12.02 추가 (check key in array)
    	private function getRequestData($key) {
    		if (array_key_exists($key, $this->requestData)) {
    			return $this->requestData[$key];
    		} else {
    			return "";
    		}
    	}

    	public function requestAction() {
    		$encodeKey = $this->requestData["EncodeKey"];
    		unset($this->requestData["EncodeKey"]);
    	    try {
        		if ($this->requestData["actionType"] != "CL0" && $this->requestData["actionType"] != "CI0") {
        		    if ($this->getRequestData("PayMethod") != "ESCROW") {
        		        $this->requestData["TID"] = $this->generateTID($this->requestData["MID"], $this->getRequestData("PayMethod"));
        		    }
        		}
        		$serviceUrl = $this->setActionType($this->requestData["actionType"], $this->getRequestData("PayMethod"));
                if ($serviceUrl == "_FAIL_" || $serviceUrl == "CNSPAY_10") {
                    $this->resultData["ResultCode"] = "JL10";
        			$this->resultData["ResultMsg"] = "actionType 설정이 잘못되었습니다.";
                    return "_FAIL_";
                }
                $this->writeLog("Request");
                $this->writeLog($this->requestData);
                $requestMessage = $this->makeRequestText($this->requestData);
                $resultMessage = $this->connectToServer($serviceUrl, $requestMessage);
                $this->writeLog("Result");
                // 2014.12.02 수신 전문 로깅 처리 제외
                //$this->writeLog($resultMessage);
    			if ($resultMessage == "_FAIL_" || substr($resultMessage, 0, 4) == "FAIL") {
    			    $resultCode = "";
    			    $resultMsg = "";
    			    $netCancelFlag = $this->requestNetCancel();
                    if ($netCancelFlag == "_TRUE_") {
    					$resultCode = "JL32";
        				$resultMsg = "PGWEB서버 통신중 오류가 발생하였습니다. (NET_CANCEL)";
    				} else { // netCancel 실패이면,
        				$resultCode = "JL33";
    					$resultMsg = "네트웍이 불안정으로 승인 실패하였습니다. 결제가 비 정상 처리 될 수 있으니 거래내역을 반드시 확인해주십시오.";
        			}
    				$this->resultData["ResultCode"] = $resultCode;
        			$this->resultData["ResultMsg"] = $resultMsg;
    				return "_FAIL_";
    			}
    			$resultMessage = $this->parseResult($resultMessage);
    			//$this->writeLog($this->resultData);
    			// 2014.12.02 로깅 시 주요 데이터 마스킹 처리
    			$this->writeLog($this->resultDataMask($this->resultData));
    			if ($resultMessage == "_FAIL_" || $resultMessage == "CNSPAY_41") {
        		    $this->resultData["ResultCode"] = "JL41";
        			$this->resultData["ResultMsg"] = "응답전문이 없습니다.";
                    return "_FAIL_";
    			}
    			return "_TRUE_";
    		} catch (Exception $ex) {
        	    $this->writeLog("requestAction() Exception Code ".$ex->getCode()." : ".$ex->getMessage()." in ".$ex->getFile()." on line ".$ex->getLine());
        	    return "_FAIL_";
        	}
    	}

    	// 2014.12.02 결과 배열 마스킹
    	private function resultDataMask($strLogText) {
    		$arrMask = array();
    		if (is_array($strLogText)) {
	    		foreach (array_keys($strLogText) as $key) {
	    			$k = str_replace("\n", "", trim($key));
	    			$arrMask[$k] = $this->requestMask($k, $strLogText[$key]);
	    		}
	    		return $arrMask;
    		} else {
    			return str_replace("\n", "", trim($strLogText));
    		}
    	}

    	// 2014.12.02 주요 정보 마스킹
    	private function requestMask ($name, $text) {
    		$value = str_replace("\n", "", trim($text));

    		if ($value == null || strlen(trim($value)) == 0) return "";

			if ($name == "X_CARDNO" || $name == "realPan" || $name == "cardNo"
					|| $name == "CardBin" || $name == "CardNo") {
				return $this->masking($value, 6, true, false);
			} else if ($name == "BuyerName" || $name == "buyerName") {
				return $this->masking($value, 1, true, false);
			} else if ($name == "BuyerEmail") {
				return $this->masking($value, 6, false, true);
			} else if ($name == "BuyerTel" || $name == "DstAddr") {
				return $this->masking($value, 5, false, false);
			} else if ($name == "BuyerAddr") {
				return $this->masking($value, 6, true, false);
			} else if ($name == "UserIP" || $name == "MallIP" || $name == "CancelPwd"
					|| $name == "mallUserID" || $name == "MallUserID"
					|| $name == "CancelIP") {
				return $this->masking($value, mb_strlen(iconv('euc-kr','utf-8',$value), 'utf-8'), true, true);
			} else {
				return $value;
			}
		}

		// 2014.12.02 마스킹 처리
		private function masking($string, $num, $isLeftOrder, $beginMasking) {

			if ( $string == null )
				return "";

			$res = "";
			$res2 = "";
			$sleng = 0;

			$str = iconv('euc-kr','utf-8',$string);
			$n = mb_strlen($str, 'utf-8');

			if ( $num >= 1 ) {
				if ( $n < $num ) {
					$res = $str;
				} else {
					if($beginMasking) {
						if ($isLeftOrder) {
							$res = str_repeat("*", $n);
						} else {
							$sleng = $num;
							$res2 = mb_substr($str, $sleng, $n, 'utf-8');
							for ( $j = 0; $j < $sleng; $j++ ) {
								$res .= "*";
							}
							$res .= $res2;
						}
					} else {
						$sleng = $num;
						$res2 = mb_substr($str, 0, $sleng, 'utf-8');
						for ( $j = $sleng; $j < $n; $j++ ) {
							$res .= "*";
						}
						$res = $res2 . $res;
					}
				}
			} else {
				$res = $str;
			}

			return iconv('utf-8','euc-kr',$res);
		}

    	private function requestNetCancel() {
    	    try {
        		// 예기치 못한 오류인경우 망상취소 시도.
        		$serviceUrl = $this->cancelUrl;
        		$this->requestData["actionType"] = "CL0";
        		$this->requestData["CancelIP"] = $this->requestData["MallIP"];
        		if ($this->requestData["Amt"] == null) {
        		    return "_FAIL_";
        		} else {
        		    if (is_numeric($this->requestData["Amt"])) {
        			    $this->requestData["CancelAmt"] = $this->requestData["Amt"];
        			} else {
        			    $this->requestData["CancelAmt"] = parameterDecrypt($encodeKey, $this->requestData["Amt"]);
        			}
        		}
        		$this->requestData["CancelMsg"] = "NICE_NET_CANCEL";
        		$this->requestData["PartialCancelCode"] = "0";
        		$this->requestData["NetCancelCode"] = "1";
        		if ($this->getRequestData("PayMethod") == "BILL" || $this->getRequestData("PayMethod") == "KAKAOPAY") $this->requestData["PayMethod"] = "CARD";
        		$requestMessage = makeRequestText($this->requestData);
        	    $resultMessage = connectToServer($serviceUrl, $this->requestData);
        		if ($resultMessage == "_FAIL_" || substr($resultMessage, 0, 4) == "FAIL") {
    			    $resultMessage = connectToServer2($serviceUrl, $this->requestData, 20);
    			    if ($resultMessage == "_FAIL_" || substr($resultMessage, 0, 4) == "FAIL") {
        			    //$this->resultData["ResultCode"] = "JL41";
            			//$this->resultData["ResultMsg"] = "망상취소 오류";
            			return "_FAIL_";
    			    }
    			}
        		return "_TRUE_";
    		} catch (Exception $ex) {
        	    $this->writeLog("requestNetCancel() Exception Code ".$ex->getCode()." : ".$ex->getMessage()." in ".$ex->getFile()." on line ".$ex->getLine());
        	    return "_FAIL_";
        	}
    	}
    	private function generateTID($mid, $svcCd) {
    	    try {
        		$iRandom = str_pad(rand(0, 9999), 4, "0", STR_PAD_LEFT);
        		return $mid.$this->getSvcCd($svcCd)."01".date("ymdHis").$iRandom;
        	} catch (Exception $ex) {
        	    $this->writeLog("generateTID() Exception Code ".$ex->getCode()." : ".$ex->getMessage()." in ".$ex->getFile()." on line ".$ex->getLine());
        	    return "_FAIL_";
        	}
    	}
    	private function getSvcCd($svcCd) {
    	    try {
        		if ($svcCd == "CARD" || $svcCd == "BILL" || $svcCd == "KAKAOPAY") {
        			return "01";
        		} else if ($svcCd == "BANK") {
        			return "02";
        		} else if ($svcCd == "VBANK") {
        			return "03";
        		} else if ($svcCd == "CELLPHONE") {
        			return "05";
        		} else if ($svcCd == "MOBILE_BILLING") {
        			return "05";
        		} else if ($svcCd == "MOBILE_BILL") {
        			return "05";
        		}
        		return "00";
        	} catch (Exception $ex) {
        	    $this->writeLog("getSvcCd() Exception Code ".$ex->getCode()." : ".$ex->getMessage()." in ".$ex->getFile()." on line ".$ex->getLine());
        	    return "_FAIL_";
        	}
    	}
    	private function setActionType($type, $paymethod) {
    	    try {
        		if ($type == null) return "CNSPAY_10";
        		$builder = $this->ActionUrl;
        		if ($type == "CL0") {
        			$builder = $builder."/lite/cancelProcess.jsp";
        		} else if ($type == "CI0") {
        			$builder = $builder."/lite/tidInfoProcess.jsp";
        		} else if ($type == "PY0") {
        			if ($paymethod == "CASHRCPT") { // 현금영수증인경우
        				$builder = $builder."/lite/cashReceiptProcess.jsp";
        			} else if ($paymethod == "BILL") {
        				$builder = $builder."/lite/billingProcess.jsp";
        			} else if ($paymethod == "BILLKEY") {
        				$builder = $builder."/lite/billkeyProcess.jsp";
        			} else if ($paymethod == "ESCROW") {
        				$builder = $builder."/lite/escrowProcess.jsp";
        			} else if ($paymethod == "MOBILE_AUTH") {
        				$builder = $builder."/lite/mobileAuth.jsp";
        			} else if ($paymethod == "MOBILE_BILL") {
        				$builder = $builder."/lite/mobileBill.jsp";
        			} else if ($paymethod == "MOBILE_BILLING") {
        				$builder = $builder."/lite/mobileBillingProcess.jsp";
        			} else if ($paymethod == "MOBILE_AUTH_REQ") {
        				$builder = $builder."/lite/mobileConfirmRequest.jsp";
        			} else if ($paymethod == "MOBILE_AUTH_RES") {
        				$builder = $builder."/lite/mobileConfirmResult.jsp";
        			} else if ($paymethod == "CARD_ARS") {
        				$builder = $builder."/lite/cardArsProcess.jsp";
        			} else if ($paymethod == "MOBILE_AUTH_NS") {
        				$builder = $builder."/lite/mobileAuth_NS.jsp";
        			} else if ($paymethod == "OM_SUB_INS") {
        				$builder = $builder."/lite/payproxy/subMallSetProcess.jsp";
        			} else if ($paymethod == "OM_SUB_PAY") {
        				$builder = $builder."/lite/payproxy/subMallIcheProcess.jsp";
        			} else if ($paymethod == "LOTTE_POINT") {
        				$builder = $builder."/api/checkLottePoint.jsp";
        			} else if ($paymethod == "HPBILLKEY") {
        				$builder = $builder."/lite/hpBillkeyProcess.jsp";
        			} else if ($paymethod == "HPCARD_AUTH") {
        				$builder = $builder."/lite/hpCardAuthProcess.jsp";
        			} else if ($paymethod == "HPCARD_BILLKEY") {
        				$builder = $builder."/lite/hpCardBillkeyProcess.jsp";
        			} else {
        				$builder = $builder."/lite/payProcess.jsp";
        			}
        		}
        		return $builder;
        	} catch (Exception $ex) {
        	    $this->writeLog("setActionType() Exception Code ".$ex->getCode()." : ".$ex->getMessage()." in ".$ex->getFile()." on line ".$ex->getLine());
        	    return "_FAIL_";
        	}
    	}
    	private function makeRequestText($reqData) {
    	    try {
        		$strParameter = "";
                foreach (array_keys($reqData) as $key) {
                    $strParameter = $strParameter.$key."=".urlencode($reqData[$key])."&";
                }
                $strParameter = substr($strParameter, 0, strlen($strParameter) - 1);
                return $strParameter;
            } catch (Exception $ex) {
        	    $this->writeLog("makeRequestText() Exception Code ".$ex->getCode()." : ".$ex->getMessage()." in ".$ex->getFile()." on line ".$ex->getLine());
        	    return "_FAIL_";
        	}
    	}
    	private function connectToServer($urlStr, $reqData) {
    	    try {
    		    return $this->connectToServer2($urlStr, $reqData, 15);
    		} catch (Exception $ex) {
        	    $this->writeLog("connectToServer() Exception Code ".$ex->getCode()." : ".$ex->getMessage()." in ".$ex->getFile()." on line ".$ex->getLine());
        	    return "_FAIL_";
        	}
    	}
    	private function connectToServer2($urlStr, $reqData, $timeout) {
    	    try {
        	    // php에 cURL 모듈 설치 필요(리눅스 - curl.so, 윈도우 - php_curl.dll 확장모듈 필요)
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $urlStr); //접속할 URL 주소
                //curl_setopt($ch, CURLOPT_PORT, 6464); //접속할 port, 주소에 있으므로 설정하지 않음
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // 인증서 체크같은데 true 시 안되는 경우가 많다.
                //curl_setopt($ch, CURLOPT_SSLVERSION, 3); // SSL 버젼 (https 접속시에 필요, 기본값으로 해야하므로 설정하지 않음)
                curl_setopt($ch, CURLOPT_HEADER, 0); // 헤더 출력 여부
                curl_setopt($ch, CURLOPT_POST, 1); // Post Get 접속 여부
                curl_setopt($ch, CURLOPT_POSTFIELDS, $reqData); // Post 값 Get 방식처럼적는다.
                curl_setopt($ch, CURLOPT_TIMEOUT, $timeout); // TimeOut 값
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
                $this->writeLog("connectToServer2() Exception Code ".$ex->getCode()." : ".$ex->getMessage()." in ".$ex->getFile()." on line ".$ex->getLine());
                return "_FAIL_";
            }
    	}
    	private function parseResult($resultMessage) {
    	    try {
        		if ($resultMessage == null) return "CNSPAY_41";
        		$parsedArr = explode("|", $resultMessage);
        		foreach ($parsedArr as $valueArr) {
        		    $posit = strpos($valueArr, "=");
                    $key = substr($valueArr, 0, $posit);
                    $value = substr($valueArr, $posit + 1);
                    $this->resultData[$key] = $value;
                }
                return "_TRUE_";
            } catch (Exception $ex) {
                $this->writeLog("parseResult() Exception Code ".$ex->getCode()." : ".$ex->getMessage()." in ".$ex->getFile()." on line ".$ex->getLine());
                return "_FAIL_";
            }
    	}
    	public function writeLog($strLogText) {
    	    $log_string = "";
            $exclude = array('MID', 'merchantEncKey', 'merchantHashKey', 'CancelPwd', 'site_cd', 'def_site_cd', 'CST_MID', 'LGD_MID');
    	    if (is_array($strLogText)) {
    	        $log_string = "[".date("Y/m/d H:i:s")."] \r\n";
    	        foreach (array_keys($strLogText) as $key) {
                    if(in_array($key, $exclude))
                        continue;

                    if(preg_match('#^od_.+$#', $key))
                        continue;

                    $log_string = $log_string."                      [".$key."] => ".$strLogText[$key]."\r\n";
                }
            } else {
                $log_string = "[".date("Y/m/d H:i:s")."] ".$strLogText."\r\n";
            }
            $log_filenm = $this->LogPath.date("Ymd")."_CNSpay.log";
            $log_file = fopen($log_filenm, "a");
            if($log_file == false) return;
            flock($log_file, LOCK_EX);
            //fwrite($log_file, $log_string);
            fputs($log_file, $log_string);
            fflush($log_file);
            flock($log_file, LOCK_UN);
            fclose($log_file);
        }
        public function makeDateString($sDate) {
            try {
        		if ($sDate == null) return "";
        		$strValue = "";
        		if (strlen($sDate) == 12) {
        			$strValue = $strValue."20".substr($sDate, 0, 2)."-";
        			$strValue = $strValue.substr($sDate, 2, 2)."-";
        			$strValue = $strValue.substr($sDate, 4, 2). " ";
        			$strValue = $strValue.substr($sDate, 6, 2).":";
        			$strValue = $strValue.substr($sDate, 8, 2).":";
        			$strValue = $strValue.substr($sDate, 10, 2);
        		} else if (strlen($sDate) == 14) {
        			$strValue = $strValue.substr($sDate, 0, 4)."-";
        			$strValue = $strValue.substr($sDate, 4, 2)."-";
        			$strValue = $strValue.substr($sDate, 6, 2)." ";
        			$strValue = $strValue.substr($sDate, 8, 2).":";
        			$strValue = $strValue.substr($sDate, 10, 2).":";
        			$strValue = $strValue.substr($sDate, 12, 2);
        		} else if (strlen($sDate) == 8) {
        		    $strValue = $strValue.substr($sDate, 0, 4)."-";
        			$strValue = $strValue.substr($sDate, 4, 2)."-";
        			$strValue = $strValue.substr($sDate, 6, 2);
        		} else {
        			$strValue = $sDate;
        		}
        		return $strValue;
        	} catch (Exception $ex) {
        	    writeLog("makeDateString() Exception Code ".$ex->getCode()." : ".$ex->getMessage()." in ".$ex->getFile()." on line ".$ex->getLine());
        	    return "_FAIL_";
        	}
    	}


      public function makeHashInputString($salt) {

        $result = "";

				for($count = 0;$count < strlen($salt)/2;$count++) {
					$temp0 = substr($salt, 2*$count, 2);
					$temp1 = hexdec($temp0);
					$temp3 = reset(unpack("l", pack("l", $temp1 +0xffffff00)));
					$temp4 = pack('C*', $temp3);

					$result = $result.$temp4;

				}

				return $result;

    	}

    }
?>
