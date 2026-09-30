<?php
if (!defined('_GNUBOARD_')) exit;

// 첨부파일 전용 CSPRNG. 안전한 난수가 없으면 예측 가능한 값으로 대체하지 않습니다.
function g5_attachment_random_bytes()
{
    try {
        if (function_exists('random_bytes')) {
            $bytes = random_bytes(16);
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            $strong = false;
            $bytes = openssl_random_pseudo_bytes(16, $strong);
            if ($strong !== true) return false;
        } elseif (DIRECTORY_SEPARATOR === '/' && @is_readable('/dev/urandom')) {
            // PHP 5.2 등 난수 API가 없는 Unix 환경
            $fp = @fopen('/dev/urandom', 'rb');
            if ($fp === false) return false;
            $bytes = '';
            while (strlen($bytes) < 16) {
                $part = @fread($fp, 16 - strlen($bytes));
                if ($part === false || $part === '') break;
                $bytes .= $part;
            }
            fclose($fp);
        } else {
            return false;
        }
    } catch (Exception $e) {
        return false;
    }

    return is_string($bytes) && strlen($bytes) === 16 ? $bytes : false;
}

// 성공하면 저장 파일명, 실패하면 false를 반환합니다. 원본 이름은 호출자가 DB에 별도 보관합니다.
function g5_store_attachment($tmp_file, $filename, $directory)
{
    if (!is_uploaded_file($tmp_file)) return false;

    // 기존 실행 방지 규칙을 적용하고 확장자만 보존합니다. IP/세션/시각을 이름에 사용하지 않습니다.
    $filename = preg_replace("/\.(php|pht|phtm|htm|shtml|shtm|cgi|pl|exe|jsp|asp|inc|phar|svg|svgz)/i", "$0-x", $filename);
    $info = pathinfo($filename);
    $suffix = !empty($info['extension']) ? '.'.$info['extension'] : '';

    for ($attempt = 0; $attempt < 10; $attempt++) {
        $bytes = g5_attachment_random_bytes();
        if ($bytes === false) return false;
        $stored_file = bin2hex($bytes).$suffix;
        $destination = $directory.'/'.$stored_file;

        // 배타적으로 경로를 확보하여 동시 업로드와 기존 파일/심볼릭 링크 충돌을 방지합니다.
        $reserved = @fopen($destination, 'x');
        if ($reserved === false) {
            if (file_exists($destination) || is_link($destination)) continue;
            return false;
        }
        fclose($reserved);

        if (!move_uploaded_file($tmp_file, $destination)) {
            @unlink($destination);
            return false;
        }
        return $stored_file;
    }

    return false;
}
