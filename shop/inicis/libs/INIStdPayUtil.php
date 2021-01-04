<?php

class INIStdPayUtil {

    function getTimestamp() {
        // timezone 을 설정하지 않으면 getTimestapme() 실행시 오류가 발생한다.
        // php.ini 에 timezone 설정이 되어 잇으면 아래 코드가 필요없다. 
        // php 5.3 이후로는 반드시 timezone 설정을 해야하기 때문에 아래 코드가 필요없을 수 있음. 나중에 확인 후 수정필요.
        // 이니시스 플로우에서 timestamp 값이 중요하게 사용되는 것으로 보이기 때문에 정확한 timezone 설정후 timestamp 값이 필요하지 않을까 함.
        
        /**********php5
        date_default_timezone_set('Asia/Seoul');
        $date = new DateTime();
        */
        
        putenv('TZ=Asia/Seoul');

        $milliseconds = round(microtime(true) * 1000);
        $tempValue1 = round($milliseconds / 1000);  //max integer 자릿수가 9이므로 뒤 3자리를 뺀다
        $tempValue2 = round((float) microtime(false) * 1000); //뒤 3자리를 저장
        switch (strlen($tempValue2)) {
            case '3':
                break;
            case '2':
                $tempValue2 = "0" . $tempValue2;
                break;
            case '1':
                $tempValue2 = "00" . $tempValue2;
                break;
            default:
                $tempValue2 = "000";
                break;
        }

        return "" . $tempValue1 . $tempValue2;
    }

    /*
      //*** 위변조 방지체크를 signature 생성 ***

      mid, price, timestamp 3개의 키와 값을
      key=value 형식으로 하여 '&'로 연결한 하여 SHA-256 Hash로 생성 된값
      ex) mid=INIpayTest&price=819000&timestamp=2012-02-01 09:19:04.004

     * key기준 알파벳 정렬
     * timestamp는 반드시 signature생성에 사용한 timestamp 값을 timestamp input에 그데로 사용하여야함
     */

    function makeSignature($signParam) {
        ksort($signParam);
        $string = "";
        foreach ($signParam as $key => $value) {
            $string .= "&$key=$value";
        }
        $string = substr($string, 1); // remove leading "&"

        $sign = hash( "sha256", $string);

        return $sign;
    }

    function makeHash($data, $alg) {
        // $s = hash_hmac('sha256', $data, 'secret', true);
        // return base64_encode($s);
        
        ///$ret = openssl_digest($data, $alg);
        $ret = hash($alg, $data);
        return $ret;
    }
	
	//
	function makeSignatureAuth($parameters) {

		if ($parameters == null || sizeof($parameters) == 0) {
			throw new Exception("<p>Parameters can not be empty.</P>");
		}

		$stringToSign = "";															//반환용 text
		$mid          = $parameters["mid"];                                                //mid
		$tstamp       = $parameters["tstamp"];												//auth timestamp
		$MOID         = $parameters["MOID"];												//OID
		$TotPrice     = $parameters["TotPrice"];											//total price
		$tstampKey    = substr($parameters["tstamp"], strlen($parameters["tstamp"]) - 1);	// timestamp 마지막 자리 1자리 숫자
		
		switch (intval($tstampKey)){
		case 1 :
			$stringToSign = "MOID=" . $MOID . "&mid=" . $mid . "&tstamp=" . $tstamp ;
			break;
		case 2 :
			$stringToSign = "MOID=" . $MOID . "&tstamp=" . $tstamp . "&mid=" . $mid ;
			break;
		case 3 :
			$stringToSign = "mid=" . $mid . "&MOID=" . $MOID . "&tstamp=" . $tstamp ;
			break;
		case 4 :
			$stringToSign = "mid=" . $mid . "&tstamp=" . $tstamp . "&MOID=" . $MOID ;
			break;
		case 5 :
			$stringToSign = "tstamp=" . $tstamp . "&mid=" . $mid . "&MOID=" . $MOID ;
			break;
		case 6 :
			$stringToSign = "tstamp=" . $tstamp . "&MOID=" . $MOID . "&mid=" . $mid ;
			break;
		case 7 :
			$stringToSign = "TotPrice=" . $TotPrice . "&mid=" . $mid . "&tstamp=" . $tstamp ;
			break;
		case 8 :
			$stringToSign = "TotPrice=" . $TotPrice . "&tstamp=" . $tstamp . "&mid=" . $mid ;
			break;
		case 9 :
			$stringToSign = "TotPrice=" . $TotPrice . "&MOID=" . $MOID . "&tstamp=" . $tstamp ;
			break;
		case 0 :
			$stringToSign = "TotPrice=" . $TotPrice . "&tstamp=" . $tstamp . "&MOID=" . $MOID ;
			break;
		}

		$signature = hash("sha256", $stringToSign);            				// sha256 처리하여 hash 암호화
		//$signature = $this->makeHash($stringToSign, "sha256");            // sha256 처리하여 hash 암호화

		return $signature;
	}

}