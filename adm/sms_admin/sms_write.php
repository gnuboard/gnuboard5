<?php
$sub_menu = "900300";
include_once("./_common.php");

auth_check_menu($auth, $sub_menu, "r");

$wr_no = isset($_REQUEST['wr_no']) ? (int) $_REQUEST['wr_no'] : 0;
$bk_no = isset($_REQUEST['bk_no']) ? (int) $_REQUEST['bk_no'] : 0;
$fo_no = isset($_REQUEST['fo_no']) ? (int) $_REQUEST['fo_no'] : 0;

$g5['title'] = "문자 보내기";

include_once(G5_ADMIN_PATH.'/admin.head.php');
?>

<div class="local_ov01 local_ov">
    회원정보 최근 업데이트 : <?php echo isset($sms5['cf_datetime']) ? $sms5['cf_datetime'] : ''; ?>
</div>

<?php
if ($config['cf_sms_use'] == 'icode') { // 아이코드 사용
?>
<div id="sms5_send">

    <div id="send_emo">
        <h2>이모티콘 목록</h2>
        <?php include_once('./sms_write_form.php'); ?>
    </div>

    <div id="send_write">
        <form name="form_sms" id="form_sms" method="post" action="sms_write_send.php" onsubmit="return sms5_chk_send(this);"  >
        <input type="hidden" name="send_list" value="">

        <h2>보낼내용</h2>
        <div class="sms5_box write_wrap">
            <span class="box_ico"></span>
            <label for="wr_message" id="wr_message_lbl">내용</label>
            <textarea name="wr_message" id="wr_message" class="box_txt box_square" onkeyup="byte_check('wr_message', 'sms_bytes');" accesskey="m"></textarea>

            <div id="sms_byte"><span id="sms_bytes">0</span> / <span id="sms_max_bytes"><?php echo ($config['cf_sms_type'] == 'LMS' ? 90 : 80); ?></span> byte</div>

            <button type="button" id="write_sc_btn" class="write_scemo_btn">특수<br>기호</button>
            <div id="write_sc" class="write_scemo">
                <span class="scemo_ico"></span>
                <div class="scemo_list">
                    <button type="button" class="scemo_add" onclick="javascript:add('■')">■</button>
                    <button type="button" class="scemo_add" onclick="javascript:add('□')">□</button>
                    <button type="button" class="scemo_add" onclick="javascript:add('▣')">▣</button>
                    <button type="button" class="scemo_add" onclick="javascript:add('◈')">◈</button>
                    <button type="button" class="scemo_add" onclick="javascript:add('◆')">◆</button>
                    <button type="button" class="scemo_add" onclick="javascript:add('◇')">◇</button>
                    <button type="button" class="scemo_add" onclick="javascript:add('♥')">♥</button>
                    <button type="button" class="scemo_add" onclick="javascript:add('♡')">♡</button>
                    <button type="button" class="scemo_add" onclick="javascript:add('●')">●</button>
                    <button type="button" class="scemo_add" onclick="javascript:add('○')">○</button>
                    <button type="button" class="scemo_add" onclick="javascript:add('▲')">▲</button>
                    <button type="button" class="scemo_add" onclick="javascript:add('▼')">▼</button>
                    <button type="button" class="scemo_add" onclick="javascript:add('▶')">▶</button>
                    <button type="button" class="scemo_add" onclick="javascript:add('▷')">▷</button>
                    <button type="button" class="scemo_add" onclick="javascript:add('◀')">◀</button>
                    <button type="button" class="scemo_add" onclick="javascript:add('◁')">◁</button>
                    <button type="button" class="scemo_add" onclick="javascript:add('☎')">☎</button>
                    <button type="button" class="scemo_add" onclick="javascript:add('☏')">☏</button>
                    <button type="button" class="scemo_add" onclick="javascript:add('♠')">♠</button>
                    <button type="button" class="scemo_add" onclick="javascript:add('♤')">♤</button>
                    <button type="button" class="scemo_add" onclick="javascript:add('♣')">♣</button>
                    <button type="button" class="scemo_add" onclick="javascript:add('♧')">♧</button>
                    <button type="button" class="scemo_add" onclick="javascript:add('★')">★</button>
                    <button type="button" class="scemo_add" onclick="javascript:add('☆')">☆</button>
                    <button type="button" class="scemo_add" onclick="javascript:add('☞')">☞</button>
                    <button type="button" class="scemo_add" onclick="javascript:add('☜')">☜</button>
                    <button type="button" class="scemo_add" onclick="javascript:add('▒')">▒</button>
                    <button type="button" class="scemo_add" onclick="javascript:add('⊙')">⊙</button>
                    <button type="button" class="scemo_add" onclick="javascript:add('㈜')">㈜</button>
                    <button type="button" class="scemo_add" onclick="javascript:add('№')">№</button>
                    <button type="button" class="scemo_add" onclick="javascript:add('㉿')">㉿</button>
                    <button type="button" class="scemo_add" onclick="javascript:add('♨')">♨</button>
                    <button type="button" class="scemo_add" onclick="javascript:add('™')">™</button>
                    <button type="button" class="scemo_add" onclick="javascript:add('℡')">℡</button>
                    <button type="button" class="scemo_add" onclick="javascript:add('∑')">∑</button>
                    <button type="button" class="scemo_add" onclick="javascript:add('∏')">∏</button>
                    <button type="button" class="scemo_add" onclick="javascript:add('♬')">♬</button>
                    <button type="button" class="scemo_add" onclick="javascript:add('♪')">♪</button>
                    <button type="button" class="scemo_add" onclick="javascript:add('♩')">♩</button>
                    <button type="button" class="scemo_add" onclick="javascript:add('♭')">♭</button>
                </div>
                <div class="scemo_cls"><button type="button" class="scemo_cls_btn">닫기</button></div>
            </div>
            <button type="button" id="write_emo_btn" class="write_scemo_btn">이모<br>티콘</button>
            <div id="write_emo" class="write_scemo">
                <span class="scemo_ico"></span>
                <div class="scemo_list">
                    <button type="button" class="scemo_add" onclick="javascript:add('*^^*')">*^^*</button>
                    <button type="button" class="scemo_add" onclick="javascript:add('♡.♡')">♡.♡</button>
                    <button type="button" class="scemo_add" onclick="javascript:add('@_@')">@_@</button>
                    <button type="button" class="scemo_add" onclick="javascript:add('☞_☜')">☞_☜</button>
                    <button type="button" class="scemo_add" onclick="javascript:add('ㅠ ㅠ')">ㅠ ㅠ</button>
                    <button type="button" class="scemo_add" onclick="javascript:add('Θ.Θ')">Θ.Θ</button>
                    <button type="button" class="scemo_add" onclick="javascript:add('^_~♥')">^_~♥</button>
                    <button type="button" class="scemo_add" onclick="javascript:add('~o~')">~o~</button>
                    <button type="button" class="scemo_add" onclick="javascript:add('★.★')">★.★</button>
                    <button type="button" class="scemo_add" onclick="javascript:add('(!.!)')">(!.!)</button>
                    <button type="button" class="scemo_add" onclick="javascript:add('⊙.⊙')">⊙.⊙</button>
                    <button type="button" class="scemo_add" onclick="javascript:add('q.p')">q.p</button>
                    <button type="button" class="scemo_add emo_long" onclick="javascript:add('┏( \'\')┛')">┏( \'\')┛</button>
                    <button type="button" class="scemo_add emo_long" onclick="javascript:add('@)-)--')">@)-)--')</button>
                    <button type="button" class="scemo_add emo_long" onclick="javascript:add('↖(^-^)↗')">↖(^-^)↗</button>
                    <button type="button" class="scemo_add emo_long" onclick="javascript:add('(*^-^*)')">(*^-^*)</button>
                </div>
                <div class="scemo_cls"><button type="button" class="scemo_cls_btn">닫기</button></div>
            </div>

        </div>

        <div id="write_preset">
            {이름} : 받는사람 이름
        </div>

        <div id="write_reply">
            <label for="wr_reply">회신<strong class="sound_only"> 필수</strong></label>
            <input type="text" name="wr_reply" value="<?php echo isset($sms5['cf_phone']) ? get_sanitize_input($sms5['cf_phone']) : ''; ?>" id="wr_reply" required class="frm_input required" size="17" maxlength="20" readonly="readonly">
        </div>

        <div id="write_recv" class="write_inner">
            <h2>받는사람</h2>
            <button type="button" class="write_floater write_floater_btn" onclick="hp_list_del()">선택삭제</button>

            <label for="hp_list" class="sound_only">받는사람들</label>
            <select name="hp_list" id="hp_list" size="5"></select>

            <div id="recv_add">
                <label for="hp_name" class="sound_only">이름</label>
                <input type="text" name="hp_name" id="hp_name" class="frm_input" size="11" maxlength="20" onkeypress="if(event.keyCode==13) document.getElementById('hp_number').focus();" placeholder="이름"><br>
                <label for="hp_number" class="sound_only">번호</label>
                <input type="text" name="hp_number" id="hp_number" class="frm_input" size="11" maxlength="20" onkeypress="if(event.keyCode==13) hp_add()" placeholder="번호">
                <button type="button" onclick="hp_add()">추가</button><br>
            </div>
        </div>

        <div id="write_rsv" class="write_inner">
            <h2>예약전송</h2>

            <div class="write_floater">
                <label for="wr_booking"><span class="sound_only">예약전송 </span>사용</label>
                <input type="checkbox" name="wr_booking" id="wr_booking" onclick="booking(this.checked)">
            </div>

            <select name="wr_by" id="wr_by" disabled>
                <option value="<?php echo date('Y')?>"><?php echo date('Y')?></option>
                <option value="<?php echo date('Y')+1?>"><?php echo date('Y')+1?></option>
            </select>
            <label for="wr_by">년</label><br>
            <select name="wr_bm" id="wr_bm" disabled>
                <?php for ($i=1; $i<=12; $i++) { ?>
                <option value="<?php echo sprintf("%02d",$i)?>"<?php echo get_selected(date('m'), $i); ?>><?php echo sprintf("%02d",$i)?></option>
            <?php } ?>
            </select>
            <label for="wr_bm">월</label>
            <select name="wr_bd" id="wr_bd" disabled>
                <?php for ($i=1; $i<=31; $i++) { ?>
                <option value="<?php echo sprintf("%02d",$i)?>"<?php echo get_selected(date('d'), $i); ?>><?php echo sprintf("%02d",$i)?></option>
                <?php } ?>
            </select>
            <label for="wr_bd">일</label><br>
                <select name="wr_bh" id="wr_bh" disabled>
                <?php for ($i=0; $i<24; $i++) { ?>
                <option value="<?php echo sprintf("%02d",$i)?>"<?php echo get_selected(date('H')+1, $i); ?>><?php echo sprintf("%02d",$i)?></option>
                <?php } ?>
            </select>
            <label for="wr_bh">시</label>
            <select name="wr_bi" id="wr_bi" disabled>
                <?php for ($i=0; $i<=59; $i+=5) { ?>
                <option value="<?php echo sprintf("%02d",$i)?>"><?php echo sprintf("%02d",$i)?></option>
                <?php } ?>
            </select>
            <label for="wr_bi">분</label>
        </div>

        <div class="btn_confirm01 btn_confirm">
            <input type="submit" value="전송" class="btn_submit">
            <!-- <input type="submit" value="전송" onclick="send()"> -->
        </div>
        </form>
    </div>

    <div id="send_book">
        <h2>휴대폰번호 목록</h2>
        <div id="book_tab">
            <a href="#book_group" id="book_group" class="btn btn_02">그룹</a>
            <a href="#book_person" id="book_person" class="btn btn_02">개인</a>
            <a href="#book_level" id="book_level" class="btn btn_02">권한</a>
        </div>

        <div id="num_book"></div>

        <div id="book_desc">SMS 수신을 허용한 회원님만 출력됩니다.</div>
    </div>
