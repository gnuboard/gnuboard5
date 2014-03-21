<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$sms5_skin_url.'/mobile.css">', 0);
?>

<div id="sms5_send" class="new_win">
    <h1 id="win_title">SMS 보내기</h1>

    <div id="send_write">
        <form action="<?php echo $action_url?>" onsubmit="return smssend_submit(this);" name="smsform" method="post" autocomplete="off">
        <input type="hidden" name="token" value="<?php echo $token?>">
        <input type="hidden" name="mh_hp" value="">
        <input type="hidden" name="mb_id" value="<?php echo $mb_id?>">
        <h2>보낼내용</h2>
        <div class="sms5_box">
            <span class="box_ico"></span>
            <label for="mh_message" id="wr_message_lbl">내용</label>
            <textarea name="mh_message" id="mh_message" class="box_txt" onkeyup="byte_check('mh_message', 'sms_bytes');"></textarea>
            <div id="sms_byte"><span id="sms_bytes">0</span> / 80 byte</div>
        </div>

        <div class="write_inner">
            <?php if( $mb['mb_id'] ){ //회원 아이디가 있다면 ?>
            <div id="write_rcv">
                <strong>수신회원</strong> <?php echo $mb['mb_nick']?>
            </div>
            <?php } ?>
            <div id="write_reply">
                <label for="mh_reply">회신번호</label>
                <input type="text" name="mh_reply" value="<?php echo $member['mb_hp']?>" id="mh_reply" <?php if ($is_admin != 'super') { ?> readonly<?php } ?>>
            </div>
        </div>

        <div id="write_rsv" class="write_inner">
            <h2>예약전송</h2>

            <div class="write_floater">
                <label for="booking_flag"><span class="sound_only">예약전송 </span>사용</label>
                <input type="checkbox" name="booking_flag" id="booking_flag" value="true" onclick="booking_show()" >
            </div>

            <select name="mh_by" id="mh_by" disabled>
                <option value="<?php echo date('Y')?>"><?php echo date('Y')?></option>
                <option value="<?php echo date('Y')+1?>"><?php echo date('Y')+1?></option>
            </select> 년
            <select name="mh_bm" id="mh_bm" disabled>
                <?php for ($i=1; $i<=12; $i++) { ?>
                <option value="<?php echo sprintf("%02d",$i)?>" <?php echo date('m')==$i?'selected':''?>><?php echo sprintf("%02d",$i)?></option>
                <?php } ?>
            </select> 월
            <span class="rsv_line"></span>
            <select name="mh_bd" id="mh_bd" disabled>
                <?php for ($i=1; $i<=31; $i++) { ?>
                <option value="<?php echo sprintf("%02d",$i)?>" <?php echo date('d')==$i?'selected':''?>><?php echo sprintf("%02d",$i)?></option>
                <?php } ?>
            </select> 일
            <select name="mh_bh" id="mh_bh" disabled>
                <?php for ($i=0; $i<24; $i++) { ?>
                <option value="<?php echo sprintf("%02d",$i)?>" <?php echo date('H')+1==$i?'selected':''?>><?php echo sprintf("%02d",$i)?></option>
                <?php } ?>
            </select> 시
            <select name="mh_bi" id="mh_bi" disabled>
                <?php for ($i=0; $i<=59; $i+=5) { ?>
                <option value="<?php echo sprintf("%02d",$i)?>"><?php echo sprintf("%02d",$i)?></option>
                <?php } ?>
            </select> 분
        </div>

        <div class="write_inner">
            <div id="write_sc" class="write_scemo">
                <button type="button" id="scemo_sc" class="scemo_btn">특수기호</button>
                <div class="scemo_list scemo_sc">
                    <div class="list_closer"><button type="button" class="list_closer_btn">특수기호닫기</button></div>
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
                    <div class="list_closer"><button type="button" class="list_closer_btn">특수기호닫기</button></div>
                </div>
            </div>
            <div id="write_emo" class="write_scemo">
                <button type="button" id="scemo_emo" class="scemo_btn">이모티콘</button>
                <div class="scemo_list scemo_emo">
                    <div class="list_closer"><button type="button" class="list_closer_btn">이모티콘닫기</button></div>
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
                    <button type="button" class="scemo_add emo_long" onclick="javascript:add('↖(^-^)↗')">↖(^-^)↗</button>
                    <button type="button" class="scemo_add emo_long" onclick="javascript:add('(*^-^*)')">(*^-^*)</button>
                    <button type="button" class="scemo_add emo_long" onclick="javascript:add('d(^-^)b')">d(^-^)b</button>
                    <div class="list_closer"><button type="button" class="list_closer_btn">이모티콘닫기</button></div>
                </div>
            </div>
        </div>

        <div class="win_btn">
            <input type="submit" value="전송" class="btn_submit">
            <button type="button" onclick="window.close();">창닫기</button>
        </div>
        </form>

    </div>

    <?php if( count($emoticon_group) ){ //회원에게 공개된 이모티콘 그룹이 있다면 ?>
    <div id="send_emo">
        <h2>이모티콘 목록</h2>
        <form name="emoticon_form">
        <label for="emo_sel" class="sound_only">이모티콘 그룹</label>
        <select name="fg_no" id="emo_sel">
            <option value="" <?php echo $fg_no?'':'selected'?>>전체</option>
            <?php for($i=0; $i<count($emoticon_group); $i++) {?>
            <option value="<?php echo $emoticon_group[$i]['fg_no']?>"<?php echo ($fg_no==$emoticon_group[$i]['fg_no'])?'selected':''?>><?php echo $emoticon_group[$i]['fg_name']?> (<?php echo number_format($emoticon_group[$i]['fg_count'])?>)</option>
            <?php } ?>
        </select>
        </form>

        <ul class="emo_list">
        </ul>

        <nav class="pg_wrap">
            <span class="pg" id="emoticon_pg"></span>
        </nav>

        <form name="emoticon_search" id="emoticon_search">
        <input type="hidden" name="page" id="hidden_page" >
        </form>
    </div>
    <?php } ?>

