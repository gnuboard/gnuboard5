/*
$(function() {
    $('#kcaptcha_image').bind('click', function() {
        $.ajax({
            type: 'POST',
            url: g4_path+'/'+g4_bbs+'/kcaptcha_session.php',
            cache: false,
            async: false,
            success: function(text) {
                $('#kcaptcha_image').attr('src', g4_path+'/'+g4_bbs+'/kcaptcha_image.php?t=' + (new Date).getTime());
            }
        });
    })
    .css('cursor', 'pointer')
    .attr('title', '글자가 잘 안보이시는 경우 클릭하시면 새로운 글자가 나옵니다.')
    .attr('width', '120')
    .attr('height', '60')
    .trigger('click');
});
*/

// jQuery에 사용자 정의 함수를 PLug-in 형식으로 추가할 수 있다.
$.extend({
    kcaptcha_load: function() {
        $('#kcaptcha_image').bind('click', function() {
            $.ajax({
                type: 'POST',
                url: g4_path+'/'+g4_bbs+'/kcaptcha_session.php',
                cache: false,
                async: false,
                success: function(text) {
                    $('#kcaptcha_image').attr('src', g4_path+'/'+g4_bbs+'/kcaptcha_image.php?t=' + (new Date).getTime());
                }
            });
        })
        .css('cursor', 'pointer')
        .attr('title', '글자가 잘 안보이시는 경우 클릭하시면 새로운 글자가 나옵니다.')
        .attr('width', '120')
        .attr('height', '60');
    },
    kcaptcha_run: function() {
        $.kcaptcha_load();
        $('#kcaptcha_image').trigger("click");
    }
});

$(function() {
    $.kcaptcha_run();
});

// 출력된 캡챠이미지의 키값과 입력한 키값이 같은지 비교한다.
function check_kcaptcha(input_key)
{
    if (typeof(input_key) != 'undefined') {
        var captcha_result = false;
        $.ajax({
            type: 'POST',
            url: g4_path+'/'+g4_bbs+'/kcaptcha_result.php',
            data: {
                'captcha_key': input_key.value 
            },
            cache: false,
            async: false,
            success: function(text) {
                captcha_result = text;
            }
        });

        if (!captcha_result) {
            alert('글자가 틀렸거나 입력 횟수가 넘었습니다.\n\n이미지를 클릭하여 다시 입력해 주십시오.');
            input_key.select();
            return false;
        }
    }
    return true;
}