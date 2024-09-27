<?php

namespace API\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use IPTools\Range;


class IpCheckMiddleware
{

    /**
     * @see https://www.cloudflare.com/ko-kr/ips/
     * @var string[] 
     */
    private $cloudflare_ipv4 = [
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
        '104.16.0.0/13',
        '104.24.0.0/14',
        '172.64.0.0/13',
        '131.0.72.0/22',
    ];

    /**
     * @see https://www.cloudflare.com/ko-kr/ips/
     * @var string[] 
     */
    private $cloudflare_ipv6 = [
        '2400:cb00::/32',
        '2606:4700::/32',
        '2803:f800::/32',
        '2405:b500::/32',
        '2405:8100::/32',
        '2a06:98c0::/29',
        '2c0f:f248::/32',
    ];

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $ip = $_SERVER['REMOTE_ADDR'];

        // HTTP_CF_CONNECTING_IP 헤더 & Cloudflare IP 대역 요청이면 REMOTE_ADDR를 HTTP_CF_CONNECTING_IP로 변경
        if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            $ip_obj = new \IPTools\IP($ip);
            foreach ($this->cloudflare_ipv4 as $range) {
                if (Range::parse($range)->contains($ip_obj)) {
                    $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CF_CONNECTING_IP'];
                    return $handler->handle($request);
                }
            }

            foreach ($this->cloudflare_ipv6 as $range) {
                if (Range::parse($range)->contains($ip_obj)) {
                    $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CF_CONNECTING_IP'];
                    return $handler->handle($request);
                }
            }
        }

        if (!isset($_SERVER['SERVER_ADDR'])) {
            $_SERVER['SERVER_ADDR'] = isset($_SERVER['LOCAL_ADDR']) ? $_SERVER['LOCAL_ADDR'] : '';
        }

        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6)) {
            $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }

        return $handler->handle($request);
    }


}