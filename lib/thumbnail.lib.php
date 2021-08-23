<?php
if (!defined('_GNUBOARD_')) exit;

@ini_set('memory_limit', '-1');

// 게시글리스트 썸네일 생성
function get_list_thumbnail($bo_table, $wr_id, $thumb_width, $thumb_height, $is_create=false, $is_crop=false, $crop_mode='center', $is_sharpen=false, $um_value='80/0.5/3')
{
    global $g5, $config;
    $filename = $alt = $data_path = '';
    $edt = false;

    $row = get_thumbnail_find_cache($bo_table, $wr_id, 'file');
    $empty_array = array('src'=>'', 'ori'=>'', 'alt'=>'');

    if(isset($row['bf_file']) && $row['bf_file']) {
        $filename = $row['bf_file'];
        $filepath = G5_DATA_PATH.'/file/'.$bo_table;
        $alt = get_text($row['bf_content']);
    } else {
        $write = get_thumbnail_find_cache($bo_table, $wr_id, 'content');
        $edt = true;
        
        if( $matches = get_editor_image($write['wr_content'], false) ){
            for($i=0; $i<count($matches[1]); $i++)
            {
                // 이미지 path 구함
                $p = parse_url($matches[1][$i]);
                if(strpos($p['path'], '/'.G5_DATA_DIR.'/') != 0)
                    $data_path = preg_replace('/^\/.*\/'.G5_DATA_DIR.'/', '/'.G5_DATA_DIR, $p['path']);
                else
                    $data_path = $p['path'];

                $srcfile = G5_PATH.$data_path;

                if(preg_match("/\.({$config['cf_image_extension']})$/i", $srcfile) && is_file($srcfile)) {
                    $size = @getimagesize($srcfile);
                    if(empty($size))
                        continue;

                    $filename = basename($srcfile);
                    $filepath = dirname($srcfile);

                    preg_match("/alt=[\"\']?([^\"\']*)[\"\']?/", $matches[0][$i], $malt);
                    $alt = isset($malt[1]) ? get_text($malt[1]) : '';

                    break;
                }

                $filename = run_replace('get_editor_filename', $filename, $p);
            }   //end for
        }   //end if
    }

    if(!$filename)
        return $empty_array;
    
    if( $thumbnail_info = run_replace('get_list_thumbnail_info', array(), array('bo_table'=>$bo_table, 'wr_id'=>$wr_id, 'data_path'=>$data_path, 'edt'=>$edt, 'filename'=>$filename, 'filepath'=>$filepath, 'thumb_width'=>$thumb_width, 'thumb_height'=>$thumb_height, 'is_create'=>$is_create, 'is_crop'=>$is_crop, 'crop_mode'=>$crop_mode, 'is_sharpen'=>$is_sharpen, 'um_value'=>$um_value)) ){
        return $thumbnail_info;
    }

    $tname = thumbnail($filename, $filepath, $filepath, $thumb_width, $thumb_height, $is_create, $is_crop, $crop_mode, $is_sharpen, $um_value);

    if($tname) {
        if($edt) {
            // 오리지날 이미지
            $ori = G5_URL.$data_path;
            // 썸네일 이미지
            $src = G5_URL.str_replace($filename, $tname, $data_path);
        } else {
            $ori = G5_DATA_URL.'/file/'.$bo_table.'/'.$filename;
            $src = G5_DATA_URL.'/file/'.$bo_table.'/'.$tname;
        }
    } else {
        return $empty_array;
    }

    $thumb = array("src"=>$src, "ori"=>$ori, "alt"=>$alt);

    return $thumb;
}

// 게시글보기 파일 썸네일 리턴
function get_file_thumbnail($file){
    
    if( ! is_array($file) ) return '';

    if( preg_match('/(\.jpg|\.jpeg|\.gif|\.png|\.bmp|\.webp)$/i', $file['file']) && $contents = run_replace('get_file_thumbnail_tags', '', $file) ){
        return $contents;
    } else if ($file['view']) {
        return get_view_thumbnail($file['view']);
    }

    return $file['view'];
}

