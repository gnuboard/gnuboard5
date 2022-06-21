<?php
include_once('./_common.php');

$txId = isset($_POST['txId']) ? clean_xss_tags($_POST['txId'], 1, 1) : '';
$mid  = substr($txId, 6, 10);

if ($txId && isset($_POST["resultCode"]) && $_POST["resultCode"] === "0000") {

    $data = array(
        'mid' => $mid,        
        'txId' => $txId
    );

    $post_data = json_encode($data);

    $authRequestUrl = isset($_POST["authRequestUrl"]) ? is_inicis_url_return($_POST["authRequestUrl"]) : '';
    if(!$authRequestUrl){
        alert('잘못된 요청입니다.', G5_URL);
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

    if($res_data['resultCode'] === "0000") {

        $cert_type      = 'simple';                                 // 인증타입
        $cert_no        = $res_data['txId'];                    // 이니시스 트랜잭션 ID
        $phone_no       = $res_data['userPhone'];               // 전화번호
        $user_name      = $res_data['userName'];                // 이름
        $birth_day      = $res_data['userBirthday'];            // 생년월일
        $ci             = $res_data['userCi'];                  // CI           

        @insert_cert_history($member['mb_id'], 'inicis', $cert_type); // 인증성공 시 내역 기록

        if(!$phone_no)
        alert_close("정상적인 인증이 아닙니다. 올바른 방법으로 이용해 주세요.");

        $md5_ci = md5($ci . $ci);
        $phone_no = hyphen_hp_number($phone_no);
        $mb_dupinfo = $md5_ci;

        $row = sql_fetch("select mb_id from {$g5['member_table']} where mb_id <> '{$member['mb_id']}' and mb_dupinfo = '{$mb_dupinfo}'"); // ci데이터로 찾음
        if(empty($row['mb_id'])) { // ci로 등록된 계정이 없다면
            alert_close("인증하신 정보로 가입된 회원정보가 없습니다.");
            exit;                
        }

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
        set_session('ss_cert_mb_id', $row['mb_id']);
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
<form name="mbFindForm" method="POST">
<input type="hidden" name="mb_id" value="<?php echo isset($row["mb_id"]) ? get_text($row["mb_id"]) : ''; ?>">
</form>
<script>
    jQuery(function($) {
        
        var $opener = window.opener;
        var is_mobile = false;        
        $opener.name="parentPage";

        if (typeof g5_is_mobile != "undefined" && g5_is_mobile ) {
            $opener = window.parent;
            is_mobile = true;
        } else {
            $opener = window.opener;
        }
            
        document.mbFindForm.target = "parentPage";
        document.mbFindForm.action = "<?php echo G5_BBS_URL.'/password_reset.php'?>";
        document.mbFindForm.submit();

        alert("본인인증이 완료되었습니다.");
        window.close();        
    });
</script>
<?php
include_once(G5_PATH.'/tail.sub.php');