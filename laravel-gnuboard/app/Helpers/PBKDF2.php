<?php

namespace App\Helpers;

class PBKDF2
{
    // Constants from GnuBoard5
    const HASH_ALGORITHM = 'SHA256';
    const ITERATIONS = 12000;
    const SALT_BYTES = 24;
    const HASH_BYTES = 24;

    /**
     * Create a password hash using PBKDF2
     *
     * @param string $password
     * @param bool $force_compat
     * @return string
     */
    public static function hash($password, $force_compat = false)
    {
        // Generate the salt
        $salt = base64_encode(random_bytes(self::SALT_BYTES));
        
        // Determine the best supported algorithm and iteration count
        $algo = strtolower(self::HASH_ALGORITHM);
        $iterations = self::ITERATIONS;
        
        if ($force_compat || !in_array($algo, hash_algos())) {
            $algo = false;
            $iterations = round($iterations / 5);
        }
        
        // Return format: algorithm:iterations:salt:hash
        $pbkdf2 = self::pbkdf2Default($algo, $password, $salt, $iterations, self::HASH_BYTES);
        $prefix = $algo ? $algo : 'sha1';
        
        return $prefix . ':' . $iterations . ':' . $salt . ':' . base64_encode($pbkdf2);
    }

    /**
     * Validate a password against a hash
     *
     * @param string $password
     * @param string $hash
     * @return bool
     */
    public static function verify($password, $hash)
    {
        // Check if it's bcrypt format (Laravel default)
        if (strpos($hash, '$2y$') === 0 || strpos($hash, '$2a$') === 0) {
            return password_verify($password, $hash);
        }
        
        // Split the hash into 4 parts for PBKDF2
        $params = explode(':', $hash);
        if (count($params) < 4) {
            return false;
        }
        
        // Recalculate the hash and compare it with the original
        $pbkdf2 = base64_decode($params[3]);
        $pbkdf2_check = self::pbkdf2Default($params[0], $password, $params[2], (int)$params[1], strlen($pbkdf2));
        
        return self::slowEquals($pbkdf2, $pbkdf2_check);
    }

    /**
     * Check if a hash needs upgrading
     *
     * @param string $hash
     * @return bool
     */
    public static function needsUpgrade($hash)
    {
        // Get the current algorithm and iteration count
        $params = explode(':', $hash);
        if (count($params) < 4) {
            return true;
        }
        
        $algo = $params[0];
        $iterations = (int)$params[1];
        
        // Compare the current hash with the best supported options
        if (!in_array($algo, hash_algos())) {
            return false;
        } elseif ($algo === strtolower(self::HASH_ALGORITHM) && $iterations >= self::ITERATIONS) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Compare two strings in constant time
     *
     * @param string $a
     * @param string $b
     * @return bool
     */
    private static function slowEquals($a, $b)
    {
        $diff = strlen($a) ^ strlen($b);
        for ($i = 0; $i < strlen($a) && $i < strlen($b); $i++) {
            $diff |= ord($a[$i]) ^ ord($b[$i]);
        }
        return $diff === 0;
    }

    /**
     * PBKDF2 key derivation function
     *
     * @param string|false $algo
     * @param string $password
     * @param string $salt
     * @param int $count
     * @param int $key_length
     * @return string
     */
    private static function pbkdf2Default($algo, $password, $salt, $count, $key_length)
    {
        // Sanity check
        if ($count <= 0 || $key_length <= 0) {
            throw new \InvalidArgumentException('PBKDF2 ERROR: Invalid parameters.');
        }
        
        // Check if we should use the fallback function
        if (!$algo) {
            return self::pbkdf2Fallback($password, $salt, $count, $key_length);
        }
        
        // Check if the selected algorithm is available
        $algo = strtolower($algo);
        if (!in_array($algo, hash_algos())) {
            if ($algo === 'sha1') {
                return self::pbkdf2Fallback($password, $salt, $count, $key_length);
            } else {
                throw new \InvalidArgumentException('PBKDF2 ERROR: Hash algorithm not supported.');
            }
        }
        
        // Use built-in function if available
        if (function_exists('hash_pbkdf2')) {
            return hash_pbkdf2($algo, $password, $salt, $count, $key_length, true);
        }
        
        // Count the blocks
        $hash_length = strlen(hash($algo, '', true));
        $block_count = ceil($key_length / $hash_length);
        
        // Hash it!
        $output = '';
        for ($i = 1; $i <= $block_count; $i++) {
            $last = $salt . pack('N', $i);
            $last = $xorsum = hash_hmac($algo, $last, $password, true);
            for ($j = 1; $j < $count; $j++) {
                $xorsum ^= ($last = hash_hmac($algo, $last, $password, true));
            }
            $output .= $xorsum;
        }
        
        // Truncate and return
        return substr($output, 0, $key_length);
    }

    /**
     * Fallback function using sha1()
     *
     * @param string $password
     * @param string $salt
     * @param int $count
     * @param int $key_length
     * @return string
     */
    private static function pbkdf2Fallback($password, $salt, $count, $key_length)
    {
        // Count the blocks
        $hash_length = 20;
        $block_count = ceil($key_length / $hash_length);
        
        // Prepare the HMAC key and padding
        if (strlen($password) > 64) {
            $password = str_pad(sha1($password, true), 64, chr(0));
        } else {
            $password = str_pad($password, 64, chr(0));
        }
        
        $opad = str_repeat(chr(0x5C), 64) ^ $password;
        $ipad = str_repeat(chr(0x36), 64) ^ $password;
        
        // Hash it!
        $output = '';
        for ($i = 1; $i <= $block_count; $i++) {
            $last = $salt . pack('N', $i);
            $xorsum = $last = pack('H*', sha1($opad . pack('H*', sha1($ipad . $last))));
            for ($j = 1; $j < $count; $j++) {
                $last = pack('H*', sha1($opad . pack('H*', sha1($ipad . $last))));
                $xorsum ^= $last;
            }
            $output .= $xorsum;
        }
        
        // Truncate and return
        return substr($output, 0, $key_length);
    }
}