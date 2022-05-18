<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// 전자결제를 사용할 때만 실행
if($default['de_iche_use'] || $default['de_vbank_use'] || $default['de_hp_use'] || $default['de_card_use'] || $default['de_easy_pay_use']) {
?>

<?php if ($default['de_card_test']) {   // 테스트 결제시 ?>
<script language="javascript" src="https://pretest.uplus.co.kr:9443/xpay/js/xpay_crossplatform.js" type="text/javascript"></script>
<?php } else {      //실 결제시 ?>
<script language="javascript" src="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on') ? 'https' : 'http'; ?>://xpay.uplus.co.kr/xpay/js/xpay_crossplatform.js" type="text/javascript"></script>
<?php } ?>

<script type="text/javascript">

/*
* 수정불가.
*/
var LGD_window_type = "<?php echo $LGD_WINDOW_TYPE; ?>";

/*
* 수정불가
*/
function launchCrossPlatform(frm) {
    $.ajax({
        url: g5_url+"/shop/lg/xpay_request.php",
        type: "POST",
        data: $("#LGD_PAYREQUEST input").serialize(),
        dataType: "json",
        async: false,
        cache: false,
        success: function(data) {
            frm.LGD_HASHDATA.value = data.LGD_HASHDATA;

            lgdwin = openXpay(frm, '<?php echo $CST_PLATFORM; ?>', LGD_window_type, null, "", "");
        },
        error: function(data) {
            console.log(data);
        }
    });
}
/*
* FORM 명만  수정 가능
*/
function getFormObject() {
    return document.getElementById("forderform");
}

/*
 * 인증결과 처리
 */
function payment_return() {
    var fDoc;

    fDoc = lgdwin.contentWindow || lgdwin.contentDocument;

    if (fDoc.document.getElementById('LGD_RESPCODE').value == "0000") {
        document.getElementById("LGD_PAYKEY").value = fDoc.document.getElementById('LGD_PAYKEY').value;
        document.getElementById("forderform").target = "_self";
        document.getElementById("forderform").action = "<?php echo $order_action_url; ?>";
        document.getElementById("forderform").submit();
    } else {
        document.getElementById("forderform").target = "_self";
        document.getElementById("forderform").action = "<?php echo $order_action_url; ?>";
        alert("LGD_RESPCODE (결과코드) : " + fDoc.document.getElementById('LGD_RESPCODE').value + "\n" + "LGD_RESPMSG (결과메시지): " + fDoc.document.getElementById('LGD_RESPMSG').value);
        closeIframe();
    }
}
</script>
<?php }