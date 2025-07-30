<?php

namespace App\Helpers;

class PBKDF2
{
    /**
     * PBKDF2를 사용한 비밀번호 해시 생성
     *
     * @param string $password
     * @return string
     */
    public static function hash($password)
    {
        // PBKDF2 기본 설정
        $algo = 'sha256';
        $iterations = 12000;
        $salt = base64_encode(random_bytes(24));
        
        // PBKDF2 해시 생성
        $hash = hash_pbkdf2($algo, $password, base64_decode($salt), $iterations, 32, true);
        $hash = base64_encode($hash);
        
        // 형식: algo:iterations:salt:hash
        return sprintf('%s:%d:%s:%s', $algo, $iterations, $salt, $hash);
    }
    
    /**
     * PBKDF2 해시 비밀번호 검증
     *
     * @param string $password
     * @param string $hash
     * @return bool
     */
    public static function verify($password, $hash)
    {
        // 해시 형식 파싱
        $parts = explode(':', $hash);
        if (count($parts) !== 4) {
            return false;
        }
        
        list($algo, $iterations, $salt, $storedHash) = $parts;
        $iterations = (int) $iterations;
        
        // 동일한 설정으로 해시 생성
        $computedHash = hash_pbkdf2($algo, $password, base64_decode($salt), $iterations, 32, true);
        $computedHash = base64_encode($computedHash);
        
        // 타이밍 공격 방지를 위한 안전한 비교
        return hash_equals($storedHash, $computedHash);
    }
}