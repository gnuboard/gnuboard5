function chk_captcha()
{
    var captcha_key = document.getElementById("captcha_key");
    if (typeof(captcha_key) == "undefined") return true;

    var captcha_result = false;
    $.ajax({
        type: "POST",
        url: g4_gcaptcha_url+"/get.php",
        data: { 
            "captcha_key": captcha_key.value 
        },
        cache: false,
        async: false,
        success: function(result) {
            captcha_result = result;
        }
    });
    if (!captcha_result) {
        alert("자동등록방지 숫자가 틀렸습니다.");
        captcha_key.select();
        return false;
    }
    return true;
}

$(function() {
    $("#captcha").click(function(e) {
        this.setAttribute("src", g4_url+"/plugin/captcha/run.php?t="+(new Date).getTime());
        var keycode = (e.keyCode ? e.keyCode : e.which);
        // 첫 실행에서는 포커스를 주지 않음
        if (typeof(keycode) != "undefined") {
            $("#captcha_key").focus();//이미지 새로고침 후 입력박스에 포커스 : 지운아빠 2012-07-13
        }
    })
    .trigger("click");

    $("#captcha_mp3").click(function(){
        $("body").css("cursor", "wait");

        var mp3_url = this.href+"?t="+new Date().getTime();

        var html5use = false;
        var html5audio = document.createElement("audio");
        if (html5audio.canPlayType && html5audio.canPlayType("audio/mpeg")) {
            var wav = new Audio(mp3_url);
            wav.id = "mp3_audio";
            wav.autoplay = true;
            wav.controls = false;
            wav.autobuffer = false;
            wav.loop = false;
            
            if ($("#mp3_audio").length) $("#mp3_audio").remove();
            $("#captcha_mp3").after(wav);

            html5use = true;
        } 
        
        if (!html5use) {
            var object = '<object id="mp3_object" classid="clsid:22d6f312-b0f6-11d0-94ab-0080c74c7e95" height="0" width="0" style="width:0; height:0;">';
            object += '<param name="AutoStart" value="1" />';
            object += '<param name="Volume" value="0" />';
            object += '<param name="PlayCount" value="1" />';
            object += '<param name="FileName" value="' + mp3_url + '" />';
            object += '<embed id="mp3_embed" src="' + mp3_url + '" autoplay="true" hidden="true" volume="100" type="audio/x-wav" style="display:inline;" />';
            object += '</object>';
            if ($("#mp3_object").length) $("#mp3_object").remove();
            $("#captcha_mp3").after(object);
        }
        
        $("body").css("cursor", "default");
        return false;
    });
});