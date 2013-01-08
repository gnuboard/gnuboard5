<?
if (!defined('_GNUBOARD_')) exit;

function empty_mb_id($reg_mb_id)
{
    if (trim($reg_mb_id)=='')
        return "회원아이디를 입력해 주십시오.";
    else 
        return "";
}

function valid_mb_id($reg_mb_id)
{
    if (preg_match("/[^0-9a-z_]+/i", $reg_mb_id))
        return "회원아이디는 영문자, 숫자, _ 만 입력하세요.";
    else 
        return "";
}

function count_mb_id($reg_mb_id)
{
    if (strlen($reg_mb_id) < 3)
        return "회원아이디는 최소 3글자 이상 입력하세요.";
    else 
        return "";
}

function exist_mb_id($reg_mb_id)
{
    global $g4;
    $sql = " select count(*) as cnt from `{$g4['member_table']}` where mb_id = '$reg_mb_id' ";
    $row = sql_fetch($sql);
    if ($row['cnt'])
        return "이미 사용중인 회원아이디 입니다.";
    else 
        return "";
}

function reserve_mb_id($reg_mb_id)
{
    global $config;
    if (preg_match("/[\,]?{$reg_mb_id}/i", $config['cf_prohibit_id']))
        return "이미 예약된 단어로 사용할 수 없는 회원아이디 입니다.";
    else 
        return "";
}

function empty_mb_nick($reg_mb_nick) 
{
    if (!trim($reg_mb_nick))
        return "별명을 입력해 주십시오.";
    else 
        return "";
}

function valid_mb_nick($reg_mb_nick) 
{
    if (!check_string($reg_mb_nick, _G4_HANGUL_ + _G4_ALPHABETIC_ + _G4_NUMERIC_))
        return "별명은 공백없이 한글, 영문, 숫자만 입력 가능합니다.";
    else 
        return "";
}

function count_mb_nick($reg_mb_nick) 
{
    if (strlen($reg_mb_nick) < 4)
        return "별명은 한글 2글자, 영문 4글자 이상 입력 가능합니다.";
    else 
        return "";
}

function exist_mb_nick($reg_mb_nick, $reg_mb_id)
{
    global $g4;
    $row = sql_fetch(" select count(*) as cnt from {$g4['member_table']} where mb_nick = '$reg_mb_nick' and mb_id <> '$reg_mb_id' ");
    if ($row['cnt'])
        return "이미 존재하는 별명입니다.";
    else
        return "";
}

function reserve_mb_nick($reg_mb_nick)
{
    global $config;
    if (preg_match("/[\,]?{$reg_mb_nick}/i", $config['cf_prohibit_id']))
        return "이미 예약된 단어로 사용할 수 없는 별명 입니다.";
    else 
        return "";
}

function empty_mb_email($reg_mb_email) 
{
    if (!trim($reg_mb_email))
        return "E-mail 주소를 입력해 주십시오.";
    else 
        return "";
}

function valid_mb_email($reg_mb_email)
{
    if (!preg_match("/([0-9a-zA-Z_-]+)@([0-9a-zA-Z_-]+)\.([0-9a-zA-Z_-]+)/", $reg_mb_email))
        return "E-mail 주소가 형식에 맞지 않습니다.";
    else
        return "";
}

function exist_mb_email($reg_mb_email, $reg_mb_id)
{
    global $g4;
    $row = sql_fetch(" select count(*) as cnt from `{$g4['member_table']}` where mb_email = '$reg_mb_email' and mb_id <> '$reg_mb_id' ");
    if ($row['cnt'])
        return "이미 사용중인 E-mail 주소입니다.";
    else
        return "";
}

function empty_mb_name($reg_mb_name) 
{
    if (!trim($reg_mb_name))
        return "이름을 입력해 주십시오.";
    else 
        return "";
}

function valid_mb_name($mb_name)
{
    if (!check_string($mb_name, _G4_HANGUL_))
        return "이름은 공백없이 한글만 입력 가능합니다.";
    else 
        return "";
}

return;
?>


// 회원가입시 회원아이디 오류 검사
function check_mb_id($reg_mb_id, $length=3) 
{
    global $config, $g4;

    if (!isset($reg_mb_id) || !$reg_mb_id) 
        return "회원아이디를 입력해 주십시오.";

    if (preg_match("/[^0-9a-z_]+/i", $reg_mb_id)) {
        return "회원아이디는 영문자, 숫자, _ 만 입력하세요."; // 유효하지 않은 회원아이디
    } else if (strlen($reg_mb_id) < $length) {
        return "회원아이디는 최소 {$length}글자 이상 입력하세요.";
    } else {
        $row = sql_fetch(" select count(*) as cnt from {$g4['member_table']} where mb_id = '$reg_mb_id' ");
        if ($row['cnt']) {
            return "이미 사용중인 회원아이디 입니다."; // 이미 존재하는 회원아이디
        } else {
            if (preg_match("/[\,]?{$reg_mb_id}/i", $config['cf_prohibit_id'])) {
                return "이미 예약된 단어로 사용할 수 없는 회원아이디 입니다."; // 예약어로 금지된 회원아이디
            }
        }
    }
    return ""; // 사용해도 좋은 회원아이디
}

function check_mb_nick($reg_mb_nick, $reg_mb_id)
{
    global $g4;

    if (!isset($reg_mb_nick) || !$reg_mb_nick) 
        return "별명을 입력해 주십시오.";

    // 별명은 한글, 영문, 숫자만 가능
    if (!check_string($reg_mb_nick, _G4_HANGUL_ + _G4_ALPHABETIC_ + _G4_NUMERIC_)) {
        return "별명은 공백없이 한글, 영문, 숫자만 입력 가능합니다.";
    } else if (strlen($reg_mb_nick) < 4) {
        return "별명은 한글 2글자, 영문 4글자 이상 입력 가능합니다.";
    } else {
        $row = sql_fetch(" select count(*) as cnt from {$g4['member_table']} where mb_nick = '$reg_mb_nick' and mb_id <> '$reg_mb_id' ");
        if ($row['cnt']) {
            return "이미 존재하는 별명입니다.";
        }
    }
    return ""; // 사용해도 좋은 별명
}


function check_mb_email($reg_mb_email, $reg_mb_id)
{
    global $g4;

    if (!isset($reg_mb_email) || !$reg_mb_email) 
        return "E-mail 주소를 입력해 주십시오.";

    if (!preg_match("/([0-9a-zA-Z_-]+)@([0-9a-zA-Z_-]+)\.([0-9a-zA-Z_-]+)/", $reg_mb_email)) {
        return "E-mail 주소가 형식에 맞지 않습니다.";
    } else {
        $row = sql_fetch(" select count(*) as cnt from {$g4['member_table']} where mb_email = '$reg_mb_email' and mb_id <> '$reg_mb_id' ");
        if ($row['cnt']) {
            return "이미 사용중인 E-mail 주소입니다.";
        }
    }
    return "";
}
