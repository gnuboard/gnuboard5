<?php
include_once("./_common.php");
include_once("$g4[path]/head.sub.php");
?>
<script src="<?="{$g4['path']}/plugin/tcaptcha/tcaptcha.js"?>"></script>

<h2>텍스트 캡챠 샘플 페이지</h2>
<br />
<br />
<br />
<form method="post" onsubmit="return form_submit(this);">
<h2>문) <span id="tcaptcha"></span></h2>
<div>
    답) 반드시 숫자로 입력하세요. <input type='text' id='user_answer' name='user_answer' size='3' maxlength='3' title='자동가입방지' required='required' />
    <input type='hidden' id='user_token' name='user_token' />
    <input type='submit' />
</div>
</form>

<script>
function form_submit(f) 
{
    if (!chk_tcaptcha(f.user_answer, f.user_token)) {
        return false;
    }
    alert("정답");
    return true;
}
</script>

<?
include_once("$g4[path]/tail.sub.php");
?>