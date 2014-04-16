<?php
if (!defined('_GNUBOARD_')) exit;

/*************************************************************************
**
**  sms5에 사용할 함수 모음
**
*************************************************************************/

// 스킨디렉토리를 SELECT 형식으로 얻음
function get_sms5_skin_select($skin_gubun, $id, $name, $selected='', $event='')
{
    $skins = get_skin_dir($skin_gubun, G5_SMS5_PATH);
    $str = "<select id=\"$id\" name=\"$name\" $event>\n";
    for ($i=0; $i<count($skins); $i++) {
        if ($i == 0) $str .= "<option value=\"\">선택</option>";
        $str .= option_selected($skins[$i], $selected);
    }
    $str .= "</select>";
    return $str;
}

// 한페이지에 보여줄 행, 현재페이지, 총페이지수, URL
function sms5_sub_paging($write_pages, $cur_page, $total_page, $url, $add="", $starget="")
{
    if( $starget ){
        $url = preg_replace('#&amp;'.$starget.'=[0-9]*#', '', $url) . '&amp;'.$starget.'=';
    }
    
    $str = '';
    if ($cur_page > 1) {
        $str .= '<a href="'.$url.'1'.$add.'" class="pg_page pg_start">처음</a>'.PHP_EOL;
    }

    $start_page = ( ( (int)( ($cur_page - 1 ) / $write_pages ) ) * $write_pages ) + 1;
    $end_page = $start_page + $write_pages - 1;

    if ($end_page >= $total_page) $end_page = $total_page;

    if ($start_page > 1) $str .= '<a href="'.$url.($start_page-1).$add.'" class="pg_page pg_prev">이전</a>'.PHP_EOL;

    if ($total_page > 1) {
        for ($k=$start_page;$k<=$end_page;$k++) {
            if ($cur_page != $k)
                $str .= '<a href="'.$url.$k.$add.'" class="pg_page">'.$k.'<span class="sound_only">페이지</span></a>'.PHP_EOL;
            else
                $str .= '<span class="sound_only">열린</span><strong class="pg_current">'.$k.'</strong><span class="sound_only">페이지</span>'.PHP_EOL;
        }
    }

    if ($total_page > $end_page) $str .= '<a href="'.$url.($end_page+1).$add.'" class="pg_page pg_next">다음</a>'.PHP_EOL;

    if ($cur_page < $total_page) {
        $str .= '<a href="'.$url.$total_page.$add.'" class="pg_page pg_end">맨끝</a>'.PHP_EOL;
    }

    if ($str)
        return "<nav class=\"pg_wrap\"><span class=\"pg\">{$str}</span></nav>";
    else
        return "";
}

// 권한 검사
function ajax_auth_check($auth, $attr)
{
    global $is_admin;

    if ($is_admin == 'super') return;

    if (!trim($auth))
        die("{\"error\":\"이 메뉴에는 접근 권한이 없습니다.\\n\\n접근 권한은 최고관리자만 부여할 수 있습니다.\"}");

    $attr = strtolower($attr);

    if (!strstr($auth, $attr)) {
        if ($attr == 'r')
            die("{\"error\":\"읽을 권한이 없습니다.\"}");
        else if ($attr == 'w')
            die("{\"error\":\"입력, 추가, 생성, 수정 권한이 없습니다.\"}");
        else if ($attr == 'd')
            die("{\"error\":\"삭제 권한이 없습니다.\"}");
        else
            die("{\"error\":\"속성이 잘못 되었습니다.\"}");
    }
}

