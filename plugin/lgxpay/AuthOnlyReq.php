<?php
include_once('./_common.php');

// 금일 인증시도 회수 체크
certify_count_check($member['mb_id'], 'hp');

/*
 * [본인확인 요청페이지]
 *
 * 샘플페이지에서는 기본 파라미터만 예시되어 있으며, 별도로 필요하신 파라미터는 연동메뉴얼을 참고하시어 추가 하시기 바랍니다.
 */

//LG유플러스 결제 서비스 선택(test:테스트, service:서비스)
if($config['cf_cert_use'] == 2)
    $CST_PLATFORM = 'service';
else
    $CST_PLATFORM = 'test';
$CST_MID                    = 'si_'.$config['cf_lg_mid'];       // 상점아이디(LG유플러스으로 부터 발급받으신 상점아이디를 입력하세요)
                                                                //테스트 아이디는 't'를 반드시 제외하고 입력하세요.
$LGD_MID                    = (('test' == $CST_PLATFORM) ? 't':'').$CST_MID;  //상점아이디(자동생성)
$LGD_BUYER                  = '홍길동';                         // 성명 (보안을 위해 DB난 세션에서 가져오세요)
$LGD_BUYERSSN               = '0000000000000';                  // 주민등록번호 (보안을 위해 DB나 세션에서 가져오세요)
                                                                // 휴대폰 본인인증을 사용할 경우 주민번호는 '0' 13자리를 넘기세요. 예)0000000000000
                                                                // 기타 인증도 사용할 경우 실 주민등록번호 (보안을 위해 DB나 세션에 저장처리 권장)
$LGD_MOBILE_SUBAUTH_SITECD  = '';                               // 신용평가사에서 부여받은 회원사 고유 코드
                                                                //  (CI값만 필요한 경우 옵션, DI값도 필요한 경우 필수)
$LGD_TIMESTAMP              = date('YmdHis');                   // 타임스탬프 (YYYYMMDDhhmmss)
$LGD_CUSTOM_SKIN            = 'red';                            // 상점정의 인증창 스킨 (red, blue, cyan, green, yellow)

/*
 *************************************************
 * 2. MD5 해쉬암호화 (수정하지 마세요) - BEGIN
 *
 * MD5 해쉬암호화는 거래 위변조를 막기위한 방법입니다.
 *************************************************
 *
 * 해쉬 암호화 적용( LGD_MID + LGD_BUYERSSN + LGD_TIMESTAMP + LGD_MERTKEY )
 * LGD_MID          : 상점아이디
 * LGD_BUYERSSN     : 주민등록번호
 * LGD_TIMESTAMP    : 타임스탬프
 * LGD_MERTKEY      : 상점MertKey (mertkey는 상점관리자 -> 계약정보 -> 상점정보관리에서 확인하실수 있습니다)
 *
 * MD5 해쉬데이터 암호화 검증을 위해
 * LG유플러스에서 발급한 상점키(MertKey)를 환경설정 파일(lgdacom/conf/mall.conf)에 반드시 입력하여 주시기 바랍니다.
 */

$LGD_MERTKEY    = $config['cf_lg_mert_key'];
$LGD_HASHDATA   = md5($LGD_MID.$LGD_BUYERSSN.$LGD_TIMESTAMP.$LGD_MERTKEY);

/*
 *************************************************
 * 2. MD5 해쉬암호화 (수정하지 마세요) - END
 *************************************************
 */
?>

