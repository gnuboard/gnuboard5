<?php
if (!defined('_GNUBOARD_')) exit;

// PHP 5.2.17에서도 인증용 난수는 강한 난수원만 사용한다.
function shop_order_random_hex($length)
{
    $bytes = false;
    if (function_exists('random_bytes')) {
        try { $bytes = random_bytes($length); } catch (Exception $e) { $bytes = false; }
    }
    if ($bytes === false && function_exists('openssl_random_pseudo_bytes')) {
        $strong = false;
        $candidate = openssl_random_pseudo_bytes($length, $strong);
        if ($strong) $bytes = $candidate;
    }
    if ($bytes === false && DIRECTORY_SEPARATOR === '/') {
        $stream = @fopen('/dev/urandom', 'rb');
        if ($stream) {
            $bytes = '';
            while (strlen($bytes) < $length) {
                $part = @fread($stream, $length - strlen($bytes));
                if ($part === false || $part === '') break;
                $bytes .= $part;
            }
            fclose($stream);
        }
    }
    if ($bytes === false && function_exists('mcrypt_create_iv') && defined('MCRYPT_DEV_URANDOM')) {
        $bytes = mcrypt_create_iv($length, MCRYPT_DEV_URANDOM);
    }
    if (!is_string($bytes) || strlen($bytes) !== $length) shop_order_access_fail();
    return bin2hex($bytes);
}

// 토큰·다이제스트는 길이가 공개된 고정 길이 값이다. 같은 길이는 끝까지 비교한다.
function shop_order_equals($known, $input)
{
    if (!is_string($known) || !is_string($input) || strlen($known) !== strlen($input)) return false;
    $difference = 0;
    for ($i = 0, $length = strlen($known); $i < $length; $i++) {
        $difference |= ord($known[$i]) ^ ord($input[$i]);
    }
    return $difference === 0;
}

// PHP 버전과 무관하게 객체·참조를 생성하지 않는다. 기존 serialize/base64 행과 호환한다.
function shop_order_decode_data($encoded)
{
    if (!is_string($encoded) || strlen($encoded) > 1398104) return false;
    $serialized = base64_decode($encoded, true);
    if ($serialized === false || strlen($serialized) > 1048576) return false;
    $offset = 0;
    $nodes = 0;
    $valid = true;
    $value = shop_order_decode_value($serialized, $offset, $nodes, $valid, 0);
    return $valid && $offset === strlen($serialized) && is_array($value) ? $value : false;
}

function shop_order_decode_value($data, &$offset, &$nodes, &$valid, $depth)
{
    if (!$valid || $depth > 32 || ++$nodes > 20000 || $offset >= strlen($data)) {
        $valid = false;
        return null;
    }
    $type = $data[$offset++];
    if ($type === 'N' && substr($data, $offset++, 1) === ';') return null;
    if (substr($data, $offset++, 1) !== ':') { $valid = false; return null; }
    if ($type === 's' || $type === 'a') {
        $end = strpos($data, ':', $offset);
        if ($end === false) { $valid = false; return null; }
        $number = substr($data, $offset, $end - $offset);
        if (!preg_match('/\A(?:0|[1-9][0-9]{0,6})\z/D', $number)) { $valid = false; return null; }
        $count = (int)$number;
        $offset = $end + 1;
        if ($type === 's') {
            if (substr($data, $offset++, 1) !== '"' || $count > strlen($data) - $offset - 2) { $valid = false; return null; }
            $value = substr($data, $offset, $count);
            $offset += $count;
            if (substr($data, $offset, 2) !== '";') $valid = false;
            $offset += 2;
            return $value;
        }
        if ($count > 10000 || substr($data, $offset++, 1) !== '{') { $valid = false; return null; }
        $value = array();
        for ($i = 0; $i < $count && $valid; $i++) {
            $key = shop_order_decode_value($data, $offset, $nodes, $valid, $depth + 1);
            if (!is_int($key) && !is_string($key)) { $valid = false; break; }
            $item = shop_order_decode_value($data, $offset, $nodes, $valid, $depth + 1);
            if ($valid) $value[$key] = $item;
        }
        if (substr($data, $offset++, 1) !== '}') $valid = false;
        return $value;
    }
    if ($type === 'b' || $type === 'i' || $type === 'd') {
        $end = strpos($data, ';', $offset);
        if ($end === false) { $valid = false; return null; }
        $number = substr($data, $offset, $end - $offset);
        $offset = $end + 1;
        if ($type === 'b' && ($number === '0' || $number === '1')) return $number === '1';
        if ($type === 'i' && preg_match('/\A-?(?:0|[1-9][0-9]*)\z/D', $number) && (string)(int)$number === $number) return (int)$number;
        if ($type === 'd' && preg_match('/\A-?(?:[0-9]+(?:\.[0-9]*)?|\.[0-9]+)(?:[Ee][+-]?[0-9]+)?\z/D', $number) && is_finite((float)$number)) return (float)$number;
    }
    $valid = false;
    return null;
}
