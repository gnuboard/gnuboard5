<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// 전자결제를 사용할 때만 실행
if($default['de_iche_use'] || $default['de_vbank_use'] || $default['de_hp_use'] || $default['de_card_use']) {
?>

<script language=javascript src="<?php echo $ini_js_url; ?>"></script>
<script language=javascript>
StartSmartUpdate();
</script>

<script language=javascript>
var openwin;

function set_encrypt_data(frm)
{
    // 데이터 암호화 처리
    var result = true;
    $.ajax({
        url: g5_url+"/shop/inicis/encryptdata.php",
        type: "POST",
        data: {
            price : frm.good_mny.value
        },
        dataType: "json",
        async: false,
        cache: false,
        success: function(data) {
            if(data.error == "") {
                frm.ini_encfield.value = data.ini_encfield;
                frm.ini_certid.value = data.ini_certid;
            } else {
                alert(data.error);
                result = false;
            }
        }
    });

    return result;
}

function pay(frm)
{
	// MakePayMessage()를 호출함으로써 플러그인이 화면에 나타나며, Hidden Field
	// 에 값들이 채워지게 됩니다. 일반적인 경우, 플러그인은 결제처리를 직접하는 것이
	// 아니라, 중요한 정보를 암호화 하여 Hidden Field의 값들을 채우고 종료하며,
	// 다음 페이지인 INIsecureresult.php로 데이터가 포스트 되어 결제 처리됨을 유의하시기 바랍니다.

	if(document.forderform.clickcontrol.value == "enable")
	{

		if(document.forderform.goodname.value == "")  // 필수항목 체크 (상품명, 상품가격, 구매자명, 구매자 이메일주소, 구매자 전화번호)
		{
			alert("상품명이 빠졌습니다. 필수항목입니다.");
			return false;
		}
		else if(document.forderform.buyername.value == "")
		{
			alert("구매자명이 빠졌습니다. 필수항목입니다.");
			return false;
		}
		else if(document.forderform.buyeremail.value == "")
		{
			alert("구매자 이메일주소가 빠졌습니다. 필수항목입니다.");
			return false;
		}
		else if(document.forderform.buyertel.value == "")
		{
			alert("구매자 전화번호가 빠졌습니다. 필수항목입니다.");
			return false;
		}
		else if( ( navigator.userAgent.indexOf("MSIE") >= 0 || navigator.appName == 'Microsoft Internet Explorer' ) &&  (document.INIpay == null || document.INIpay.object == null) )  // 플러그인 설치유무 체크
		{
			alert("\n이니페이 플러그인 128이 설치되지 않았습니다. \n\n안전한 결제를 위하여 이니페이 플러그인 128의 설치가 필요합니다. \n\n다시 설치하시려면 Ctrl + F5키를 누르시거나 메뉴의 [보기/새로고침]을 선택하여 주십시오.");
			return false;
		}
		else
		{
			/******
			 * 플러그인이 참조하는 각종 결제옵션을 이곳에서 수행할 수 있습니다.
			 * (자바스크립트를 이용한 동적 옵션처리)
			 */


			if (MakePayMessage(frm))
			{
				disable_click();
                document.getElementById("display_pay_button").style.display = "none";
                document.getElementById('display_pay_process').style.display = '';
				return true;
			}
			else
			{
			  if( IsPluginModule() )     //plugin타입 체크
        {
				  alert("결제를 취소하셨습니다.");
				  return false;
				}
			}
		}
	}
	else
	{
		return false;
	}
}


function enable_click()
{
	document.forderform.clickcontrol.value = "enable"
}

function disable_click()
{
	document.forderform.clickcontrol.value = "disable"
}

function focus_control()
{
	if(document.forderform.clickcontrol.value == "disable")
		openwin.focus();
}
</script>

<?php } ?>