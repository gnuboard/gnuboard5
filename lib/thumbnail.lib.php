<?php
if (!defined('_GNUBOARD_')) exit;

@ini_set('memory_limit', '512M');

function it_img_thumb($filename, $filepath, $thumb_width, $thumb_height, $is_create=false)
{
    return thumbnail($filename, $filepath, $filepath, $thumb_width, $thumb_height, $is_create);
}

// 게시글보기 썸네일 생성
function get_view_thumbnail($contents)
{
    global $board;
    $dvc_width = intval($_COOKIE['device_width']);

    if(G4_IS_MOBILE && $dvc_width) {
        // 썸네일 width 설정
        $thumb_width = 320;

        if($dvc_width >= 1000) {
            return $contents;
        } else if($dvc_width >= 760 && $dvc_width < 1000) {
            $thumb_width = 760;
        } else if($dvc_width >= 480 && $dvc_width < 760) {
            $thumb_width = 480;
        }
    } else {
        $thumb_width = $board['bo_image_width'];
    }

    // $contents 중 img 태그 추출
    $matchs = get_editor_image($contents);

    if(!$matchs)
        return $contents;

    for($i=0; $i<count($matchs[1]); $i++) {
        // 이미지 path 구함
        $imgurl = parse_url($matchs[1][$i]);
        $srcfile = $_SERVER['DOCUMENT_ROOT'].$imgurl['path'];

        if(is_file($srcfile)) {
            // 썸네일 높이
            $size = @getimagesize($srcfile);
            if(empty($size))
                continue;

            // 원본 width가 thumb_width보다 작다면
            if($size[0] <= $thumb_width)
                continue;

            // Animated GIF 체크
            $is_animated = false;
            if($size[2] == 1) {
                $is_animated = is_animated_gif($srcfile);
            }

            $thumb_height = round(($thumb_width * $size[1]) / $size[0]);
            $filename = basename($srcfile);
            $filepath = dirname($srcfile);

            // 썸네일 생성
            if(!$is_animated)
                $thumb_file = thumbnail($filename, $filepath, $filepath, $thumb_width, $thumb_height, false);
            else
                $thumb_file = $filename;

            $img_tag = $matchs[0][$i];
            $thumb_tag = str_replace($filename, $thumb_file, $img_tag);

            // img 태그에 width 값이 있을 경우 width 값 바꿔줌
            preg_match("/width=[\'\"]?([0-9]+)[\'\"]?/", $img_tag, $mw);
            if(!empty($mw[1])) {
                $thumb_tag = str_replace($mw[0], str_replace($mw[1], $thumb_width, $mw[0]), $thumb_tag);
            }

            // img 태그에 height 값이 있을 경우 height 값 바꿔줌
            preg_match("/height=[\'\"]?([0-9]+)[\'\"]?/", $img_tag, $mh);
            if(!empty($mh[1])) {
                $thumb_tag = str_replace($mh[0], str_replace($mh[1], $thumb_height, $mh[0]), $thumb_tag);
            }

            $contents = str_replace($img_tag, $thumb_tag, $contents);
        }
    }

    return $contents;
}

//function thumbnail($bo_table, $file, $width, $height, $is_create=false)
function thumbnail($filename, $source_path, $target_path, $thumb_width, $thumb_height, $is_create)
{
    global $g4;

    $thumb_filename = preg_replace("/\.[^\.]+$/i", "", $filename); // 확장자제거

    if (!is_dir($target_path)) {
        @mkdir($target_path, 0707);
        @chmod($target_path, 0707);
    }

    $thumb_file = "$target_path/thumb-{$thumb_filename}_{$thumb_width}x{$thumb_height}.jpg";
    $thumb_time = @filemtime($thumb_file);
    $source_file = "$source_path/$filename";
    $source_time = @filemtime($source_file);
    if (file_exists($thumb_file)) {
        if ($is_create == false && $source_time < $thumb_time) {
            return basename($thumb_file);
        }
    }

    $size = @getimagesize($source_file);
    // 이미지 파일이 없거나 아님
    if (!$size[0]) {
        if (!$thumb_height) $thumb_height = $thumb_width;
        $thumb_file = "$target_path/thumb-noimg_{$thumb_width}x{$thumb_height}.jpg";
        if (!file_exists($thumb_file)) {
            $target = imagecreate($thumb_width, $thumb_height);
            imagecolorallocate($target, 250, 250, 250);
            imagecopy($target, $target, 0, 0, 0, 0, $thumb_width, $thumb_height);
            imagepng($target, $thumb_file, 0);
            @chmod($thumb_file, 0606); // 추후 삭제를 위하여 파일모드 변경
        }
        return basename($thumb_file);
    }

    // Animated GIF 체크
    if($size[2] == 1) {
        if(is_animated_gif($source_file))
            return basename($source_file);
    }

    $is_imagecopyresampled = false;
    $is_large = false;

    $src = null;
    if ($size[2] == 1) {
        $src = imagecreatefromgif($source_file);
    } else if ($size[2] == 2) {
        $src = imagecreatefromjpeg($source_file);
    } else if ($size[2] == 3) {
        $src = imagecreatefrompng($source_file);
    }

    if ($thumb_width) {
        if ($thumb_height) {
            $rate = $thumb_width / $size[0];
            $tmp_height = (int)($size[1] * $rate);
            if ($tmp_height < $thumb_height) {
                $dst = imagecreatetruecolor($thumb_width, $thumb_height);
                $bgcolor = imagecolorallocate($dst, 250, 250, 250); // 배경색 여기야!!!
                imagefill($dst, 0, 0, $bgcolor);
                imagecopyresampled($dst, $src, 0, 0, 0, 0, $thumb_width, $thumb_height, $size[0], $size[1]);
            } else {
                $dst = imagecreatetruecolor($thumb_width, $thumb_height);
                imagecopyresampled($dst, $src, 0, 0, 0, 0, $thumb_width, $thumb_height, $size[0], $size[1]);
            }
        } else {
            $rate = $thumb_width / $size[0];
            $tmp_height = (int)($size[1] * $rate);
            $dst = imagecreatetruecolor($thumb_width, $tmp_height);
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $thumb_width, $tmp_height, $size[0], $size[1]);
        }
    }

    //imagepng($dst, $thumb_file, 9); // 0 (no compression) ~ 9
    imagejpeg($dst, $thumb_file, 90);
    chmod($thumb_file, 0606); // 추후 삭제를 위하여 파일모드 변경

    return basename($thumb_file);
}

function is_animated_gif($filename) {
    if(!($fh = @fopen($filename, 'rb')))
        return false;
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
    return $count > 1;
}
?>