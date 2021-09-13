<?php
    include_once('./_common.php');

    $txId = $_POST['txId'];
    $mid  = substr($txId, 6, 10);
    
    if ($_POST["resultCode"] === "0000") { 

        $data = array(
            'mid' => $mid,        
            'txId' => $txId
        );

        $post_data = json_encode($data);

        // curl 통신 시작 
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $_POST["authRequestUrl"]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        curl_close($ch);
        $res_data = json_decode($response, true);

        if($res_data['resultCode'] == "0000") {

            @insert_cert_history('@password_lost@', 'kg', 'sa'); // 인증성공 시 내역 기록

            $cert_type = 'sa';                                      // 인증타입
            $cert_no        = $res_data['txId'];                    // 이니시스 트랜잭션 ID
            $phone_no       = $res_data['userPhone'];               // 전화번호
            $user_name      = $res_data['userName'];                // 이름
            $birth_day      = $res_data['userBirthday'];            // 생년월일
            $ci             = $res_data['userCi'];                  // CI           
            
            if(!$phone_no)
            alert_close("정상적인 인증이 아닙니다. 올바른 방법으로 이용해 주세요.");

            $md5_ci = md5($ci . $ci);
            $phone_no = hyphen_hp_number($phone_no);
            $mb_dupinfo = $md5_ci;
            
            $row = sql_fetch("select * from {$g5['member_table']} where mb_id <> '{$member['mb_id']}' and mb_dupinfo = '{$mb_dupinfo}'");
            if (!$row['mb_id']) {
                $row = sql_fetch("select * from {$g5['member_table']} where mb_id <> '{$member['mb_id']}' and mb_name ='{$user_name}' and mb_birth='{$birth_day}' and mb_hp='{$phone_no}'");
                if(!$row['mb_id']){
                    alert_close("인증하신 정보로 가입된 회원정보가 없습니다.");
                    exit;
                }
            }
        }else{
            // 인증실패 curl의 인증실패 체크
            alert_close('코드 : '.$res_data['resultCode'].'  '.urldecode($res_data['resultMsg']));
            exit;
        }
    } else {   // resultCode===0000 아닐경우 아래 인증 실패를 출력함 
        // 인증실패
        alert_close('코드 : '.$_POST['resultCode'].'  '.urldecode($_POST['resultMsg']));
        exit;
    }

    $g5['title'] = 'KG이니시스 통합인증 결과';
    include_once(G5_PATH.'/head.sub.php'); 
?>    
<form name="mbFindForm">
    <input type="hidden" name="mb_id" value="<?php echo $row["mb_id"]; ?>">    
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