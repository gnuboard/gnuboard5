function chk_captcha(input_key)
{
    if (typeof(input_key) != 'undefined') {
        var captcha_result = false;
        $.ajax({
            type: 'POST',
            url: g4_path+'/plugin/captcha/get.php',
            data: { 'captcha_key': input_key.value },
            cache: false,
            async: false,
            success: function(result) {
                captcha_result = result;
            }
        });
        if (!captcha_result) {
            alert('숫자가 틀렸거나 입력 횟수가 넘었습니다.\n\n이미지를 클릭하여 다시 입력해 주십시오.');
            input_key.select();
            return false;
        }
    }
    return true;
}

$(function() {
    $('#captcha').click(function() {
        this.setAttribute('src', g4_path+'/plugin/captcha/run.php?t='+(new Date).getTime());
        //$('#captcha_key').focus();//이미지 새로고침 후 입력박스에 포커스 : 지운아빠 2012-07-13
        //캡챠 클릭 시에 포커스 줘야 하는데 페이지 새로 고침되면 포커스를 줘서 주석처리 : 지운아빠 2012-08-03
    })
    .css('cursor', 'pointer')
    .trigger('click');
});