</div>

<script>
function sms_error(obj, err) {
    alert(err);
    obj.value = '';
}

function smssend_submit(f)
{
    if (!f.mh_message.value)
    {
        alert('보내실 문자를 입력하십시오.');
        f.mh_message.focus();
        return false;
    }

    if (!f.mh_reply.value)
    {
        alert('발신 번호를 입력하십시오.\n\n발신 번호는 회원정보의 휴대폰번호입니다.');
        return false;
    }

    return true;
    //f.submit();    
    //win.focus();
}

function booking_show()
{
    if (document.getElementById('booking_flag').checked) {
        document.getElementById('mh_by').disabled   = false;
        document.getElementById('mh_bm').disabled   = false;
        document.getElementById('mh_bd').disabled   = false;
        document.getElementById('mh_bh').disabled   = false;
        document.getElementById('mh_bi').disabled   = false;
    } else {
        document.getElementById('mh_by').disabled   = true;
        document.getElementById('mh_bm').disabled   = true;
        document.getElementById('mh_bd').disabled   = true;
        document.getElementById('mh_bh').disabled   = true;
        document.getElementById('mh_bi').disabled   = true;
    }
}

function add(str) {
    var conts = document.getElementById('mh_message');
    var bytes = document.getElementById('sms_bytes');
    conts.focus();
    conts.value+=str; 
    byte_check('mh_message', 'sms_bytes');
    return;
}

function byte_check(mh_message, sms_bytes)
{
    var conts = document.getElementById(mh_message);
    var bytes = document.getElementById(sms_bytes);

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
}