</div>

<script>
function overlap_check()
{
    var hp_list = document.getElementById('hp_list');
    var hp_number = document.getElementById('hp_number');
    var list = '';

    if (hp_list.length < 1) {
        alert('받는 사람을 입력해주세요.');
        hp_number.focus();
        return;
    }

    for (i=0; i<hp_list.length; i++)
        list += hp_list.options[i].value + '/';

    (function($){
        var $form = $("#form_sms");
        $form.find("input[name='send_list']").val( list );
        var params = $form.serialize();
        $.ajax({
            url: './sms_write_overlap_check.php',
            cache:false,
            timeout : 30000,
            dataType:"html",
            data:params,
            success: function(data) {
                alert(data);
            },
            error: function (xhr, ajaxOptions, thrownError) {
                alert(xhr.status);
                alert(thrownError);
            }
        });
    })(jQuery);
}

var is_sms5_submitted = false;  //중복 submit방지
function sms5_chk_send(f)
{
    if( is_sms5_submitted == false ){
        is_sms5_submitted = true;
        var hp_list = document.getElementById('hp_list');
        var wr_message = document.getElementById('wr_message');
        var hp_number = document.getElementById('hp_number');
        var wr_reply = document.getElementById('wr_reply');
        var wr_reply_regExp = /^[0-9\-]+$/;
        var list = '';

        if (!wr_message.value) {
            alert('메세지를 입력해주세요.');
            wr_message.focus();
            is_sms5_submitted = false;
            return false;
        }
        if( !wr_reply_regExp.test(wr_reply.value) ){
            alert('회신번호 형식이 잘못 되었습니다.');
            wr_reply.focus();
            is_sms5_submitted = false;
            return false;
        }
        if (hp_list.length < 1) {
            alert('받는 사람을 입력해주세요.');
            hp_number.focus();
            is_sms5_submitted = false;
            return false;
        }

        for (i=0; i<hp_list.length; i++)
            list += hp_list.options[i].value + '/';

        w = document.body.clientWidth/2 - 200;
        h = document.body.clientHeight/2 - 100;
        //act = window.open('sms_ing.php', 'act', 'width=300, height=200, left=' + w + ', top=' + h);
        //act.focus();

        f.send_list.value = list;
        return true;
    } else {
        alert("데이터 전송중입니다.");
    }
}

