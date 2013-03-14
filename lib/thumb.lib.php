<?
if (!defined('_GNUBOARD_')) exit;

/*******************************************************************************
    썸네일 Class

    사용법 : 
    
    $thumb = new g4_thumbnail(경로, 썸네일이미지폭);
    $thumb->run(이미지 경로가 포함된 컨텐츠);

*******************************************************************************/
class g4_thumb {
    var $path;
    var $width;
    var $red = 0xff;
    var $green = 0xff;
    var $blue = 0xff;
    var $quality = 100;
    var $permission = 0606;

    function g4_thumb($path, $width) {
        $this->set_path($path);
        $this->set_width($width);
    }

    // 경로
    function set_path($path) {
        $this->path = $path;
        @mkdir($path, 0707);
        @chmod($path, 0707);
    }

    function set_width($width) {
        $this->width = $width;
    }

    function get_width() {
        return $this->width;
    }

    function set_bgcolor($red, $green, $blue) {
        $this->red   = $red;
        $this->green = $green;
        $this->blue  = $blue;
    }

    function set_quality($quality) {
        $this->quality = $quality;
    }

    function set_permission($permission) {
        $this->permission = $permission;
    }

    function create($srcimg, $thumbfile) {
        $width = $this->get_width();
        $source = null;
        $size = @getimagesize($srcimg);
        if ($size[2] == 1) 
            $source = @imagecreatefromgif($srcimg);
        else if ($size[2] == 2)
            $source = @imagecreatefromjpeg($srcimg);
        else if ($size[2] == 3) 
            $source = @imagecreatefrompng($srcimg);

        if ($source == null)
            return '';

        // 이미지의 폭이 설정폭 보다 작다면
        if ($size[0] < $width) {
            $width  = $size[0];
            $height = $size[1];
        } else {
            $rate = $width / $size[0];
            $height = (int)($size[1] * $rate);
        }

        $target = @imagecreatetruecolor($width, $height);
        $bgcolor = @imagecolorallocate($target, $this->red, $this->green, $this->blue); // 썸네일 배경
        imagefilledrectangle($target, 0, 0, $width, $height, $bgcolor);
        imagecopyresampled($source, $source, 0, 0, 0, 0, $width, $height, $size[0], $size[1]);
        imagecopy($target, $source, 0, 0, 0, 0, $size[0], $size[1]);
        imagejpeg($target, $thumbfile, $this->quality);
        chmod($thumbfile, $this->permission); // 추후 삭제를 위하여 파일모드 변경
        return $thumbfile;
    }

    function thumbnail($matches)
    {
        foreach ($matches as $img) {
            preg_match("/src=[\"\']?([^\"\'\s>]+)/i", $img, $m);
            $src = trim($m[1]);

            $result = true;
            if (preg_match("/\.(jpe?g|png|gif)$/i", $src)) {
                // 유일한 파일명을 만든다.
                $filename = md5($src).'_'.$this->width;
                $thumb = $this->path.'/'.$filename;

                if (!file_exists($thumb)) {
                    $result = $this->create($src, $thumb);
                }
            }
            else {
                $thumb = $src;
            }

            if ($result) {
                $size = @getimagesize($src);
                if ($size[0] < $this->width) {
                    return "<img src='$thumb' width='$size[0]' />";
                } else {
                    return "<a href='$src' target='_blank' title='클릭하시면 이미지가 크게 보입니다.'><img src='$thumb' width='{$this->width}' border='0' /></a>";
                }
            }
        }
    }

    function run($content)
    {
        //return preg_replace_callback('#<img[^>]+>#iS', create_function('$matches', 'return g4_thumb::thumbnail($matches);'), $content);
        //return preg_replace_callback('#<img[^>]+>#iS', array('g4_thumb', 'thumbnail'), $content);
        //return preg_replace_callback('#<img[^>]+>#iS', 'g4_thumb::thumbnail', $content);
        return preg_replace_callback('#<img[^>]+>#iS', array($this, 'thumbnail'), $content);
    }
}
?>