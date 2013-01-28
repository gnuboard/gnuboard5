<?php
if (!defined('_GNUBOARD_')) exit;

@ini_set('memory_limit', '512M');

function it_img_thumb($filename, $filepath, $thumb_width, $thumb_height, $is_create=false)
{
    return thumbnail($filename, $filepath, $filepath, $thumb_width, $thumb_height, $is_create);
}

//function thumbnail($bo_table, $file, $width, $height, $is_create=false)
function thumbnail($filename, $source_path, $target_path, $thumb_width, $thumb_height, $is_create);
{
    global $g4;

    $thumb_dir = "$g4[path]/data/thumb/$bo_table";
    if (!is_dir($thumb_dir)) {
        @mkdir($thumb_dir, 0707);
        @chmod($thumb_dir, 0707);
    }
    $thumb_file = "$thumb_dir/{$width}x{$height}_{$file}.png";
    $thumb_time = @filemtime($thumb_file);
    $source_file = "$g4[path]/data/file/$bo_table/$file";
    $source_time = @filemtime($source_file);
    if (file_exists($thumb_file)) {
        if ($is_create == false && $source_time < $thumb_time) {
            return $thumb_file;
        }
    }

    $size = @getimagesize($source_file);
    // 이미지 파일이 없거나 아님
    if (!$size[0]) {
        if (!$height) $height = $width;
        $thumb_file = "$g4[path]/data/thumb/{$width}x{$height}_noimg.gif";
        if (!file_exists($thumb_file)) {
            $target = imagecreate($width, $height);
            imagecolorallocate($target, 250, 250, 250);
            imagecopy($target, $target, 0, 0, 0, 0, $width, $height);
            imagepng($target, $thumb_file, 0);
            @chmod($thumb_file, 0606); // 추후 삭제를 위하여 파일모드 변경
        }
        return $thumb_file;
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

    if ($width) {
        if ($height) {
            $rate = $width / $size[0];
            $tmp_height = (int)($size[1] * $rate);
            if ($tmp_height < $height) {
                $dst = imagecreatetruecolor($width, $height);
                $bgcolor = imagecolorallocate($dst, 250, 250, 250); // 배경색 여기야!!!
                imagefill($dst, 0, 0, $bgcolor);
                imagecopyresampled($dst, $src, 0, 0, 0, 0, $width, $tmp_height, $size[0], $size[1]);
            } else {
                $dst = imagecreatetruecolor($width, $height);
                imagecopyresampled($dst, $src, 0, 0, 0, 0, $width, $tmp_height, $size[0], $size[1]);
            }
        } else {
            $rate = $width / $size[0];
            $tmp_height = (int)($size[1] * $rate);
            $dst = imagecreatetruecolor($width, $tmp_height);
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $width, $tmp_height, $size[0], $size[1]);
        }
    }

    imagepng($dst, $thumb_file, 0); // 0 (no compression) ~ 9
    chmod($thumb_file, 0606); // 추후 삭제를 위하여 파일모드 변경
    return $thumb_file;
}
?>