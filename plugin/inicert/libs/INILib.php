<?php

function String2Hex($string) {
    $hex = array();
    for ($i = 0; $i < strlen($string); $i++) {
        $hex[] = dechex(ord($string[$i]));
    }
    return $hex;
}

function Hex2String($hex) {
    $str = "";
    for ($i = 0; $i < count($hex); $i++) {
        $str .= chr(hexdec($hex[$i]));
    }
    return $str;
}

function encrypt_SEED($str, $bszUser_key, $bszIV) {
    $planBytes = String2Hex($str);
    $keyBytes = String2Hex(base64_decode($bszUser_key));
    $IVBytes = String2Hex(($bszIV));

    for ($i = 0; $i < 16; $i++) {
        $keyBytes[$i] = hexdec(($keyBytes[$i]));
        $IVBytes[$i] = hexdec(($IVBytes[$i]));
    }

    for ($i = 0; $i < count($planBytes); $i++) {
        $planBytes[$i] = hexdec($planBytes[$i]);
    }

    if (count($planBytes) == 0) {
        return $str;
    }
    $ret = null;
    $bszChiperText = null;
    $pdwRoundKey = array_pad(array(), 32, 0);

    $bszChiperText = KISA_SEED_CBC::SEED_CBC_Encrypt($keyBytes, $IVBytes, $planBytes, 0, count($planBytes));
    $r = count($bszChiperText);

    for ($i = 0; $i < $r; $i++) {
        $ret[] = sprintf("%02X", $bszChiperText[$i]);
    }
    return base64_encode(Hex2String($ret));
}

function decrypt_SEED($str, $bszUser_key, $bszIV) {
    $planBytes = String2Hex(base64_decode($str));
    $keyBytes = String2Hex(base64_decode($bszUser_key));
    $IVBytes = String2Hex(($bszIV));

    for ($i = 0; $i < 16; $i++) {
        $keyBytes[$i] = hexdec(($keyBytes[$i]));
        $IVBytes[$i] = hexdec(($IVBytes[$i]));
    }

    for ($i = 0; $i < count($planBytes); $i++) {
        $planBytes[$i] = hexdec($planBytes[$i]);
    }

    if (count($planBytes) == 0) {
        return $str;
    }

    $pdwRoundKey = array_pad(array(), 32, 0);

    $bszPlainText = null;
    $planBytresMessage = array();

    // 방법 1
    $bszPlainText = KISA_SEED_CBC::SEED_CBC_Decrypt($keyBytes, $IVBytes, $planBytes, 0, count($planBytes));
    for ($i = 0; $i < sizeof((array) $bszPlainText); $i++) {
        $planBytresMessage[] = sprintf("%02X", $bszPlainText[$i]);
    }
    return Hex2String($planBytresMessage);
}
