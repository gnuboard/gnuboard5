<?
$sub_menu = "500200";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

$g4[title] = "SMS 문자전송";
include_once ("$g4[admin_path]/admin.head.php");

// 발신자번호
$send_number = preg_replace("/[^0-9]/", "", $default[de_admin_company_tel]);
?>

<?=subtitle($g4[title])?>

<script language="JavaScript">
function byte_check(cont, bytes)
{
    var i = 0;
    var cnt = 0;
    var exceed = 0;
    var ch = '';

    for (i=0; i<cont.value.length; i++) {
        ch = cont.value.charAt(i);
        if (escape(ch).length > 4) {
            cnt += 2;
        } else {
            cnt += 1;
        }
    }

    //byte.value = cnt + ' / 80 bytes';
    bytes.innerHTML = cnt + ' / 80 bytes';

    if (cnt > 80) {
        exceed = cnt - 80;
        alert('메시지 내용은 80바이트를 넘을수 없습니다.\r\n작성하신 메세지 내용은 '+ exceed +'byte가 초과되었습니다.\r\n초과된 부분은 자동으로 삭제됩니다.');
        var tcnt = 0;
        var xcnt = 0;
        var tmp = cont.value;
        for (i=0; i<tmp.length; i++) {
            ch = tmp.charAt(i);
            if (escape(ch).length > 4) {
                tcnt += 2;
            } else {
                tcnt += 1;
            }

            if (tcnt > 80) {
                tmp = tmp.substring(0,i);
                break;
            } else {
                xcnt = tcnt;
            }
        }
        cont.value = tmp;
        //byte.value = xcnt + ' / 80 bytes';
        bytes.innerHTML = xcnt + ' / 80 bytes';
        return;
    }
}
</script>


<script language="JavaScript" type="text/JavaScript">
var StrComma = "";

function tel_enter()
{
    /*
    if(window.event.keyCode ==13) 
    {
        receive_add();
    }
    */
    var code = document.getElementById('keycode').value;
    if (code == 13)
    {
        receive_add();
    }
}

function receive_add()
{
	var intCount = 0;
	var strMobile = document.smsform.receive_input.value;
	//strMobile = strMobile.replace("-", "", strMobile);
    strMobile = strMobile.replace("-", ""); 

	for (i = 0; i < document.smsform.receive_buffer.length; i++)
	{
		if (strMobile == document.smsform.receive_buffer.options[i].value)
		{
			return alert("같은 번호는 재입력 하실수 없습니다");
			document.smsform.receive_buffer.options.remove(i);
			intCount = intCount - 1;
			document.smsform.count.value = intCount ;
			document.smsform.receive_input.focus();
		}
	}



	strDigit= "0123456789-";
	intIdLength = strMobile.length;
	var blnChkFlag;

	for (i = 0; i < intIdLength; i++)
	{
		strNumberChar = strMobile.charAt(i);
		blnChkFlag = false;

		for (j = 0; j < strDigit.length ; j++)
		{
			strCompChar = strDigit.charAt(j);

			if (strNumberChar == strCompChar)
			{
				blnChkFlag = true;
			}
		}

		if (blnChkFlag == false)
		{
			break;
		}
	}

	if (strMobile == "" )
	{
		alert ("추가할 수신번호를 입력해 주세요");
	}
	else if (strMobile.length < 10 || strMobile.length > 13 )
	{
		alert ("수신번호는 최대 13자, 최소 10자이내로 입력해 주세요.\n\n 예) 01X-123-4567 또는 01X1234567  ");
		document.smsform.receive_input.value="";
		document.smsform.receive_input.focus();
	}
	else if ( !blnChkFlag )
	{
		alert("수신번호는 숫자만 가능합니다.");
		document.smsform.receive_input.value="";
		document.smsform.receive_input.focus();
	}
	else
	{


		document.smsform.receive_number.value = document.smsform.receive_number.value + document.smsform.receive_input.value + "," ;
        StrComma = ",";
		add() ;

	}

}

function add()
{
		var intCount = document.smsform.count.value ;
		var newOpt = document.createElement('OPTION');
		newOpt.text =  document.smsform.receive_input.value;
		newOpt.value = document.smsform.receive_input.value;
		document.smsform.receive_buffer.options.add(newOpt);

		document.smsform.receive_input.value = "" ;
		intCount = intCount - 1 + 2;
		document.smsform.count.value = intCount ;
		document.smsform.receive_input.focus();

}

