<?php

class cryptor {
    public function encrypt($key, $data) {
        // 키 설정
        $publickey = $this->keyInstance($key);

        openssl_public_encrypt($data, $encrypted, $publickey, OPENSSL_PKCS1_OAEP_PADDING);
        
        return base64_encode($encrypted);
    }

    public function keyInstance($publickey) {
        // startline , endline 설정
        $start_line = "-----BEGIN PUBLIC KEY-----";
        $end_line = "-----END PUBLIC KEY-----";

        // key 추출 (정규식)
        $pattern = "/-+([a-zA-Z\s]*)-+([^-]*)-+([a-zA-Z\s]*)-+/";
        if(preg_match($pattern, $publickey, $matches)) {
            $splitKey = $matches[2];
            $splitKey = preg_replace('/\s+/', '', $splitKey);
        } else {
            return null;
        }

        $key = "";

        for($pos=1; $pos <= strlen($splitKey); $pos++) {
            if($pos % 64 == 0) {
                $key = $key . $splitKey[$pos-1] . "\n";
            } else {
                $key = $key . $splitKey[$pos-1];
            }
        }

        // startline, endline 추가
        $key = $start_line . "\n" . $key . "\n" . $end_line;

        return $key;
    }
}

?>