<?
// 원본 이미지를 넘기면 비율에 따라 썸네일 이미지를 생성함
// 가로, 세로, 파일경로, 생성경로, true
function createThumb($imgWidth, $imgHeight, $imgSource, $imgThumb='', $iscut=false)
{
    if (!$imgThumb)
        $imgThumb = $imgSource;

    $size = getimagesize($imgSource);

    if ($size[2] == 1) 
        $source = imagecreatefromgif($imgSource);
    else if ($size[2] == 2) 
        $source = imagecreatefromjpeg($imgSource);
    else if ($size[2] == 3) 
        $source = imagecreatefrompng($imgSource);
    else 
        continue;

    $rate = $imgWidth / $size[0];
    $height = (int)($size[1] * $rate);

    if ($height < $imgHeight) {

        $target = @imagecreatetruecolor($imgWidth, $height);

    } else {

        $target = @imagecreatetruecolor($imgWidth, $imgHeight);

    }

    @imagecopyresampled($target, $source, 0, 0, 0, 0, $imgWidth, $height, $size[0], $size[1]);
    @imagejpeg($target, $imgThumb, 100);
    @chmod($imgThumb, 0606); // 추후 삭제를 위하여 파일모드 변경
}

// 프로그램 : 불당썸 2.0.x
// 개 발 자 : 아빠불당 (echo4me@gmail.com)
// 라이센스 : 프로그램(이하 불당썸)의 라이센스는 GPL이며, 이프로그램을 발췌 또는 개작하여 판매하는 것은 허용하지 않습니다.
//            불당썸의 배포는 sir.co.kr과 opencode.co.kr에서만 할 수 있습니다.