if ( ! function_exists('array_overlap')) {
    function array_overlap($arr, $val) {
        for ($i=0, $m=count($arr); $i<$m; $i++) {
            if ($arr[$i] == $val)
                return true;
        }
        return false;
    }
}
if ( ! function_exists('get_hp')) {
    function get_hp($hp, $hyphen=1)
    {
        global $g5;

        if (!is_hp($hp)) return '';

        if ($hyphen) $preg = "$1-$2-$3"; else $preg = "$1$2$3";

        $hp = str_replace('-', '', trim($hp));
        $hp = preg_replace("/^(01[016789])([0-9]{3,4})([0-9]{4})$/", $preg, $hp);

        if ($g5['sms5_demo'])
            $hp = '0100000000';

        return $hp;
    }
}
if ( ! function_exists('is_hp')) {
    function is_hp($hp)
    {
        $hp = str_replace('-', '', trim($hp));
        if (preg_match("/^(01[016789])([0-9]{3,4})([0-9]{4})$/", $hp))
            return true;
        else
            return false;
    }
}
if ( ! function_exists('alert_just')) {
    // 경고메세지를 경고창으로
    function alert_just($msg='', $url='')
    {
        global $g5;

        if (!$msg) $msg = '올바른 방법으로 이용해 주십시오.';

        //header("Content-Type: text/html; charset=$g5[charset]");
        echo "<meta charset=\"utf-8\">";
        echo "<script language='javascript'>alert('$msg');";
        echo "</script>";
        exit;
    }
}

if ( ! function_exists('utf2euc')) {
    function utf2euc($str) {
        return iconv("UTF-8","cp949//IGNORE", $str);
    }
}
if ( ! function_exists('is_ie')) {
    function is_ie() {
        return isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'Trident') !== false || strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false);
    }
}

/**
 * SMS 발송을 관장하는 메인 클래스이다.
 *
 * 접속, 발송, URL발송, 결과등의 실질적으로 쓰이는 모든 부분이 포함되어 있다.
 */

class SMS5 extends SMS {
	var $Log = array();

	function SMS_con($sms_server,$sms_id,$sms_pw,$port) {
		$this->ID=$sms_id;		// 계약 후 지정
		$this->PWD=$sms_pw;		// 계약 후 지정
		$this->SMS_Server=$sms_server;
		$this->SMS_Port=$port;
		$this->ID = spacing($this->ID,10);
		$this->PWD = spacing($this->PWD,10);
	}

     /**
     * 발송번호의 값이 정확한 값인지 확인합니다.
     *
     * @param	strDest	발송번호 배열입니다.
     *			nCount	배열의 크기입니다.
     * @return			처리결과입니다.
     */
    function CheckCommonTypeDest($strDest, $nCount) {
        for ($i=0; $i<$nCount; $i++) {
            $hp_number = preg_replace("/[^0-9]/","",$strDest[$i]['bk_hp']);
            if (strlen($hp_number)<10 || strlen($hp_number)>11) return "휴대폰 번호가 틀렸습니다";

            $CID=substr($hp_number,0,3);
            if ( preg_match("/[^0-9]/",$CID) || ($CID!='010' && $CID!='011' && $CID!='016' && $CID!='017' && $CID!='018' && $CID!='019') ) return "휴대폰 앞자리 번호가 잘못되었습니다";
        }
    }

    /**
     * 회신번호의 값이 정확한 값인지 확인합니다.
     *
     * @param	strDest	회신번호입니다.
     * @return			처리결과입니다.
     */
    function CheckCommonTypeCallBack($strCallBack) {
        if (preg_match("/[^0-9]/", $strCallBack)) return "회신 전화번호가 잘못되었습니다";
    }


    /**
     * 예약날짜의 값이 정확한 값인지 확인합니다.
     *
     * @param	text	원하는 문자열입니다.
     *			size	원하는 길이입니다.
     * @return			처리결과입니다.
     */
    function CheckCommonTypeDate($strDate) {
        $strDate=preg_replace("/[^0-9]/","",$strDate);
        if ($strDate) {
            if (!checkdate(substr($strDate,4,2),substr($strDate,6,2),substr($rsvTime,0,4))) return "예약날짜가 잘못되었습니다";
            if (substr($strDate,8,2)>23 || substr($strDate,10,2)>59) return "예약시간이 잘못되었습니다";
        }
    }


