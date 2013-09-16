<?php
if (!defined('_GNUBOARD_')) exit;

// 원본 이미지를 넘기면 비율에 따라 썸네일 이미지를 생성함
function mobile_create_thumb($srcImg, $width, $thumb) 
{
    $size = @getimagesize($srcImg);
    if ($size[2] == 1) 
        $source = @imagecreatefromgif($srcImg);
    else if ($size[2] == 2)
        $source = @imagecreatefromjpeg($srcImg);
    else if ($size[2] == 3) 
        $source = @imagecreatefrompng($srcImg);
    else 
        return "";

    if (!$source)
        return "";

    if ($size[0] < $width) {
        $width  = $size[0];
        $height = $size[1];
    } 
    else {
        $rate = $width / $size[0];
        $height = (int)($size[1] * $rate);
    }

    $target = @imagecreatetruecolor($width, $height);
    $bgcolor = @imagecolorallocate($target, 255, 255, 255); // 썸네일 배경
    imagefilledrectangle($target, 0, 0, $width, $height, $bgcolor);
    imagecopyresampled($source, $source, 0, 0, 0, 0, $width, $height, $size[0], $size[1]);
    imagecopy($target, $source, 0, 0, 0, 0, $size[0], $size[1]);

    imagejpeg($target, $thumb, 100);
    chmod($thumb, G5_FILE_PERMISSION); // 추후 삭제를 위하여 파일모드 변경

    return $thumb;
}


function mobile_thumb($matches)
{
    global $is_admin;
    global $g5, $bo_table, $wr_id;

    $width = 300; // (소스이미지 width pixel)

    //if ($is_admin) print_r2($matches);

    if ($is_admin) {
        foreach ($matches as $img) {
            echo "<p>";

            preg_match("/src=[\"\']?([^\"\'\s>]+)/i", $img, $m);
            $src = trim($m[1]);
            //echo $src;

            // 상대경로(..)로 시작되면 sir.co.kr 도메인으로 여긴다.
            $src = preg_replace("/^\.\.\//", "http://m.sir.co.kr/", $src);
            $absolute = preg_replace("/^http\:\/\/(www\.)?sir\.co\.kr\/(.*)$/", "/home/sir/$2", $src);

            $thumb_dir = G5_DATA_PATH.'/thumb/'.$bo_table;
            if (!is_dir($thumb_dir)) {
                @mkdir($thumb_dir, G5_DIR_PERMISSION);
                @chmod($thumb_dir, G5_DIR_PERMISSION);
            }

            $result = true;

            if (preg_match("/\.(jpe?g|png)$/i", $src)) {
                // 유일한 파일명을 만든다.
                $src_md5 = md5($src.$width);
                $thumb = "$thumb_dir/{$wr_id}-{$src_md5}";

                if (!file_exists($thumb)) {
                    $result = mobile_create_thumb($src, $width, $thumb);
                }
            }
            else {
                $thumb = $src;
            }

            if ($result) {
                $size = @getimagesize($absolute);
                if ($size[2] == IMAGETYPE_GIF)
                    $w = ($size[0] < $width) ? $size[0] : $width;
                else
                    $w = ($size[0] < $width) ? $size[0] : "100%";
                return "<img src='$thumb' width='$w' />";
            }
        }
    } else {

        foreach ($matches as $img) {
            preg_match("/src=[\"\']?([^\"\'\s>]+)/i", $img, $m);
            
            $result = true;

            $src = trim($m[1]);
            //if ($is_admin) echo $src."<br>";
            if (preg_match("/\.(jpe?g|png)$/i", $src)) {
                // 상대경로(..)로 시작되면 도메인으로 여긴다.
                $src = preg_replace("/^\.\.\//", 'http://'.$_SERVER['SERVER_NAME'].'/', $src);

                // 유일한 파일명을 만든다.
                $src_md5 = md5($src.$width);
                $thumb = G5_DATA_PATH.'/thumb/'.$bo_table.'-'.$wr_id.'-'.$src_md5;

                if (!file_exists($thumb)) {
                    $result = mobile_create_thumb($src, $width, $thumb);
                }
            }
            else {
                $thumb = $src;
            }

            if ($result) {
                //if ($is_admin) { $begin_time = get_microtime(); }
                //echo $thumb;
                $size = @getimagesize($thumb);
                //if ($is_admin) print_r2($size);
                if ($size[2] == IMAGETYPE_GIF)
                    $w = ($size[0] < $width) ? $size[0] : $width;
                else
                    $w = ($size[0] < $width) ? $size[0] : "100%";
                //if ($is_admin) { echo "<p>time : "; echo get_microtime() - $begin_time; }
                return "<img src='$thumb' width='$w' />";
            }
        }

    }

}

function mobile_embed($matches)
{
    foreach ($matches as $embed) {
        //$embed = preg_replace("#height\=\d+#i", "", $embed);
        //$embed = preg_replace("#width\=\d+#i", "", $embed);

        return $embed;
    }
}
?>