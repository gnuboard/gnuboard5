<?php


namespace API\Service;

use const PHP_VERSION_ID;

/**
 * 썸네일관련 이미지 함수들을 제공하는 클래스
 */
class ThumbnailService
{

    /**
     * @var ?int 현재 설정된 php.ini 의 메모리 제한
     */
    private static $current_memory_mb;

    /**
     * HTML 속 원본 이미지태그를 썸네일 이미지 태그로 바꿉니다.
     *
     * @param string $contents html 내용
     * @param int $board_image_width 게시판 이미지 가로 크기
     * @param int $thumb_width 썸네일 가로 크기
     * @return string|null 썸네일 이미지 태그로 변환된 html | 훅스에서 null 을 반환할 경우.
     */
    public static function getThumbnailHtml($contents, $board_image_width, $thumb_width = 0)
    {
        $config = ConfigService::getConfig();

        if (!$thumb_width) {
            $thumb_width = $board_image_width;
        }

        $matches = self::getImageTagFromHtml($contents, true);

        if (empty($matches)) {
            return $contents;
        }

        for ($i = 0;$i < count($matches[1]);$i++) {
            $img = $matches[1][$i];
            $img_tag = $matches[0][$i] ?? '';

            preg_match("/src=[\'\"]?([^>\'\"]+[^>\'\"]+)/i", $img, $match_img);
            $src = $match_img[1] ?? '';
            preg_match("/style=[\"\']?([^\"\'>]+)/i", $img, $match_img);
            $style = $match_img[1] ?? '';
            preg_match("/width:\s*(\d+)px/", $style, $match_img);
            $width = $match_img[1] ?? '';
            preg_match("/height:\s*(\d+)px/", $style, $match_img);
            $height = $match_img[1] ?? '';
            preg_match("/alt=[\"\']?([^\"\']*)[\"\']?/", $img, $match_img);
            $alt = isset($match_img[1]) ? get_text($match_img[1]) : '';

            // 이미지 path 구함
            $url_parts = parse_url($src);
            if (!$url_parts) {
                continue;
            }
            $url_path = $url_parts['path'] ?? '';
            if (strpos($url_path, '/' . G5_DATA_DIR . '/') !== false) {
                $data_path = preg_replace('/^\/.*\/' . G5_DATA_DIR . '/', '/' . G5_DATA_DIR, $url_path);
            } else {
                $data_path = $url_path;
            }

            $srcfile = G5_PATH . $data_path;
            $filename = basename($srcfile);
            $filepath = dirname($srcfile);
            $thumb_file = self::createThumbnail($filename, $filepath, $filepath, $thumb_width);

            if (!$thumb_file) {
                continue;
            }

            if ($width && $height) {
                $thumb_tag = '<img src="' . G5_URL . str_replace($filename, $thumb_file, $data_path) . '" alt="' . $alt . '" width="' . $width . '" height="' . $height . '"/>';
            } else {
                $thumb_tag = '<img src="' . G5_URL . str_replace($filename, $thumb_file, $data_path) . '" alt="' . $alt . '"/>';
            }

            // $img_tag에 editor 경로가 있으면 원본보기 링크 추가
            if (strpos($img_tag, G5_DATA_DIR . '/' . G5_EDITOR_DIR) && preg_match("/\.({$config['cf_image_extension']})$/i", $filename)) {
                $imgurl = str_replace(G5_URL, "", $src);
                $attr_href = run_replace('thumb_view_image_href', G5_BBS_URL . '/view_image.php?fn=' . rawurlencode($imgurl), $filename, '', $width, $height, $alt);
                $thumb_tag = '<a href="' . $attr_href . '" target="_blank" class="view_image">' . $thumb_tag . '</a>';
            }

            $contents = str_replace($img_tag, $thumb_tag, $contents);
        }

        return run_replace('get_view_thumbnail', $contents);
    }


