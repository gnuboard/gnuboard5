<?php

namespace API\Service;

/**
 * 문자열의 암호화, 복호화 기능을 제공합니다.
 */
class EncryptionService
{
    private static $secret_key = '';

    /**
     * 암호화
     * @param string $plain_text
     * @return string
     */
    public static function encrypt($plain_text)
    {
        if(!self::$secret_key) {
            self::$secret_key = $_ENV['ENCRYPTION_KEY'];
        }
        // 랜덤 초기 백터값
        $iv = openssl_random_pseudo_bytes(16);
        $enc_text = openssl_encrypt($plain_text, 'aes-256-cbc', self::$secret_key, OPENSSL_RAW_DATA, $iv);
        return base64_encode($iv . $enc_text);
    }

    /**
     * 복호화
     * @param string $enc_text_base64
     * @return false|string
     */
    public static function decrypt($enc_text_base64)
    {
        if(!self::$secret_key) {
            self::$secret_key = $_ENV['ENCRYPTION_KEY'];
        }
        $data = base64_decode($enc_text_base64);
        $iv = substr($data, 0, 16);
        $enc_text = substr($data, 16);
        return openssl_decrypt($enc_text, 'aes-256-cbc', self::$secret_key, OPENSSL_RAW_DATA, $iv);
    }
}