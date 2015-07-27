<?php
include_once('./_common.php');
include_once(G5_LIB_PATH.'/json.lib.php');
include_once(G5_SHOP_PATH.'/settle_inicis.inc.php');

$price = trim($_POST['price']);

/**************************
 * 3. 암호화 대상/값 설정 *
 **************************/
$inipay->SetField("type",   "chkfake"); // 고정 (절대 수정 불가)
$inipay->SetField("enctype","asym");  //asym:비대칭, symm:대칭(현재 asym으로 고정)
/**************************************************************************************************
 * admin 은 키패스워드 변수명입니다. 수정하시면 안됩니다. 1111의 부분만 수정해서 사용하시기 바랍니다.
 * 키패스워드는 상점관리자 페이지(https://iniweb.inicis.com)의 비밀번호가 아닙니다. 주의해 주시기 바랍니다.
 * 키패스워드는 숫자 4자리로만 구성됩니다. 이 값은 키파일 발급시 결정됩니다.
 * 키패스워드 값을 확인하시려면 상점측에 발급된 키파일 안의 readme.txt 파일을 참조해 주십시오.
 **************************************************************************************************/
$inipay->SetField("admin",    $default['de_inicis_admin_key']); // 키패스워드(키발급시 생성, 상점관리자 패스워드와 상관없음)
$inipay->SetField("checkopt", "false");                      //base64함:false, base64안함:true(현재 false로 고정)

//필수항목 : mid, price, nointerest, quotabase
//추가가능 : INIregno, oid
//*주의* :    추가가능한 항목중 암호화 대상항목에 추가한 필드는 반드시 hidden 필드에선 제거하고
// SESSION이나 DB를 이용해 다음페이지(INIsecureresult.php)로 전달/셋팅되어야 합니다.
$inipay->SetField("mid",        $default['de_inicis_mid']); // 상점아이디
$inipay->SetField("price",      $price);                    // 가격
$inipay->SetField("nointerest", $inipay_nointerest);        // 무이자여부(no:일반, yes:무이자)
$inipay->SetField("quotabase",  iconv_euckr($inipay_quotabase));//할부기간

/********************************
 * 4. 암호화 대상/값을 암호화함 *
 ********************************/
$inipay->startAction();

/*********************
 * 5. 암호화 결과  *
 *********************/
if( $inipay->GetResult("ResultCode") != "00" )
{
    die('{"error":"'.$inipay->GetResult("ResultMsg").'"}');
}

/*********************
 * 6. 세션정보 저장  *
 *********************/
set_session('INI_MID',     $default['de_inicis_mid']);       //상점ID
set_session('INI_ADMIN',   $default['de_inicis_admin_key']); // 키패스워드(키발급시 생성, 상점관리자 패스워드와 상관없음)
set_session('INI_PRICE',   $price);                          //가격
set_session('INI_RN',      $inipay->GetResult("rn"));        //고정 (절대 수정 불가)
set_session('INI_ENCTYPE', $inipay->GetResult("enctype"));   //고정 (절대 수정 불가)

$ini_encfield = $inipay->GetResult("encfield");
$ini_certid   = $inipay->GetResult("certid");

$result = array(
    'error' => '',
    'ini_encfield' => $ini_encfield,
    'ini_certid' => $ini_certid
);

die(json_encode($result));

//die('{"error":"", "ini_encfield":"'.$ini_encfield.'", "ini_certid":"'.$ini_certid.'"}');
?>