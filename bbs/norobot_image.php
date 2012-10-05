<?
include_once("./_common.php");

function echo_check_image($str, $width, $height, $background_color, $text_color, $grid_color)
{
    $number = $str;

    $background_color = str_replace("#", "", $background_color);
    $text_color = str_replace("#", "", $text_color);
    $grid_color = str_replace("#", "", $grid_color);

    // WebColor -> RGB
    $BR = hexdec(substr($background_color, 0, 2)); 
    $BG = hexdec(substr($background_color, 2, 2)); 
    $BB = hexdec(substr($background_color, 4, 2)); 

    $TR = hexdec(substr($text_color, 0, 2)); 
    $TG = hexdec(substr($text_color, 2, 2)); 
    $TB = hexdec(substr($text_color, 4, 2)); 

    $GR = hexdec(substr($grid_color, 0, 2)); 
    $GG = hexdec(substr($grid_color, 2, 2)); 
    $GB = hexdec(substr($grid_color, 4, 2)); 

    $im = @imagecreate($width, $height) or die("Cannot Initialize new GD image stream");
    header ("Content-type: image/png");

    $background_color = imagecolorallocate($im, $BR, $BG, $BB); 
    $text_color = imagecolorallocate($im, $TR, $TG, $TB);
    $grid_color = imagecolorallocate($im, $GR, $GG, $GB);


    image_random_grid($im, $width, $height, 5, $grid_color);
    imagestring($im, rand(4,6), rand(5,15), rand(1,3),  $number, $text_color);
    imagepng($im);
    imagedestroy($im);
}

function image_random_grid($im, $w, $h, $s, $color)
{
    for($i=1; $i<$w/$s; $i++) {
        $tmp = rand($s-$s/3, $s+$s/3);
        imageline($im, $i*$tmp, 0, $i*$tmp, $h, $color);
    }

    for($i=1; $i<$h/$s; $i++) {
        $tmp = rand($s-$s/3, $s+$s/3);
        imageline($im, 0, $i*$tmp, $w, $i*$tmp, $color);
    }
}

//echo_check_image(rand(4,6), 65, 20, "#FF33CC", "#FFFFFF", "#FF79DE");
echo_check_image($_SESSION['ss_norobot_key'], 80, 19, "#FF33CC", "#FFFFFF", "#FF79DE");
?>