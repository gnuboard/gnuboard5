<?php
include_once('./_common.php');
include_once(G5_KCPCERT_PATH.'/kcpcert_config.php');

$site_cd       = "";
$ordr_idxx     = "";

$cert_no       = "";
$cert_enc_use  = "";
$enc_info      = "";
$enc_data      = "";
$req_tx        = "";

$enc_cert_data = "";
$enc_cert_data2 = "";
$cert_info     = "";

$tran_cd       = "";
$res_cd        = "";
$res_msg       = "";

$dn_hash       = "";

/*------------------------------------------------------------------------*/
/*  :: 전체 파라미터 남기기                                               */
/*------------------------------------------------------------------------*/

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

    if ( $nmParam == "ordr_idxx" )
    {
        $ordr_idxx = f_get_parm_str ( $valParam );
    }

    if ( $nmParam == "res_cd" )
    {
        $res_cd = f_get_parm_str ( $valParam );
    }

    if ( $nmParam == "cert_enc_use" )
    {
        $cert_enc_use = f_get_parm_str ( $valParam );
    }

    if ( $nmParam == "req_tx" )
    {
        $req_tx = f_get_parm_str ( $valParam );
    }

    if ( $nmParam == "cert_no" )
    {
        $cert_no = f_get_parm_str ( $valParam );
    }

    if ( $nmParam == "enc_cert_data" )
    {
        $enc_cert_data = f_get_parm_str ( $valParam );
    }
    
    if ( $nmParam == "enc_cert_data2" )
    {
        $enc_cert_data2 = f_get_parm_str ( $valParam );
    }

    if ( $nmParam == "dn_hash" )
    {
        $dn_hash = f_get_parm_str ( $valParam );
   }

    // 부모창으로 넘기는 form 데이터 생성 필드
    $sbParam .= "<input type='hidden' name='" . $nmParam . "' value='" . f_get_parm_str( $valParam ) . "'/>";
}

$ct_cert = new C_CT_CLI;
$ct_cert->mf_clear();


$g5['title'] = '휴대폰인증 결과';
include_once(G5_PATH.'/head.sub.php');

// 결과 처리