// $file_name   : 파일명
// $width       : 썸네일의 폭
// $height      : 썸네일의 높이 (지정하지 않으면 썸네일의 넓이를 사용)
//                * $width, $height에 모두 값이 없으면, 이미지 사이즈 그대로 thumb을 생성
// $is_create   : 썸네일이 이미 있을 때, 새로 생성할지 여부를 결정
// $is_crop     : 세로 높이가 $height를 넘을 때 crop 할 것인지를 결정
//                0 : crop 하지 않습니다
//                1 : 기본 crop
//                2 : 중간을 기준으로 crop
// $quality     : 썸네일의 quality (jpeg, png에만 해당하며, gif에는 해당 없슴)
// $small_thumb : 1 (true)이면, 이미지가 썸네일의 폭/높이보다 작을 때에도 썸을 생성
//                2이면, 이미지가 썸네일의 폭/높이보다 작을 때 확대된 썸을 생성
// $watermark   : 워터마크 출력에 대한 설정 
//                $watermark[][filename] - 워터마크 파일명
//                $watermark[location] - center, top, top_left, top_right, bottom, bottom_left, bottom_right
//                $watermark[x],$watermark[y] - location에서의 offset
// $filter      : php imagefilter, http://kr.php.net/imagefilter
//                $filter[type], [arg1] ... [arg4]
// $noimg       : $noimg(이미지파일)
// $thumb_type  : 저장할 썸네일의 형식 (jpg/gif/png. 나머지는 원본대로)
/*
thumbnail의 if 로직입니다. 구조화 되지 않고 너무 많은 if를 써버렸습니다. ㅠ..ㅠ...

$width에 값이 있으면
    - $height에 값이 있으면
       - $width > 이미지크기
           - $height > 이미지크기 : 이미지 크기대로 썸을 생성
           - else
                - $is_crop : 크롭
                - else : 비율대로 썸을 생성
       - else 
          $ratio로 $height를 구해서, 
          - $height > $tmp_y : 비율대로 썸 생성 (높이가 좀 부족 합니다) <-- 이부분에서 높이를 맞추고 넓기를 crop하자는 의견도 있어요
          - else : 이미지 비율로 조정한 후 높이를 crop
    - $height에 값이 없으면 (crop 하지 않습니다)
       - $width가 이미지 크기보다 더 크면 : 이미지 크기대로 썸을 생성
       - else : 비율대로 썸을 생성

$width에 값이 없으면 (높이로만 정렬하는 갤러리의 경우)
    - $height가 이미지 크기보다 더 크면 : 이미지 크기대로 썸을 생성
    - else
        - $is_crop : crop
        - else : 비율대로 썸을 생성 
*/
function thumbnail($file_name, $width=0, $height=0, $is_create=false, $is_crop=2, $quality=90, $small_thumb=1, $watermark="", $filter="", $noimg="", $thumb_type="") 
{
    //if (!$file_name)
    //    return;

    // memory limit 설정 변경
    @ini_set("memory_limit", -1);

    // 썸네일 디렉토리
    $real_dir = dirname($_SERVER['DOCUMENT_ROOT'] . "/nothing");
    $dir = dirname(file_path($file_name));
    $file = basename($file_name);

    $thumb_dir = $dir . "/thumb";

    // 썸네일을 저장할 디렉토리
    $thumb_path = $thumb_dir . "/" . $width . "x" . $height . "_" . $quality;

    if (!file_exists($thumb_dir)) {
        @mkdir($thumb_dir, 0707);
        @chmod($thumb_dir, 0707);
    }

    if (!file_exists($thumb_path)) {
        @mkdir($thumb_path, 0707);
        @chmod($thumb_path, 0707);
    }

    $source_file = $dir . "/" . $file;

    $size = @getimagesize($source_file);
    $size_org = $size;

    // animated gif에 대해서 썸을 만들고 싶으면 코멘트를 풀어주세요. 아래코드는 cpu와 disk access를 크게 유발할 수 있습니다
    //if ($size[2] == IMG_GIF && is_ani_gif($file_name)) return;

    // 이미지 파일이 없는 경우
    if (!$size[0]) {

        // $nomimg에 설정이 없으면 빈 이미지 파일을 생성
        if ($noimg)
            return $noimg;
        else
        {
            if (!$width)
                $width = 30;
            if (!$height) 
                $height = $width;
            $thumb_file = $thumb_dir . "/" . $width . "x" . $height . "_noimg.gif";

            if (@file_exists($thumb_file))
                ;
            else
            {
                $target = imagecreate($width, $height);
                
                $bg_color = imagecolorallocate($target, 250, 250, 250);
                $font_color = imagecolorallocate($target, 0, 0, 0);
                $font_size = 12;
                $ttf = "$real_dir/img/han.ttf";
                $text = "no image...";
                $size = imagettfbbox($font_size, 0, $ttf, $text);
                $xsize = abs($size[0]) + abs($size[2])+($padding*2);
                $ysize = abs($size[5]) + abs($size[1])+($padding*2);
                $xloc = $width/2-$xsize/2;
                $yloc = $height/2-$ysize/2;
                imagefttext($target, $font_size, 0, $xloc, $yloc, $font_color, $ttf, $text);
                //imagecopy($target, $target, 0, 0, 0, 0, $width, $height);
                imagegif($target, $thumb_file, $quality);
                @chmod($thumb_file, 0606); // 추후 삭제를 위하여 파일모드 변경
            }
            return str_replace($real_dir, "", $thumb_file);
        }
    }

    $thumb_file = $thumb_path . "/" . $file;

    // 썸파일이 있으면서 소스파일보다 생성 날짜가 최근일 때
    if (@file_exists($thumb_file)) {
        $thumb_time = @filemtime($thumb_file);
        $source_time = @filemtime($source_file);
        if ($is_create == false && $source_time < $thumb_time) {
            return str_replace($real_dir, "", $thumb_file);
        }
    }

    // $width, $height 값이 모두 없는 경우는 현재 사이즈 그대로 thumb을 생성
    if (!$width && !$height)
        $width = $size[0];

    // 작은 이미지의 경우에도 썸네일을 생성하는 옵션이 없고, 원본 이미지의 size가 thumb보다 작으면 썸네일을 만들지 않는다 (높이가 지정되지 않으면 pass~!)
    if (!$small_thumb && $width >= $size[0] && $height && $height >= $size[1])
        return str_replace($real_dir, "", $source_file);

    $is_imagecopyresampled = false;
    $is_large = false;

    if ($size[2] == 1)
        $source = imagecreatefromgif($source_file);
    else if ($size[2] == 2) {
        // php.net에서 - As of PHP 5.1.3, if you are dealing with corrupted JPEG images 
        //               you should set the 'gd.jpeg_ignore_warning' directive to 1 to ignore warnings that could mess up your code.
        // 어지간한 경고는 무시하는데, 메모리 부족이 나면 그냥 쥑어 버립니다. 아무런 워닝이나 오류도 없이. 상황종료
        @ini_set('gd.jpeg_ignore_warning', 1);

        // $msize=php의 할당메모리, $isize=24bit plain에서 본 필요 메모리
        // 메모리가 부족하면 워닝이고 뭐고간에 그냥 죽으므로, 썸을 못 만든다.
        $msize = memory_get_usage();
        $isize = $size['bits'] / 8 * $size[0] * $size[1];
        if ($isize > $msize)
            return $file_name;

        $source = imagecreatefromjpeg($source_file);
        // jpeg 파일의 오류가 나왔을 때, 워터마크가 있으면 오류생성? - 워터마크 없으면 원본을 그냥 사용 (빈도가 낮으니까)
        if (!$source) {
            if (trim($watermark) && count($watermark) > 0)
                ;
            else
                return $file_name;
        }
    }
    else if ($size[2] == 3)
        $source = imagecreatefrompng($source_file);
    else if ($size[2] == 6) 
    {
        // bmp 파일은 gif 형식으로 썸네일을 생성
        $source = ImageCreateFromBMP($source_file);
        $size[2] = 1;
    }
    else if ($size[2] == 5) {
        // psd로 썸네일 만들기
        $source = imagecreatefrompsd($source_file);
        $size[2] = 1;
    } else {
        return str_replace($real_dir, "", $source_file);
    }

    // 썸네일 확대
    if ($small_thumb == 2) {
        $size0 = $size[0];
        $size1 = $size[1];

        if ($width) {
            $size[0] = $width;
            $size[1] = (int) $width * ($size1/$size0);
        } else if ($height) 
        {
            $size[1] = $height;
            $size[0] = (int) $height * ($size0/$size1);
        }
        else
            return str_replace($real_dir, "", $source_file);
        

        /*
        if ($height && $height > $size[1]) {
            $size[1] = $height;
            $size[0] = (int) $width*($size[0]/$size[1]);
        }
        */

        $target = imagecreatetruecolor($size[0], $size[1]);
        imagecopyresampled($target, $source, 0, 0, 0, 0, $size[0], $size[1], $size0, $size1);
        $source = $target;
        unset($target);
    }

    if ($width) {
        $x = $width;
        if ($height) { 

            if ($width > $size[0]) {  // $width가 이미지 폭보다 클때 ($width의 resize는 불필요)
                if ($height > $size[1]) {
                    $x = $size[0];
                    $tmp_y = $size[1];
                    $target = imagecreatetruecolor($x, $tmp_y);
                    imagecopyresampled($target, $source, 0, 0, 0, 0, $x, $tmp_y, $size[0], $size[1]);
                } else {
                    if ($is_crop) { // 넘치는 높이를 잘라줘야 합니다
                        $x = $size[0];
                        $y = $size[1];
                        $tmp_y = $height;
                        $target = imagecreatetruecolor($x, $tmp_y);
                        $tmp_target = imagecreatetruecolor($x, $tmp_y);
                        imagecopyresampled($tmp_target, $source, 0, 0, 0, 0, $x, $y, $size[0], $size[1]);
                        imagecopy($target, $tmp_target, 0, 0, 0, 0, $x, $tmp_y);
                    } else {
                        $y = $height;
                        $rate = $y / $size[1];
                        $x = (int)($size[0] * $rate);
                        $target = imagecreatetruecolor($x, $y);
                        imagecopyresampled($target, $source, 0, 0, 0, 0, $x, $y, $size[0], $size[1]);
                    }
                }
            } else { // $width가 이미지 폭보다 작을 때 (폭의 resize가 필요)
                $y = $height;
                $rate = $x / $size[0];
                $tmp_y = (int)($size[1] * $rate);
                if ($height > $tmp_y) {
                    if ($height < $size[1]) {
                        if ($is_crop) {     // 높이가 작으므로 이미지의 폭만 crop
                            $rate = $y / $size[1];
                            $tmp_x = (int)($size[0] * $rate);
                            $target = imagecreatetruecolor($x, $y);
                            $tmp_target = imagecreatetruecolor($tmp_x, $y);
                            imagecopyresampled($tmp_target, $source, 0, 0, 0, 0, $tmp_x, $y, $size[0], $size[1]);
                            // copy하는 위치가 이미지의 수평중심이 되게 조정
                            $src_x = (int)(($tmp_x - $x)/2);
                            imagecopy($target, $tmp_target, 0, 0, $src_x, 0, $x, $y);
                        } else {
                            $target = imagecreatetruecolor($x, $tmp_y);
                            imagecopyresampled($target, $source, 0, 0, 0, 0, $x, $tmp_y, $size[0], $size[1]);
                        }
                    } else {
                        // 썸 생성후의 높이가 최종 높이보다 낮으므로 이미지의 폭만 crop
                        if ($is_crop == 1) {          // 좌측에서 부터
                            $tmp_x = (int)$size[0];
                            $tmp_y = (int)$size[1];
                            $target = imagecreatetruecolor($x, $tmp_y);
                            imagecopyresampled($target, $source, 0, 0, 0, 0, $x, $tmp_y, $x, $tmp_y);
                        } else if ($is_crop == 2) {   // 중간에서
                            $tmp_x = (int)($size[0]/2) - (int)($x/2);
                            $tmp_y = (int)$size[1];
                            $target = imagecreatetruecolor($x, $tmp_y);
                            imagecopyresampled($target, $source, 0, 0, $tmp_x, 0, $x, $tmp_y, $x, $tmp_y);
                        } else {                      // 생각없이 썸 생성
                            $target = imagecreatetruecolor($x, $tmp_y);
                            imagecopyresampled($target, $source, 0, 0, 0, 0, $x, $tmp_y, $size[0], $size[1]);
                    }
                    }
                } else {
                    if ($is_crop) {
                        $target = imagecreatetruecolor($x, $y);
                        $tmp_target = imagecreatetruecolor($x, $tmp_y);
                        imagecopyresampled($tmp_target, $source, 0, 0, 0, 0, $x, $tmp_y, $size[0], $size[1]);
                        imagecopy($target, $tmp_target, 0, 0, 0, 0, $x, $y);
                    } else {
                        $rate = $y / $size[1];
                        $tmp_x = (int)($size[0] * $rate);
                        $target = imagecreatetruecolor($tmp_x, $y);
                        imagecopyresampled($target, $source, 0, 0, 0, 0, $tmp_x, $y, $size[0], $size[1]);
                    }
                }
            }
        } 
        else 
        { // $height에 값이 없는 경우 (crop은 해당 사항이 없죠? ^^)
            if ($width >= $size[0]) { // 썸네일의 폭보다 $width가 더 크면, 이미지의 폭으로 썸에일을 만듭니다 (확대된 썸은 허용않음)
                $x = $size[0];
                $tmp_y = $size[1];
            } else {
                $rate = $x / $size[0];
                $tmp_y = (int)($size[1] * $rate);
            }
            
            $target = imagecreatetruecolor($x, $tmp_y);
            imagecopyresampled($target, $source, 0, 0, 0, 0, $x, $tmp_y, $size[0], $size[1]);
        }
    } 
    else // $width는 없고 $height만 있는 경우
    {
        if ($height > $size[1]) {   // 썸네일의 높이보다 $height가 더 크면, 이미지의 높이로 썸네일을 만듭니다 (확대된 썸은 허용않음)
            $y = $size[1];
            $tmp_x = $size[0];
            $target = imagecreatetruecolor($tmp_x, $y);
            imagecopyresampled($target, $source, 0, 0, 0, 0, $tmp_x, $y, $size[0], $size[1]);
        } else {
            $x = $size[0];
            $y = $height;
            $tmp_y = $size[1];
            if ($is_crop) {
                $target = imagecreatetruecolor($x, $y);
                $tmp_target = imagecreatetruecolor($x, $tmp_y);
                imagecopyresampled($tmp_target, $source, 0, 0, 0, 0, $x, $tmp_y, $size[0], $size[1]);
                imagecopy($target, $tmp_target, 0, 0, 0, 0, $x, $tmp_y);
            } else {
                $rate = $y / $size[1];
                $tmp_x = (int)($size[0] * $rate);
                $target = imagecreatetruecolor($tmp_x, $y);
                imagecopyresampled($target, $source, 0, 0, 0, 0, $tmp_x, $y, $size[0], $size[1]);
            }
        }
    }

    // 이미지 퀄러티를 재조정
    ob_start();
    if ($size[2] == 1)
        imagegif($target, "", $quality);
    else if ($size[2] == 2)
        imagejpeg($target, "", $quality);
    else if ($size[2] == 3)
        imagepng($target, "", round(10 - ($quality / 10))); //imagepng의 퀄리티는 0~9까지 사용 가능합니다 (Lusia). 0(no compression) 입니다
    $tmp_image_str = ob_get_contents();
    ob_end_clean();
    $target = imagecreatefromstring($tmp_image_str);
    unset($tmp_image_str);

    // watermark 이미지 넣어주기
    if (trim($watermark) && count($watermark) > 0) {
        foreach ($watermark as $w1) {
            // 파일이름과 디렉토리를 구분
            $w1_file = $w1['filename'];
            if (!$w1_file) continue;

            $w_dir = dirname(file_path($w1_file));
            $w_file = basename($w1_file);
    
            $w1_file = $w_dir . "/" . $w_file;

            // 워터마크 파일이 없으면 워터마크를 찍지 않습니다
            if (!file_exists($w1_file))
                break;

            // 워터마크 이미지의 width, height
            $sizew = getimagesize($w1_file);
            $wx = $sizew[0];
            $wy = $sizew[1];
            // watermark 이미지 읽어들이기
            if ($sizew[2] == 1)
                $w1_source = imagecreatefromgif($w1_file);
            else if ($sizew[2] == 2)
                $w1_source = imagecreatefromjpeg($w1_file);
            else if ($sizew[2] == 3)
                $w1_source = imagecreatefrompng($w1_file);

            // $target 이미지의 width, height
            $sx = imagesx($target);
            $sy = imagesy($target);

            switch ($w1[location]) {
              case "center"       : 
                    $tx = (int)($sx/2 - $wx/2) + $w1[x];
                    $ty = (int)($sy/2 - $wy/2) + $w1[y];
                    break;
              case "top"          :
                    $tx = (int)($sx/2 - $wx/2) + $w1[x];
                    $ty = $w1[y];
                    break;
              case "top_left"     :
                    $tx = $w1[x];
                    $ty = $w1[y];
                    break;
              case "top_right"    :
                    $tx = $sx - $wx - $w1[x];
                    $ty = $w1[y];
                    break;
              case "bottom"       :
                    $tx = (int)($sx/2 - $wx/2) + $w1[x];
                    $ty = $sy - $w1[y] - $wy;
                    break;
              case "bottom_left"  :
                    $tx = $w1[x];
                    $ty = $sy - $w1[y] - $wy;
                    break;
              case "bottom_right" : 
              default             :
                    $tx = $sx - $w1[x] - $wx;
                    $ty = $sy - $w1[y] - $wy;
            }
            imagecopyresampled($target, $w1_source, $tx, $ty, 0, 0, $wx, $wy, $wx, $wy);
        }
    }

    // php imagefilter
    //if ($filter and $size[2] == 2) { //$size[2] == 2 , jpg만 필터 적용
    if ($filter) {
        $filter_type = $filter[type];
        switch($filter_type) {
          case  IMG_FILTER_COLORIZE : imagefilter($target, $filter_type, $filter[arg1], $filter[arg2], $filter[arg3], $filter[arg4]);
                                      break;
          case  IMG_FILTER_PIXELATE : imagefilter($target, $filter_type, $filter[arg1], $filter[arg2]);
                                      break;
          case  IMG_FILTER_BRIGHTNESS :
          case  IMG_FILTER_CONTRAST :
          case  IMG_FILTER_SMOOTH   : imagefilter($target, $filter_type, $filter[arg1]);
                                      break;
          case  IMG_FILTER_NEGATE   :
          case  IMG_FILTER_GRAYSCALE:
          case  IMG_FILTER_EDGEDETECT:
          case  IMG_FILTER_EMBOSS   :
          case  IMG_FILTER_GAUSSIAN_BLUR :
          case  IMG_FILTER_SELECTIVE_BLUR:
          case  IMG_FILTER_MEAN_REMOVAL:  imagefilter($target, $filter_type);
                                          break;
          case  99: UnsharpMask4($target, $filter[arg1], $filter[arg2], $filter[arg3]);
                                        break;
          default                   : ; // 필터 타입이 틀리면 아무것도 안합니다
        }
    }

    $quality=100;
    if ($size[2] == 1 || $thumb_type=="gif")
        imagegif($target, $thumb_file, 100);    // gif
    else if ($size[2] == 2 || $thumb_type=="jpg")
        imagejpeg($target, $thumb_file, 100);   // jpeg
    else if ($size[2] == 3 || $thumb_type=="png") {
        // Turn off alpha blending and set alpha flag
        imagealphablending($target, false);
        imagesavealpha($target, true);

        imagepng($target, $thumb_file, 0); //imagepng의 퀄리티는 0~9까지 사용 가능합니다 (Lusia). 0(no compression) 입니다
    }
    else
        imagegif($target, $thumb_file, 100);

    @chmod($thumb_file, 0606); // 추후 삭제를 위하여 파일모드 변경

    // 메모리를 부숴줍니다 - http://kr2.php.net/manual/kr/function.imagedestroy.php
    if ($target)
        imagedestroy($target);
    if ($source)
        imagedestroy($source);
    if ($tmp_target) 
        imagedestroy($tmp_target);

    return str_replace($real_dir, "", $thumb_file);
}

