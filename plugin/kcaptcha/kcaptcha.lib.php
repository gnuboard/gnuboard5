<?php

# KCAPTCHA PROJECT VERSION 1.2.6

# Automatic test to tell computers and humans apart

# Copyright by Kruglov Sergei, 2006, 2007, 2008
# www.captcha.ru, www.kruglov.ru

# System requirements: PHP 4.0.6+ w/ GD

# KCAPTCHA is a free software. You can freely use it for building own site or software.
# If you use this software as a part of own sofware, you must leave copyright notices intact or add KCAPTCHA copyright notices to own.
# As a default configuration, KCAPTCHA has a small credits text at bottom of CAPTCHA image.
# You can remove it, but I would be pleased if you left it. ;)

# See kcaptcha_config.php for customization

class KCAPTCHA{

	// generates keystring and image
	function image(){
        require(dirname(__FILE__).'/kcaptcha_config.php');

		$fonts=array();
		$fontsdir_absolute=dirname(__FILE__).'/'.$fontsdir;
		if ($handle = opendir($fontsdir_absolute)) {
			while (false !== ($file = readdir($handle))) {
				if (preg_match('/\.png$/i', $file)) {
					$fonts[]=$fontsdir_absolute.'/'.$file;
				}
			}
		    closedir($handle);
		}

		$alphabet_length=strlen($alphabet);


        $font_file=$fonts[mt_rand(0, count($fonts)-1)];
        $font=imagecreatefrompng($font_file);
        imagealphablending($font, true);
        $fontfile_width=imagesx($font);
        $fontfile_height=imagesy($font)-1;
        $font_metrics=array();
        $symbol=0;
        $reading_symbol=false;

        // loading font
        for($i=0;$i<$fontfile_width && $symbol<$alphabet_length;$i++){
            $transparent = (imagecolorat($font, $i, 0) >> 24) == 127;

            if(!$reading_symbol && !$transparent){
                $font_metrics[$alphabet{$symbol}]=array('start'=>$i);
                $reading_symbol=true;
                continue;
            }

            if($reading_symbol && $transparent){
                $font_metrics[$alphabet{$symbol}]['end']=$i;
                $reading_symbol=false;
                $symbol++;
                continue;
            }
        }

        $img=imagecreatetruecolor($width, $height);
        imagealphablending($img, true);
        $white=imagecolorallocate($img, 255, 255, 255);
        $black=imagecolorallocate($img, 0, 0, 0);

        imagefilledrectangle($img, 0, 0, $width-1, $height-1, $white);

        // draw text
        $x=1;
        $odd=mt_rand(0,1);
        if($odd==0) $odd=-1;
        for($i=0;$i<$length;$i++){
            $m=$font_metrics[$this->keystring{$i}];

            $y=(($i%2)*$fluctuation_amplitude - $fluctuation_amplitude/2)*$odd
                + mt_rand(-round($fluctuation_amplitude/3), round($fluctuation_amplitude/3))
                + ($height-$fontfile_height)/2;

            if($no_spaces){
                $shift=0;
                if($i>0){
                    $shift=10000;
                    for($sy=3;$sy<$fontfile_height-10;$sy+=1){
                        for($sx=$m['start']-1;$sx<$m['end'];$sx+=1){
                            $rgb=imagecolorat($font, $sx, $sy);
                            $opacity=$rgb>>24;
                            if($opacity<127){
                                $left=$sx-$m['start']+$x;
                                $py=$sy+$y;
                                if($py>$height) break;
                                for($px=min($left,$width-1);$px>$left-200 && $px>=0;$px-=1){
                                    $color=imagecolorat($img, $px, $py) & 0xff;
                                    if($color+$opacity<170){ // 170 - threshold
                                        if($shift>$left-$px){
                                            $shift=$left-$px;
                                        }
                                        break;
                                    }
                                }
                                break;
                            }
                        }
                    }
                    if($shift==10000){
                        $shift=mt_rand(4,6);
                    }

                }
            }else{
                $shift=1;
            }
            imagecopy($img, $font, $x-$shift, $y, $m['start'], 1, $m['end']-$m['start'], $fontfile_height);
            $x+=$m['end']-$m['start']-$shift;
        }

		//noise
		$white=imagecolorallocate($font, 255, 255, 255);
		$black=imagecolorallocate($font, 0, 0, 0);
		for($i=0;$i<(($height-30)*$x)*$white_noise_density;$i++){
			imagesetpixel($img, mt_rand(0, $x-1), mt_rand(10, $height-15), $white);
		}
		for($i=0;$i<(($height-30)*$x)*$black_noise_density;$i++){
			imagesetpixel($img, mt_rand(0, $x-1), mt_rand(10, $height-15), $black);
		}

		$center=$x/2;

		// credits. To remove, see configuration file
		$img2=imagecreatetruecolor($width, $height+($show_credits?12:0));
		$foreground=imagecolorallocate($img2, $foreground_color[0], $foreground_color[1], $foreground_color[2]);
		$background=imagecolorallocate($img2, $background_color[0], $background_color[1], $background_color[2]);
		imagefilledrectangle($img2, 0, 0, $width-1, $height-1, $background);
		imagefilledrectangle($img2, 0, $height, $width-1, $height+12, $foreground);
		$credits=empty($credits)?$_SERVER['HTTP_HOST']:$credits;
		imagestring($img2, 2, $width/2-imagefontwidth(2)*strlen($credits)/2, $height-2, $credits, $background);

		// periods
		$rand1=mt_rand(750000,1200000)/10000000;
		$rand2=mt_rand(750000,1200000)/10000000;
		$rand3=mt_rand(750000,1200000)/10000000;
		$rand4=mt_rand(750000,1200000)/10000000;
		// phases
		$rand5=mt_rand(0,31415926)/10000000;
		$rand6=mt_rand(0,31415926)/10000000;
		$rand7=mt_rand(0,31415926)/10000000;
		$rand8=mt_rand(0,31415926)/10000000;
		// amplitudes
		$rand9=mt_rand(330,420)/110;
		$rand10=mt_rand(330,450)/110;

		//wave distortion

		for($x=0;$x<$width;$x++){
			for($y=0;$y<$height;$y++){
                if ($wave) {
                    $sx=$x+(sin($x*$rand1+$rand5)+sin($y*$rand3+$rand6))*$rand9-$width/2+$center+1;
                    $sy=$y+(sin($x*$rand2+$rand7)+sin($y*$rand4+$rand8))*$rand10;
                }
                else {
                    $sx=$x-$width/2+$center+1;
                    $sy=$y+(sin($x*$rand2+$rand7)+sin($y*$rand4+$rand8))*1.5;
                }

				if($sx<0 || $sy<0 || $sx>=$width-1 || $sy>=$height-1){
					continue;
				}else{
					$color=imagecolorat($img, $sx, $sy) & 0xFF;
					$color_x=imagecolorat($img, $sx+1, $sy) & 0xFF;
					$color_y=imagecolorat($img, $sx, $sy+1) & 0xFF;
					$color_xy=imagecolorat($img, $sx+1, $sy+1) & 0xFF;
				}

				if($color==255 && $color_x==255 && $color_y==255 && $color_xy==255){
					continue;
				}else if($color==0 && $color_x==0 && $color_y==0 && $color_xy==0){
					$newred=$foreground_color[0];
					$newgreen=$foreground_color[1];
					$newblue=$foreground_color[2];
				}else{
					$frsx=$sx-floor($sx);
					$frsy=$sy-floor($sy);
					$frsx1=1-$frsx;
					$frsy1=1-$frsy;

					$newcolor=(
						$color*$frsx1*$frsy1+
						$color_x*$frsx*$frsy1+
						$color_y*$frsx1*$frsy+
						$color_xy*$frsx*$frsy);

					if($newcolor>255) $newcolor=255;
					$newcolor=$newcolor/255;
					$newcolor0=1-$newcolor;

					$newred=$newcolor0*$foreground_color[0]+$newcolor*$background_color[0];
					$newgreen=$newcolor0*$foreground_color[1]+$newcolor*$background_color[1];
					$newblue=$newcolor0*$foreground_color[2]+$newcolor*$background_color[2];
				}

				imagesetpixel($img2, $x, $y, imagecolorallocate($img2, $newred, $newgreen, $newblue));
			}
		}

		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Cache-Control: no-store, no-cache, must-revalidate');
		header('Cache-Control: post-check=0, pre-check=0', FALSE);
		header('Pragma: no-cache');

		if(function_exists("imagejpeg")){
			header("Content-Type: image/jpeg");
			imagejpeg($img2, null, $jpeg_quality);
		}else if(function_exists("imagegif")){
			header("Content-Type: image/gif");
			imagegif($img2);
		}else if(function_exists("imagepng")){
			header("Content-Type: image/x-png");
			imagepng($img2);
		}
	}