byte_check('mh_message', 'sms_bytes');
</script>
<script src="<?php echo G5_JS_URL?>/jquery.sms_paging.js"></script>
<script>
var emoticon_list = {
    go : function(fo_no){
        var wr_message = document.getElementById('mh_message');

        //wr_message.focus();
        wr_message.value = document.getElementById('fo_contents_' + fo_no).value;

        byte_check('mh_message', 'sms_bytes');
    }
};
(function($){
    $(".box_txt").bind("focus keydown", function(){
        $("#wr_message_lbl").hide();
    });

    var $search_form = $("form#emoticon_search");
    emoticon_list.fn_paging = function( hash_val,total_page ){
        $('#emoticon_pg').paging({
            current:hash_val ? hash_val : 1,
            max:total_page == 0 || total_page ? total_page : 45,
            length : 5,
            liitem : 'span',
            format:'{0}',
            next:'next',
            prev:'prev',
            first:'&lt;&lt;',last:'&gt;&gt;',
            href:'#',
            itemCurrent:'pg_current',
            itemClass:'pg_page',
            appendhtml:'<span class="sound_only">페이지</span>',
            onclick:function(e,page){
                e.preventDefault();
                $("#hidden_page").val( page );
                var params = $($search_form).serialize();
                emoticon_list.select_page( params, "json" );
            }
        });
    }
    emoticon_list.loading = function( el, src ){
        if( !el || !src) return;
        $(el).append("<span class='tmp_loading'><img src='"+src+"' title='loading...' ></span>");
    }
    emoticon_list.loadingEnd = function( el ){
        $(".tmp_loading", $(el)).remove();
    }
    emoticon_list.select_page = function( params, type ){
        if( !type ){
            type = "json";
        }
        emoticon_list.loading(".emo_list", "<?php echo $sms5_skin_url?>/img/ajax-loader.gif" ); //로딩 이미지 보여줌
        $.ajax({
            url: "./ajax.sms_emoticon.php",
            cache:false,
            timeout : 30000,
            dataType:type,
            data:params,
            success: function(HttpRequest) {
                if( type == "json" ){
                    if (HttpRequest.error) {
                        alert(HttpRequest.error);
                        return false;
                    } else {
                        var $emoticon_box = $(".emo_list");
                        var list_text = "";
                        $.each( HttpRequest.list_text , function(num) {
                            var list_data = HttpRequest.list_text[num];
                            list_text = list_text + "<li class=\"screen_list\"><div class=\"sms5_box\"><span class=\"box_ico\"></span><textarea class=\"sms_textarea box_txt box_square\" readonly onclick=\"emoticon_list.go("+list_data.fo_no+")\">"+list_data.fo_content+"</textarea><textarea id=\"fo_contents_"+list_data.fo_no+"\" style=\"display:none; width:0; height:0\">"+list_data.fo_content+"</textarea><strong class=\"emo_tit\">"+list_data.fo_name+"</strong></div></li>";
                        });
                        if( !list_text ){
                            list_text = "<li>데이터가 없습니다.</li>";
                        }
                        $emoticon_box.html( list_text );
                        emoticon_list.fn_paging( HttpRequest.page, HttpRequest.total_page );
                        $("#hidden_page").val( HttpRequest.page );
                    }
                }
                emoticon_list.loadingEnd(".emo_list"); //로딩 이미지 지움
            }
        });
    }

    $("#emo_sel").bind("change", function(e){
        var params = { fg_no : $(this).val() };
        $search_form[0].reset();
        $("#hidden_fg_no").val( $(this).val() );
        emoticon_list.select_page( params, "json" );
    });
    $search_form.submit(function(e){
        e.preventDefault();
        var $form = $(this),
            params = $(this).serialize();
        emoticon_list.select_page( params, "json" );
    });
    if( $("#emo_sel").length ){
        $("#emo_sel").trigger("change");
    }

    $(".scemo_btn").click(function(){
        var scemoid = $(this).attr("id");
        $(this).hide();
        $(".scemo_list").hide();
        $("."+scemoid).show();
    });
    $(".list_closer_btn").click(function(){
        $(".scemo_btn").show();
        $(".scemo_list").hide();
    });
})(jQuery);
</script>