// 게시글보기 썸네일 생성
function get_view_thumbnail($contents, $thumb_width=0)
{
    global $board, $config;

    if (!$thumb_width)
        $thumb_width = $board['bo_image_width'];

    // $contents 중 img 태그 추출
    $matches = get_editor_image($contents, true);

    if(empty($matches))
        return $contents;

    $extensions = array(1=>'gif', 2=>'jpg', 3=>'png', 18=>'webp');

    for($i=0; $i<count($matches[1]); $i++) {

        $img = $matches[1][$i];
        $img_tag = isset($matches[0][$i]) ? $matches[0][$i] : '';

        preg_match("/src=[\'\"]?([^>\'\"]+[^>\'\"]+)/i", $img, $m);
        $src = isset($m[1]) ? $m[1] : '';
        preg_match("/style=[\"\']?([^\"\'>]+)/i", $img, $m);
        $style = isset($m[1]) ? $m[1] : '';
        preg_match("/width:\s*(\d+)px/", $style, $m);
        $width = isset($m[1]) ? $m[1] : '';
        preg_match("/height:\s*(\d+)px/", $style, $m);
        $height = isset($m[1]) ? $m[1] : '';
        preg_match("/alt=[\"\']?([^\"\']*)[\"\']?/", $img, $m);
        $alt = isset($m[1]) ? get_text($m[1]) : '';

        // 이미지 path 구함
        $p = parse_url($src);
        if(strpos($p['path'], '/'.G5_DATA_DIR.'/') != 0)
            $data_path = preg_replace('/^\/.*\/'.G5_DATA_DIR.'/', '/'.G5_DATA_DIR, $p['path']);
        else
            $data_path = $p['path'];

        $srcfile = G5_PATH.$data_path;

        if(is_file($srcfile)) {
            $size = @getimagesize($srcfile);
            if(empty($size))
                continue;

            $file_ext = $extensions[$size[2]];
            if (!$file_ext) continue;

            // jpg 이면 exif 체크
            if( $file_ext === 'jpg' && function_exists('exif_read_data')) {
                $degree = 0;
                $exif = @exif_read_data($srcfile);
                if(!empty($exif['Orientation'])) {
                    switch($exif['Orientation']) {
                        case 8:
                            $degree = 90;
                            break;
                        case 3:
                            $degree = 180;
                            break;
                        case 6:
                            $degree = -90;
                            break;
                    }

                    // 세로사진의 경우 가로, 세로 값 바꿈
                    if($degree == 90 || $degree == -90) {
                        $tmp = $size;
                        $size[0] = $tmp[1];
                        $size[1] = $tmp[0];
                    }
                }
            }

            // Animated GIF 체크
            $is_animated = false;
            if($file_ext === 'gif') {
                $is_animated = is_animated_gif($srcfile);

                if($replace_content = run_replace('thumbnail_is_animated_gif_content', '', $contents, $srcfile, $is_animated, $img_tag, $data_path, $size)){

                    $contents = $replace_content;
                    continue;
                }
            }

            // 원본 width가 thumb_width보다 작다면
            if($size[0] <= $thumb_width)
                continue;

            // 썸네일 높이
            $thumb_height = round(($thumb_width * $size[1]) / $size[0]);
            $filename = basename($srcfile);
            $filepath = dirname($srcfile);

            // 썸네일 생성
            if(!$is_animated)
                $thumb_file = thumbnail($filename, $filepath, $filepath, $thumb_width, $thumb_height, false);
            else
                $thumb_file = $filename;

            if(!$thumb_file)
                continue;

            if ($width) {
                $thumb_tag = '<img src="'.G5_URL.str_replace($filename, $thumb_file, $data_path).'" alt="'.$alt.'" width="'.$width.'" height="'.$height.'"/>';
            } else {
                $thumb_tag = '<img src="'.G5_URL.str_replace($filename, $thumb_file, $data_path).'" alt="'.$alt.'"/>';
            }
            
            // $img_tag에 editor 경로가 있으면 원본보기 링크 추가
            if(strpos($img_tag, G5_DATA_DIR.'/'.G5_EDITOR_DIR) && preg_match("/\.({$config['cf_image_extension']})$/i", $filename)) {
                $imgurl = str_replace(G5_URL, "", $src);
                $attr_href = run_replace('thumb_view_image_href', G5_BBS_URL.'/view_image.php?fn='.urlencode($imgurl), $filename, '', $width, $height, $alt);
                $thumb_tag = '<a href="'.$attr_href.'" target="_blank" class="view_image">'.$thumb_tag.'</a>';
            }

            $contents = str_replace($img_tag, $thumb_tag, $contents);
        }
    }

    return run_replace('get_view_thumbnail', $contents);
}

