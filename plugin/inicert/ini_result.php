<?php
include_once('./_common.php');
require_once (dirname(__FILE__) .'/libs/KISA_SEED_CBC.php');
require_once (dirname(__FILE__) .'/libs/INILib.php');
require_once (dirname(__FILE__) .'/libs/inicert.lib.php');

// 시작 단계(ini_request.php)에서 저장한 거래 정보를 먼저 꺼내 지웁니다. 결과가 성공이든 실패든 한 번만 쓸 수 있습니다.
$inicert_saved = inicert_consume_request('register');
if (!$inicert_saved) {
    alert_close('인증 요청 정보가 없거나 만료되었습니다. 다시 시도해 주십시오.');
}

$SEEDIV = 'SASHOSTSIRIAS000';

if (isset($_POST['txId']) && isset($_POST["resultCode"]) && $_POST["resultCode"] === "0000") {

    $inicert_req = inicert_check_return($inicert_saved, $_POST);
    if (!is_array($inicert_req)) {
        alert_close($inicert_req);
    }
    $txId = $inicert_req['txId'];
    $SEEDKEY = $inicert_req['token'];

    // 고정된 이니시스 HTTPS 주소로 인증서를 검증해 조회하고 응답 형식을 확인합니다.
    $res_data = inicert_fetch_result($inicert_req, $inicert_saved);
    if (!is_array($res_data)) {
        alert_close($res_data);
    }

    if($res_data['resultCode'] === "0000") {
        $cert_type      = 'simple';                                 // 인증타입
        $cert_no        = $txId;                                // 이니시스 트랜잭션 ID(응답 txId는 같을 때만 통과)
        $phone_no       = $res_data['userPhone'];               // 전화번호
        $user_name      = $res_data['userName'];                // 이름
        $birth_day      = $res_data['userBirthday'];            // 생년월일
        $ci             = $res_data['userCi'];                  // CI
        
        if (defined('KGINICIS_USE_CERT_SEED') && KGINICIS_USE_CERT_SEED) {
            // 개인정보SEED 암호화 된것을 복호화 합니다.
            $user_name = decrypt_SEED($user_name, $SEEDKEY, $SEEDIV);
            $phone_no = decrypt_SEED($phone_no, $SEEDKEY, $SEEDIV);
            $birth_day = decrypt_SEED($birth_day, $SEEDKEY, $SEEDIV);
            $ci = decrypt_SEED($ci, $SEEDKEY, $SEEDIV);
        }

        if (!inicert_valid_identity($user_name, $phone_no, $birth_day, $ci))
        alert_close("정상적인 인증이 아닙니다. 올바른 방법으로 이용해 주세요.");

        @insert_cert_history($member['mb_id'], 'inicis', $cert_type); // 인증성공 시 내역 기록

        $mb_dupinfo = md5($ci . $ci);
        $phone_no = hyphen_hp_number($phone_no);

        // 명의 변경 체크
        if (!empty($member['mb_certify']) && !empty($member['mb_dupinfo']) && strlen($member['mb_dupinfo']) != 64) { // 이미 인증된 계정중에 dupinfo가 di(64 length)가 아닐때
            if($member['mb_dupinfo'] != $mb_dupinfo) alert_close("해당 계정은 이미 다른명의로 본인인증 되어있는 계정입니다.");
        }

        $sql = " select mb_id from {$g5['member_table']} where mb_id <> '{$member['mb_id']}' and mb_dupinfo = '{$mb_dupinfo}' ";
        $row = sql_fetch($sql);
        if (!empty($row['mb_id'])) {
            alert_close("입력하신 본인확인 정보로 이미 가입된 내역이 존재합니다.\\n회원아이디 : ".$row['mb_id']);
        }

        // hash 데이터
        
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
        //set_session("ss_cert_sex",     ($sex_code=="01"?"M":"F")); // 이니시스 간편인증은 성별정보 리턴 없음
        set_session('ss_cert_dupinfo', $mb_dupinfo);       

    } else {
        // 인증실패 curl의 인증실패 체크
        alert_close('정상적인 인증이 아닙니다. 올바른 방법으로 이용해 주세요.');
        exit;
    }
} else {   // resultCode===0000 아닐경우 아래 인증 실패를 출력함 
    // 인증실패
    alert_close('코드 : '.(isset($_POST['resultCode']) ? clean_xss_tags($_POST['resultCode'], 1, 1) : '').'  '.(isset($_POST['resultMsg']) ? clean_xss_tags(urldecode($_POST['resultMsg']), 1, 1) : ''));
    exit;
}

$g5['title'] = 'KG이니시스 간편인증 결과';
include_once(G5_PATH.'/head.sub.php');
?>    
<script>
    jQuery(function($) {        
        var $opener = window.opener;

        // 인증정보
        $opener.$("input[name=cert_type]").val(<?php echo get_js_safe_string($cert_type); ?>);
        $opener.$("input[name=mb_name]").val(<?php echo get_js_safe_string($user_name); ?>).attr("readonly", true);
        $opener.$("input[name=mb_hp]").val(<?php echo get_js_safe_string($phone_no); ?>).attr("readonly", true);
        $opener.$("input[name=cert_no]").val(<?php echo get_js_safe_string($md5_cert_no); ?>);
        
        alert("본인인증이 완료되었습니다.");

        if($opener.$("form[name=fcertrefreshform]") != undefined){
            $opener.$("form[name=fcertrefreshform]").submit();
        }   
        window.close();
    });
</script>
<?php
include_once(G5_PATH.'/tail.sub.php');