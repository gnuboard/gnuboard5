<?php
include_once('./_common.php');
require_once (dirname(__FILE__) .'/libs/KISA_SEED_CBC.php');
require_once (dirname(__FILE__) .'/libs/INILib.php');

$txId = isset($_POST['txId']) ? clean_xss_tags($_POST['txId'], 1, 1) : '';
$mid  = substr($txId, 6, 10);
$SEEDKEY = isset($_POST['token']) ? clean_xss_tags($_POST['token'], 1, 1) : '';
$SEEDIV = 'SASHOSTSIRIAS000';

if ($txId && isset($_POST["resultCode"]) && $_POST["resultCode"] === "0000") {

    $data = array(
        'mid' => $mid,        
        'txId' => $txId
    );

    $post_data = json_encode($data);

    $authRequestUrl = isset($_POST["authRequestUrl"]) ? is_inicis_url_return($_POST["authRequestUrl"]) : '';

    // SaSample 에 나와있는대로 url을 검증합니다.
    if (!(strpos($authRequestUrl,"https://kssa.inicis.com") == 0 || strpos($authRequestUrl,"https://fcsa.inicis.com") == 0)) {
        $authRequestUrl = '';
    }

    if (! $authRequestUrl) {
        alert('잘못된 요청입니다.', G5_URL);
        exit;
    }

    // curl 통신 시작 
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $authRequestUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    curl_close($ch);
    $res_data = json_decode($response, true);
    // -------------------- 결과 수신 -------------------------------------------
    //  echo '<결과내역>'." '{$mid}' <br/><br/>";
    //  echo $response;

    if($res_data['resultCode'] === "0000") {
        $cert_type      = 'simple';                                 // 인증타입
        $cert_no        = $res_data['txId'];                    // 이니시스 트랜잭션 ID
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

        @insert_cert_history($member['mb_id'], 'inicis', $cert_type); // 인증성공 시 내역 기록

        if(!$phone_no)
        alert_close("정상적인 인증이 아닙니다. 올바른 방법으로 이용해 주세요.");

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
        alert_close('코드 : '.$res_data['resultCode'].'  '.urldecode($res_data['resultMsg']));
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
        $opener.$("input[name=cert_type]").val("<?php echo $cert_type; ?>");
        $opener.$("input[name=mb_name]").val("<?php echo $user_name; ?>").attr("readonly", true);
        $opener.$("input[name=mb_hp]").val("<?php echo $phone_no; ?>").attr("readonly", true);
        $opener.$("input[name=cert_no]").val("<?php echo $md5_cert_no; ?>");
        
        alert("본인인증이 완료되었습니다.");

        if($opener.$("form[name=fcertrefreshform]") != undefined){
            $opener.$("form[name=fcertrefreshform]").submit();
        }   
        window.close();
    });
</script>
<?php
include_once(G5_PATH.'/tail.sub.php');