// php imagefilter for PHP4 - http://mgccl.com/2007/03/02/imagefilter-function-for-php-user-without-bundled-gd
//
//include this file whenever you have to use imageconvolution...
//you can use in your project, but keep the comment below :)
//great for any image manipulation library
//Made by Chao Xu(Mgccl) 3/1/07
//www.webdevlogs.com
//V 1.0
if(!function_exists('imagefilter')){
	function imagefilter($source, $var, $arg1 = null, $arg2 = null, $arg3 = null){
		#define('IMAGE_FILTER_NEGATE',0);
		#define('IMAGE_FILTER_GRAYSCALE',0);
		#define('IMAGE_FILTER_BRIGHTNESS',2);
		#define('IMAGE_FILTER_CONTRAST',3);
		#define('IMAGE_FILTER_COLORIZE',4);
		#define('IMAGE_FILTER_EDGEDETECT',5);
		#define('IMAGE_FILTER_EMBOSS',6);
		#define('IMAGE_FILTER_GAUSSIAN_BLUR',7);
		#define('IMAGE_FILTER_SELECTIVE_BLUR',8);
		#define('IMAGE_FILTER_MEAN_REMOVAL',9);
		#define('IMAGE_FILTER_SMOOTH',10);
		$max_y = imagesy($source);
		$max_x = imagesx($source);
	   switch ($var){
	       case 0:
	           $y = 0;
	           while($y<$max_y) {
	               $x = 0;
	               while($x<$max_x){
	                   $rgb = imagecolorat($source,$x,$y);
	                   $r = 255 - (($rgb >> 16) & 0xFF);
	                   $g = 255 - (($rgb >> 8) & 0xFF);
	                   $b = 255 - ($rgb & 0xFF);
	                   $a = $rgb >> 24;
	                   $new_pxl = imagecolorallocatealpha($source, $r, $g, $b, $a);
	                   if ($new_pxl == false){
	                       $new_pxl = imagecolorclosestalpha($source, $r, $g, $b, $a);
	                   }
	                   imagesetpixel($source,$x,$y,$new_pxl);
	                   ++$x;
	               }
	               ++$y;
	           }
	           return true;
	       break;
	       case 1:
	           $y = 0;
	           while($y<$max_y) {
	               $x = 0;
	               while($x<$max_x){
	                   $rgb = imagecolorat($source,$x,$y);
	                   $a = $rgb >> 24;
	                   $r = ((($rgb >> 16) & 0xFF)*0.299)+((($rgb >> 8) & 0xFF)*0.587)+(($rgb & 0xFF)*0.114);
	                   $new_pxl = imagecolorallocatealpha($source, $r, $r, $r, $a);
	                   if ($new_pxl == false){
	                       $new_pxl = imagecolorclosestalpha($source, $r, $r, $r, $a);
	                   }
	                   imagesetpixel($source,$x,$y,$new_pxl);
	                   ++$x;
	               }
	               ++$y;
	           }
	           return true;
	       break;
	       case 2:
	           $y = 0;
	           while($y<$max_y) {
	               $x = 0;
	               while($x<$max_x){
	                   $rgb = imagecolorat($source,$x,$y);
	                   $r = (($rgb >> 16) & 0xFF) + $arg1;
	                   $g = (($rgb >> 8) & 0xFF) + $arg1;
	                   $b = ($rgb & 0xFF) + $arg1;
	                   $a = $rgb >> 24;
	                     $r = ($r > 255)? 255 : (($r < 0)? 0:$r);
	                   $g = ($g > 255)? 255 : (($g < 0)? 0:$g);
	                   $b = ($b > 255)? 255 : (($b < 0)? 0:$b);
	                   $new_pxl = imagecolorallocatealpha($source, $r, $g, $b, $a);
	                   if ($new_pxl == false){
	                       $new_pxl = imagecolorclosestalpha($source, $r, $g, $b, $a);
	                   }
	                   imagesetpixel($source,$x,$y,$new_pxl);
	                   ++$x;
	               }
	               ++$y;
	           }
	           return true;
	       break;
	       case 3:
	           $contrast = pow((100-$arg1)/100,2);
	           $y = 0;
	           while($y<$max_y) {
	               $x = 0;
	               while($x<$max_x){
	                   $rgb = imagecolorat($source,$x,$y);
	                   $a = $rgb >> 24;
	                   $r = (((((($rgb >> 16) & 0xFF)/255)-0.5)*$contrast)+0.5)*255;
	                   $g = (((((($rgb >> 8) & 0xFF)/255)-0.5)*$contrast)+0.5)*255;
	                   $b = ((((($rgb & 0xFF)/255)-0.5)*$contrast)+0.5)*255;
	                   $r = ($r > 255)? 255 : (($r < 0)? 0:$r);
	                   $g = ($g > 255)? 255 : (($g < 0)? 0:$g);
	                   $b = ($b > 255)? 255 : (($b < 0)? 0:$b);
	                   $new_pxl = imagecolorallocatealpha($source, $r, $g, $b, $a);
	                   if ($new_pxl == false){
	                       $new_pxl = imagecolorclosestalpha($source, $r, $g, $b, $a);
	                   }
	                   imagesetpixel($source,$x,$y,$new_pxl);
	                   ++$x;
	               }
	               ++$y;
	           }
	           return true;
	       break;
	       case 4:
	           $x = 0;
	           while($x<$max_x){
	               $y = 0;
	               while($y<$max_y){
	                   $rgb = imagecolorat($source, $x, $y);
	                   $r = (($rgb >> 16) & 0xFF) + $arg1;
	                   $g = (($rgb >> 8) & 0xFF) + $arg2;
	                   $b = ($rgb & 0xFF) + $arg3;
	                   $a = $rgb >> 24;
	                   $r = ($r > 255)? 255 : (($r < 0)? 0:$r);
	                   $g = ($g > 255)? 255 : (($g < 0)? 0:$g);
	                   $b = ($b > 255)? 255 : (($b < 0)? 0:$b);
	                   $new_pxl = imagecolorallocatealpha($source, $r, $g, $b, $a);
	                   if ($new_pxl == false){
	                       $new_pxl = imagecolorclosestalpha($source, $r, $g, $b, $a);
	                   }
	                   imagesetpixel($source,$x,$y,$new_pxl);
	                   ++$y;
	                   }
	               ++$x;
	           }
	           return true;
	       break;
	       case 5:
	           return imageconvolution($source, array(array(-1,0,-1), array(0,4,0), array(-1,0,-1)), 1, 127);
	       break;
	       case 6:
	           return imageconvolution($source, array(array(1.5, 0, 0), array(0, 0, 0), array(0, 0, -1.5)), 1, 127);
	       break;
	       case 7:
	           return imageconvolution($source, array(array(1, 2, 1), array(2, 4, 2), array(1, 2, 1)), 16, 0);
	       break;
	       case 8:
	   for($y = 0; $y<$max_y; $y++) {
	       for ($x = 0; $x<$max_x; $x++) {
	             $flt_r_sum = $flt_g_sum = $flt_b_sum = 0;
	           $cpxl = imagecolorat($source, $x, $y);
	           for ($j=0; $j<3; $j++) {
	               for ($i=0; $i<3; $i++) {
	                   if (($j == 1) && ($i == 1)) {
	                       $flt_r[1][1] = $flt_g[1][1] = $flt_b[1][1] = 0.5;
	                   } else {
	                       $pxl = imagecolorat($source, $x-(3>>1)+$i, $y-(3>>1)+$j);
 
	                       $new_a = $pxl >> 24;
	                       //$r = (($pxl >> 16) & 0xFF);
	                       //$g = (($pxl >> 8) & 0xFF);
	                       //$b = ($pxl & 0xFF);
	                       $new_r = abs((($cpxl >> 16) & 0xFF) - (($pxl >> 16) & 0xFF));
	                       if ($new_r != 0) {
	                           $flt_r[$j][$i] = 1/$new_r;
	                       } else {
	                           $flt_r[$j][$i] = 1;
	                       }
 
	                       $new_g = abs((($cpxl >> 8) & 0xFF) - (($pxl >> 8) & 0xFF));
	                       if ($new_g != 0) {
	                           $flt_g[$j][$i] = 1/$new_g;
	                       } else {
	                           $flt_g[$j][$i] = 1;
	                       }
 
	                       $new_b = abs(($cpxl & 0xFF) - ($pxl & 0xFF));
	                       if ($new_b != 0) {
	                           $flt_b[$j][$i] = 1/$new_b;
	                       } else {
	                           $flt_b[$j][$i] = 1;
	                       }
	                   }
 
	                   $flt_r_sum += $flt_r[$j][$i];
	                   $flt_g_sum += $flt_g[$j][$i];
	                   $flt_b_sum += $flt_b[$j][$i];
	               }
	           }
 
	           for ($j=0; $j<3; $j++) {
	               for ($i=0; $i<3; $i++) {
	                   if ($flt_r_sum != 0) {
	                       $flt_r[$j][$i] /= $flt_r_sum;
	                   }
	                   if ($flt_g_sum != 0) {
	                       $flt_g[$j][$i] /= $flt_g_sum;
	                   }
	                   if ($flt_b_sum != 0) {
	                       $flt_b[$j][$i] /= $flt_b_sum;
	                   }
	               }
	           }
 
	           $new_r = $new_g = $new_b = 0;
 
	           for ($j=0; $j<3; $j++) {
	               for ($i=0; $i<3; $i++) {
	                   $pxl = imagecolorat($source, $x-(3>>1)+$i, $y-(3>>1)+$j);
	                   $new_r += (($pxl >> 16) & 0xFF) * $flt_r[$j][$i];
	                   $new_g += (($pxl >> 8) & 0xFF) * $flt_g[$j][$i];
	                   $new_b += ($pxl & 0xFF) * $flt_b[$j][$i];
	               }
	           }
 
	           $new_r = ($new_r > 255)? 255 : (($new_r < 0)? 0:$new_r);
	           $new_g = ($new_g > 255)? 255 : (($new_g < 0)? 0:$new_g);
	           $new_b = ($new_b > 255)? 255 : (($new_b < 0)? 0:$new_b);
	           $new_pxl = ImageColorAllocateAlpha($source, (int)$new_r, (int)$new_g, (int)$new_b, $new_a);
	           if ($new_pxl == false) {
	               $new_pxl = ImageColorClosestAlpha($source, (int)$new_r, (int)$new_g, (int)$new_b, $new_a);
	           }
	           imagesetpixel($source,$x,$y,$new_pxl);
	       }
	   }
	   return true;
	       break;
	       case 9:
	           return imageconvolution($source, array(array(-1,-1,-1),array(-1,9,-1),array(-1,-1,-1)), 1, 0);
	       break;
	       case 10:
	           return imageconvolution($source, array(array(1,1,1),array(1,$arg1,1),array(1,1,1)), $arg1+8, 0);
	       break;
	   }
	}
}

