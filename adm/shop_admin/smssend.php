<?
$sub_menu = '500200';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g4['title'] = 'SMS 문자전송';
include_once (G4_ADMIN_PATH.'/admin.head.php');

// 발신자번호
$send_number = preg_replace("/[^0-9]/", "", $default['de_admin_company_tel']);
?>

<?
//if ($default['de_sms_use'] == "icode") { // 아이코드 사용
if ($is_admin) {
?>
<form action="./smssendicode.php" name="smsform" method="post" autocomplete="off">
<input type="hidden" name="receive_number" value="">

<script>
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


<script>
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

<section id="sms_send" class="cbox">
    <h2>SMS 문자전송 내용 입력</h2>
    <p>예약발송 기능을 이용하시면, 예약된 시간에 맞춰 SMS 문자를 일괄발송할 수 있습니다.</p>

    <div id="sms_frm">
        <table class="frm_tbl">
        <colgroup>
            <col class="grid_3">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th>발신번호</th>
            <td>
                <?=help('SMS 발신자 번호를 입력하세요.')?>
                <input name="send_number" type="text" value="<?=$send_number?>" id="send_number" class="frm_input">
            </td>
        </tr>
        <tr>
            <th>수신번호</th>
            <td>
                <?=help('여러명에게 보내실 때는 전화번호를 엔터로 구분하세요.')?>
                <textarea></textarea>
                <div><span>총 수신인 <strong></strong>명</span></div>
            </td>
        </tr>
        <tr>
            <th>문자내용</th>
            <td>
                <?=help("주의! 80 bytes 까지만 전송됩니다.\n영문 한글자 : 1byte , 한글 한글자 : 2bytes , 특수문자의 경우 1 또는 2 bytes 입니다.")?>
                <textarea name="sms_contents" ONKEYUP="byte_check(document.smsform.sms_contents, bytes);"></textarea>
                <div id="bytes">0 / 80 바이트</div>
            </td>
        </tr>
        <tr>
            <th><label for="reserved_flag">예약발송</label></th>
            <td>
                <input type="checkbox" name="reserved_flag" value="true" id="reserved_flag">
                예약발송 사용
                <label for="reserved_year" class="sound_only">연도 설정</label>
                <select name="reserved_year" id="reserved_year">
                    <? for ($i=date("Y"); $i<=date("Y")+1; $i++) { echo "<option value='$i'>".substr($i,-2)."</option>"; } ?>
                </select>년
                <label for="reserved_month" class="sound_only">월 설정</label>
                <select name="reserved_month" id="reserved_month">
                    <? for ($i=1; $i<=12; $i++) { echo "<option value='$i'>$i</option>"; } ?>
                </select>월
                <label for="reserved_day" class="sound_only">일 설정</label>
                <select name="reserved_day" id="reserved_day">
                    <? for ($i=1; $i<=31; $i++) { echo "<option value='$i'>$i</option>"; } ?>
                </select>일
                <label for="reserved_hour" class="sound_only">시 설정</label>
                <select name="reserved_hour" id="reserved_hour">
                    <? for ($i=1; $i<=24; $i++) { echo "<option value='$i'>$i</option>"; } ?>
                </select>시
                <label for="reserved_minute" class="sound_only">분 설정</label>
                <select name="reserved_minute" id="reserved_minute">
                    <? for ($i=1; $i<=60; $i++) { echo "<option value='$i'>$i</option>"; } ?>
                </select>분
            </td>
        </tr>
        </tbody>
        </table>

        <div class="btn_confirm">
            <input type="submit" value="전송" class="btn_submit">
        </div>
    </div>

    <div id="sms_sm">
        <span id="sms_sm_text">여기에 문자내용 입력한 것이 실시간으로...</span>
        <p>이 이미지는 이해를 돕기 위한 이미지로써, 실제 발송 시 화면에서 보이는 것과 차이가 있을 수 있습니다.</p>
    </div>
</section>


<table border="0" cellpadding="0" cellspacing="0">
<tr>
    <td>
    <table align=center><tr><td></table>
    <table border="0" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center" valign="middle"><table width="182" border="0" cellspacing="0" cellpadding="0">
            <tr>
              <td height="184" colspan="3" background="./img/skinL1_top.gif" align=center><bR><br><br>
              </td>
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
                          <td width="102" height="22"> </td>
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
                    <td width="164" height="25" class=small></td>
                  </tr>
                  <tr>
                    <td>&nbsp;</td>
                    <td height="23" class=small></td>
                  </tr>
                </table></td>
            </tr>
          </table></td>
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
    if (file_exists(G4_PATH.'/DEMO')) {
        echo "alert('데모에서는 문자메세지를 발송할 수 없습니다.');";
        echo "return;";
    }


    if ($default['de_sms_use'] == "") {
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

<? } else { ?>

<section class="cbox">
    <h2>SMS 문자전송 서비스를 사용할 수 없습니다.</h2>
    <p>
        SMS 를 사용하지 않고 있기 때문에, 문자 전송을 할 수 없습니다.<br>
        SMS 사용 설정은 <a href="./configform.php#frm_sms" class="btn_frmline">쇼핑몰관리 &gt; 쇼핑몰설정 &gt; SMS설정</a> 에서 하실 수 있습니다.
    </p>
</section>

<? } ?>


<?
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>
