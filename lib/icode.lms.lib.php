<?php
if (!defined('_GNUBOARD_')) exit;

// 요금제에 따른 port 구분
function get_icode_port_type($id, $pw)
{
    $userinfo = get_icode_userinfo($id, $pw);

    if($userinfo['payment'] == 'A') { // 충전제
        return 1;
    } else if($userinfo['payment'] == 'C') { // 정액제
        return 1;
    } else {
        return false;
    }
}

/**
* SMS 발송을 관장하는 메인 클래스이다.
*
* 접속, 발송, URL발송, 결과등의 실질적으로 쓰이는 모든 부분이 포함되어 있다.
*/
class LMS {
	var $icode_id;
	var $icode_pw;
	var $socket_host;
	var $socket_port;
	var $socket_portcode;
	var $Data = array();
	var $Result = array();

	// SMS 서버 접속
	function SMS_con($host, $id, $pw, $portcode) {
		$this->socket_host	    = $host;
		$this->socket_portcode	= $portcode;
		$this->icode_id		    = FillSpace($id, 10);
		$this->icode_pw		    = FillSpace($pw, 10);
	}

	function Init() {
		$this->Data		= "";	// 발송하기 위한 패킷내용이 배열로 들어간다.
		$this->Result	= "";	// 발송결과값이 배열로 들어간다.
	}

	function Add($strDest, $strCallBack, $strCaller, $strSubject, $strURL, $strData, $strDate="", $nCount) {

		// 문자 타입별 Port 설정.
		$sendType = strlen($strData) > 90 ? 1 : 0; // 0: SMS / 1: LMS

		/* 개발 완료 후 아래 포트를 rand 함수를 이용하는 라인으로 변경 바랍니다.*/

		// 충전식
		if ($this->socket_portcode == 1) {
				if($sendType && $sendType == 1) {
					//$this->socket_port = 8200;		// LMS
					$this->socket_port=(int)rand(8200,8201);	// LMS
				} else {
					//$this->socket_port = 6295;		// SMS
					$this->socket_port=(int)rand(6295,6297);	// SMS
				}
		}
		// 정액제
		else {
			if($sendType && $sendType == 1) {
				//$this->socket_port = 8300; //	LMS
				$this->socket_port=(int)rand(8300,8301); //	LMS
			} else {
				//$this->socket_port = 6291; //	SMS
				$this->socket_port=(int)rand(6291,6293); //	SMS
			}
		}

		$strCallBack	= FillSpace($strCallBack, 11);       // 회신번호
		$strDate			= FillSpace($strDate, 12);           // 즉시(12byte 공백), 예약전송(YmdHi)

		if ($sendType && $sendType == 1) {

			/** LMS 제목 **/
			/*
			제목필드의 값이 없을 경우 단말기 기종및 설정에 따라 표기 방법이 다름
			1.설정에서 제목필드보기 설정 Disable -> 제목필드값을 넣어도 미표기
			2.설정에서 제목필드보기 설정 Enable  -> 제목을 넣지 않을 경우 제목없음으로 자동표시

			제목의 첫글자에 "<",">", 개행문자가 있을경우 단말기종류 및 통신사에 따라 메세지 전송실패 -> 글자를 체크하거나 취환처리요망
            */
			$strSubject = str_replace("\r\n", " ", $strSubject);
			$strSubject = str_replace("<", "[", $strSubject);
			$strSubject = str_replace(">", "]", $strSubject);

			$strSubject = FillSpace($strSubject,30);
			$strData	= FillSpace(CutChar($strData,1500),1500);
		} else if (!$strURL) {
			$strData	= FillSpace(CutChar($strData,90),90);
			$strCaller  = FillSpace($strCaller,10);
		} else {
			$strURL		= FillSpace($strURL,50);
		}

		$Error = CheckCommonTypeDest($strDest, $nCount);
		$Error = is_vaild_callback($strCallBack);
		$Error = CheckCommonTypeDate($strDate);

		for ($i=0; $i<$nCount; $i++) {

			$strDest[$i] = FillSpace($strDest[$i],11);
			if ($sendType && $sendType == 1) {
				$this->Data[$i]	= '01144 '.$this->icode_id.$this->icode_pw.$strDest[$i].$strCallBack.$strSubject.$strDate.$strData;
			} else if (!$strURL) {
				$this->Data[$i]	= '01144 '.$this->icode_id.$this->icode_pw.$strDest[$i].$strCallBack.$strCaller.$strDate.$strData;
			} else {
				$strData = FillSpace(CheckCallCenter($strURL, $strDest[$i], $strData),80);
				$this->Data[$i]	= '05173 '.$this->icode_id.$this->icode_pw.$strDest[$i].$strCallBack.$strURL.$strDate.$strData;
			}
		}
		return true;
	}


