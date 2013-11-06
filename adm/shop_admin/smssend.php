<?php
$sub_menu = '500200';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g5['title'] = 'SMS 문자전송';
include_once (G5_ADMIN_PATH.'/admin.head.php');

// 발신자번호
$send_number = preg_replace("/[^0-9]/", "", $default['de_admin_company_tel']);
?>

<?php
if ($config['cf_sms_use'] == 'icode') { // 아이코드 사용
?>
<div id="sms_send">
    <h2 class="h2_frm">SMS 문자전송 내용 입력</h2>

    <form action="./smssendicode.php" name="smsform" id="sms_frm" method="post" onsubmit="return smsform_check(this);" autocomplete="off">

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption><?php echo $g5['title']; ?> 내용 입력</caption>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row"><label for="send_number">발신번호</label></th>
            <td>
                <?php echo help('SMS 발신자 번호를 입력하세요.'); ?>
                <input name="send_number" type="text" value="<?php echo $send_number; ?>" id="send_number" class="frm_input">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="receive_number">수신번호</th>
            <td>
                <?php echo help('여러명에게 보내실 때는 전화번호를 엔터로 구분하세요.'); ?>
                <textarea name="receive_number" id="receive_number" onkeyup="addressee_count();"></textarea>
                <div><span>총 수신인 <strong id="sms_addressee">0</strong>명</span></div>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="sms_contents">문자내용</label></th>
            <td>
                <?php echo help("주의! 80 bytes 까지만 전송됩니다.\n영문 한글자 : 1byte , 한글 한글자 : 2bytes , 특수문자의 경우 1 또는 2 bytes 입니다."); ?>
                <textarea name="sms_contents" id="sms_contents" onkeyup="byte_check();"></textarea>
                <div id="bytes">0 / 80 바이트</div>
            </td>
        </tr>
        <tr>
            <th scope="row">예약발송</th>
            <td>
                <input type="checkbox" name="reserved_flag" value="true" id="reserved_flag">
                <label for="reserved_flag">예약발송 사용</label>
                <label for="reserved_year" class="sound_only">연도</label>
                <select name="reserved_year" id="reserved_year">
                    <?php
                    $yy = date("Y");
                    for ($i=$yy; $i<=$yy+1; $i++) {
                        echo '<option value="'.$i.'">'.substr($i,-2).'</option>';
                    }
                    ?>
                </select> 년
                <label for="reserved_month" class="sound_only">월</label>
                <select name="reserved_month" id="reserved_month">
                    <?php
                    $mm = date("n");
                    for ($i=1; $i<=12; $i++) {
                        echo '<option value="'.$i.'">'.$i.'</option>';
                    }
                    ?>
                </select> 월
                <label for="reserved_day" class="sound_only">일</label>
                <select name="reserved_day" id="reserved_day">
                    <?php
                    $dd = date("j");
                    for ($i=1; $i<=31; $i++) {
                        echo '<option value="'.$i.'">'.$i.'</option>';
                    }
                    ?>
                </select> 일
                <label for="reserved_hour" class="sound_only">시</label>
                <select name="reserved_hour" id="reserved_hour">
                    <?php
                    for ($i=1; $i<=24; $i++) {
                        echo '<option value="'.$i.'">'.$i.'</option>';
                    }
                    ?>
                </select> 시
                <label for="reserved_minute" class="sound_only">분</label>
                <select name="reserved_minute" id="reserved_minute">
                    <?php
                    for ($i=1; $i<=60; $i++) {
                        echo '<option value="'.$i.'">'.$i.'</option>';
                    }
                    ?>
                </select> 분
            </td>
        </tr>
        </tbody>
        </table>
    </div>

    <div class="local_desc01 local_desc">
        <p>예약발송 기능을 이용하시면, 예약된 시간에 맞춰 SMS 문자를 일괄발송할 수 있습니다.</p>
    </div>

    <div class="btn_confirm01 btn_confirm">
        <input type="submit" value="전송" class="btn_submit">
    </div>

    </form>

    <div id="sms_sm">
        <span id="sms_sm_text">문자내용을 입력해 주세요</span>
        <p>이 이미지는 이해를 돕기 위한 이미지이므로,<br>실제 발송 시 화면과 다를 수 있습니다.</p>
    </div>
</div>
</form>

<script>
function byte_check()
{
    var i = 0;
    var cnt = 0;
    var exceed = 0;
    var ch = "";
    var cont = document.smsform.sms_contents;
    var bytes = document.getElementById("bytes");
    var disp = document.getElementById("sms_sm_text");
    var disp_str = "";

    for (i=0; i<cont.value.length; i++) {
        ch = cont.value.charAt(i);
        if (escape(ch).length > 4) {
            cnt += 2;
        } else {
            cnt += 1;
        }
    }

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
        bytes.innerHTML = xcnt + ' / 80 bytes';
        return;
    }

    if(cnt == 0)
        disp_str = "문자내용을 입력해 주세요";
    else
        disp_str = cont.value;

    disp.innerHTML = disp_str;
}

function addressee_count()
{
    if(window.event.keyCode == 13) {
        var count = 0;
        var number = document.smsform.receive_number.value;
        var tel = "";
        number = number.replace(/\s+$/g, "");
        var list = number.split("\n");
        for(i=0; i<list.length; i++) {
            tel = list[i].replace(/[^0-9]/g, "");
            if(tel.length)
                count++;
        }
    } else {
        return;
    }

    document.getElementById("sms_addressee").innerHTML = number_format(String(count));
}

function smsform_check(f)
{
    <?php
    if (file_exists(G5_PATH.'/DEMO')) {
        echo "alert('데모에서는 문자메세지를 발송할 수 없습니다.');";
        echo "return false;";
    }

    if ($config['cf_sms_use'] == "") {
        echo "alert('우선 SMS 환경을 설정하여 주십시오.');";
        echo "return false;";
    }
    ?>

    var count = 0;
    var number = f.receive_number.value;
    if(number == "") {
        alert("수신번호를 입력하십시오.");
        f.receive_number.focus();
        return false;
    }
    var tel = "";
    number = number.replace(/\s+$/g, "");
    var list = number.split("\n");
    for(i=0; i<list.length; i++) {
        tel = list[i].replace(/[^0-9]/g, "");
        if(tel.length)
            count++;
    }
    if(count == 0) {
        alert("수신번호를 올바르게 입력하십시오.");
        f.receive_number.focus();
        return false;
    }

    if (f.sms_contents.value == "") {
        alert("문자내용을 입력하십시오");
        f.sms_contents.focus();
        return false;
    }

    return true;
}
</script>

<?php } else { ?>

<section>
    <h2 class="h2_frm">SMS 문자전송 서비스를 사용할 수 없습니다.</h2>
    <div class="local_desc01 local_desc">
        <p>
            SMS 를 사용하지 않고 있기 때문에, 문자 전송을 할 수 없습니다.<br>
            SMS 사용 설정은 <a href="./configform.php#anc_scf_sms" class="btn_frmline">쇼핑몰관리 &gt; 쇼핑몰설정 &gt; SMS설정</a> 에서 하실 수 있습니다.
        </p>
    </div>
</section>

<?php } ?>


<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