function hp_add()
{
    var hp_number = document.getElementById('hp_number'),
        hp_name = document.getElementById('hp_name'),
        hp_list = document.getElementById('hp_list'),
        pattern = /^01[016789][0-9]{3,4}[0-9]{4}$/,
        pattern2 = /^01[016789]-[0-9]{3,4}-[0-9]{4}$/;

    if( !hp_number.value ){
        alert("휴대폰번호를 입력해 주세요.");
        hp_number.select();
        return;
    }

    if(!pattern.test(hp_number.value) && !pattern2.test(hp_number.value)) {
        alert("휴대폰번호 형식이 올바르지 않습니다.");
        hp_number.select();
        return;
    }

    if (!pattern2.test(hp_number.value)) {
        hp_number.value = hp_number.value.replace(new RegExp("(01[016789])([0-9]{3,4})([0-9]{4})"), "$1-$2-$3");
    }

    var item = '';
    if (trim(hp_name.value))
        item = hp_name.value + ' (' + hp_number.value + ')';
    else
        item = hp_number.value;

    var value = 'h,' + hp_name.value + ':' + hp_number.value;

    for (i=0; i<hp_list.length; i++) {
        if (hp_list[i].value == value) {
            alert('이미 같은 목록이 있습니다.');
            return;
        }
    }

    if( jQuery.inArray( hp_number.value , sms_obj.phone_number ) > -1 ){
       alert('목록에 이미 같은 휴대폰 번호가 있습니다.');
       return;
    } else {
        sms_obj.phone_number.push( hp_number.value );
    }
    hp_list.options[hp_list.length] = new Option(item, value);

    hp_number.value = '';
    hp_name.value = '';
    hp_name.select();
}