if( $cert_enc_use == "Y" )
{
    // 인증내역기록
    @insert_cert_history($member['mb_id'], 'kcp', 'hp');

    if( $res_cd == "0000" )
    {
        // dn_hash 검증
        // KCP 가 리턴해 드리는 dn_hash 와 사이트 코드, 주문번호 , 인증번호를 검증하여
        // 해당 데이터의 위변조를 방지합니다
         $veri_str = $site_cd.$ordr_idxx.$cert_no; // 사이트 코드 + 주문번호 + 인증거래번호

        $enc_cert_real_data = $enc_cert_data2;
        $bin_path = 'bin';
        
        if ((int)$config['cf_cert_use'] === 2 && !$config['cf_cert_kcp_enckey']) {
            $bin_path = 'bin_old';
            $enc_cert_real_data = $enc_cert_data;
        }
        
        if ( $ct_cert->check_valid_hash ( $home_dir , $kcp_enc_key, $dn_hash , $veri_str ) != "1" )
        {
            if(strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
                // 검증 실패시 처리 영역
                if(PHP_INT_MAX == 2147483647) // 32-bit
                    $bin_exe = '/'.$bin_path.'/ct_cli';
                else
                    $bin_exe = '/'.$bin_path.'/ct_cli_x64';
            } else {
                $bin_exe = '/'.$bin_path.'/ct_cli_exe.exe';
            }

            echo "dn_hash 변조 위험있음 (".G5_KCPCERT_PATH.$bin_exe." 파일에 실행권한이 있는지 확인하세요.)";
            exit;
            // 오류 처리 ( dn_hash 변조 위험있음)
        }

        // 가맹점 DB 처리 페이지 영역
            
        // 인증데이터 복호화 함수
        // 해당 함수는 암호화된 enc_cert_data 를
        // site_cd 와 cert_no 를 가지고 복화화 하는 함수 입니다.
        // 정상적으로 복호화 된경우에만 인증데이터를 가져올수 있습니다.
        $opt = "1" ; // 복호화 인코딩 옵션 ( UTF - 8 사용시 "1" )
        $ct_cert->decrypt_enc_cert( $home_dir , $kcp_enc_key, $site_cd , $cert_no , $enc_cert_real_data , $opt );

        $comm_id        = $ct_cert->mf_get_key_value("comm_id"    );                // 이동통신사 코드
        $phone_no       = $ct_cert->mf_get_key_value("phone_no"   );                // 전화번호
        $user_name      = $ct_cert->mf_get_key_value("user_name"  );                // 이름
        $birth_day      = $ct_cert->mf_get_key_value("birth_day"  );                // 생년월일
        $sex_code       = $ct_cert->mf_get_key_value("sex_code"   );                // 성별코드
        $local_code     = $ct_cert->mf_get_key_value("local_code" );                // 내/외국인 정보
        $ci             = $ct_cert->mf_get_key_value("ci"         );                // CI
        $di             = $ct_cert->mf_get_key_value("di"         );                // DI 중복가입 확인값
        $ci_url         = urldecode( $ct_cert->mf_get_key_value("ci"         ) );   // CI
        $di_url         = urldecode( $ct_cert->mf_get_key_value("di"         ) );   // DI 중복가입 확인값
        $dec_res_cd     = $ct_cert->mf_get_key_value("res_cd"     );                // 암호화된 결과코드
        $dec_mes_msg    = $ct_cert->mf_get_key_value("res_msg"    );                // 암호화된 결과메시지

        if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' && function_exists('mb_detect_encoding') ){
            if( mb_detect_encoding($user_name, 'EUC-KR') === 'EUC-KR' ){
                $user_name = iconv_utf8($user_name);
                $dec_mes_msg = iconv_utf8($dec_mes_msg);
            }
        }

        // 정상인증인지 체크
        if(!$phone_no)
            alert_close("정상적인 인증이 아닙니다. 올바른 방법으로 이용해 주세요.");

        $phone_no = hyphen_hp_number($phone_no);
        $mb_dupinfo = md5($ci.$ci);

        // 명의 변경 체크
        if (!empty($member['mb_certify']) && !empty($member['mb_dupinfo']) && strlen($member['mb_dupinfo']) != 64) { // 이미 인증된 계정중에 dupinfo가 di(64 length)가 아닐때
            if($member['mb_dupinfo'] != $mb_dupinfo) alert_close("해당 계정은 이미 다른명의로 본인인증 되어있는 계정입니다.");
        }

        $sql = " select mb_id from {$g5['member_table']} where mb_id <> '{$member['mb_id']}' and mb_dupinfo = '{$mb_dupinfo}' ";
        $row = sql_fetch($sql);
        if (!empty($row['mb_id'])) {
            alert_close("입력하신 본인확인 정보로 가입된 내역이 존재합니다.\\n회원아이디 : ".$row['mb_id']);
        }

        // hash 데이터
        $cert_type = 'hp';
        $md5_cert_no = md5($cert_no);
        $hash_data   = md5($user_name.$cert_type.$birth_day.$phone_no.$md5_cert_no);

        // 성인인증결과
        $adult_day = date("Ymd", strtotime("-19 years", G5_SERVER_TIME));
        $adult = ((int)$birth_day <= (int)$adult_day) ? 1 : 0;

        set_session("ss_cert_type",    $cert_type);
        set_session("ss_cert_no",      $md5_cert_no);
        set_session("ss_cert_hash",    $hash_data);
        set_session("ss_cert_adult",   $adult);
        set_session("ss_cert_birth",   $birth_day);
        set_session("ss_cert_sex",     ($sex_code=="01"?"M":"F"));
        set_session('ss_cert_dupinfo', $mb_dupinfo);
    }
    else if( $res_cd != "0000" )
    {
        // 인증실패
        alert_close('코드 : '.$_POST['res_cd'].'  '.urldecode($_POST['res_msg']));
        exit;
    }
}
else if( $cert_enc_use != "Y" )
{
    // 암호화 인증 안함
    if( G5_IS_MOBILE ){
        echo '<script>'.PHP_EOL;
        echo 'window.parent.$("#cert_info").css("display", "");'.PHP_EOL;
        echo 'window.parent.$("#kcp_cert" ).css("display", "none");'.PHP_EOL;
        echo '</script>'.PHP_EOL;
    } else {
        alert_close("휴대폰 본인확인을 취소 하셨습니다.");
    }
    exit;
}

$ct_cert->mf_clear();
?>

<form name="form_auth" method="post">
    <?php echo $sbParam; ?>
</form>

<script>
$(function() {
    var $opener;
    var is_mobile = false;
    // iframe에서 세션공유 문제가 있어서 더 이상 iframe 을 사용하지 않습니다.
    var use_iframe = false;

    if(use_iframe && ( navigator.userAgent.indexOf("Android") > - 1 || navigator.userAgent.indexOf("iPhone") > - 1 ) ) {
        $opener = window.parent;
        is_mobile = true;
    } else {
        $opener = window.opener;
    }

    // up_hash 검증
    if( document.form_auth.up_hash.value != $opener.$("input[name=veri_up_hash]").val() ) {
        alert("up_hash 변조 위험있음");
    }

    // 인증정보
    $opener.$("input[name=cert_type]").val("<?php echo $cert_type; ?>");
    $opener.$("input[name=mb_name]").val("<?php echo $user_name; ?>").attr("readonly", true);
    $opener.$("input[name=mb_hp]").val("<?php echo $phone_no; ?>").attr("readonly", true);
    $opener.$("input[name=cert_no]").val("<?php echo $md5_cert_no; ?>");

    if(is_mobile) {
        $opener.$("#cert_info").css("display", "");
        $opener.$("#kcp_cert" ).css("display", "none");
    }

    alert("본인의 휴대폰번호로 확인 되었습니다.");

    if($opener.$("form[name=fcertrefreshform]") != undefined){
        $opener.$("form[name=fcertrefreshform]").submit();
    }

    window.close();
});
</script>

<?php
include_once(G5_PATH.'/tail.sub.php');