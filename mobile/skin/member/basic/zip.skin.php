<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$member_skin_url.'/style.css">', 0);
?>

<!-- 우편번호 찾기 시작 { -->
<script src="<?php echo G5_JS_URL; ?>/zip.js"></script>

<div id="post_code" class="new_win mbskin">
    <h1 id="win_title"><?php echo $g5['title'] ?></h1>

    <div id="code_sel">
        <input type="radio" name="sch_sel" id="sch_sel_1" value="search" checked="checked">
        <label for="sch_sel_1">주소검색</label>
        <input type="radio" name="sch_sel" id="sch_sel_0" value="direct">
        <label for="sch_sel_0">직접입력</label>
    </div>

    <div id="zip_search_frm" class="zip_frm">
        <p>
            시도 및 시군구 선택없이 도로명, 읍/면/동, 건물명 등으로 검색하실 수 있습니다.<br>
            만약 검색결과에 찾으시는 주소가 없을 때는 시도와 시군구를 선택하신 후 다시 검색해 주십시오.<br>
            (검색결과는 최대 1,000건만 표시됩니다.)
        </p>

        <form name="fzip" method="get" onsubmit="search_call(); return false;" autocomplete="off">
        <!-- 검색어 입력 시작 { -->
        <div id="code_sch">
            <label for="sido" class="sound_only">시도선택</label>
            <select name="sido" id="sido">
                <option value="">- 시도 선택 -</option>
            </select>
            <label for="gugun" class="sound_only">시군구</label>
            <select name="gugun" id="gugun">
                <option value="">- 시군구 선택 -</option>
            </select>
            <div id="sch_q">
                <label for="q" class="sound_only">검색어</label>
                <input type="text" name="q" value="" id="q" required  class="required frm_input">
                <input type="submit" value="검색" class="btn_submit">
            </div>
        </div>
        <!-- } 검색어 입력 끝 -->
        </form>

        <div id="result"><span id="result_b4">검색어를 입력해주세요.</span></div>
    </div>
    <div id="zip_direct_frm" class="zip_frm">
        <p>직접 주소를 입력하실 경우 우편번호와 기본주소는 반드시 입력하셔야 합니다.</p>
        <form name="fzip2">
            우편번호
            <label for="frm_zip1" class="sound_only">우편번호앞자리</label>
            <input type="text" name="frm_zip1" id="frm_zip1" class="required frm_input" size="3" maxlength="3"> -
            <label for="frm_zip2" class="sound_only">우편번호뒷자리</label>
            <input type="text" name="frm_zip2" id="frm_zip2" class="required frm_input" size="3" maxlength="3"><br>
            <label for="frm_addr1" class="sound_only">기본주소</label>
            <input type="text" name="frm_addr1" placeholder="기본주소" id="frm_addr1" class="required frm_input frm_addr"><br>
            <label for="frm_addr2" class="sound_only">상세주소</label>
            <input type="text" name="frm_addr2" placeholder="상세주소" id="frm_addr2" class="frm_input frm_addr"><br>
            <label for="frm_addr3" class="sound_only">참고항목</label>
            <input type="text" name="frm_addr3" placeholder="참고항목" id="frm_addr3" class="frm_input frm_addr"><br>
            <label for="frm_jibeon" class="sound_only">지번주소</label>
            <input type="text" name="frm_jibeon" placeholder="지번주소" id="frm_jibeon" class="frm_input frm_addr">

            <div id="sch_dq">
                <button type="button" id="put_addr" class="btn_submit">주소입력</button>
            </div>
        </form>
    </div>

    <div class="win_btn">
        <button type="button" onclick="window.close();">창닫기</button>
    </div>

</div>

<script>
$(function() {
    $("input[name='sch_sel']").click(function() {
        var val = $(this).val();

        $(".zip_frm").hide();
        $("#zip_"+val+"_frm").show();
    });

    var msg_alert = true;
    $("input#q").bind("focusin", function() {
        if(!msg_alert) {
            return false;
        } else {
            alert("정확하고 빠른 검색을 위해 아래의 예시처럼 입력해 주세요.\n\n입력예1) 강남대로37길 24-6\n입력예2) 서초동 1362-19\n입력예3) 서초2동 1362-19");
            msg_alert = false;
        }
    });

    $("#put_addr").click(function() {
        var zip1 = $.trim($("#frm_zip1").val());
        var zip2 = $.trim($("#frm_zip2").val());
        var addr1 = $.trim($("#frm_addr1").val());
        var addr2 = $.trim($("#frm_addr2").val());
        var addr3 = $.trim($("#frm_addr3").val());
        var jibeon = $.trim($("#frm_jibeon").val());

        if(zip1.length < 1) {
            alert("우편번호 앞자리를 입력해 주십시오.");
            return false;
        }

        if(zip2.length < 1) {
            alert("우편번호 뒷자리를 입력해 주십시오.");
            return false;
        }

        if(addr1.length < 1) {
            alert("기본주소를 입력해 주십시오.");
            return false;
        }

        put_data2(zip1, zip2, addr1, addr2, addr3, jibeon);
    });
});

function put_data(zip1, zip2, addr1, addr3, jibeon)
{
    var of = window.opener.document.<?php echo $frm_name; ?>;

    of.<?php echo $frm_zip1; ?>.value = zip1;
    of.<?php echo $frm_zip2; ?>.value = zip2;
    of.<?php echo $frm_addr1; ?>.value = addr1;
    of.<?php echo $frm_addr2; ?>.value = "";
    of.<?php echo $frm_addr3; ?>.value = addr3;

    window.opener.$("#<?php echo $frm_jibeon; ?>").text("지번주소 : "+jibeon);

    if(of.<?php echo $frm_jibeon; ?> !== undefined)
        of.<?php echo $frm_jibeon; ?>.value = jibeon;

    of.<?php echo $frm_addr2; ?>.focus();

    window.close();
}

function put_data2(zip1, zip2, addr1, addr2, addr3, jibeon)
{
    var of = window.opener.document.<?php echo $frm_name; ?>;

    of.<?php echo $frm_zip1; ?>.value = zip1;
    of.<?php echo $frm_zip2; ?>.value = zip2;
    of.<?php echo $frm_addr1; ?>.value = addr1;
    of.<?php echo $frm_addr2; ?>.value = addr2;
    of.<?php echo $frm_addr3; ?>.value = addr3;

    window.opener.$("#<?php echo $frm_jibeon; ?>").text("지번주소 : "+jibeon);
    if(of.<?php echo $frm_jibeon; ?> !== undefined)
        of.<?php echo $frm_jibeon; ?>.value = jibeon;

    window.close();
}
</script>
<!-- } 우편번호 찾기 끝 -->