function receive_del()
{
	if (document.smsform.receive_buffer.selectedIndex < 0)
	{
		alert ("삭제할 번호를 선택해 주세요");
	}
	else
	{
		var aaa;
		aaa = document.smsform.receive_number.value ;
		aaa = aaa.replace(document.smsform.receive_buffer.value + ",","");
		document.smsform.receive_number.value = aaa ;

		var num ;
		var intCount = document.smsform.count.value ;
		num = document.smsform.receive_buffer.selectedIndex ;
		document.smsform.receive_buffer.options.remove(num);
		intCount = intCount - 1;
		document.smsform.count.value = intCount ;
	}
}

function receive_alldel()
{

		document.smsform.receive_number.value = "0" ;
		var intCount = document.smsform.count.value ;
		for (i = 0; i < intCount; i++)
		{
			document.smsform.receive_buffer.options.remove(0);
		}
		document.smsform.count.value = "0" ;
}
</script>

<? if ($default[de_sms_use] == "icode") { // 아이코드 사용 ?>
<form action="./smssendicode.php" name="smsform" method=post autocomplete=off>
<input type="hidden" name="receive_number" value="">
<? } else { ?>
<form action="javascript:alert('SMS 사용을 하고 있지 않아 문자를 전송할 수 없습니다.');" name="smsform" method=post autocomplete=off>
<input type="hidden" name="receive_number" value="">
<? } ?>

