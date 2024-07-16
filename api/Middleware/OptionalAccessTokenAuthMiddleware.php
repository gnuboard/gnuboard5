<?php

namespace API\Middleware;

use API\Auth\JwtTokenManager;
use API\EnvironmentConfig;
use API\Service\MemberService;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;


class OptionalAccessTokenAuthMiddleware
{
    private $algorithm;
    private $secretKey;
    private $member_service;
    private $default_member = array('mb_id' => '', 'mb_level' => 1, 'mb_name' => '', 'mb_point' => 0, 'mb_certify' => '', 'mb_email' => '', 'mb_open' => '', 'mb_homepage' => '', 'mb_tel' => '', 'mb_hp' => '', 'mb_zip1' => '', 'mb_zip2' => '', 'mb_addr1' => '', 'mb_addr2' => '', 'mb_addr3' => '', 'mb_addr_jibeon' => '', 'mb_signature' => '', 'mb_profile' => '');

    public function __construct()
    {
        $token_manager = new JwtTokenManager(new EnvironmentConfig());

        $this->secretKey = $token_manager->secret_key();
        $this->algorithm = $token_manager->algorithm;
        $this->member_service = new MemberService();
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $token = $this->extract_token($request);
        if ($token) {
            $decode = JWT::decode($token, new Key($this->secretKey, $this->algorithm));
            $member = $this->member_service->fetchMemberById($decode->sub);
            $request = $request->withAttribute('member', $member);
        } else {
            $request = $request->withAttribute('member', $this->default_member);
        }

        return $handler->handle($request);
    }

    private function extract_token(Request $request): ?string
    {
        $token = $request->getHeaderLine('Authorization');
        $token = trim(str_replace('Bearer', '', $token));
        return $token ?: null;
    }
}
