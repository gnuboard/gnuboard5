<?php
if (!class_exists('Crypto_KCP_V2')) {
    class Crypto_KCP_V2
    {
        private static $a = "c2hhMjU2";
        private static $b = 10000;
        private static $c = 32;
        private static $d = "QUVTLTI1Ni1DQkM=";

        private static function x($p, $s)
        {
            return hash_pbkdf2(base64_decode(self::$a), $p, $s, self::$b, self::$c, true);
        }

        private static function y($d, $k, $i)
        {
            return openssl_encrypt($d, base64_decode(self::$d), $k, OPENSSL_RAW_DATA, $i);
        }

        private static function z($d, $k, $i)
        {
            return openssl_decrypt($d, base64_decode(self::$d), $k, OPENSSL_RAW_DATA, $i);
        }

        public static function encryptJson($data, $key, $site)
        {
            $s = random_bytes(16);

            $k = self::x($key, $s);
            $v = self::x($site, $s);

            $i = substr($v, 0, 16);

            $e = self::y($data, $k, $i);

            return array(
                "encData" => base64_encode($e),
                "rv" => base64_encode($s),
            );
        }

        public static function decryptJson($enc, $rv, $key, $site)
        {
            $s = base64_decode($rv);

            $k = self::x($key, $s);
            $v = self::x($site, $s);

            $i = substr($v, 0, 16);

            return self::z(base64_decode($enc), $k, $i);
        }
    }
}
