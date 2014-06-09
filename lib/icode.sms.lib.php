<?php
if (!defined('_GNUBOARD_')) exit;
// 아이코드에서 제공하는 함수

///////////////////////////////////////////////////////////////////////////////////////////
// 이 부분은 건드릴 필요가 없습니다.

function spacing($text,$size) {
	for ($i=0; $i<$size; $i++) $text.=" ";
	$text = substr($text,0,$size);
	return $text;
}

function cut_char($word, $cut) {
//	$word=trim(stripslashes($word));
	$word=substr($word,0,$cut);						// 필요한 길이만큼 취함.
	for ($k=$cut-1; $k>1; $k--) {
		if (ord(substr($word,$k,1))<128) break;		// 한글값은 160 이상.
	}
	$word=substr($word,0,$cut-($cut-$k+1)%2);
	return $word;
}

function CheckCommonType($dest, $rsvTime) {
	//$dest=eregi_replace("[^0-9]","",$dest);
	$dest=preg_replace("/[^0-9]/i","",$dest);
	if (strlen($dest)<10 || strlen($dest)>11) return "휴대폰 번호가 틀렸습니다";
	$CID=substr($dest,0,3);
	//if ( eregi("[^0-9]",$CID) || ($CID!='010' && $CID!='011' && $CID!='016' && $CID!='017' && $CID!='018' && $CID!='019') ) return "휴대폰 앞자리 번호가 잘못되었습니다";
	if ( preg_match("/[^0-9]/i",$CID) || ($CID!='010' && $CID!='011' && $CID!='016' && $CID!='017' && $CID!='018' && $CID!='019') ) return "휴대폰 앞자리 번호가 잘못되었습니다";
	//$rsvTime=eregi_replace("[^0-9]","",$rsvTime);
	$rsvTime=preg_replace("/[^0-9]/i","",$rsvTime);
	if ($rsvTime) {
		if (!checkdate(substr($rsvTime,4,2),substr($rsvTime,6,2),substr($rsvTime,0,4))) return "예약날짜가 잘못되었습니다";
		if (substr($rsvTime,8,2)>23 || substr($rsvTime,10,2)>59) return "예약시간이 잘못되었습니다";
	}
}

class SMS {
	var $ID;
	var $PWD;
	var $SMS_Server;
	var $port;
	var $SMS_Port;
	var $Data = array();
	var $Result = array();

	function SMS_con($sms_server,$sms_id,$sms_pw,$port) {
		$this->ID=$sms_id;		// 계약 후 지정
		$this->PWD=$sms_pw;		// 계약 후 지정
		$this->SMS_Server=$sms_server;
		$this->SMS_Port=$port;
		$this->ID = spacing($this->ID,10);
		$this->PWD = spacing($this->PWD,10);
	}

	function Init() {
		$this->Data = "";
		$this->Result = "";
	}

	function Add($dest, $callBack, $Caller, $msg, $rsvTime="") {
        global $g5;

		// 내용 검사 1
		$Error = CheckCommonType($dest, $rsvTime);
		if ($Error) return $Error;
		// 내용 검사 2
		//if ( eregi("[^0-9]",$callBack) ) return "회신 전화번호가 잘못되었습니다";
		if ( preg_match("/[^0-9]/i",$callBack) ) return "회신 전화번호가 잘못되었습니다";

        $msg=cut_char($msg,80); // 80자 제한
		// 보낼 내용을 배열에 집어넣기
		$dest = spacing($dest,11);
		$callBack = spacing($callBack,11);
		$Caller = spacing($Caller,10);
		$rsvTime = spacing($rsvTime,12);
		$msg = spacing($msg,80);

		$this->Data[] = '01144 '.$this->ID.$this->PWD.$dest.$callBack.$Caller.$rsvTime.$msg;
		return "";
	}

	function AddURL($dest, $callBack, $URL, $msg, $rsvTime="") {
		// 내용 검사 1
		$Error = CheckCommonType($dest, $rsvTime);
		if ($Error) return $Error;
		// 내용 검사 2
		//$URL=str_replace("http://","",$URL);
		if (strlen($URL)>50) return "URL이 50자가 넘었습니다";
		switch (substr($dest,0,3)) {
			case '010': //20바이트
                $msg=cut_char($msg,20);
				break;
			case '011': //80바이트
                $msg=cut_char($msg,80);
				break;
			case '016': // 80바이트
				$msg=cut_char($msg,80);
				break;
			case '017': // URL 포함 80바이트
				$msg=cut_char($msg,80-strlen($URL));
				break;
			case '018': // 20바이트
				$msg=cut_char($msg,20);
				break;
			case '019': // 20바이트
				$msg=cut_char($msg,20);
				break;
			default:
				return "아직 URL CallBack이 지원되지 않는 번호입니다";
				break;
		}
		// 보낼 내용을 배열에 집어넣기
		$dest = spacing($dest,11);
		$URL = spacing($URL,50);
		$callBack = spacing($callBack,11);
		$rsvTime = spacing($rsvTime,12);
		$msg = spacing($msg,80);
		$this->Data[] = '05173 '.$this->ID.$this->PWD.$dest.$callBack.$URL.$rsvTime.$msg;
		return "";
	}

	function Send () {
		$fp=@fsockopen(trim($this->SMS_Server),trim($this->SMS_Port));
		if (!$fp) return false;
		set_time_limit(300);

		## php4.3.10일경우
        ## zend 최신버전으로 업해주세요..
        ## 또는 122번째 줄을 $this->Data as $tmp => $puts 로 변경해 주세요.

		foreach($this->Data as $puts) {
			$dest = substr($puts,26,11);
			fputs($fp,$puts);
			while(!$gets) { $gets=fgets($fp,30); }
			if (substr($gets,0,19)=="0223  00".$dest) $this->Result[]=$dest.":".substr($gets,19,10);
			else $this->Result[$dest]=$dest.":Error";
			$gets="";
		}
		fclose($fp);
		$this->Data="";
		return true;
	}
}
?>