function thumbnail($filename, $source_path, $target_path, $thumb_width, $thumb_height, $is_create, $is_crop=false, $crop_mode='center', $is_sharpen=false, $um_value='80/0.5/3')
{
    global $g5;

    if(!$thumb_width && !$thumb_height)
        return;

    $source_file = "$source_path/$filename";

    if(!is_file($source_file)) // 원본 파일이 없다면
        return;


    $size = @getimagesize($source_file);

    $extensions = array(1 => 'gif', 2 => 'jpg', 3 => 'png', 18 => 'webp');
    $file_ext = $extensions[$size[2]]; // 파일 확장자
    if (!$file_ext) return;

    // gif, jpg, png, webp 에 대해서만 적용
    // if ( !(isset($size[2]) && ($size[2] == 1 || $size[2] == 2 || $size[2] == 3 || $size[2] == 18)) ) 
    //     return;

    // $extensions 배열에 없는 확장자 라면 썸네일 만들지 않음
    // if (!in_array($file_ext, $extensions))
    //     return;

    if (!is_dir($target_path)) {
        @mkdir($target_path, G5_DIR_PERMISSION);
        @chmod($target_path, G5_DIR_PERMISSION);
    }

    // 디렉토리가 존재하지 않거나 쓰기 권한이 없으면 썸네일 생성하지 않음
    if(!(is_dir($target_path) && is_writable($target_path)))
        return '';

    // Animated GIF는 썸네일 생성하지 않음
    if($file_ext === 'gif') {
        if(is_animated_gif($source_file))
            return basename($source_file);
    } else if ($file_ext === 'webp') {
        if(is_animated_webp($source_file))
            return basename($source_file);
    }


    $thumb_filename = preg_replace("/\.[^\.]+$/i", "", $filename); // 확장자제거
    // $thumb_file = "$target_path/thumb-{$thumb_filename}_{$thumb_width}x{$thumb_height}.".$ext[$size[2]];
    $thumb_file = "$target_path/thumb-{$thumb_filename}_{$thumb_width}x{$thumb_height}.".$file_ext;
    
    $thumb_time = @filemtime($thumb_file);
    $source_time = @filemtime($source_file);

    if (file_exists($thumb_file)) {
        if ($is_create == false && $source_time < $thumb_time) {
            return basename($thumb_file);
        }
    }

    // 원본파일의 GD 이미지 생성
    $src = null;
    $degree = 0;

    if ($file_ext === 'gif') {
        $src = @imagecreatefromgif($source_file);
        $src_transparency = @imagecolortransparent($src);
    } else if ($file_ext === 'jpg') {
        $src = @imagecreatefromjpeg($source_file);

        if(function_exists('exif_read_data')) {
            // exif 정보를 기준으로 회전각도 구함
            $exif = @exif_read_data($source_file);
            if(!empty($exif['Orientation'])) {
                switch($exif['Orientation']) {
                    case 8:
                        $degree = 90;
                        break;
                    case 3:
                        $degree = 180;
                        break;
                    case 6:
                        $degree = -90;
                        break;
                }

                // 회전각도 있으면 이미지 회전
                if($degree) {
                    $src = imagerotate($src, $degree, 0);

                    // 세로사진의 경우 가로, 세로 값 바꿈
                    if($degree == 90 || $degree == -90) {
                        $tmp = $size;
                        $size[0] = $tmp[1];
                        $size[1] = $tmp[0];
                    }
                }
            }
        }
    } else if ($file_ext === 'png') {
        $src = @imagecreatefrompng($source_file);
        @imagealphablending($src, true);
    } else if ($file_ext === 'webp') {
        $src = @imagecreatefromwebp($source_file);
        @imagealphablending($src, true);
    } else {
        return;
    }

    if(!$src)
        return;

    $is_large = true;
    // width, height 설정

    if($thumb_width) {
        if(!$thumb_height) {
            $thumb_height = round(($thumb_width * $size[1]) / $size[0]);
        } else {
            if($crop_mode === 'center' && ($size[0] > $thumb_width || $size[1] > $thumb_height)){
                $is_large = true;
            } else if($size[0] < $thumb_width || $size[1] < $thumb_height) {
                $is_large = false;
            }
        }
    } else {
        if($thumb_height) {
            $thumb_width = round(($thumb_height * $size[0]) / $size[1]);
        }
    }

    $dst_x = 0;
    $dst_y = 0;
    $src_x = 0;
    $src_y = 0;
    $dst_w = $thumb_width;
    $dst_h = $thumb_height;
    $src_w = $size[0];
    $src_h = $size[1];

    $ratio = $dst_h / $dst_w;

    if($is_large) {
        // 크롭처리
        if($is_crop) {
            switch($crop_mode)
            {
                case 'center':
                    if($size[1] / $size[0] >= $ratio) {
                        $src_h = round($src_w * $ratio);
                        $src_y = round(($size[1] - $src_h) / 2);
                    } else {
                        $src_w = round($size[1] / $ratio);
                        $src_x = round(($size[0] - $src_w) / 2);
                    }
                    break;
                default:
                    if($size[1] / $size[0] >= $ratio) {
                        $src_h = round($src_w * $ratio);
                    } else {
                        $src_w = round($size[1] / $ratio);
                    }
                    break;
            }

            $dst = imagecreatetruecolor($dst_w, $dst_h);

            if($file_ext === 'png') {
                imagealphablending($dst, false);
                imagesavealpha($dst, true);
            } else if($file_ext === 'gif') {
                $palletsize = imagecolorstotal($src);
                if($src_transparency >= 0 && $src_transparency < $palletsize) {
                    $transparent_color   = imagecolorsforindex($src, $src_transparency);
                    $current_transparent = imagecolorallocate($dst, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']);
                    imagefill($dst, 0, 0, $current_transparent);
                    imagecolortransparent($dst, $current_transparent);
                }
            }
        } else { // 비율에 맞게 생성
            $dst = imagecreatetruecolor($dst_w, $dst_h);
            $bgcolor = imagecolorallocate($dst, 255, 255, 255); // 배경색

            if ( !((defined('G5_USE_THUMB_RATIO') && false === G5_USE_THUMB_RATIO) || (defined('G5_THEME_USE_THUMB_RATIO') && false === G5_THEME_USE_THUMB_RATIO)) ){
                if($src_w > $src_h) {
                    $tmp_h = round(($dst_w * $src_h) / $src_w);
                    $dst_y = round(($dst_h - $tmp_h) / 2);
                    $dst_h = $tmp_h;
                } else {
                    $tmp_w = round(($dst_h * $src_w) / $src_h);
                    $dst_x = round(($dst_w - $tmp_w) / 2);
                    $dst_w = $tmp_w;
                }
            }

            if($file_ext === 'png') {
                $bgcolor = imagecolorallocatealpha($dst, 0, 0, 0, 127);
                imagefill($dst, 0, 0, $bgcolor);
                imagealphablending($dst, false);
                imagesavealpha($dst, true);
            } else if($file_ext === 'gif') {
                $palletsize = imagecolorstotal($src);
                if($src_transparency >= 0 && $src_transparency < $palletsize) {
                    $transparent_color   = imagecolorsforindex($src, $src_transparency);
                    $current_transparent = imagecolorallocate($dst, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']);
                    imagefill($dst, 0, 0, $current_transparent);
                    imagecolortransparent($dst, $current_transparent);
                } else {
                    imagefill($dst, 0, 0, $bgcolor);
                }
            } else {
                imagefill($dst, 0, 0, $bgcolor);
            }
        }
    } else {
        $dst = imagecreatetruecolor($dst_w, $dst_h);
        $bgcolor = imagecolorallocate($dst, 255, 255, 255); // 배경색

        if ( ((defined('G5_USE_THUMB_RATIO') && false === G5_USE_THUMB_RATIO) || (defined('G5_THEME_USE_THUMB_RATIO') && false === G5_THEME_USE_THUMB_RATIO)) ){
            //이미지 썸네일을 비율 유지하지 않습니다.  (5.2.6 버전 이하에서 처리된 부분과 같음)

            if($src_w < $dst_w) {
                if($src_h >= $dst_h) {
                    $dst_x = round(($dst_w - $src_w) / 2);
                    $src_h = $dst_h;
                    if( $dst_w > $src_w ){
                        $dst_w = $src_w;
                    }
                } else {
                    $dst_x = round(($dst_w - $src_w) / 2);
                    $dst_y = round(($dst_h - $src_h) / 2);
                    $dst_w = $src_w;
                    $dst_h = $src_h;
                }
            } else {
                if($src_h < $dst_h) {
                    $dst_y = round(($dst_h - $src_h) / 2);
                    $dst_h = $src_h;
                    $src_w = $dst_w;
                }
            }

        } else {
            //이미지 썸네일을 비율 유지하며 썸네일 생성합니다.
            if($src_w < $dst_w) {
                if($src_h >= $dst_h) {
                    if( $src_h > $src_w ){
                        $tmp_w = round(($dst_h * $src_w) / $src_h);
                        $dst_x = round(($dst_w - $tmp_w) / 2);
                        $dst_w = $tmp_w;
                    } else {
                        $dst_x = round(($dst_w - $src_w) / 2);
                        $src_h = $dst_h;
                        if( $dst_w > $src_w ){
                            $dst_w = $src_w;
                        }
                    }
                } else {
                    $dst_x = round(($dst_w - $src_w) / 2);
                    $dst_y = round(($dst_h - $src_h) / 2);
                    $dst_w = $src_w;
                    $dst_h = $src_h;
                }
            } else {
                if($src_h < $dst_h) {
                    if( $src_w > $dst_w ){
                        $tmp_h = round(($dst_w * $src_h) / $src_w);
                        $dst_y = round(($dst_h - $tmp_h) / 2);
                        $dst_h = $tmp_h;
                    } else {
                        $dst_y = round(($dst_h - $src_h) / 2);
                        $dst_h = $src_h;
                        $src_w = $dst_w;
                    }
                }
            }
        }

        if($file_ext === 'png') {
            $bgcolor = imagecolorallocatealpha($dst, 0, 0, 0, 127);
            imagefill($dst, 0, 0, $bgcolor);
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
        } else if($file_ext === 'gif') {
            $palletsize = imagecolorstotal($src);
            if($src_transparency >= 0 && $src_transparency < $palletsize) {
                $transparent_color   = imagecolorsforindex($src, $src_transparency);
                $current_transparent = imagecolorallocate($dst, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']);
                imagefill($dst, 0, 0, $current_transparent);
                imagecolortransparent($dst, $current_transparent);
            } else {
                imagefill($dst, 0, 0, $bgcolor);
            }
        } else {
            imagefill($dst, 0, 0, $bgcolor);
        }
    }

    imagecopyresampled($dst, $src, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);

    // sharpen 적용
    if($is_sharpen && $is_large) {
        $val = explode('/', $um_value);
        UnsharpMask($dst, $val[0], $val[1], $val[2]);
    }

    if($file_ext === 'gif') {
        imagegif($dst, $thumb_file);
    } else if($file_ext === 'png') {
        if(!defined('G5_THUMB_PNG_COMPRESS'))
            $png_compress = 5;
        else
            $png_compress = G5_THUMB_PNG_COMPRESS;

        imagepng($dst, $thumb_file, $png_compress);
    } else if ($file_ext === 'jpg') {
        if(!defined('G5_THUMB_JPG_QUALITY'))
            $jpg_quality = 90;
        else
            $jpg_quality = G5_THUMB_JPG_QUALITY;

        imagejpeg($dst, $thumb_file, $jpg_quality);
    } else if ($file_ext === 'webp') {
        imagewebp($dst, $thumb_file);
    }

    chmod($thumb_file, G5_FILE_PERMISSION); // 추후 삭제를 위하여 파일모드 변경

    imagedestroy($src);
    imagedestroy($dst);

    return basename($thumb_file);
}

function UnsharpMask($img, $amount, $radius, $threshold)    {

/*
출처 : http://vikjavev.no/computing/ump.php
New:
- In version 2.1 (February 26 2007) Tom Bishop has done some important speed enhancements.
- From version 2 (July 17 2006) the script uses the imageconvolution function in PHP
version >= 5.1, which improves the performance considerably.


Unsharp masking is a traditional darkroom technique that has proven very suitable for
digital imaging. The principle of unsharp masking is to create a blurred copy of the image
and compare it to the underlying original. The difference in colour values
between the two images is greatest for the pixels near sharp edges. When this
difference is subtracted from the original image, the edges will be
accentuated.

The Amount parameter simply says how much of the effect you want. 100 is 'normal'.
Radius is the radius of the blurring circle of the mask. 'Threshold' is the least
difference in colour values that is allowed between the original and the mask. In practice
this means that low-contrast areas of the picture are left unrendered whereas edges
are treated normally. This is good for pictures of e.g. skin or blue skies.

Any suggenstions for improvement of the algorithm, expecially regarding the speed
and the roundoff errors in the Gaussian blur process, are welcome.

*/

////////////////////////////////////////////////////////////////////////////////////////////////
////
////                  Unsharp Mask for PHP - version 2.1.1
////
////    Unsharp mask algorithm by Torstein Hønsi 2003-07.
////             thoensi_at_netcom_dot_no.
////               Please leave this notice.
////
///////////////////////////////////////////////////////////////////////////////////////////////



    // $img is an image that is already created within php using
    // imgcreatetruecolor. No url! $img must be a truecolor image.

    // Attempt to calibrate the parameters to Photoshop:
    if ($amount > 500)    $amount = 500;
    $amount = $amount * 0.016;
    if ($radius > 50)    $radius = 50;
    $radius = $radius * 2;
    if ($threshold > 255)    $threshold = 255;

    $radius = abs(round($radius));     // Only integers make sense.
    if ($radius == 0) {
        return $img; imagedestroy($img);        }
    $w = imagesx($img); $h = imagesy($img);
    $imgCanvas = imagecreatetruecolor($w, $h);
    $imgBlur = imagecreatetruecolor($w, $h);


    // Gaussian blur matrix:
    //
    //    1    2    1
    //    2    4    2
    //    1    2    1
    //
    //////////////////////////////////////////////////


    if (function_exists('imageconvolution')) { // PHP >= 5.1
            $matrix = array(
            array( 1, 2, 1 ),
            array( 2, 4, 2 ),
            array( 1, 2, 1 )
        );
        $divisor = array_sum(array_map('array_sum', $matrix));
        $offset = 0;

        imagecopy ($imgBlur, $img, 0, 0, 0, 0, $w, $h);
        imageconvolution($imgBlur, $matrix, $divisor, $offset);
    }
    else {

    // Move copies of the image around one pixel at the time and merge them with weight
    // according to the matrix. The same matrix is simply repeated for higher radii.
        for ($i = 0; $i < $radius; $i++)    {
            imagecopy ($imgBlur, $img, 0, 0, 1, 0, $w - 1, $h); // left
            imagecopymerge ($imgBlur, $img, 1, 0, 0, 0, $w, $h, 50); // right
            imagecopymerge ($imgBlur, $img, 0, 0, 0, 0, $w, $h, 50); // center
            imagecopy ($imgCanvas, $imgBlur, 0, 0, 0, 0, $w, $h);

            imagecopymerge ($imgBlur, $imgCanvas, 0, 0, 0, 1, $w, $h - 1, 33.33333 ); // up
            imagecopymerge ($imgBlur, $imgCanvas, 0, 1, 0, 0, $w, $h, 25); // down
        }
    }

    if($threshold>0){
        // Calculate the difference between the blurred pixels and the original
        // and set the pixels
        for ($x = 0; $x < $w-1; $x++)    { // each row
            for ($y = 0; $y < $h; $y++)    { // each pixel

                $rgbOrig = ImageColorAt($img, $x, $y);
                $rOrig = (($rgbOrig >> 16) & 0xFF);
                $gOrig = (($rgbOrig >> 8) & 0xFF);
                $bOrig = ($rgbOrig & 0xFF);

                $rgbBlur = ImageColorAt($imgBlur, $x, $y);

                $rBlur = (($rgbBlur >> 16) & 0xFF);
                $gBlur = (($rgbBlur >> 8) & 0xFF);
                $bBlur = ($rgbBlur & 0xFF);

                // When the masked pixels differ less from the original
                // than the threshold specifies, they are set to their original value.
                $rNew = (abs($rOrig - $rBlur) >= $threshold)
                    ? max(0, min(255, ($amount * ($rOrig - $rBlur)) + $rOrig))
                    : $rOrig;
                $gNew = (abs($gOrig - $gBlur) >= $threshold)
                    ? max(0, min(255, ($amount * ($gOrig - $gBlur)) + $gOrig))
                    : $gOrig;
                $bNew = (abs($bOrig - $bBlur) >= $threshold)
                    ? max(0, min(255, ($amount * ($bOrig - $bBlur)) + $bOrig))
                    : $bOrig;



                if (($rOrig != $rNew) || ($gOrig != $gNew) || ($bOrig != $bNew)) {
                        $pixCol = ImageColorAllocate($img, $rNew, $gNew, $bNew);
                        ImageSetPixel($img, $x, $y, $pixCol);
                    }
            }
        }
    }
    else{
        for ($x = 0; $x < $w; $x++)    { // each row
            for ($y = 0; $y < $h; $y++)    { // each pixel
                $rgbOrig = ImageColorAt($img, $x, $y);
                $rOrig = (($rgbOrig >> 16) & 0xFF);
                $gOrig = (($rgbOrig >> 8) & 0xFF);
                $bOrig = ($rgbOrig & 0xFF);

                $rgbBlur = ImageColorAt($imgBlur, $x, $y);

                $rBlur = (($rgbBlur >> 16) & 0xFF);
                $gBlur = (($rgbBlur >> 8) & 0xFF);
                $bBlur = ($rgbBlur & 0xFF);

                $rNew = ($amount * ($rOrig - $rBlur)) + $rOrig;
                    if($rNew>255){$rNew=255;}
                    elseif($rNew<0){$rNew=0;}
                $gNew = ($amount * ($gOrig - $gBlur)) + $gOrig;
                    if($gNew>255){$gNew=255;}
                    elseif($gNew<0){$gNew=0;}
                $bNew = ($amount * ($bOrig - $bBlur)) + $bOrig;
                    if($bNew>255){$bNew=255;}
                    elseif($bNew<0){$bNew=0;}
                $rgbNew = ($rNew << 16) + ($gNew <<8) + $bNew;
                    ImageSetPixel($img, $x, $y, $rgbNew);
            }
        }
    }
    imagedestroy($imgCanvas);
    imagedestroy($imgBlur);

    return true;

}

// 움직이는 webp 파일인지 검사한다.
// 출처) https://stackoverflow.com/questions/45190469/how-to-identify-whether-webp-image-is-static-or-animated?answertab=votes#tab-top
function is_animated_webp($filename) {
    $contents = file_get_contents($filename);
    $where = strpos($contents, "ANMF");
    if ($where !== false){
        // animated
        $is_animated = true;
    }
    else{
        // non animated
        $is_animated = false;
    }
    return $is_animated;
}

function is_animated_gif($filename) {

    static $cache = array();
    $key = md5($filename);

    if( isset($cache[$key]) ){
        return $cache[$key];
    }

    if(!($fh = @fopen($filename, 'rb'))){
        $cache[$key] = false;
        return false;
    }

    $count = 0;
    // 출처 : http://www.php.net/manual/en/function.imagecreatefromgif.php#104473
    // an animated gif contains multiple "frames", with each frame having a
    // header made up of:
    // * a static 4-byte sequence (\x00\x21\xF9\x04)
    // * 4 variable bytes
    // * a static 2-byte sequence (\x00\x2C) (some variants may use \x00\x21 ?)

    // We read through the file til we reach the end of the file, or we've found
    // at least 2 frame headers
    while(!feof($fh) && $count < 2) {
        $chunk = fread($fh, 1024 * 100); //read 100kb at a time
        $count += preg_match_all('#\x00\x21\xF9\x04.{4}\x00(\x2C|\x21)#s', $chunk, $matches);
   }

    fclose($fh);

    $cache[$key] = ($count > 1) ? true : false;

    run_event('is_animated_gif_after', $filename, $cache[$key]);

    return $cache[$key];
}