function hp_list_del()
{
    var hp_list = document.getElementById('hp_list');

    if (hp_list.selectedIndex < 0) {
        alert('삭제할 목록을 선택해주세요.');
        return;
    }

    var regExp = /(01[016789]{1}|02|0[3-9]{1}[0-9]{1})-?[0-9]{3,4}-?[0-9]{4}/,
        hp_number_option = hp_list.options[hp_list.selectedIndex],
        result = (hp_number_option.outerHTML.match(regExp));
    if( result !== null ){
        sms_obj.phone_number = sms_obj.array_remove( sms_obj.phone_number, result[0] );
    }
    hp_list.options[hp_list.selectedIndex] = null;
}

function book_change(id)
{
    var book_group  = document.getElementById('book_group');
    var book_person = document.getElementById('book_person');
    var num_book    = document.getElementById('num_book');
    var menu_group  = document.getElementById('menu_group');

    if (id == 'book_group')
    {
        book_group.style.fontWeight    = 'bold';
        book_person.style.fontWeight   = 'normal';
        book_level.style.fontWeight    = 'normal';
    }
    else if (id == 'book_person')
    {
        book_group.style.fontWeight    = 'normal';
        book_person.style.fontWeight   = 'bold';
        book_level.style.fontWeight    = 'normal';
    }
    else if (id == 'book_level')
    {
        book_group.style.fontWeight    = 'normal';
        book_person.style.fontWeight   = 'normal';
        book_level.style.fontWeight    = 'bold';
    }
}

