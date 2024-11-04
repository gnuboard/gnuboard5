<?php
// CloudFlare를 사용시, 사용자 환경에 맞는 $_SERVER['REMOTE_ADDR']과 $_SERVER['HTTPS'] 사용 여부를 수정합니다.
class G5CloudflareRequestHandler {
    public static function check_cloudflare_ips($user_ip){
        
        // 클라우드플레어 IP, https://www.cloudflare.com/ips
        $cloudflare_ips = array(
            // IPv4
            '173.245.48.0/20',
            '103.21.244.0/22',
            '103.22.200.0/22',
            '103.31.4.0/22',
            '141.101.64.0/18',
            '108.162.192.0/18',
            '190.93.240.0/20',
            '188.114.96.0/20',
            '197.234.240.0/22',
            '198.41.128.0/17',
            '162.158.0.0/15',
            '104.16.0.0/12',
            '104.24.0.0/14',
            '172.64.0.0/13',
            '131.0.72.0/22',
            // IPv6
            '2400:cb00::/32',
            '2606:4700::/32',
            '2803:f800::/32',
            '2405:b500::/32',
            '2405:8100::/32',
            '2a06:98c0::/29',
            '2c0f:f248::/32',
        );
            
        // 사용자 IP가 Cloudflare IP 대역 범위에 있는지 확인
        $is_cloudflare_ip = false;
        foreach ($cloudflare_ips as $cidr) {
            if (self::ip_in_range($user_ip, $cidr)) {
                $is_cloudflare_ip = true;
                break;
            }
        }
        
        return $is_cloudflare_ip;
    }

    // IP 주소가 CIDR 범위 내에 있는지 확인하는 함수 (IPv4, IPv6 모두 가능)
    public static function ip_in_range($ip, $range) {
        // IPv4와 IPv6를 구분
        if (strpos($range, ':') === false && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $ip)) {
            // IPv4 처리
            return self::ipv4_in_range($ip, $range);
        } elseif (strpos($range, ':') !== false && strpos($ip, ':') !== false) {
            // IPv6 처리
            return self::ipv6_in_range($ip, $range);
        }
        
        return false;
    }

    // IPv4 CIDR 범위 검사
    public static function ipv4_in_range($ip, $range) {
        list($range, $netmask) = explode('/', $range, 2);
        $range_decimal = ip2long($range);
        $ip_decimal = ip2long($ip);
        $wildcard_decimal = pow(2, (32 - $netmask)) - 1;
        $netmask_decimal = ~$wildcard_decimal;
        
        return (($ip_decimal & $netmask_decimal) == ($range_decimal & $netmask_decimal));
    }

    // IPv6 CIDR 범위 검사
    public static function ipv6_in_range($ip, $range) {
        list($range_ip, $netmask) = explode('/', $range, 2);
        $netmask = (int) $netmask;

        $ip_bin = @inet_pton($ip);
        $range_bin = @inet_pton($range_ip);
        
        // IPv6 주소는 128비트이므로, 비트마스크를 적용
        $unpacked = unpack('A16', $ip_bin);
        $ip_bits = $unpacked[1];
        $unpacked2 = unpack('A16', $range_bin);
        $range_bits = $unpacked2[1];

        for ($i = 0; $i < (int)($netmask / 8); $i++) {
            if ($ip_bits[$i] !== $range_bits[$i]) {
                return false;
            }
        }

        $remainder = $netmask % 8;
        if ($remainder) {
            $mask = 0xFF << (8 - $remainder);
            return (ord($ip_bits[$i]) & $mask) === (ord($range_bits[$i]) & $mask);
        }

        return true;
    }

}
if ($_SERVER['HTTP_CF_CONNECTING_IP'] && G5CloudflareRequestHandler::check_cloudflare_ips($_SERVER['REMOTE_ADDR'])) {
    $_SERVER['REMOTE_ADDR'] = preg_replace('/[^0-9a-fA-F:.]/', '', $_SERVER['HTTP_CF_CONNECTING_IP']);
    
    // Cloudflare 환경을 고려한 https 사용여부
    if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === "https") {
        $_SERVER['HTTPS'] = 'on';
    } elseif (isset($_SERVER['HTTP_CF_VISITOR']) && stripos($_SERVER['HTTP_CF_VISITOR'], 'https') !== false) {
        $_SERVER['HTTPS'] = 'on';
    }
}