<?php

# KCAPTCHA configuration file

$alphabet = "0123456789abcdefghijklmnopqrstuvwxyz"; # do not change without changing font files!

# symbols used to draw CAPTCHA
$allowed_symbols = "0123456789"; #digits
//$allowed_symbols = "0123456789abcdef"; #digits // 스캔 방지를 위하여 abcdef 추가 151029 15:00
//$allowed_symbols = "abcdeghkmnpqsuvxyz"; #digits
//$allowed_symbols = "23456789abcdeghkmnpqsuvxyz"; #alphabet without similar symbols (o=0, 1=l, i=j, t=f)

# folder with fonts
$fontsdir = 'fonts';

# CAPTCHA string length
//$length = mt_rand(5,6); # random 5 or 6
$length = 6;

# CAPTCHA image size (you do not need to change it, whis parameters is optimal)
$width = 160;
$height = 60;

# symbol's vertical fluctuation amplitude divided by 2
//$fluctuation_amplitude = 5;
//$fluctuation_amplitude = 11; // 파동&진폭 151028 14:00
$fluctuation_amplitude = 5; // 파동&진폭 원래대로 151029 15:00

#noise
//$white_noise_density=0; // no white noise
$white_noise_density=1/6;
//$black_noise_density=0; // no black noise
$black_noise_density=1/20;

# increase safety by prevention of spaces between symbols
$no_spaces = false;

# show credits
$show_credits = false; # set to false to remove credits line. Credits adds 12 pixels to image height
$credits = 'www.captcha.ru'; # if empty, HTTP_HOST will be shown

# CAPTCHA image colors (RGB, 0-255)
$foreground_color = array(0, 0, 0);
$background_color = array(255, 255, 255);
//$foreground_color = array(mt_rand(0,100), mt_rand(0,100), mt_rand(0,100));
//$background_color = array(mt_rand(200,255), mt_rand(200,255), mt_rand(200,255));

# JPEG quality of CAPTCHA image (bigger is better quality, but larger file size)
$jpeg_quality = 90;

$wave = true;
?>