	function Send() {
		$fsocket = fsockopen($this->socket_host,$this->socket_port, $errno, $errstr, 2);
		if (!$fsocket) return false;
		set_time_limit(300);

		foreach($this->Data as $puts) {
			fputs($fsocket, $puts);
			while(!$gets) { $gets = fgets($fsocket,30); }
			$dest = substr($puts,26,11);
			if (substr($gets,0,19) == "0223  00".$dest) {
				$this->Result[] = $dest.":".substr($gets,19,10);
			} else {
				$this->Result[$dest] = $dest.":Error(".substr($gets,6,2).")";
			}
			$gets = "";
		}

		fclose($fsocket);
		$this->Data = "";
		return true;
	}
}

/**
 * 원하는 문자열의 길이를 원하는 길이만큼 공백을 넣어 맞추도록 합니다.
 *
 * @param	text	원하는 문자열입니다.
 *				size	원하는 길이입니다.
 * @return			변경된 문자열을 넘깁니다.
 */
function FillSpace($text,$size) {
	for ($i=0; $i<$size; $i++) $text.= " ";
	$text = substr($text,0,$size);
	return $text;
}

/**
 * 원하는 문자열을 원하는 길에 맞는지 확인해서 조정하는 기능을 합니다.
 *
 * @param	word	원하는 문자열입니다.
 *			cut			원하는 길이입니다.
 * @return			변경된 문자열입니다.
 */
function CutChar($word, $cut) {
	$word=substr($word,0,$cut);									// 필요한 길이만큼 취함.
	for ($k = $cut-1; $k > 1; $k--) {
		if (ord(substr($word,$k,1))<128) break;		// 한글값은 160 이상.
	}
	$word = substr($word, 0, $cut-($cut-$k+1)%2);
	return $word;
}

/**
* 수신번호의 값이 정확한 값인지 확인합니다.
*
* @param	strDest	발송번호 배열입니다.
*					nCount	배열의 크기입니다.
* @return					처리결과입니다.
*/
function CheckCommonTypeDest($strDest, $nCount) {
	for ($i=0; $i<$nCount; $i++) {
		$strDest[$i] = preg_replace("/[^0-9]/","",$strDest[$i]);
		if(!preg_match("/^01[0-9]{8,9}$/", $strDest[$i]))
			return "수신번호오류";
	}
}


/**
* 회신번호 유효성 여부조회 *
* @param		string callback	회신번호
* @return		처리결과입니다
* 한국인터넷진흥원 권고
*/
function is_vaild_callback($callback){

	$_callback = preg_replace('/[^0-9]/', '', $callback);

	if (!preg_match("/^(02|0[3-6]\d|01(0|1|3|5|6|7|8|9)|070|080|007)\-?\d{3,4}\-?\d{4,5}$/", $_callback) &&
		  !preg_match("/^(15|16|18)\d{2}\-?\d{4,5}$/", $_callback)){
		return "회신번호오류";
	}

	if (preg_match("/^(02|0[3-6]\d|01(0|1|3|5|6|7|8|9)|070|080)\-?0{3,4}\-?\d{4}$/", $_callback)){
		return "회신번호오류";
	}
}


/**
* 예약날짜의 값이 정확한 값인지 확인합니다.
*
* @param		string	strDate (예약시간)
* @return		처리결과입니다
*/
function CheckCommonTypeDate($strDate) {
	$strDate = preg_replace("/[^0-9]/", "", $strDate);
	if ($strDate){
		if (!checkdate(substr($strDate,4,2),substr($strDate,6,2),substr($rsvTime,0,4)))
		return "예약날짜오류";
		if (substr($strDate,8,2)>23 || substr($strDate,10,2)>59) return false;
		return "예약날짜오류";
	}
}

/**
* URL콜백용으로 메세지 크기를 수정합니다.
*
* @param	url		URL 내용입니다.
*			    msg		결과메시지입니다.
*			    desk	문자내용입니다.
*/
function CheckCallCenter($url, $dest, $data) {
	switch (substr($dest,0,3)) {
		case '010': //20바이트
			return CutChar($data,20);	break;
		case '011': //80바이트
			return CutChar($data,80);	break;
		case '016': // 80바이트
			return CutChar($data,80);	break;
		case '017': // URL 포함 80바이트
			return CutChar($data,80 - strlen($url)); break;
		case '018': // 20바이트
			return CutChar($data,20); break;
		case '019': // 20바이트
			return CutChar($data,20); break;
		default:
			return CutChar($data,80);	break;
	}
}
?>