<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8">
<title>LG유플러스 전자결제 본인확인서비스</title>
<!-- 고객사 사이트가 https인 경우는 아래 http://을 https:// 으로 변경하시면 됩니다. -->
<link rel="stylesheet" href="<?php echo G5_CSS_URL;?>/default.css">
<script language="javascript" src="//xpay.uplus.co.kr/xpay/js/xpay_authonly.js" type="text/javascript"></script>
<script>
function do_Authonly() {
    ret = xpay_authonly_check(document.getElementById("LGD_PAYINFO"), document.getElementById("CST_PLATFORM").value);
    if (ret == "00"){     //ActiveX 로딩 성공
        if(dpop.getData("LGD_RESPCODE") == "0000"){
            document.getElementById("LGD_AUTHONLYKEY").value = dpop.getData("LGD_AUTHONLYKEY");
            document.getElementById("LGD_PAYTYPE").value = dpop.getData("LGD_PAYTYPE");
            //alert("인증요청을 합니다.");
            document.getElementById("LGD_PAYINFO").submit();
        } else {
            alert(dpop.getData("LGD_RESPMSG"));
        }
    } else {
        alert("LG유플러스 본인확인서비스를 위한 ActiveX 설치 실패\nInternet Explorer 외의 브라우저에서는 사용할 수 없습니다.");
        //window.close();
    }
}
</script>
<style>
#uplus_win {}
.up_cmt {text-align:center; font-size:14px;}
.up_cmt img {display:block; margin:0 auto 20px}
.up_info {background:#eee;padding:13px;margin:28px 25px 20px;}
.up_info a {float:left; margin-right:10px}
.up_info p {padding:10px 0 0; line-height:18px;}
.up_info:after {clear:both; display:block; content:"";}
.win_btn {clear:both;}
</style>
</head>
<body>
<form method="post" id="LGD_PAYINFO" action="<?php echo G5_LGXPAY_URL; ?>/AuthOnlyRes.php">
<input type="hidden" name="CST_MID" id="CST_MID" value="<?php echo $CST_MID; ?>" />
<input type="hidden" name="LGD_MID" id="LGD_MID" value="<?php echo $LGD_MID; ?>"/>
<input type="hidden" name="CST_PLATFORM" id="CST_PLATFORM" value="<?php echo $CST_PLATFORM; ?>"/>
<input type="hidden" name="LGD_BUYERSSN" value="<?php echo $LGD_BUYERSSN; ?>"/>
<input type="hidden" name="LGD_BUYER" value="<?php echo $LGD_BUYER; ?>"/>
<input type="hidden" name="LGD_MOBILE_SUBAUTH_SITECD" value="<?php echo  $LGD_MOBILE_SUBAUTH_SITECD; ?>"/>
<input type="hidden" name="LGD_TIMESTAMP" value="<?php echo $LGD_TIMESTAMP; ?>"/>
<input type="hidden" name="LGD_HASHDATA" value="<?php echo $LGD_HASHDATA; ?>"/>
<input type="hidden" name="LGD_NAMECHECKYN" value="N">
<input type="hidden" name="LGD_HOLDCHECKYN" value="Y">
<input type="hidden" name="LGD_CUSTOM_SKIN" value="red">
<input type="hidden" name="LGD_CUSTOM_FIRSTPAY" value="ASC007">
<input type="hidden" name="LGD_CUSTOM_USABLEPAY" value="ASC007">
<input type="hidden" name="LGD_PAYTYPE" id="LGD_PAYTYPE"/>
<input type="hidden" name="LGD_AUTHONLYKEY" id="LGD_AUTHONLYKEY"/>
</form>

<div id="uplus_win" class="new_win mbskin">
    <h1 id="win_title">휴대폰 본인확인</h1>
    <p class="up_cmt"><img src="<?php echo G5_LGXPAY_URL; ?>/img/upluslogo.jpg" alt="">LG U+에 휴대폰 본인확인 요청 중입니다.</p>
    <div class="up_info">
        <a href="http://pgweb.uplus.co.kr:8080/pg/wmp/Home2009/skill/payment_error_center01.jsp" target="_blank"><img src="<?php echo G5_LGXPAY_URL; ?>/img/btn_gouplus.jpg" alt="U+ 오류 해결방법 바로가기"></a>
        <p>본인확인이 진행되지 않는다면<br /> 왼쪽의 링크로 이동하여보세요.</p>
        <!--[If lte IE 7]><span style="clear:both; display:block; content:'';"></span><![endif]-->
    </div>
    <div class="win_btn">
        <button type="button" onclick="window.close();">창닫기</button>
    </div>
</div>
<script>
setTimeout("do_Authonly();",300);
</script>
</body>
</html>