if(!function_exists('file_path')){
// 파일의 경로를 가지고 옵니다 (불당팩, /lib/common.lib.php에 정의된 함수)
function file_path($path) {

    $dir = dirname($path);
    $file = basename($path);
    
    if (substr($dir,0,1) == "/") {
        $real_dir = dirname($_SERVER['DOCUMENT_ROOT'] . "/nothing");
        $dir = $real_dir . $dir;
    }
    
    return $dir . "/" . $file;
}
}

////////////////////////////////////////////////////////////////////////////////////////////////   
////   
////                  Unsharp Mask for PHP - version 2.1.1   
////   
////    Unsharp mask algorithm by Torstein Hønsi 2003-07.   
////             thoensi_at_netcom_dot_no.   
////               Please leave this notice.   
////   
////   http://vikjavev.no/computing/ump.php
////
///////////////////////////////////////////////////////////////////////////////////////////////  
if(!function_exists('UnsharpMask4')){
function UnsharpMask4($img, $amount, $radius, $threshold)
{

  // $img is an image that is already created within php using  
  // imgcreatetruecolor. No url! $img must be a truecolor image.  

  // Attempt to calibrate the parameters to Photoshop:  
	if ($amount > 500) $amount = 500;
	$amount = $amount * 0.016;
	if ($radius > 50) $radius = 50;
	$radius = $radius * 2;
	if ($threshold > 255) $threshold = 255;

	$radius = abs(round($radius)); 	// Only integers make sense.
	if ($radius == 0) {	return $img; imagedestroy($img); break;	}
	$w = imagesx($img); $h = imagesy($img);
	$imgCanvas = $img;
	$imgCanvas2 = $img;
	$imgBlur = imagecreatetruecolor($w, $h);

	// Gaussian blur matrix:
	//	1	2	1		
	//	2	4	2		
	//	1	2	1		

	// Move copies of the image around one pixel at the time and merge them with weight
	// according to the matrix. The same matrix is simply repeated for higher radii.
	for ($i = 0; $i < $radius; $i++)
		{
		imagecopy	  ($imgBlur, $imgCanvas, 0, 0, 1, 1, $w - 1, $h - 1); // up left
		imagecopymerge ($imgBlur, $imgCanvas, 1, 1, 0, 0, $w, $h, 50); // down right
		imagecopymerge ($imgBlur, $imgCanvas, 0, 1, 1, 0, $w - 1, $h, 33.33333); // down left
		imagecopymerge ($imgBlur, $imgCanvas, 1, 0, 0, 1, $w, $h - 1, 25); // up right
		imagecopymerge ($imgBlur, $imgCanvas, 0, 0, 1, 0, $w - 1, $h, 33.33333); // left
		imagecopymerge ($imgBlur, $imgCanvas, 1, 0, 0, 0, $w, $h, 25); // right
		imagecopymerge ($imgBlur, $imgCanvas, 0, 0, 0, 1, $w, $h - 1, 20 ); // up
		imagecopymerge ($imgBlur, $imgCanvas, 0, 1, 0, 0, $w, $h, 16.666667); // down
		imagecopymerge ($imgBlur, $imgCanvas, 0, 0, 0, 0, $w, $h, 50); // center
		}
	$imgCanvas = $imgBlur;	
		
	// Calculate the difference between the blurred pixels and the original
	// and set the pixels
	for ($x = 0; $x < $w; $x++)
		{ // each row
		for ($y = 0; $y < $h; $y++)
			{ // each pixel
			$rgbOrig = ImageColorAt($imgCanvas2, $x, $y);
			$rOrig = (($rgbOrig >> 16) & 0xFF);
			$gOrig = (($rgbOrig >> 8) & 0xFF);
			$bOrig = ($rgbOrig & 0xFF);
			$rgbBlur = ImageColorAt($imgCanvas, $x, $y);
			$rBlur = (($rgbBlur >> 16) & 0xFF);
			$gBlur = (($rgbBlur >> 8) & 0xFF);
			$bBlur = ($rgbBlur & 0xFF);

			// When the masked pixels differ less from the original
			// than the threshold specifies, they are set to their original value.
			$rNew = (abs($rOrig - $rBlur) >= $threshold) ? max(0, min(255, ($amount * ($rOrig - $rBlur)) + $rOrig)) : $rOrig;
			$gNew = (abs($gOrig - $gBlur) >= $threshold) ? max(0, min(255, ($amount * ($gOrig - $gBlur)) + $gOrig)) : $gOrig;
			$bNew = (abs($bOrig - $bBlur) >= $threshold) ? max(0, min(255, ($amount * ($bOrig - $bBlur)) + $bOrig)) : $bOrig;
			
			if (($rOrig != $rNew) || ($gOrig != $gNew) || ($bOrig != $bNew))
				{
				$pixCol = ImageColorAllocate($img, $rNew, $gNew, $bNew);
				ImageSetPixel($img, $x, $y, $pixCol);
				}
			}
		}
	return $img;
}
}