    /**
     * 썸네일 파일을 생성합니다.
     *
     * 원본 확장자를 유지, 저장 디렉토리도 같이생성
     * gif, jpeg/jpg, png, webp 에 대해서만 적용
     *
     * @param string $filename
     * @param string $source_path 원본 파일 경로
     * @param string $target_path 썸네일 파일 저장할 경로
     * @param int $thumb_width
     * @param int $thumb_height
     * @param bool $is_create
     * @param bool $use_crop 썸네일 생성시 크롭 여부
     * @param string $crop_mode 크롭모드 center
     * @param bool $use_sharpen_filter 이미지 선명하게 필터 사용여부 (unsharp 마스킹 적용 여부)
     * @param string $um_value 이미지 unsharp 마스크 적용값 amount, radius, threshold
     * @return false|string  썸네일 파일명| 생성 실패 시 false
     */
    public static function createThumbnail(
        string $filename,
        $source_path,
        $target_path,
        $thumb_width,
        $thumb_height = 0,
        bool $is_create = false,
        bool $use_crop = false,
        string $crop_mode = 'center',
        bool $use_sharpen_filter = false,
        string $um_value = '80/0.5/3'
    ) {
        // 0. 썸네일 생성 조건 확인
        if (!$thumb_width && !$thumb_height) {
            return false;
        }

        $source_file = "$source_path" . DIRECTORY_SEPARATOR . "$filename";
        if (!is_file($source_file)) {
            return false;
        }

        $result_getimagesize = getimagesize($source_file);
        if (!$result_getimagesize) {
            return false;
        }

        // gif, jpeg/jpg, png, webp 에 대해서만 적용
        $extensions = [1 => 'gif', 2 => 'jpg', 3 => 'png', 18 => 'webp'];
        $file_ext = $extensions[$result_getimagesize[2]] ?? '';
        if (!$file_ext) {
            return false;
        }

        // 원본 width가 thumb_width보다 작다면
        if ($result_getimagesize[0] <= $thumb_width) {
            return false;
        }

        // 썸네일 높이
        $thumb_height = (int)round(($thumb_width * $result_getimagesize[1]) / $result_getimagesize[0]);

        // 움직이는 gif 는 썸네일 생성하지 않음
        if ($file_ext === 'gif' && self::isAnimatedGif($source_file)) {
            return basename($source_file);
        }

        // 움직이는 WebP 는 썸네일 생성하지 않음
        if ($file_ext === 'webp' && self::isAnimatedWebp($source_file)) {
            return basename($source_file);
        }

        $is_dir = is_dir($target_path);
        if (!$is_dir) {
            mkdir($target_path, G5_DIR_PERMISSION);
            chmod($target_path, G5_DIR_PERMISSION);
        }

        // 디렉토리가 존재하지 않거나 쓰기 권한이 없으면 썸네일 생성하지 않음
        if (!($is_dir && is_writable($target_path))) {
            return false;
        }

        //썸네일 생성 조건 end

        /**
         * $thumb_name 확장자제거한 이름
         */
        $thumb_name = preg_replace('/\.[^\.]+$/', '', $filename);
        if ($thumb_name === null) {
            return false;
        }

        // 원본파일보다 생성일자가 더 최신이면 기존 썸네일 이미지 반환
        $thumb_file = "$target_path/thumb-{$thumb_name}_{$thumb_width}x{$thumb_height}.{$file_ext}";
        $thumb_time = @filemtime($thumb_file) ?: 0;
        if ($thumb_time !== 0) {
            $source_time = @filemtime($source_file) ?: 0;
            if ($is_create === false && $source_time < $thumb_time) {
                return basename($thumb_file);
            }
        }

        if (self::$current_memory_mb === null) {
            self::$current_memory_mb = self::convertMemoryLimitToMB(ini_get('memory_limit')) ?: 0; //계산 불가시 0
        }

        if (self::$current_memory_mb !== -1) {
            // 현재 이미지 크기에 따라 메모리 제한 설정 (php.ini 설정값보다 크면 변경)
            $require_memory_mb = self::memoryLimitCalcByImageInfo($result_getimagesize);
            if ($require_memory_mb !== false && self::$current_memory_mb) {
                if ($require_memory_mb > self::$current_memory_mb) {
                    ini_set('memory_limit', $require_memory_mb . 'M');
                }
            }
        }

        // 1. 원본파일에서 GD 이미지 생성
        try {
            if ($file_ext === 'gif') {
                $src = imagecreatefromgif($source_file);
                if (!$src) {
                    return false;
                }

                $gif_src_transparency = imagecolortransparent($src);
                if ($gif_src_transparency === -1) {
                    return false;
                }
            } else if ($file_ext === 'jpg') {
                $src = imagecreatefromjpeg($source_file);
                if (!$src) {
                    return false;
                }

                if (EXISTS_EXIF_EXTENSION) {
                    // exif 정보를 기준으로 회전각도 구함
                    $exif = exif_read_data($source_file);
                    if ($exif && isset($exif['Orientation'])) {
                        $degree = 0;
                        switch ($exif['Orientation']) {
                            case 3:
                                $degree = 180;
                                break;
                            case 6:
                                $degree = -90;
                                break;
                            case 8:
                                $degree = 90;
                                break;
                        }

                        // 회전된 이미지는 0 도로 회전.
                        if ($degree) {
                            $src = imagerotate($src, $degree, 0);

                            // 세로사진의 경우 가로, 세로 값 바꿈
                            if ($degree === 90 || $degree === -90) {
                                $tmp = $result_getimagesize;
                                $result_getimagesize[0] = $tmp[1];
                                $result_getimagesize[1] = $tmp[0];
                            }
                        }
                    }
                }
            } else if ($file_ext === 'png') {
                $src = imagecreatefrompng($source_file);
                if (!$src) {
                    return false;
                }
                imagealphablending($src, true);
            } else if ($file_ext === 'webp') {
                $src = imagecreatefromwebp($source_file);
                if (!$src) {
                    return false;
                }
                imagealphablending($src, true);
            } else {
                // 지정된 이미지형식이 아님
                return false;
            }

            if (!$src) {
                return false;
            }

            // 2. 크기 계산
            /**
             * @var bool $is_large 원본이 썸네일 지정 크기보다 가로, 세로 둘 중 하나라도 큰지 여부
             */
            $is_large = true;

            // 썸네일 width, height 설정
            if ($thumb_width) {
                if ($thumb_height) {
                    if ($crop_mode === 'center' && ($result_getimagesize[0] > $thumb_width || $result_getimagesize[1] > $thumb_height)) {
                        $is_large = true;
                    } else if ($result_getimagesize[0] < $thumb_width || $result_getimagesize[1] < $thumb_height) {
                        $is_large = false;
                    }
                } else {
                    $thumb_height = (int)round(($thumb_width * $result_getimagesize[1]) / $result_getimagesize[0]);
                }
            } else if ($thumb_height) {
                $thumb_width = (int)round(($thumb_height * $result_getimagesize[0]) / $result_getimagesize[1]);
            }

            $dst_x = 0;
            $dst_y = 0;
            $src_x = 0;
            $src_y = 0;
            $dst_w = $thumb_width;
            $dst_h = $thumb_height;
            $src_w = $result_getimagesize[0];
            $src_h = $result_getimagesize[1];

            $ratio = $dst_h / $dst_w;

            if ($is_large) {
                // 크롭 처리
                if ($use_crop) {
                    switch ($crop_mode) {
                        case 'center':
                            if ($result_getimagesize[1] / $result_getimagesize[0] >= $ratio) {
                                $src_h = round($src_w * $ratio);
                                $src_y = round(($result_getimagesize[1] - $src_h) / 2);
                            } else {
                                $src_w = round($result_getimagesize[1] / $ratio);
                                $src_x = round(($result_getimagesize[0] - $src_w) / 2);
                            }
                            break;
                        default:
                            if ($result_getimagesize[1] / $result_getimagesize[0] >= $ratio) {
                                $src_h = round($src_w * $ratio);
                            } else {
                                $src_w = round($result_getimagesize[1] / $ratio);
                            }
                            break;
                    }
                } else { // 비율에 맞게 생성.
                    if (!((defined('G5_USE_THUMB_RATIO') && false === G5_USE_THUMB_RATIO) ||
                        (defined('G5_THEME_USE_THUMB_RATIO') && false === G5_THEME_USE_THUMB_RATIO)
                    )) {
                        if ($src_w > $src_h) {
                            $tmp_h = round(($dst_w * $src_h) / $src_w);
                            $dst_y = round(($dst_h - $tmp_h) / 2);
                            $dst_h = $tmp_h;
                        } else {
                            $tmp_w = round(($dst_h * $src_w) / $src_h);
                            $dst_x = round(($dst_w - $tmp_w) / 2);
                            $dst_w = $tmp_w;
                        }
                    }
                }
            } else {
                if (((defined('G5_USE_THUMB_RATIO') && false === G5_USE_THUMB_RATIO)
                    || (defined('G5_THEME_USE_THUMB_RATIO') && false === G5_THEME_USE_THUMB_RATIO))) {
                    //이미지 썸네일을 비율 유지 안함.
                    if ($src_w < $dst_w) {
                        if ($src_h >= $dst_h) {
                            $dst_x = round(($dst_w - $src_w) / 2);
                            $src_h = $dst_h;
                            $dst_w = $src_w;
                        } else {
                            $dst_x = round(($dst_w - $src_w) / 2);
                            $dst_y = round(($dst_h - $src_h) / 2);
                            $dst_w = $src_w;
                            $dst_h = $src_h;
                        }
                    } else {
                        if ($src_h < $dst_h) {
                            $dst_y = round(($dst_h - $src_h) / 2);
                            $dst_h = $src_h;
                            $src_w = $dst_w;
                        }
                    }
                } else {
                    //이미지 썸네일을 비율 유지.
                    if ($src_w < $dst_w) {
                        if ($src_h >= $dst_h) {
                            if ($src_h > $src_w) {
                                $tmp_w = round(($dst_h * $src_w) / $src_h);
                                $dst_x = round(($dst_w - $tmp_w) / 2);
                                $dst_w = $tmp_w;
                            } else {
                                $dst_x = round(($dst_w - $src_w) / 2);
                                $src_h = $dst_h;
                                $dst_w = $src_w;
                            }
                        } else {
                            $dst_x = round(($dst_w - $src_w) / 2);
                            $dst_y = round(($dst_h - $src_h) / 2);
                            $dst_w = $src_w;
                            $dst_h = $src_h;
                        }
                    } else {
                        if ($src_h < $dst_h) {
                            if ($src_w > $dst_w) {
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
            }
            // 크기 계산 end

            // 3. 대상 이미지 생성
            if ($use_crop) {
                // 크롭 이미지 생성
                $dst = imagecreatetruecolor($dst_w, $dst_h);
                if ($file_ext === 'png') {
                    imagealphablending($dst, false);
                    $result = imagesavealpha($dst, true);
                    if (!$result) {
                        return false;
                    }
                } else if ($file_ext === 'gif') {
                    $palletsize = imagecolorstotal($src);
                    if ($gif_src_transparency < $palletsize) {
                        $transparent_color = imagecolorsforindex($src, $gif_src_transparency);
                        if (!$transparent_color) {
                            return false;
                        }
                        $current_transparent = imagecolorallocate($dst, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']);
                        imagefill($dst, 0, 0, $current_transparent);
                        imagecolortransparent($dst, $current_transparent);
                    }
                }
            } else {
                // 크롭 아님
                $dst = imagecreatetruecolor($dst_w, $dst_h);
                $bgcolor = imagecolorallocate($dst, 255, 255, 255); // 배경색
                if ($file_ext === 'png') {
                    $bgcolor = imagecolorallocatealpha($dst, 0, 0, 0, 127);
                    imagefill($dst, 0, 0, $bgcolor);
                    imagealphablending($dst, false);
                    imagesavealpha($dst, true);
                } else if ($file_ext === 'gif') {
                    $palletsize = imagecolorstotal($src);
                    if ($gif_src_transparency < $palletsize) {
                        $transparent_color = imagecolorsforindex($src, $gif_src_transparency);
                        if (!$transparent_color) {
                            return false;
                        }
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

            $copyresample_result = imagecopyresampled(
                $dst, $src,
                $dst_x, $dst_y,
                $src_x, $src_y,
                $dst_w, $dst_h,
                $src_w, $src_h
            );
            if (!$copyresample_result) {
                return false;
            }

            // 4. sharpen 적용
            if ($use_sharpen_filter && $is_large) {
                $val = explode('/', $um_value);
                self::UnsharpMask($dst, $val[0], $val[1], $val[2]);
            }

            // 5. 원래이미지 확장자로 썸네일 이미지 생성
            if ($file_ext === 'gif') {
                imagegif($dst, $thumb_file);
            } else if ($file_ext === 'png') {
                if (defined('G5_THUMB_PNG_COMPRESS')) {
                    $png_compress = G5_THUMB_PNG_COMPRESS;
                } else {
                    $png_compress = 5;
                }

                imagepng($dst, $thumb_file, $png_compress);
            } else if ($file_ext === 'jpg') {
                if (defined('G5_THUMB_JPG_QUALITY')) {
                    $jpg_quality = G5_THUMB_JPG_QUALITY;
                } else {
                    $jpg_quality = 84;
                }

                imagejpeg($dst, $thumb_file, $jpg_quality);
            } else if ($file_ext === 'webp') {
                imagewebp($dst, $thumb_file);
            }

            chmod($thumb_file, G5_FILE_PERMISSION); // 추후 삭제를 위하여 파일모드 변경

            // 6. 결과 반환

            // 썸네일 생성 실패.
            if (!$dst) {
                return false;
            }

            return basename($thumb_file);
        } catch (\Exception|\Throwable $e) {
            // PHP 8.0 부터 발생하는 ValueError , TypeError 처리
            error_log($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());
            return false;
        } finally {
            // 7. 메모리 할당 해제
            ini_restore('memory_limit');

            // imagedestroy() php 7.4 이하만 동작
            if (PHP_VERSION_ID < 80000) {
                if (isset($src) && $src) {
                    @imagedestroy($src);
                }
                if (isset($dst) && $dst) {
                    @imagedestroy($dst);
                }
            }
        }
    }

    /**
     * 언샤프 마스크
     * 이미지 색상간 대비(콘트라스트) 높이는 함수
     * @param $img : 이미지
     * @param $amount : 대비 적용 강도
     * @param $radius : 대비 적용 범위
     * @param $threshold : 색상경계 대비 임계값
     * @return mixed|true
     */
    public static function UnsharpMask($img, $amount, $radius, $threshold)
    {
        /*
        출처 : http://vikjavev.no/computing/ump.php
        New:
        - In version 2.1 (February 26 2007) Tom Bishop has done some important speed enhancements.
        - From version 2 (July 17 2006) the script uses the imageconvolution public function in PHP
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

        //
        //    Unsharp Mask for PHP - version 2.1.1
        //
        //    Unsharp mask algorithm by Torstein Hønsi 2003-07.
        //    thoensi_at_netcom_dot_no.
        //    Please leave this notice.
        //


        // $img is an image that is already created within php using
        // imgcreatetruecolor. No url! $img must be a truecolor image.

        // Attempt to calibrate the parameters to Photoshop:
        // $img is an image that is already created within php using
        // imgcreatetruecolor. No url! $img must be a truecolor image.
        if ($amount > 500) {
            $amount = 500;
        }
        $amount *= 0.016;
        if ($radius > 50) {
            $radius = 50;
        }
        $radius *= 2;
        if ($threshold > 255) {
            $threshold = 255;
        }

        $radius = abs(round($radius));     // Only integers make sense.
        if ($radius == 0) {
            imagedestroy($img);
            return $img;
        }
        $w = imagesx($img);
        $h = imagesy($img);
        $imgCanvas = imagecreatetruecolor($w, $h);
        $imgBlur = imagecreatetruecolor($w, $h);


        // Gaussian blur matrix:
        //
        //    1    2    1
        //    2    4    2
        //    1    2    1
        //

        // Move copies of the image around one pixel at the time and merge them with weight
        // according to the matrix. The same matrix is simply repeated for higher radii.
        for ($i = 0;$i < $radius;$i++) {
            imagecopy($imgBlur, $img, 0, 0, 1, 0, $w - 1, $h); // left
            imagecopymerge($imgBlur, $img, 1, 0, 0, 0, $w, $h, 50); // right
            imagecopymerge($imgBlur, $img, 0, 0, 0, 0, $w, $h, 50); // center
            imagecopy($imgCanvas, $imgBlur, 0, 0, 0, 0, $w, $h);

            imagecopymerge($imgBlur, $imgCanvas, 0, 0, 0, 1, $w, $h - 1, 33.33333); // up
            imagecopymerge($imgBlur, $imgCanvas, 0, 1, 0, 0, $w, $h, 25); // down
        }


        if ($threshold > 0) {
            // Calculate the difference between the blurred pixels and the original
            // and set the pixels
            for ($x = 0;$x < $w - 1;$x++) { // each row
                for ($y = 0;$y < $h;$y++) { // each pixel
                    $rgbOrig = imagecolorat($img, $x, $y);
                    $rOrig = (($rgbOrig >> 16) & 0xFF);
                    $gOrig = (($rgbOrig >> 8) & 0xFF);
                    $bOrig = ($rgbOrig & 0xFF);

                    $rgbBlur = imagecolorat($imgBlur, $x, $y);

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
                        $pixCol = imagecolorallocate($img, $rNew, $gNew, $bNew);
                        imagesetpixel($img, $x, $y, $pixCol);
                    }
                }
            }
        } else {
            for ($x = 0;$x < $w;$x++) { // each row
                for ($y = 0;$y < $h;$y++) { // each pixel
                    $rgbOrig = imagecolorat($img, $x, $y);
                    $rOrig = (($rgbOrig >> 16) & 0xFF);
                    $gOrig = (($rgbOrig >> 8) & 0xFF);
                    $bOrig = ($rgbOrig & 0xFF);

                    $rgbBlur = imagecolorat($imgBlur, $x, $y);

                    $rBlur = (($rgbBlur >> 16) & 0xFF);
                    $gBlur = (($rgbBlur >> 8) & 0xFF);
                    $bBlur = ($rgbBlur & 0xFF);

                    $rNew = ($amount * ($rOrig - $rBlur)) + $rOrig;
                    if ($rNew > 255) {
                        $rNew = 255;
                    } elseif ($rNew < 0) {
                        $rNew = 0;
                    }
                    $gNew = ($amount * ($gOrig - $gBlur)) + $gOrig;
                    if ($gNew > 255) {
                        $gNew = 255;
                    } elseif ($gNew < 0) {
                        $gNew = 0;
                    }
                    $bNew = ($amount * ($bOrig - $bBlur)) + $bOrig;
                    if ($bNew > 255) {
                        $bNew = 255;
                    } elseif ($bNew < 0) {
                        $bNew = 0;
                    }
                    $rgbNew = ($rNew << 16) + ($gNew << 8) + $bNew;
                    imagesetpixel($img, $x, $y, $rgbNew);
                }
            }
        }
        imagedestroy($imgCanvas);
        imagedestroy($imgBlur);

        return true;
    }

    /**
     * 움직이는 webp 파일인지 검사한다.
     * @param string $filename
     * @return bool
     *
     */
    public static function isAnimatedWebp($filename)
    {
        $result = false;
        $file_handler = fopen($filename, "rb");
        fseek($file_handler, 12);
        if (fread($file_handler, 4) === 'VP8X') {
            fseek($file_handler, 20);
            $file_chunk = fread($file_handler, 1);
            $result = ((ord($file_chunk) >> 1) & 1) ? true : false;
        }
        fclose($file_handler);
        return $result;
    }

    /**
     * 움직이는 gif 파일인지 검사한다.
     * @param string $filename
     * @return bool|mixed
     */
    public static function isAnimatedGif(string $filename)
    {
        static $cache = array();
        $key = md5($filename);

        if (isset($cache[$key])) {
            return $cache[$key];
        }

        if (!($fh = @fopen($filename, 'rb'))) {
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
        while (!feof($fh) && $count < 2) {
            $chunk = fread($fh, 1024 * 100); //read 100kb at a time
            $count += preg_match_all('#\x00\x21\xF9\x04.{4}\x00(\x2C|\x21)#s', $chunk, $matches);
        }

        fclose($fh);

        $cache[$key] = $count > 1;

        run_event('is_animated_gif_after', $filename, $cache[$key]);

        return $cache[$key];
    }

    /**
     * getimagesize() 함수로 이미지 정보를 가져와 필요한 메모리 사용량을 계산한다.
     * @see https://www.php.net/manual/en/function.imagecreatefromjpeg.php#61709
     * @param array $image_info getimagesize() 함수반환값인 이미지 정보배열
     *
     * @return int|false 이미지 메모리 사용량 MB 단위의 정수 리턴
     */
    public static function memoryLimitCalcByImageInfo($image_info)
    {
        if (empty($image_info)) {
            return false;
        }

        $width = $image_info[0];
        $height = $image_info[1];
        $bits = $image_info['bits'] ?? 32; // 32bit 이미지
        if (!isset($image_info['bits'])) {
            $bits = 32;
        }

        $channels = $image_info['channels'] ?? 4; // RGBA 4 채널
        $gd_overhead = 2; // gd 오버헤드 계수

        $memory_byte = (int)(round($width * $height * $bits * $channels / 8 + 65536)) * $gd_overhead;
        $calc_mega_byte = $memory_byte >> 20;
        return $calc_mega_byte;
    }

    /**
     * @param string $memory_limit_size
     * @return int | false  무제한(-1) 또는 계산 실패시 false
     */
    public static function convertMemoryLimitToMB(string $memory_limit_size)
    {
        $memory_limit_number = filter_var($memory_limit_size, FILTER_SANITIZE_NUMBER_INT);
        $memory_unit = strtoupper(preg_replace('/[^a-zA-Z]/', '', $memory_limit_size));
        if ($memory_limit_number === -1) {
            return -1;
        }

        if ($memory_unit === 'M') {
            return (int)$memory_limit_number;
        }

        if ($memory_unit === 'G') {
            return (int)$memory_limit_number * 1024;
        }

        return false;
    }

    /**
     * html 속 이미지 태그들 추출
     * @param $contents
     * @param $view
     * @return false|\string[][]
     */
    public static function getImageTagFromHtml($contents, $view = true)
    {
        if (!$contents) {
            return false;
        }

        // $contents 중 img 태그 추출
        if ($view) {
            $pattern = "/<img([^>]*)>/iS";
        } else {
            $pattern = "/<img[^>]*src=[\'\"]?([^>\'\"]+[^>\'\"]+)[\'\"]?[^>]*>/i";
        }
        preg_match_all($pattern, $contents, $matchs);

        return $matchs;
    }

    /**
     * html 속 첫번째 이미지 태그 추출
     * @param string $html
     * @param bool $is_allow_external_url 외부 url 썸네일 허용
     * @return string|false
     */
    public static function getFirstImageTag($html, $is_allow_external_url = false)
    {
        $result = self::getImageTagFromHtml($html, false);
        $images = $result[0] ?? [];
        if (!$images) {
            return false;
        }

        if ($is_allow_external_url) {
            return $images[0] ?? false;
        }

        $image_host_url = G5_URL;
        $site_image_matches = array_filter($images, function ($item) use ($image_host_url) {
            return strpos($item, $image_host_url) !== false;
        });

        if (!$site_image_matches) {
            return false;
        }

        return $site_image_matches[0] ?? false;
    }

}