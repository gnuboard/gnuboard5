<?php
include_once('./_common.php');

$_POST = array_map_deep('conv_unescape_nl', $_POST);

/*
 * [본인확인 처리 페이지]
 *
 * LG유플러스으로 부터 내려받은 LGD_AUTHONLYKEY(인증Key)를 가지고 최종 인증요청.(파라미터 전달시 POST를 사용하세요)
 */

/*
 *************************************************
 * 1.최종인증 요청 - BEGIN
 *************************************************
 */

//LG유플러스 결제 서비스 선택(test:테스트, service:서비스)
if($config['cf_cert_use'] == 2)
    $CST_PLATFORM = 'service';
else
    $CST_PLATFORM = 'test';
$CST_MID                = 'si_'.$config['cf_lg_mid'];                       //상점아이디(LG유플러스으로 부터 발급받으신 상점아이디를 입력하세요)
                                                                            //테스트 아이디는 't'를 반드시 제외하고 입력하세요.
$LGD_MID                = (('test' == $CST_PLATFORM) ? 't' : '').$CST_MID;  //상점아이디(자동생성)
$LGD_AUTHONLYKEY        = $_POST['LGD_AUTHONLYKEY'];			            //LG유플러스으로부터 부여받은 인증키
$LGD_PAYTYPE  			= $_POST['LGD_PAYTYPE'];				            //인증요청타입 (신용카드:ASC001, 휴대폰:ASC002, 계좌:ASC004)

require_once(G5_LGXPAY_PATH.'/lgdacom/XPayClient.php');

// mall.conf 설정 추가를 위한 XPayClient 확장
class XPay extends XPayClient
{
    public function set_config_value($key, $val)
    {
        $this->config[$key] = $val;
    }
}

$configPath = G5_LGXPAY_PATH.'/lgdacom'; //LG유플러스에서 제공한 환경파일("/conf/lgdacom.conf,/conf/mall.conf") 위치 지정.

$xpay = new XPay($configPath, $CST_PLATFORM);

// Mert Key 설정
$xpay->set_config_value('t'.$LGD_MID, $config['cf_lg_mert_key']);
$xpay->set_config_value($LGD_MID, $config['cf_lg_mert_key']);

$xpay->Init_TX($LGD_MID);
$xpay->Set("LGD_TXNAME", "AuthOnlyByKey");
$xpay->Set("LGD_AUTHONLYKEY", $LGD_AUTHONLYKEY);
$xpay->Set("LGD_PAYTYPE", $LGD_PAYTYPE);

$g5['title'] = '휴대폰인증 결과';
include_once(G5_PATH.'/head.sub.php');

/*
 *************************************************
 * 1.최종인증 요청(수정하지 마세요) - END
 *************************************************
 */

/*
 * 2. 최종인증 요청 결과처리
 *
 * 최종 인증요청 결과 리턴 파라미터는 연동메뉴얼을 참고하시기 바랍니다.
 */