/*********************************************/
/* Fonction: ImageCreateFromBMP              */
/* Author:   DHKold                          */
/* Contact:  admin@dhkold.com                */
/* Date:     The 15th of June 2005           */
/* Version:  2.0B                            */
/*********************************************/

if(!function_exists('ImageCreateFromBMP')){
function ImageCreateFromBMP($filename)
{
 //Ouverture du fichier en mode binaire
   if (! $f1 = fopen($filename,"rb")) return FALSE;
echo $filename;
 //1 : Chargement des ent?tes FICHIER
   $FILE = unpack("vfile_type/Vfile_size/Vreserved/Vbitmap_offset", fread($f1,14));
   if ($FILE['file_type'] != 19778) return FALSE;

 //2 : Chargement des ent?tes BMP
   $BMP = unpack('Vheader_size/Vwidth/Vheight/vplanes/vbits_per_pixel'.
                 '/Vcompression/Vsize_bitmap/Vhoriz_resolution'.
                 '/Vvert_resolution/Vcolors_used/Vcolors_important', fread($f1,40));
   $BMP['colors'] = pow(2,$BMP['bits_per_pixel']);
   if ($BMP['size_bitmap'] == 0) $BMP['size_bitmap'] = $FILE['file_size'] - $FILE['bitmap_offset'];
   $BMP['bytes_per_pixel'] = $BMP['bits_per_pixel']/8;
   $BMP['bytes_per_pixel2'] = ceil($BMP['bytes_per_pixel']);
   $BMP['decal'] = ($BMP['width']*$BMP['bytes_per_pixel']/4);
   $BMP['decal'] -= floor($BMP['width']*$BMP['bytes_per_pixel']/4);
   $BMP['decal'] = 4-(4*$BMP['decal']);
   if ($BMP['decal'] == 4) $BMP['decal'] = 0;

 //3 : Chargement des couleurs de la palette
   $PALETTE = array();
   if ($BMP['colors'] < 16777216)
   {
    $PALETTE = unpack('V'.$BMP['colors'], fread($f1,$BMP['colors']*4));
   }

 //4 : Cr?ation de l'image
   $IMG = fread($f1,$BMP['size_bitmap']);
   $VIDE = chr(0);

   $res = imagecreatetruecolor($BMP['width'],$BMP['height']);
   $P = 0;
   $Y = $BMP['height']-1;
   while ($Y >= 0)
   {
    $X=0;
    while ($X < $BMP['width'])
    {
     if ($BMP['bits_per_pixel'] == 24)
        $COLOR = unpack("V",substr($IMG,$P,3).$VIDE);
     elseif ($BMP['bits_per_pixel'] == 16)
     {  
        $COLOR = unpack("v",substr($IMG,$P,2));
        $blue  = (($COLOR[1] & 0x001f) << 3) + 7;
        $green = (($COLOR[1] & 0x03e0) >> 2) + 7;
        $red   = (($COLOR[1] & 0xfc00) >> 7) + 7;
        $COLOR[1] = $red * 65536 + $green * 256 + $blue;
     }
     elseif ($BMP['bits_per_pixel'] == 8)
     {  
        $COLOR = unpack("n",$VIDE.substr($IMG,$P,1));
        $COLOR[1] = $PALETTE[$COLOR[1]+1];
     }
     elseif ($BMP['bits_per_pixel'] == 4)
     {
        $COLOR = unpack("n",$VIDE.substr($IMG,floor($P),1));
        if (($P*2)%2 == 0) $COLOR[1] = ($COLOR[1] >> 4) ; else $COLOR[1] = ($COLOR[1] & 0x0F);
        $COLOR[1] = $PALETTE[$COLOR[1]+1];
     }
     elseif ($BMP['bits_per_pixel'] == 1)
     {
        $COLOR = unpack("n",$VIDE.substr($IMG,floor($P),1));
        if     (($P*8)%8 == 0) $COLOR[1] =  $COLOR[1]        >>7;
        elseif (($P*8)%8 == 1) $COLOR[1] = ($COLOR[1] & 0x40)>>6;
        elseif (($P*8)%8 == 2) $COLOR[1] = ($COLOR[1] & 0x20)>>5;
        elseif (($P*8)%8 == 3) $COLOR[1] = ($COLOR[1] & 0x10)>>4;
        elseif (($P*8)%8 == 4) $COLOR[1] = ($COLOR[1] & 0x8)>>3;
        elseif (($P*8)%8 == 5) $COLOR[1] = ($COLOR[1] & 0x4)>>2;
        elseif (($P*8)%8 == 6) $COLOR[1] = ($COLOR[1] & 0x2)>>1;
        elseif (($P*8)%8 == 7) $COLOR[1] = ($COLOR[1] & 0x1);
        $COLOR[1] = $PALETTE[$COLOR[1]+1];
     }
     else
        return FALSE;
     imagesetpixel($res,$X,$Y,$COLOR[1]);
     $X++;
     $P += $BMP['bytes_per_pixel'];
    }
    $Y--;
    $P+=$BMP['decal'];
   }

 //Fermeture du fichier
   fclose($f1);

 return $res;
}
}

