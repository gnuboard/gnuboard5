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
	$dest=preg_replace("/[^0-9]/i","",$dest);
	if (strlen($dest)<10 || strlen($dest)>11) return "휴대폰 번호가 틀렸습니다";
	$CID=substr($dest,0,3);
	if ( preg_match("/[^0-9]/i",$CID) || ($CID!='010' && $CID!='011' && $CID!='016' && $CID!='017' && $CID!='018' && $CID!='019') ) return "휴대폰 앞자리 번호가 잘못되었습니다";
	$rsvTime=preg_replace("/[^0-9]/i","",$rsvTime);
	if ($rsvTime) {
		if (!checkdate(substr($rsvTime,4,2),substr($rsvTime,6,2),substr($rsvTime,0,4))) return "예약날짜가 잘못되었습니다";
		if (substr($rsvTime,8,2)>23 || substr($rsvTime,10,2)>59) return "예약시간이 잘못되었습니다";
	}
}

class SMS {
	public $ID;
    public $PWD;
    public $SMS_Server;
    public $port;
    public $SMS_Port;
    public $Data = array();
    public $Result = array();
    public $icode_key;
    public $socket_port;
    public $socket_host;

	function SMS_con($sms_server,$sms_id,$sms_pw,$port) {
        global $config;

        // 토큰키를 사용한다면
        if(isset($config['cf_icode_token_key']) && $config['cf_icode_token_key']){
            $this->icode_key = $config['cf_icode_token_key'];
            $this->socket_host = ICODE_JSON_SOCKET_HOST;
            $this->socket_port = ICODE_JSON_SOCKET_PORT;
        }

		$this->ID=$sms_id;		// 계약 후 지정
		$this->PWD=$sms_pw;		// 계약 후 지정
		$this->SMS_Server=$sms_server;
		$this->SMS_Port=$port;
		$this->ID = spacing($this->ID,10);
		$this->PWD = spacing($this->PWD,10);
	}

	function Init() {
		$this->Data = array();
		$this->Result = array();
	}

	function Add($dest, $callBack, $Caller, $msg, $rsvTime="") {
        global $g5, $config;

        // 토큰키를 사용한다면
        if( isset($config['cf_icode_token_key']) && $config['cf_icode_token_key'] === $this->icode_key ){

            // 내용 검사 1
            $Error = CheckCommonType($dest, $rsvTime);
            if ($Error) return $Error;
            if ( preg_match("/[^0-9]/i",$callBack) ) return "회신 전화번호가 잘못되었습니다";

            // 개행치환
            $msg = preg_replace("/\r\n/", "\n", $msg);
            $msg = preg_replace("/\r/", "\n", $msg);
            // 90byte 이내는 SMS, 90 ~ 2000 byte 는 LMS 그 이상은 절삭 되어 LMS로 발송
            // SMS 이기 때문에 90byte 이내로 합니다.
            $msg=cut_char($msg, 90);
            $msg = spacing($msg, 90);

            // 한글 깨진것이 있는지 체크합니다.
            if( preg_match('/^([\x00-\x7e]|.{2})*/', $msg, $z) ){
                $msg = $z[0];
            }

            // 문자 내용이 euc-kr 인지 체크합니다.
            $enc = mb_detect_encoding($msg, array('EUC-KR', 'UTF-8'));

            // 문자 내용이 euc-kr 이면 json_encode 에서 깨지기 때문에  utf-8 로 변환합니다.
            if($enc === 'EUC-KR'){
                $msg = iconv_utf8($msg);
            }

            // 보낼 내용을 배열에 집어넣기
            $dest = spacing($dest,11);
            $callBack = spacing($callBack,11);
            $Caller = spacing($Caller,10);
            $rsvTime = $rsvTime ? spacing($rsvTime,12) : '';

            $list = array(
                "key" => $this->icode_key, 
                "tel" => $dest,
                "cb" => $callBack,
                "msg" => $msg,
                "title" => "",      //SMS 의 경우 타이틀을 지정할수 없습니다.
                "date" => $rsvTime
            );

            $packet = json_encode($list);

            if( !$packet ){ // json_encode가 잘못되었으면 보내지 않습니다.
                return "json_encode error";
            }
            $this->Data[]    = '06'.str_pad(strlen($packet), 4, "0", STR_PAD_LEFT).$packet;

            return ''; 

        } else {
            // 기존 OLD SMS

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

    function Send() {
        global $config;

        // 토큰키를 사용한다면
        if( isset($config['cf_icode_token_key']) && $config['cf_icode_token_key'] === $this->icode_key ){
            $fsocket = @fsockopen(trim($this->socket_host),trim($this->socket_port), $errno, $errstr, 2);
            if (!$fsocket) return false;
            set_time_limit(300);

            foreach($this->Data as $puts) {
                fputs($fsocket, $puts);
                $gets = '';
                while(!$gets) { $gets = fgets($fsocket,32); }
                $json = json_decode(substr($puts,6), true);

                $dest = $json["tel"];
                if (substr($gets,0,20) == "0225  00".spacing($dest,12)) {
                    $this->Result[] = $dest.":".substr($gets,20,11);

                } else {
                    $this->Result[$dest] = $dest.":Error(".substr($gets,6,2).")";
                    if(substr($gets,6,2) >= "80") break;
                }
            }
            fclose($fsocket);

        } else {

            $fp=@fsockopen(trim($this->SMS_Server),trim($this->SMS_Port));
            if (!$fp) return false;
            set_time_limit(300);

            ## php4.3.10일경우
            ## zend 최신버전으로 업해주세요..
            ## 또는 122번째 줄을 $this->Data as $tmp => $puts 로 변경해 주세요.

            foreach($this->Data as $puts) {
                $dest = substr($puts,26,11);
                fputs($fp,$puts);
                $gets = '';
                while(!$gets) { $gets=fgets($fp,30); }
                if (substr($gets,0,19)=="0223  00".$dest) $this->Result[]=$dest.":".substr($gets,19,10);
                else $this->Result[$dest]=$dest.":Error";
            }
            fclose($fp);
        }
		$this->Data=array();
        return true;
	}
}