	// returns keystring
	function getKeyString(){
		return $this->keystring;
	}

    function setKeyString($str){
        $this->keystring = $str;
	}
}


// 캡챠 HTML 코드 출력
function captcha_html($class="captcha")
{
    if(is_mobile())
        $class .= ' m_captcha';

    $html .= "\n".'<script>var g5_captcha_url  = "'.G5_CAPTCHA_URL.'";</script>';
    //$html .= "\n".'<script>var g5_captcha_path = "'.G5_CAPTCHA_PATH.'";</script>';
    $html .= "\n".'<script src="'.G5_CAPTCHA_URL.'/kcaptcha.js"></script>';
    $html .= "\n".'<fieldset id="captcha" class="'.$class.'">';
    $html .= "\n".'<legend><label for="captcha_key">자동등록방지</label></legend>';
    if (is_mobile()) $html .= '<audio id="captcha_audio" controls></audio>';
    //$html .= "\n".'<img src="#" alt="" id="captcha_img">';
    $html .= "\n".'<img src="'.G5_CAPTCHA_URL.'/img/dot.gif" alt="" id="captcha_img">';
    if (!is_mobile()) $html .= "\n".'<button type="button" id="captcha_mp3"><span></span>숫자음성듣기</button>';
    $html .= "\n".'<button type="button" id="captcha_reload"><span></span>새로고침</button>';
    $html .= '<input type="text" name="captcha_key" id="captcha_key" required class="captcha_box required" size="6" maxlength="6">';
    $html .= "\n".'<span id="captcha_info">자동등록방지 숫자를 순서대로 입력하세요.</span>';
    $html .= "\n".'</fieldset>';
    return $html;
}


// 캡챠 사용시 자바스크립트에서 입력된 캡챠를 검사함
function chk_captcha_js()
{
    return "if (!chk_captcha()) return false;\n";
}


// 세션에 저장된 캡챠값과 $_POST 로 넘어온 캡챠값을 비교
function chk_captcha()
{
    $captcha_count = (int)get_session('ss_captcha_count');
    if ($captcha_count > 5) {
        return false;
    }

    if (!isset($_POST['captcha_key'])) return false;
    if (!trim($_POST['captcha_key'])) return false;
    if ($_POST['captcha_key'] != get_session('ss_captcha_key')) {
        $_SESSION['ss_captcha_count'] = $captcha_count + 1;
        return false;
    }
    return true;
}
?>