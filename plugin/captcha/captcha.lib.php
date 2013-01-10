<?php
include_once("./_common.php");

class captcha 
{
    // 이미지크기 폭
    var $width = 80;
    // 이미지크기 높이
    var $height = 25;
    // 폰트 사이즈
    var $size = 16;
    // 폰트 기울기
    var $angle = 0;
    // 폰트 왼쪽 위치
    var $x = 5;
    // 폰트 위쪽 위치
    var $y = 21;
    // 캡챠이미지 배경색상 rgb
    var $back = array('r'=>255, 'g'=>255, 'b'=>255);
    // 글자색상 rgb
    var $text = array('r'=>0, 'g'=>0, 'b'=>0);
    // 그림자 글자색상 rgb
    var $shadow = array('r'=>128, 'g'=>128, 'b'=>128);

    var $captcha_length = 5;

    // 이미지 크기
    function set_box_size($width, $height) {
        $this->width = $width;
        $this->height = $height;
    }

    // 폰트 사이즈
    function set_size($size) {
        $this->size = $size;
    }

    // 폰트 기울기
    function set_angle($angle) {
        $this->angle = $angle;
    }

    // 폰트 위치
    function set_position($x, $y) {
        $this->x = $x;
        $this->y = $y;
    }

    function set_captcha_length($length)
    {
        $this->captcha_length = $length;
    }

    function set_back_color($r, $g, $b) {
        $this->back['r'] = $r;
        $this->back['g'] = $g;
        $this->back['b'] = $b;
    }

    function set_text_color($r, $g, $b) {
        $this->text['r'] = $r;
        $this->text['g'] = $g;
        $this->text['b'] = $b;
    }

    function set_shadow_color($r, $g, $b) {
        $this->shadow['r'] = $r;
        $this->shadow['g'] = $g;
        $this->shadow['b'] = $b;
    }

    function get_captcha_key()
    {
        $from = (int)('1'.str_repeat('0', $this->captcha_length - 1));
        $to = (int)(str_repeat('9', $this->captcha_length));
        return mt_rand($from, $to);
    }

    function run() {

        // The text to draw
        $captcha_key = $this->get_captcha_key();
        
        set_session('ss_captcha_key', $captcha_key);
        set_session('ss_captcha_cnt', 0);

        // Set the content-type
        header('Content-Type: image/gif');
        // Create the image
        $im = imagecreatetruecolor($this->width, $this->height);

        // Create some colors
        $white = imagecolorallocate($im, $this->back['r'], $this->back['g'], $this->back['b']);
        $grey = imagecolorallocate($im, $this->shadow['r'], $this->shadow['g'], $this->shadow['b']);
        $black = imagecolorallocate($im, $this->text['r'], $this->text['g'], $this->text['b']);
        imagefilledrectangle($im, 0, 0, $this->width, $this->height, $white);

        // Replace path by your own font path
        $fonts = Array();
        foreach (glob('fonts/*.ttf') as $filename) {
            $fonts[] = $filename;
        }
        $font = $fonts[mt_rand(0, count($fonts)-1)];

        $size = $this->size;
        $angle = $this->angle;
        $x = $this->x;
        $y = $this->y;

        // Add some shadow to the text
        imagettftext($im, $size, $angle, $x, $y, $grey, $font, $captcha_key);
        // Add the text
        imagettftext($im, $size, $angle, $x-1, $y-1, $black, $font, $captcha_key);
        if (mt_rand(0,1)) {
            imagettftext($im, $size, $angle, $x-2, $y-2, $white, $font, $captcha_key);
        } else if (mt_rand(0,1)) {
            imagettftext($im, $size, $angle, $x-2, $y-2, $grey, $font, $captcha_key);
        }

        // Using imagepng() results in clearer text compared with imagejpeg()
        imagegif($im);
        imagedestroy($im);
    }
}

/*
사용법 : 
$captcha = new captcha();
$captcha->set_captcha_length(mt_rand(4, 6));
$captcha->set_position(mt_rand(0, 10), mt_rand(15, 20));
$captcha->set_angle(mt_rand(-3, 3));
$captcha->set_size(mt_rand(15, 16));
$captcha->set_back_color(mt_rand(200,255), mt_rand(200,255), mt_rand(200,255));
$captcha->set_text_color(mt_rand(0,100), mt_rand(0,100), mt_rand(0,100));
$captcha->set_shadow_color(mt_rand(100,200), mt_rand(100,200), mt_rand(100,200));
$captcha->run();
*/

// 캡챠이미지는 한개만 사용 가능함.
function captcha_html($input_name, $captcha_id_suffix='')
{
    global $g4;

    // 세션생성을 한후 다음페이지에서 해당 세션이 있을때만 올바른 캡챠코드인지 비교합니다.
    set_session('ss_captcha_use', true);

    $html  = '<fieldset id="captcha'.$captcha_id_suffix.'" class="captcha">';
    $html .= '<legend class="sound_only">자동등록방지</legend>';
    //$html .= '<img src="" id="captcha" alt="자동등록방지 이미지" title="이미지를 클릭하시면 숫자가 바뀝니다.">';
    $html .= '<iframe id="captcha_iframe" name="captcha_iframe" src="'.$g4['path'].'/plugin/captcha/run.php" title="자동등록방지숫자"></iframe>';
    $html .= '<a href="'.$g4['path'].'/plugin/captcha/run.php" target="captcha_iframe">새로고침</a>';
    $html .= '<a href="'.$g4['path'].'/plugin/captcha/wav.php" id="captcha_wav">음성듣기</a>';
    $html .= '<label for="captcha_key">자동등록방지 입력</label>';
    $html .= '<input type="text" id="captcha_key" name="'.$input_name.'" class="captcha_box fieldset_input" size="10" maxlength="5" required title="자동등록방지 입력">';
    $html .= '<p class="sound_only">이미지의 숫자를 순서대로 입력하세요. 새로고침을 클릭하시면 새로운 숫자가 나타납니다.</p>';
    $html .= '</fieldset>';
    return $html;
}


function chk_captcha($input_name)
{
    if (get_session('ss_captcha_use')) {
        $key = get_session('ss_captcha_key');
        if (!($key && $key == $_POST[$input_name])) {
            set_session('ss_captcha_key', '');
            return false;
        }
    }
    return true;
}

// captcha javascript code
function captcha_js($element)
{
    return "if (!check_captcha({$element})) { return false; }";
}
?>