function booking(val)
{
    if (val)
    {
        document.getElementById('wr_by').disabled = false;
        document.getElementById('wr_bm').disabled = false;
        document.getElementById('wr_bd').disabled = false;
        document.getElementById('wr_bh').disabled = false;
        document.getElementById('wr_bi').disabled = false;
    }
    else
    {
        document.getElementById('wr_by').disabled = true;
        document.getElementById('wr_bm').disabled = true;
        document.getElementById('wr_bd').disabled = true;
        document.getElementById('wr_bh').disabled = true;
        document.getElementById('wr_bi').disabled = true;
    }
}

function add(str) {
    var conts = document.getElementById('wr_message');
    var bytes = document.getElementById('sms_bytes');
    conts.focus();
    conts.value+=str;
    byte_check('wr_message', 'sms_bytes');
    return;
}

function byte_check(wr_message, sms_bytes)
{
    var conts = document.getElementById(wr_message);
    var bytes = document.getElementById(sms_bytes);
    var max_bytes = document.getElementById("sms_max_bytes");
    var lms_max_length = <?php echo G5_ICODE_LMS_MAX_LENGTH;?>

    var i = 0;
    var cnt = 0;
    var exceed = 0;
    var ch = '';

    for (i=0; i<conts.value.length; i++)
    {
        ch = conts.value.charAt(i);
        if (escape(ch).length > 4) {
            cnt += 2;
        } else {
            cnt += 1;
        }
    }

    bytes.innerHTML = cnt;

    <?php if($config['cf_sms_type'] == 'LMS') { ?>
    if(cnt > 90)
        max_bytes.innerHTML = lms_max_length;
    else
        max_bytes.innerHTML = 90;

    if (cnt > lms_max_length)
    {
        exceed = cnt - lms_max_length;
        alert('메시지 내용은 '+ lms_max_length +'바이트를 넘을수 없습니다.\n\n작성하신 메세지 내용은 '+ exceed +'byte가 초과되었습니다.\n\n초과된 부분은 자동으로 삭제됩니다.');
        var tcnt = 0;
        var xcnt = 0;
        var tmp = conts.value;
        for (i=0; i<tmp.length; i++)
        {
            ch = tmp.charAt(i);
            if (escape(ch).length > 4) {
                tcnt += 2;
            } else {
                tcnt += 1;
            }

            if (tcnt > lms_max_length) {
                tmp = tmp.substring(0,i);
                break;
            } else {
                xcnt = tcnt;
            }
        }
        conts.value = tmp;
        bytes.innerHTML = xcnt;
        return;
    }
    <?php } else { ?>
    if (cnt > 80)
    {
        exceed = cnt - 80;
        alert('메시지 내용은 80바이트를 넘을수 없습니다.\n\n작성하신 메세지 내용은 '+ exceed +'byte가 초과되었습니다.\n\n초과된 부분은 자동으로 삭제됩니다.');
        var tcnt = 0;
        var xcnt = 0;
        var tmp = conts.value;
        for (i=0; i<tmp.length; i++)
        {
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
        conts.value = tmp;
        bytes.innerHTML = xcnt;
        return;
    }
    <?php } ?>
}

<?php
if ($bk_no) {
$row = sql_fetch("select * from {$g5['sms5_book_table']} where bk_no='$bk_no'");
?>

var hp_list = document.getElementById('hp_list');
var item    = "<?php echo $row['bk_name']?> (<?php echo $row['bk_hp']?>)";
var value   = "p,<?php echo $row['bk_no']?>";

hp_list.options[hp_list.length] = new Option(item, value);

<?php } ?>

<?php
if ($fo_no) {
    $row = sql_fetch("select * from {$g5['sms5_form_table']} where fo_no='$fo_no'");
    $fo_content = str_replace(array("\r\n","\n"), "\\n", $row['fo_content']);
    echo "add(\"$fo_content\");";
}
?>

byte_check('wr_message', 'sms_bytes');
document.getElementById('wr_message').focus();
</script>

<?php

if ($wr_no)
{
    // 메세지와 회신번호
    $row = sql_fetch(" select * from {$g5['sms5_write_table']} where wr_no = '$wr_no' ");

    echo "<script>\n";
    echo "var hp_list = document.getElementById('hp_list');\n";
    //echo "add(\"$row[wr_message]\");\n";
    $wr_message = str_replace('"', '\"', $row['wr_message']);
    $wr_message = str_replace(array("\r\n","\n"), "\\n", $wr_message);
    echo "add(\"$wr_message\");\n";
    echo "document.getElementById('wr_reply').value = '{$row['wr_reply']}';\n";

    // 회원목록
    $sql = " select * from {$g5['sms5_history_table']} where wr_no = '$wr_no' and bk_no > 0 ";
    $qry = sql_query($sql);
    $tot = sql_num_rows($qry);

    if ($tot > 0) {

        $str = '재전송그룹 ('.number_format($tot).'명)';
        $val = 'p,';

        while ($row = sql_fetch_array($qry))
        {
            $val .= $row['bk_no'].',';
        }

        echo "hp_list.options[hp_list.length] = new Option('$str', '$val');\n";
    }

    // 비회원 목록
    $sql = " select * from {$g5['sms5_history_table']} where wr_no = '$wr_no' and bk_no = 0 ";
    $qry = sql_query($sql);
    $tot = sql_num_rows($qry);

    if ($tot > 0)
    {
        while ($row = sql_fetch_array($qry))
        {
            $str = "{$row['hs_name']} ({$row['hs_hp']})";
            $val = "h,{$row['hs_name']}:{$row['hs_hp']}";
            echo "hp_list.options[hp_list.length] = new Option('$str', '$val');\n";
        }
    }
    echo "</script>\n";
}
?>
<script>
$(function(){
    $(".box_txt").bind("focus keydown", function(){
        $("#wr_message_lbl").hide();
    });
    $(".write_scemo_btn").click(function(){
        $(".write_scemo").hide();
        $(this).next(".write_scemo").show();
    });
    $(".scemo_cls_btn").click(function(){
        $(".write_scemo").hide();
    });
});

var sms_obj={
    phone_number : [],
    el_box : "#num_book",
    person_is_search : false,
    level_add : function(lev, cnt){
        if (cnt == '0') {
            alert(lev + ' 레벨은 아무도 없습니다.');
            return;
        }

        var hp_list = document.getElementById('hp_list');
        var item    = "회원 권한 " + lev + " 레벨 (" + cnt + " 명)";
        var value   = 'l,' + lev;

        for (i=0; i<hp_list.length; i++) {
            if (hp_list[i].value == value) {
                alert('이미 같은 목록이 있습니다.');
                return;
            }
        }

        hp_list.options[hp_list.length] = new Option(item, value);
    },
    array_remove : function(arr, item){
        for(var i = arr.length; i--;) {
          if(arr[i] === item) {
              arr.splice(i, 1);
          }
        }
        return arr;
    },
    book_all_checked : function(chk){
        var bk_no = document.getElementsByName('bk_no');

        if (chk) {
            for (var i=0; i<bk_no.length; i++) {
                bk_no[i].checked = true;
            }
        } else {
            for (var i=0; i<bk_no.length; i++) {
                bk_no[i].checked = false;
            }
        }
    },
    person_add : function(bk_no, bk_name, bk_hp){
        var hp_list = document.getElementById('hp_list');
        var item    = bk_name + " (" + bk_hp + ")";
        var value   = 'p,' + bk_no;

        for (i=0; i<hp_list.length; i++) {
            if (hp_list[i].value == value) {
                alert('이미 같은 목록이 있습니다.');
                return;
            }
        }
        if( jQuery.inArray( bk_hp , this.phone_number ) > -1 ){
           alert('목록에 이미 같은 휴대폰 번호가 있습니다.');
           return;
        } else {
            this.phone_number.push( bk_hp );
        }
        hp_list.options[hp_list.length] = new Option(item, value);
    },
    person_multi_add : function(){
        var bk_no = document.getElementsByName('bk_no');
        var ck_no = '';
        var count = 0;

        for (i=0; i<bk_no.length; i++) {
            if (bk_no[i].checked==true) {
                count++;
                ck_no += bk_no[i].value + ',';
            }
        }

        if (!count) {
            alert('하나이상 선택해주세요.');
            return;
        }

        var hp_list = document.getElementById('hp_list');
        var item    = "개인 (" + count + " 명)";
        var value   = 'p,' + ck_no;

        for (i=0; i<hp_list.length; i++) {
            if (hp_list[i].value == value) {
                alert('이미 같은 목록이 있습니다.');
                return;
            }
        }

        hp_list.options[hp_list.length] = new Option(item, value);
    },
    person : function(bg_no){
        var params = { bg_no : bg_no };
        this.person_is_search = true;
        this.person_select( params, "html" );
        book_change('book_person');
    },
    group_add : function(bg_no, bg_name, bg_count){
        if (bg_count == '0') {
            alert('그룹이 비어있습니다.');
            return;
        }

        var hp_list = document.getElementById('hp_list');
        var item    = bg_name + " 그룹 (" + bg_count + " 명)";
        var value   = 'g,' + bg_no;

        for (i=0; i<hp_list.length; i++) {
            if (hp_list[i].value == value) {
                alert('이미 같은 목록이 있습니다.');
                return;
            }
        }

        hp_list.options[hp_list.length] = new Option(item, value);
    }
};
(function($){
    $("#form_sms input[type=text], #form_sms select").keypress(function(e){
        return e.keyCode != 13;
    });
    sms_obj.fn_paging = function( hash_val,total_page,$el,$search_form ){
        $el.paging({
            current:hash_val ? hash_val : 1,
            max:total_page == 0 || total_page ? total_page : 45,
            length : 5,
            liitem : 'span',
            format:'{0}',
            next:'다음',
            prev:'이전',
            sideClass:'pg_page pg_next',
            prevClass:'pg_page pg_prev',
            first:'&lt;&lt;',last:'&gt;&gt;',
            href:'#',
            itemCurrent:'pg_current',
            itemClass:'pg_page',
            appendhtml:'<span class="sound_only">페이지</span>',
            onclick:function(e,page){
                e.preventDefault();
                $search_form.find("input[name='page']").val( page );
                var params = '';
                if( sms_obj.person_is_search ){
                    params = $search_form.serialize();
                } else {
                    params = { page: page };
                }
                sms_obj.person_select( params, "html" );
            }
        });
    }
    sms_obj.person_select = function( params, type ){
        emoticon_list.loading(sms_obj.el_box, "./img/ajax-loader.gif" ); //로딩 이미지 보여줌
        $.ajax({
            url: "./ajax.sms_write_person.php",
            cache:false,
            timeout : 30000,
            dataType:type,
            data:params,
            success: function(data) {
               $(sms_obj.el_box).html(data);
               var $sms_person_form = $("#sms_person_form", sms_obj.el_box),
                   total_page = $sms_person_form.find("input[name='total_pg']").val(),
                   current_page = $sms_person_form.find("input[name='page']").val()
               sms_obj.fn_paging( current_page, total_page, $("#person_pg", sms_obj.el_box), $sms_person_form );
               $sms_person_form.bind("submit", function(e){
                   e.preventDefault();
                   sms_obj.person_is_search = true;
                   $(this).find("input[name='total_pg']").val('');
                   $(this).find("input[name='page']").val('');
                   var params = $(this).serialize();
                   sms_obj.person_select( params, "html" );
                   emoticon_list.loadingEnd(sms_obj.el_box); //로딩 이미지 지움
               });
            },
            error: function (xhr, ajaxOptions, thrownError) {
                alert(xhr.status);
                alert(thrownError);
            }
        });
    }
    sms_obj.triggerclick = function( sel ){
        $(sel).trigger("click");
    }
    $("#book_level").bind("click", function(e){
        e.preventDefault();
        book_change( $(this).attr("id") );
        emoticon_list.loading(sms_obj.el_box, "./img/ajax-loader.gif" ); //로딩 이미지 보여줌
        $.ajax({
            url: "./ajax.sms_write_level.php",
            cache:false,
            timeout : 30000,
            dataType:'json',
            success: function(HttpRequest) {
                if (HttpRequest.error) {
                    alert(HttpRequest.error);
                    return false;
                } else {
                    $(sms_obj.el_box).html(HttpRequest.html);
                }
                emoticon_list.loadingEnd(sms_obj.el_box); //로딩 이미지 지움
            },
            error: function (xhr, ajaxOptions, thrownError) {
                alert(xhr.status);
                alert(thrownError);
            }
        })
    });
    $("#book_person").bind("click", function(e){
        e.preventDefault();
        book_change( $(this).attr("id") );
        sms_obj.person_is_search = false;
        sms_obj.person_select( '','html' );
    });
    $("#book_group").bind("click", function(e){
        e.preventDefault();
        book_change( $(this).attr("id") );
        emoticon_list.loading(sms_obj.el_box, "./img/ajax-loader.gif" ); //로딩 이미지 보여줌
        $.ajax({
            url: "./ajax.sms_write_group.php",
            cache:false,
            timeout : 30000,
            dataType:'html',
            success: function(data) {
                $(sms_obj.el_box).html(data);
                emoticon_list.loadingEnd(sms_obj.el_box); //로딩 이미지 지움
            },
            error: function (xhr, ajaxOptions, thrownError) {
                alert(xhr.status);
                alert(thrownError);
            }
        })
    }).trigger("click");
})(jQuery);
</script>

<?php } else { //아이코드 사용설정이 안되어 있다면... ?>

<section>
    <h2 class="h2_frm">SMS 문자전송 서비스를 사용할 수 없습니다.</h2>
    <div class="local_desc01 local_desc">
        <p>
            SMS 를 사용하지 않고 있기 때문에, 문자 전송을 할 수 없습니다.<br>
            SMS 사용 설정은 <a href="../config_form.php#anc_cf_sms" class="btn_frmline">환경설정 &gt; 기본환경설정 &gt; SMS설정</a> 에서 SMS 사용을 아이코드로 변경해 주셔야 사용하실수 있습니다.
        </p>
    </div>
</section>

<?php } ?>

<?php
include_once(G5_ADMIN_PATH.'/admin.tail.php');