    /**
     * URL콜백용으로 메세지 크기를 수정합니다.
     *
     * @param	url		URL 내용입니다.
     *			msg		결과메시지입니다.
     *			desk	문자내용입니다.
     */
    function CheckCallCenter($url, $dest, $data) {
        switch (substr($dest,0,3)) {
            case '010': //20바이트
                return cut_char($data,20);
                break;
            case '011': //80바이트
                return cut_char($data,80);
                break;
            case '016': // 80바이트
                return cut_char($data,80);
                break;
            case '017': // URL 포함 80바이트
                return cut_char($data,80 - strlen($url));
                break;
            case '018': // 20바이트
                return cut_char($data,20);
                break;
            case '019': // 20바이트
                return cut_char($data,20);
                break;
            default:
                return cut_char($data,80);
                break;
        }
    }
	function Add($strDest, $strCallBack, $strCaller, $strURL, $strMessage, $strDate="", $nCount) {
        global $g5;

        $Error = $this->CheckCommonTypeDest($strDest, $nCount);
		$Error = $this->CheckCommonTypeCallBack($strCallBack);
		$Error = $this->CheckCommonTypeDate($strDate);

		$strCallBack    = spacing($strCallBack,11);
		$strCaller      = spacing($strCaller,10);
		$strDate		= spacing($strDate,12);


		for ($i=0; $i<$nCount; $i++) {
			$hp_number	= spacing($strDest[$i]['bk_hp'],11);
            $strData = $strMessage;
            if( !empty($strDest[$i]['bk_name']) ){
                $strData    = str_replace("{이름}", $strDest[$i]['bk_name'], $strData);
            }
            // 아이코드에서는 문자에 utf-8 인코딩 형식을 아직 지원하지 않는다.
            $strData = iconv('utf-8', "euc-kr", stripslashes($strData));

			if (!$strURL) {
				$strData	= spacing(cut_char($strData,80),80);

				$this->Data[$i]	= '01144 '.$this->ID.$this->PWD.$hp_number.$strCallBack.$strCaller.$strDate.$strData;
			} else {
				$strURL		= spacing($strURL,50);
				$strData	= spacing($this->CheckCallCenter($strURL, $hp_number, $strData),80);

				$this->Data[$i]	= '05173 '.$this->ID.$this->PWD.$hp_number.$strCallBack.$strURL.$strDate.$strData;
			}
		}
		return true; // 수정대기
	}

	function Send() {
        global $g5;

        $count = 1;

        if ($g5['sms5_demo_send']) {
            foreach($this->Data as $puts) {
                if (rand(0,10)) {
                    $phone = substr($puts,26,11);
                    $code = '47022497 ';
                } else {
                    $phone = substr($puts,26,11);
                    $code = 'Error(02)';
                }
                $this->Result[] = "$phone:$code";
                $this->Log[] = $puts;
            }
            $this->Data = "";
            return true;
            exit;
        }

		$fsocket=fsockopen($this->SMS_Server,$this->SMS_Port);
		if (!$fsocket) return false;
		set_time_limit(300);

		## php4.3.10일경우
        ## zend 최신버전으로 업해주세요..
        ## 또는 69번째 줄을 $this->Data as $tmp => $puts 로 변경해 주세요.

		foreach($this->Data as $puts) {
			$dest = substr($puts,26,11);
			fputs($fsocket, $puts);
			while(!$gets) {
				$gets = fgets($fsocket,30);
			}
			if (substr($gets,0,19) == "0223  00".$dest) {
				$this->Result[] = $dest.":".substr($gets,19,10);
				$this->Log[] = $puts;
            } else {
				$this->Result[$dest] = $dest.":Error(".substr($gets,6,2).")";
				$this->Log[] = $puts;
            }
			$gets = "";

            // 1천건씩 전송 후 5초 쉼
            if ($count++%1000 == 0) sleep(5);
		}
		fclose($fsocket);
		$this->Data = "";
		return true;
	}
}
?>