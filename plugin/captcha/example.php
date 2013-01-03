<?php
include_once("./_common.php");
?>
<meta charset='utf-8' />
<form onsubmit='return form_submit(this)'>
<a href='javascript:;' onclick='change_captcha();'><img src='./run.php' id='captcha' border='0' alt='캡챠이미지' title='이미지를 클릭하시면 숫자가 바뀝니다.'/></a>
<input type='text' name='captcha_key'>
<input type='submit'>
</form>

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js" type="text/javascript"></script>
<script src="<?=$g4[path]?>/plugin/captcha/captcha.js" type="text/javascript"></script>
<script>
function change_captcha()
{
    document.getElementById('captcha').setAttribute('src', g4_path+'/plugin/captcha/run.php?t='+(new Date).getTime());
}

function form_submit(f)
{
    if (f.captcha_key.value == '') {
        alert('왼쪽의 숫자를 입력하세요.');
        f.captcha_key.focus();
        return false;
    }

    if (!chk_captcha(f.captcha_key)) {
        return false;
    }

    alert('축하합니다.\n\n올바로 입력 하셨습니다.');
        
    return false;
}
</script>