// animated gif 파일인지를 확인
// http://kr2.php.net/imagecreatefromgif
/*
function is_ani_gif($filename)
{
        $filecontents=file_get_contents($filename);

        $str_loc=0;
        $count=0;
        while ($count < 2) # There is no point in continuing after we find a 2nd frame
        {

                $where1=strpos($filecontents,"\x00\x21\xF9\x04",$str_loc);
                if ($where1 === FALSE)
                {
                        break;
                }
                else
                {
                        $str_loc=$where1+1;
                        $where2=strpos($filecontents,"\x00\x2C",$str_loc);
                        if ($where2 === FALSE)
                        {
                                break;
                        }
                        else
                        {
                                if ($where1+8 == $where2)
                                {
                                        $count++;
                                }
                                $str_loc=$where2+1;
                        }
                }
        }

        if ($count > 1)
        {
                return(true);
        }
        else
        {
                return(false);
        }
}
*/

// psd로 썸네일 만들기 - http://www.phpclasses.org/browse/file/17603.html

/* This file is released under the GPL, any version you like 
*    PHP PSD reader class, v1.3 
*    By Tim de Koning 
*    Kingsquare Information Services, 22 jan 2007 
* 
*    example use: 
*    ------------ 
*    <?php 
*    include_once('classPhpPsdReader.php') 
*    header("Content-type: image/jpeg"); 
*    print imagejpeg(imagecreatefrompsd('test.psd')); 
*    ?> 
* 
*    More info, bugs or requests, contact info@kingsquare.nl 
* 
*    Latest version and demo: http://www.kingsquare.nl/phppsdreader 
* 
*    TODO 
*    ---- 
*    - read color values for "multichannel data" PSD files 
*    - find and implement (hunter)lab to RGB algorithm 
*    - fix 32 bit colors... has something to do with gamma and exposure available since CS2, but dunno how to read them... 
*/ 