<table border="0" cellpadding="0" cellspacing="0">
<tr>
    <td>
    <table align=center><tr><td><div id=bytes align=center>0 / 80 바이트</div></table>
    <table border="0" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center" valign="middle"><table width="182" border="0" cellspacing="0" cellpadding="0">
            <tr>
              <td height="184" colspan="3" background="./img/skinL1_top.gif" align=center><bR><br><br>
			  <textarea  style='OVERFLOW: hidden; border:solid 0; width:100px; height:80px; background-color:#FFE07E; FONT-SIZE: 9pt; font-family:굴림체;' name=sms_contents cols="16"  wrap=virtual ONKEYUP="byte_check(document.smsform.sms_contents, bytes);"></textarea></td>
            </tr>
            <tr>
              <td width="39"><img src="./img/skinL1_img1.gif" width="39" height="20"></td>
              <td width="102"><img src="./img/skinL1_img2.gif" width="102" height="20"></td>
              <td><img src="./img/skinL1_img3.gif" width="41" height="20"></td>
            </tr>
            <tr valign="top" >
              <td height="226" colspan="3" background="./img/skinL1_under.gif"><table width="172" border="0" cellspacing="0" cellpadding="0">
                  <tr>
                    <td width="10" height="13"></td>
                    <td width="162" height="13"></td>
                  </tr>
                  <tr>
                    <td>&nbsp;</td>
                    <td align="center"><table width="156" height="37" border="0" cellpadding="0" cellspacing="0">
                        <tr>
                          <td><a href="javascript:smsform_check(document.smsform);"><img src="./img/skinL1_btnsnd.gif" border=0 width="76" height="30"></a></td>
                          <td align="right"><a href="javascript:;" onclick="document.smsform.sms_contents.value='';"><img src="./img/skinL1_btncnl.gif" border=0 width="76" height="30"></a></td>
                        </tr>
                      </table></td>
                  </tr>
                  <tr>
                    <td>&nbsp;</td>
                    <td><table width="164" border="0" cellspacing="0" cellpadding="0">
                        <tr>
                          <td width="10" align="right"><img src="./img/skinL1_icon.gif" width="8" height="8"></td>
                          <td width="52" align="center" valign="middle">발신번호</td>
                          <td width="102" height="22"> <input name="send_number" type="text" class=ed size="10" value="<?=$send_number?>"></td>
                        </tr>
                      </table></td>
                  </tr>
                  <tr>
                    <td>&nbsp;</td>
                    <td valign="bottom"><table width="164" border="0" cellspacing="0" cellpadding="0">
                        <tr>
                          <td width="10" align="right"><img src="./img/skinL1_icon.gif" width="8" height="8"></td>
                          <td width="52" align="center" valign="middle">수신번호</td>
                          <input type=hidden id='keycode'>
                          <td width="70"> <input name="receive_input" type="text" class=ed size="10" onkeydown="document.getElementById('keycode').value=event.keyCode; tel_enter();"></td>
                          <td width="32" height="20" align="center"><a href="javascript:receive_add();"><img src="./img/skinL1_btnpls.gif" width="27" height="18" border=0></a></td>
                        </tr>
                      </table></td>
                  </tr>
                  <tr>
                    <td>&nbsp;</td>
                    <td><table width="164" border="0" cellspacing="0" cellpadding="0">
                        <tr>
                          <td height="22" align="right">&nbsp;</td>
                          <td width="52" align="center" valign="middle"><input type="text" name="count" size="3" class=ed readonly>명</td>
                          <td width="102" rowspan="2"><select name="receive_buffer"  size=4 style="font-size: 9pt; border: 0; width:100px;" >
                          </td>
                        </tr>
                        <tr>
                          <td width="10" height="22" align="right">&nbsp;</td>
                          <td align="center" valign="middle"><table width="43" border="0" cellspacing="0" cellpadding="0">
                              <tr>
                                <td><a href="javascript:receive_del();"><img src="./img/skinL1_btndel.gif" width="17" height="32" border=0></a></td>
                                <td align="right"><a href="javascript:receive_alldel();"><img src="./img/skinL1_btnalldel.gif" width="24" height="32" border=0></a></td>
                              </tr>
                            </table></td>
                        </tr>
                      </table></td>
                  </tr>
                </table>
                <table width="172" border="0" cellspacing="0" cellpadding="0">
                  <tr>
                    <td height="10"></td>
                    <td height="8" ></td>
                  </tr>
                  <tr>
                    <td width="12">&nbsp;</td>
                    <td width="164" height="25" class=small><input type="checkbox" name="reserved_flag" value="true">예약&nbsp; <select name="reserved_month" style="font-size:8pt">
                    <? for ($i=1; $i<=12; $i++) { echo "<option value='$i'>$i</option>"; } ?>
                      </select>월<select name="reserved_day" style="font-size:8pt">
                    <? for ($i=1; $i<=31; $i++) { echo "<option value='$i'>$i</option>"; } ?>
                      </select>일</td>
                  </tr>
                  <tr>
                    <td>&nbsp;</td>
                    <td height="23" class=small><select name="reserved_year" style="font-size:8pt">
                    <? for ($i=date("Y"); $i<=date("Y")+1; $i++) { echo "<option value='$i'>".substr($i,-2)."</option>"; } ?>
                      </select>년<select name="reserved_hour" style="font-size:8pt">
                    <? for ($i=1; $i<=24; $i++) { echo "<option value='$i'>$i</option>"; } ?>
                      </select>시<select name="reserved_minute" style="font-size:8pt">
                    <? for ($i=1; $i<=60; $i++) { echo "<option value='$i'>$i</option>"; } ?>
                      </select>분</td>
                  </tr>
                </table></td>
            </tr>
          </table></td>
      </tr>
    </table>

  </td>
  </tr>
 </table>
 </form>

<script language="JavaScript">
document.smsform.reserved_year.value = '<?=date("Y")?>';
document.smsform.reserved_month.value = '<?=date("n")?>';
document.smsform.reserved_day.value = '<?=date("j")?>';

function smsform_check(f)
{
    <?
    if (file_exists("$g4[path]/DEMO")) {
        echo "alert('데모에서는 문자메세지를 발송할 수 없습니다.');";
        echo "return;";
    }


    if ($default[de_sms_use] == "") {
        echo "alert('우선 SMS 환경을 설정하여 주십시오.');";
        echo "return;";
    }
    ?>

    if (f.sms_contents.value == "") {
        alert("문자메세지를 입력하십시오");
        f.sms_contents.focus();
        return;
    }

    if (f.receive_number.value == "") {
        alert("수신 핸드폰번호를 입력하십시오");
        f.receive_input.focus();
        return;
    }

    f.submit();
}
</script>


<?
include_once ("$g4[admin_path]/admin.tail.php");
?>
