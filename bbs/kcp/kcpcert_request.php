<?
include_once('./kcpcert_config.php');

// UTF-8 환경에서 해시 데이터 오류를 막기 위한 코드
setlocale(LC_CTYPE, 'ko_KR.euc-kr');

$req_tx        = "";

$site_cd       = "";
$ordr_idxx     = "";

$year          = "";
$month         = "";
$day           = "";
$user_name     = "";
$sex_code      = "";
$local_code    = "";

$up_hash       = "";
/*------------------------------------------------------------------------*/
/*  :: 전체 파라미터 남기기                                               */
/*------------------------------------------------------------------------*/

$ct_cert = new C_CT_CLI;
$ct_cert->mf_clear();

// utf-8로 넘어돈 post 값을 euc-kr 로 변경
$_POST = array_map("iconv_euckr", $_POST);

// request 로 넘어온 값 처리
$key = array_keys($_POST);
$sbParam ="";

for($i=0; $i<count($key); $i++)
{
    $nmParam = $key[$i];
    $valParam = $_POST[$nmParam];

    if ( $nmParam == "site_cd" )
    {
        $site_cd = f_get_parm_str ( $valParam );
    }

    if ( $nmParam == "req_tx" )
    {
        $req_tx = f_get_parm_str ( $valParam );
    }

    if ( $nmParam == "ordr_idxx" )
    {
        $ordr_idxx = f_get_parm_str ( $valParam );
    }

    if ( $nmParam == "user_name" )
    {
        $user_name = f_get_parm_str ( $valParam );
    }

    if ( $nmParam == "year" )
    {
        $year = f_get_parm_int ( $valParam );
    }

    if ( $nmParam == "month" )
    {
        $month = f_get_parm_int ( $valParam );
    }

    if ( $nmParam == "day" )
    {
        $day = f_get_parm_int ( $valParam );
    }

    if ( $nmParam == "sex_code" )
    {
        $sex_code = f_get_parm_str ( $valParam );
    }

    if ( $nmParam == "local_code" )
    {
        $local_code = f_get_parm_str ( $valParam );
    }

    // 인증창으로 넘기는 form 데이터 생성 필드
    $sbParam .= "<input type='hidden' name='" . $nmParam . "' value='" . f_get_parm_str( $valParam ) . "'/>";
}

if ( $req_tx == "cert" )
{
    // !!up_hash 데이터 생성시 주의 사항
    // year , month , day 가 비어 있는 경우 "00" , "00" , "00" 으로 설정이 됩니다
    // 그외의 값은 없을 경우 ""(null) 로 세팅하시면 됩니다.
    // up_hash 데이터 생성시 site_cd 와 ordr_idxx 는 필수 값입니다.
    $hash_data = $site_cd                  .
                 $ordr_idxx                .
                 $user_name                .
                 f_get_parm_int ( $year  ) .
                 f_get_parm_int ( $month ) .
                 f_get_parm_int ( $day   ) .
                 $sex_code                 .
                 $local_code;

    $up_hash = $ct_cert->make_hash_data( $home_dir, $hash_data );

    // 인증창으로 넘기는 form 데이터 생성 필드 ( up_hash )
    $sbParam .= "<input type='hidden' name='up_hash' value='" . $up_hash . "'/>";
}

$ct_cert->mf_clear();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=euc-kr">
        <title>*** KCP Online Payment System [PHP Version] ***</title>
        <script type="text/javascript">
            window.onload=function()
            {
                var frm = document.form_auth;

                // 인증 요청 시 호출 함수
                if ( frm.req_tx.value == "cert" )
                {
                    opener.document.form_auth.veri_up_hash.value = frm.up_hash.value; // up_hash 데이터 검증을 위한 필드

                    frm.action="<?=$cert_url?>";
                    frm.submit();
                }
            }
        </script>
    </head>
    <body oncontextmenu="return false;" ondragstart="return false;" onselectstart="return false;">
        <form name="form_auth" method="post">
            <?= $sbParam ?>
        </form>
    </body>
</html>