class PhpPsdReader { 
    var $infoArray; 
    var $fp; 
    var $fileName; 
    var $tempFileName; 
    var $colorBytesLength; 

    function PhpPsdReader($fileName) { 
        set_time_limit(0); 
        $this->infoArray = array(); 
        $this->fileName = $fileName; 
        $this->fp = fopen($this->fileName,'r'); 

        if (fread($this->fp,4)=='8BPS') { 
            $this->infoArray['version id'] = $this->_getInteger(2); 
            fseek($this->fp,6,SEEK_CUR); // 6 bytes of 0's 
            $this->infoArray['channels'] = $this->_getInteger(2); 
            $this->infoArray['rows'] = $this->_getInteger(4); 
            $this->infoArray['columns'] = $this->_getInteger(4); 
            $this->infoArray['colorDepth'] = $this->_getInteger(2); 
            $this->infoArray['colorMode'] = $this->_getInteger(2); 


            /* COLOR MODE DATA SECTION */ //4bytes Length The length of the following color data. 
            $this->infoArray['colorModeDataSectionLength'] = $this->_getInteger(4); 
            fseek($this->fp,$this->infoArray['colorModeDataSectionLength'],SEEK_CUR); // ignore this snizzle 

            /*  IMAGE RESOURCES */ 
            $this->infoArray['imageResourcesSectionLength'] = $this->_getInteger(4); 
            fseek($this->fp,$this->infoArray['imageResourcesSectionLength'],SEEK_CUR); // ignore this snizzle 

            /*  LAYER AND MASK */ 
            $this->infoArray['layerMaskDataSectionLength'] = $this->_getInteger(4); 
            fseek($this->fp,$this->infoArray['layerMaskDataSectionLength'],SEEK_CUR); // ignore this snizzle 


            /*  IMAGE DATA */ 
            $this->infoArray['compressionType'] = $this->_getInteger(2); 
            $this->infoArray['oneColorChannelPixelBytes'] = $this->infoArray['colorDepth']/8; 
            $this->colorBytesLength = $this->infoArray['rows']*$this->infoArray['columns']*$this->infoArray['oneColorChannelPixelBytes']; 

            if ($this->infoArray['colorMode']==2) { 
                $this->infoArray['error'] = 'images with indexed colours are not supported yet'; 
                return false; 
            } 
        } else { 
            $this->infoArray['error'] = 'invalid or unsupported psd'; 
            return false; 
        } 
    } 


