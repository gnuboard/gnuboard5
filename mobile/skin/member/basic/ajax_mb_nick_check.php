<?
include_once("_common.php");

if (!function_exists('convert_charset')) {
    /*
    -----------------------------------------------------------
        Charset 을 변환하는 함수
    -----------------------------------------------------------
    iconv 함수가 있으면 iconv 로 변환하고
    없으면 mb_convert_encoding 함수를 사용한다.
    둘다 없으면 사용할 수 없다.
    */
    function convert_charset($from_charset, $to_charset, $str) {

        if( function_exists('iconv') )
            return iconv($from_charset, $to_charset, $str);
        elseif( function_exists('mb_convert_encoding') )
            return mb_convert_encoding($str, $to_charset, $from_charset);
        else
            die("Not found 'iconv' or 'mbstring' library in server.");
    }
}

if (strtolower($g4[charset]) == 'euc-kr') 
    $reg_mb_nick = convert_charset('UTF-8','CP949',$reg_mb_nick);

// 별명은 한글, 영문, 숫자만 가능
if (!check_string($reg_mb_nick, _G4_HANGUL_ + _G4_ALPHABETIC_ + _G4_NUMERIC_)) {
    echo "110"; // 별명은 공백없이 한글, 영문, 숫자만 입력 가능합니다.
} else if (strlen($reg_mb_nick) < 4) {
    echo "120"; // 4글자 이상 입력
} else {
    $row = sql_fetch(" select count(*) as cnt from $g4[member_table] where mb_nick = '$reg_mb_nick' ");
    if ($row[cnt]) {
        echo "130"; // 이미 존재하는 별명
    } else {
        echo "000"; // 정상
    }
}
?>