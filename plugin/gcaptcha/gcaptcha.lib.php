<?php
include_once("./_common.php");

class gcaptcha
{
    // 이미지크기 폭
    var $width = 70;
    // 이미지크기 높이
    var $height = 22;
    // 폰트 사이즈
    var $size = 13;
    // 폰트 기울기
    var $angle = 0;
    // 폰트 왼쪽 위치
    var $x = 5;
    // 폰트 위쪽 위치
    var $y = 18;
    // 캡챠이미지 배경색상 rgb
    var $back = array('r'=>255, 'g'=>255, 'b'=>255);
    // 글자색상 rgb
    var $text = array('r'=>0, 'g'=>0, 'b'=>0);
    // 그림자 글자색상 rgb
    var $shadow = array('r'=>128, 'g'=>128, 'b'=>128);

    var $captcha_length = 6;
    var $captcha_filename = '';

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

    function run()
    {
        global $g4;

        // The text to draw
        $captcha_key = $this->get_captcha_key();

        set_session('ss_captcha_cnt', 0);
        set_session('ss_captcha_key', $captcha_key);

        // Set the content-type
        //header('Content-Type: image/jpeg');
        // Create the image
        $im = imagecreatetruecolor($this->width, $this->height);

        // Create some colors
        $white = imagecolorallocate($im, $this->back['r'], $this->back['g'], $this->back['b']);
        $grey = imagecolorallocate($im, $this->shadow['r'], $this->shadow['g'], $this->shadow['b']);
        $black = imagecolorallocate($im, $this->text['r'], $this->text['g'], $this->text['b']);
        imagefilledrectangle($im, 0, 0, $this->width, $this->height, $white);

        // Replace path by your own font path
        $fonts = Array();
        foreach (glob(G4_GCAPTCHA_PATH.'/fonts/*.ttf') as $filename) {
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

        $this->captcha_filename = $this->get_captcha_filename();

        imagejpeg($im, G4_DATA_PATH.'/cache/'.$this->captcha_filename.'.jpg');
        imagedestroy($im);

        $this->make_mp3($this->captcha_filename);
    }

    function get_captcha_filename()
    {
        return 'gcaptcha-'.abs_ip2long().'_'.session_id();
    }

    function make_mp3($captcha_filename)
    {
        global $g4;

        $number = (string)$_SESSION['ss_captcha_key'];
        $mp3s = array();
        for($i=0;$i<strlen($number);$i++){
            $file = G4_GCAPTCHA_PATH.'/mp3/'.$number[$i].'.mp3';
            $mp3s[] = $file;
        }

        $mp3_filepath = G4_DATA_PATH.'/cache/'.$captcha_filename.'.mp3';

        $contents = '';
        foreach ($mp3s as $mp3) {
            $contents .= file_get_contents($mp3);
        }
        file_put_contents($mp3_filepath, $contents);
    }
}

/*
사용법 :
$gcaptcha = new gcaptcha();
$gcaptcha->set_captcha_length(mt_rand(4, 6));
$gcaptcha->set_position(mt_rand(0, 10), mt_rand(15, 20));
$gcaptcha->set_angle(mt_rand(-3, 3));
$gcaptcha->set_size(mt_rand(15, 16));
$gcaptcha->set_back_color(mt_rand(200,255), mt_rand(200,255), mt_rand(200,255));
$gcaptcha->set_text_color(mt_rand(0,100), mt_rand(0,100), mt_rand(0,100));
$gcaptcha->set_shadow_color(mt_rand(100,200), mt_rand(100,200), mt_rand(100,200));
$gcaptcha->run();
*/

// 캡챠이미지는 한개만 사용 가능함.
function captcha_html($class='captcha')
{
    global $g4, $gcaptcha;

    $obj = new gcaptcha();
    $obj->run();

    $rand = rand();
    $jpg_file_url = G4_DATA_URL.'/cache/'.$obj->captcha_filename.'.jpg';
    $mp3_file_url = G4_DATA_URL.'/cache/'.$obj->captcha_filename.'.mp3';

    $html .= "\n".'<script>var g4_gcaptcha_url = "'.G4_GCAPTCHA_URL.'";</script>';
    $html .= "\n".'<script src="'.G4_GCAPTCHA_URL.'/gcaptcha.js"></script>';
    $html .= '<fieldset id="captcha" class="'.$class.'">';
    $html .= '<legend class="sound_only">자동등록방지</legend>';
    if (G4_IS_MOBILE) $html .= '<audio src="'.$mp3_file_url.'?_='.$rand.'" controls></audio>';
    $html .= '<img src="'.$jpg_file_url.'?_='.$rand.'" alt="자동등록방지 숫자">';
    if (!G4_IS_MOBILE) $html .= '<a href="'.$mp3_file_url.'?_='.$rand.'" id="captcha_mp3" target="_blank"><img src="'.G4_GCAPTCHA_URL.'/img/sound.gif" alt="숫자를 음성으로 듣기"></a>';
    $html .= '<input type="text" id="captcha_key" name="captcha_key" class="captcha_box frm_input" size="6" maxlength="6" required title="자동등록방지 숫자 입력">';
    $html .= '<p class="sound_only">자동등록방지 숫자를 순서대로 입력하세요.</p>';
    $html .= '</fieldset>';
    return $html;
}


function chk_captcha()
{
    $captcha_cnt = (int)$_SESSION['ss_captcha_cnt'];
    if ($captcha_cnt > 5) return false;

    if (!trim($_POST['captcha_key'])) return false;
    if (!isset($_POST['captcha_key'])) return false;
    if ($_POST['captcha_key'] != $_SESSION['ss_captcha_key']) {
        $_SESSION['ss_captcha_cnt'] = $captcha_cnt + 1;
        return false;
    }
    return true;
}


function chk_captcha_js()
{
    return "if (!chk_captcha()) return false;";
}
?>