    function getImage() { 
        // decompress image data if required 
        switch($this->infoArray['compressionType']) { 
            // case 2:, case 3: zip not supported yet.. 
            case 1: 
                // packed bits 
                $this->infoArray['scanLinesByteCounts'] = array(); 
                for ($i=0; $i<($this->infoArray['rows']*$this->infoArray['channels']); $i++) $this->infoArray['scanLinesByteCounts'][] = $this->_getInteger(2); 
                $this->tempFileName = tempnam(realpath('/tmp'),'decompressedImageData'); 
                $tfp = fopen($this->tempFileName,'wb'); 
                foreach ($this->infoArray['scanLinesByteCounts'] as $scanLinesByteCount) { 
                    fwrite($tfp,$this->_getPackedBitsDecoded(fread($this->fp,$scanLinesByteCount))); 
                } 
                fclose($tfp); 
                fclose($this->fp); 
                $this->fp = fopen($this->tempFileName,'r'); 
            default: 
                // continue with current file handle; 
                break; 
        } 

        // let's write pixel by pixel.... 
        $image = imagecreatetruecolor($this->infoArray['columns'],$this->infoArray['rows']); 

        for ($rowPointer = 0; ($rowPointer < $this->infoArray['rows']); $rowPointer++) { 
            for ($columnPointer = 0; ($columnPointer < $this->infoArray['columns']); $columnPointer++) { 
                /*     The color mode of the file. Supported values are: Bitmap=0; 
                    Grayscale=1; Indexed=2; RGB=3; CMYK=4; Multichannel=7; 
                    Duotone=8; Lab=9. 
                */ 
                switch ($this->infoArray['colorMode']) { 
                    case 2: // indexed... info should be able to extract from color mode data section. not implemented yet, so is grayscale 
                        exit; 
                        break; 
                    case 0: 
                        // bit by bit 
                        if ($columnPointer == 0) $bitPointer = 0; 
                        if ($bitPointer==0) $currentByteBits = str_pad(base_convert(bin2hex(fread($this->fp,1)), 16, 2),8,'0',STR_PAD_LEFT); 
                        $r = $g = $b = (($currentByteBits[$bitPointer]=='1')?0:255); 
                        $bitPointer++; 
                        if ($bitPointer==8) $bitPointer = 0; 
                        break; 

                    case 1: 
                    case 8: // 8 is indexed with 1 color..., so grayscale 
                        $r = $g = $b = $this->_getInteger($this->infoArray['oneColorChannelPixelBytes']); 
                        break; 

                    case 4: // CMYK 
                        $c = $this->_getInteger($this->infoArray['oneColorChannelPixelBytes']); 
                        $currentPointerPos = ftell($this->fp); 
                        fseek($this->fp,$this->colorBytesLength-1,SEEK_CUR); 
                        $m = $this->_getInteger($this->infoArray['oneColorChannelPixelBytes']); 
                        fseek($this->fp,$this->colorBytesLength-1,SEEK_CUR); 
                        $y = $this->_getInteger($this->infoArray['oneColorChannelPixelBytes']); 
                        fseek($this->fp,$this->colorBytesLength-1,SEEK_CUR); 
                        $k = $this->_getInteger($this->infoArray['oneColorChannelPixelBytes']); 
                        fseek($this->fp,$currentPointerPos); 
                        $r = round(($c * $k) / (pow(2,$this->infoArray['colorDepth'])-1)); 
                        $g = round(($m * $k) / (pow(2,$this->infoArray['colorDepth'])-1)); 
                        $b = round(($y * $k) / (pow(2,$this->infoArray['colorDepth'])-1)); 

                          break; 

                          case 9: // hunter Lab 
                              // i still need an understandable lab2rgb convert algorithm... if you have one, please let me know! 
                            $l = $this->_getInteger($this->infoArray['oneColorChannelPixelBytes']); 
                            $currentPointerPos = ftell($this->fp); 
                            fseek($this->fp,$this->colorBytesLength-1,SEEK_CUR); 
                            $a = $this->_getInteger($this->infoArray['oneColorChannelPixelBytes']); 
                            fseek($this->fp,$this->colorBytesLength-1,SEEK_CUR); 
                            $b =  $this->_getInteger($this->infoArray['oneColorChannelPixelBytes']); 
                            fseek($this->fp,$currentPointerPos); 

                            $r = $l; 
                            $g = $a; 
                            $b = $b; 

                        break; 
                    default: 
                        $r = $this->_getInteger($this->infoArray['oneColorChannelPixelBytes']); 
                        $currentPointerPos = ftell($this->fp); 
                        fseek($this->fp,$this->colorBytesLength-1,SEEK_CUR); 
                        $g = $this->_getInteger($this->infoArray['oneColorChannelPixelBytes']); 
                        fseek($this->fp,$this->colorBytesLength-1,SEEK_CUR); 
                        $b =  $this->_getInteger($this->infoArray['oneColorChannelPixelBytes']); 
                        fseek($this->fp,$currentPointerPos); 
                        break; 

                } 

                if (($this->infoArray['oneColorChannelPixelBytes']==2)) { 
                    $r = $r >> 8; 
                    $g = $g >> 8; 
                    $b = $b >> 8; 
                } elseif (($this->infoArray['oneColorChannelPixelBytes']==4)) { 
                    $r = $r >> 24; 
                    $g = $g >> 24; 
                    $b = $b >> 24; 
                } 

                $pixelColor = imagecolorallocate($image,$r,$g,$b); 
                imagesetpixel($image,$columnPointer,$rowPointer,$pixelColor); 
            } 
        } 
        fclose($this->fp); 
        if (isset($this->tempFileName)) unlink($this->tempFileName); 
        return $image; 
    } 

    /** 
     * 
     * PRIVATE FUNCTIONS 
     * 
     */ 

    function _getPackedBitsDecoded($string) { 
        /* 
        The PackBits algorithm will precede a block of data with a one byte header n, where n is interpreted as follows: 
        n Meaning 
        0 to 127 Copy the next n + 1 symbols verbatim 
        -127 to -1 Repeat the next symbol 1 - n times 
        -128 Do nothing 

        Decoding: 
        Step 1. Read the block header (n). 
        Step 2. If the header is an EOF exit. 
        Step 3. If n is non-negative, copy the next n + 1 symbols to the output stream and go to step 1. 
        Step 4. If n is negative, write 1 - n copies of the next symbol to the output stream and go to step 1. 

        */ 

        $stringPointer = 0; 
        $returnString = ''; 

        while (1) { 
            if (isset($string[$stringPointer])) $headerByteValue = $this->_unsignedToSigned(hexdec(bin2hex($string[$stringPointer])),1); 
            else return $returnString; 
            $stringPointer++; 

            if ($headerByteValue >= 0) { 
                for ($i=0; $i <= $headerByteValue; $i++) { 
                    $returnString .= $string[$stringPointer]; 
                    $stringPointer++; 
                } 
            } else { 
                if ($headerByteValue != -128) { 
                    $copyByte = $string[$stringPointer]; 
                    $stringPointer++; 

                    for ($i=0; $i < (1-$headerByteValue); $i++) { 
                        $returnString .= $copyByte; 
                    } 
                } 
            } 
        } 
    } 

    function _unsignedToSigned($int,$byteSize=1) { 
        switch($byteSize) { 
            case 1: 
                if ($int<128) return $int; 
                else return -256+$int; 
                break; 

            case 2: 
                if ($int<32768) return $int; 
                else return -65536+$int; 

            case 4: 
                if ($int<2147483648) return $int; 
                else return -4294967296+$int; 

            default: 
                return $int; 
        } 
    } 

    function _hexReverse($hex) { 
        $output = ''; 
        if (strlen($hex)%2) return false; 
        for ($pointer = strlen($hex);$pointer>=0;$pointer-=2) $output .= substr($hex,$pointer,2); 
        return $output; 
    } 

    function _getInteger($byteCount=1) { 
        switch ($byteCount) { 
            case 4: 
                // for some strange reason this is still broken... 
                return @reset(unpack('N',fread($this->fp,4))); 
                break; 

            case 2: 
                return @reset(unpack('n',fread($this->fp,2))); 
                break; 

            default: 
                return hexdec($this->_hexReverse(bin2hex(fread($this->fp,$byteCount)))); 
        } 
    } 
} 

/** 
 * Returns an image identifier representing the image obtained from the given filename, using only GD, returns an empty string on failure 
 * 
 * @param string $fileName 
 * @return image identifier 
 */ 

function imagecreatefrompsd($fileName) { 
    $psdReader = new PhpPsdReader($fileName); 
    if (isset($psdReader->infoArray['error'])) return ''; 
    else return $psdReader->getImage(); 
}
?>