if ($xpay->TX()) {
    //1)인증결과 화면처리(성공,실패 결과 처리를 하시기 바랍니다.)
    
    /*
    echo "인증요청이 완료되었습니다.  <br>";
    echo "TX Response_code = " . $xpay->Response_Code() . "<br>";
    echo "TX Response_msg = " . $xpay->Response_Msg() . "<p>";

    $keys = $xpay->Response_Names();
    foreach($keys as $name) {
        echo $name . " = " . $xpay->Response($name, 0) . "<br>";
    }

    echo "</p>";
    */

    if( "0000" == $xpay->Response_Code() ) {
        //인증요청 결과 성공 DB처리
        //echo "인증요청 결과 성공 DB처리하시기 바랍니다.<br>";
        // 인증내역기록 인증 성공하면 로그를 남기는것으로 수정 2021-09-13
        @insert_cert_history($member['mb_id'], 'lg', 'hp');

        $cert_no        = $xpay->Response('LGD_TID', 0);                      // LG 인증처리번호
        $comm_id        = $xpay->Response('LGD_FINANCECODE', 0);              // 이동통신사 코드
        $phone_no       = $xpay->Response('LGD_MOBILENUM', 0);                // 전화번호
        $user_name      = $xpay->Response('LGD_MOBILE_SUBAUTH_NAME', 0);      // 이름
        $birth_day      = $xpay->Response('LGD_MOBILE_SUBAUTH_BIRTH', 0);     // 생년월일
        $sex_code       = $xpay->Response('LGD_MOBILE_SUBAUTH_SEX', 0);       // 성별코드
        $ci             = $xpay->Response('LGD_AUTHSUB_CI', 0);               // CI
        $di             = $xpay->Response('LGD_AUTHSUB_DI', 0);               // DI 중복가입 확인값

        // 내/외국인
        if($sex_code > 4)
            $local_code = 2; // 외국인
        else
            $local_code = 1; // 내국인

        // 남/여구분
        if($sex_code % 2 == 0)
            $mb_sex = 'F';
        else
            $mb_sex = 'M';

        // 생년월일
        if($sex_code < 5) {
            if($sex_code <= 2)
                $birth_prefix = '19';
            else
                $birth_prefix = '20';
        } else {
            if($sex_code <= 6)
                $birth_prefix = '19';
            else
                $birth_prefix = '20';
        }
        $birth_day = $birth_prefix.$birth_day;

        // 정상인증인지 체크
        if(!$phone_no)
            alert_close("정상적인 인증이 아닙니다. 올바른 방법으로 이용해 주세요.");

        $phone_no = hyphen_hp_number($phone_no);
        $mb_dupinfo = $di;
        $md5_ci = md5($ci.$ci);

        $row = sql_fetch("select mb_id from {$g5['member_table']} where mb_id <> '{$member['mb_id']}' and mb_dupinfo = '{$md5_ci}'"); // ci데이터로 찾음
        if (empty($row['mb_id'])) { // ci로 등록된 계정이 없다면
            $row = sql_fetch("select mb_id from {$g5['member_table']} where mb_id <> '{$member['mb_id']}' and mb_dupinfo = '{$mb_dupinfo}'"); // di데이터로 찾음
            if(empty($row['mb_id'])) {
                alert_close("인증하신 정보로 가입된 회원정보가 없습니다.");
                exit;
            }
        }else{
            $mb_dupinfo = $md5_ci;
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
        set_session("ss_cert_sex",     $mb_sex); // 이니시스 간편인증은 성별정보 리턴 없음
        set_session('ss_cert_dupinfo', $mb_dupinfo);
        set_session('ss_cert_mb_id', $row['mb_id']);
    } else {
        //인증요청 결과 실패 DB처리
        //echo "인증요청 결과 실패 DB처리하시기 바랍니다.<br>";

        if( G5_IS_MOBILE ){
            echo '<script>'.PHP_EOL;
            echo 'window.parent.$("#cert_info").css("display", "");'.PHP_EOL;
            echo 'window.parent.$("#lgu_cert" ).css("display", "none");'.PHP_EOL;
            echo 'alert("인증요청이 취소 또는 실패하였습니다.\\n\\n코드 : '.$xpay->Response_Code().'  '.$xpay->Response_Msg().'")';
            echo '</script>'.PHP_EOL;
        } else {
            alert_close('인증요청이 취소 또는 실패하였습니다.\\n\\n코드 : '.$xpay->Response_Code().'  '.$xpay->Response_Msg());
        }
        exit;
    }
} else {
    //2)API 요청실패 화면처리
    /*
    echo "인증요청이 실패하였습니다.  <br>";
    echo "TX Response_code = " . $xpay->Response_Code() . "<br>";
    echo "TX Response_msg = " . $xpay->Response_Msg() . "<p>";

    //인증요청 결과 실패 DB처리
    echo "인증요청 결과 실패 DB처리하시기 바랍니다.<br>";
    */

    if( G5_IS_MOBILE ){
        echo '<script>'.PHP_EOL;
        echo 'window.parent.$("#cert_info").css("display", "");'.PHP_EOL;
        echo 'window.parent.$("#lgu_cert" ).css("display", "none");'.PHP_EOL;
        echo 'alert("인증요청이 실패하였습니다.\\n\\n코드 : '.$xpay->Response_Code().'  '.$xpay->Response_Msg().'")';
        echo '</script>'.PHP_EOL;
    } else {
        alert_close('인증요청이 실패하였습니다.\\n\\n코드 : '.$xpay->Response_Code().'  '.$xpay->Response_Msg());
    }
    exit;
}
?>
<form name="mbFindForm" method="POST">
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