<?php

namespace API\Middleware;

use API\Auth\JwtTokenManager;
use API\Exceptions\HttpNotFoundException;
use API\Service\MemberService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class OptionalAccessTokenAuthMiddleware
{
    private JwtTokenManager $token_manager;
    private MemberService $member_service;

    /**
     * guest
     * @var array
     */
    private array $default_member = array(
        'mb_id' => '',
        'mb_level' => 1,
        'mb_name' => '',
        'mb_point' => 0,
        'mb_certify' => '',
        'mb_email' => '',
        'mb_open' => 0,
        'mb_homepage' => '',
        'mb_tel' => '',
        'mb_hp' => '',
        'mb_zip1' => '',
        'mb_zip2' => '',
        'mb_addr1' => '',
        'mb_addr2' => '',
        'mb_addr3' => '',
        'mb_addr_jibeon' => '',
        'mb_signature' => '',
        'mb_profile' => ''
    );

    public function __construct(JwtTokenManager $token_manager, MemberService $member_service)
    {
        $this->token_manager = $token_manager;
        $this->member_service = $member_service;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $token = $this->extractToken($request);
        if ($token) {
            $member = $this->getMemberFromToken($token);
            if (!$member) {
                throw new HttpNotFoundException($request, 'Member not found.');
            }

            $request = $request->withAttribute('member', $member);
        } else {
            $request = $request->withAttribute('member', $this->default_member);
        }

        return $handler->handle($request);
    }

    private function getMemberFromToken($token)
    {
        $decode = $this->token_manager->decodeToken('access', $token);

        // 비회원
        if (isset($decode->role) && $decode->role === 'guest') {
            return $this->default_member;
        }

        // 회원
        return $this->member_service->fetchMemberById($decode->sub);
    }

    /**
     * 요청 헤더에서 JWT 토큰 추출
     * @param Request $request
     * @return string|null
     */
    private function extractToken(Request $request): ?string
    {
        $token = $request->getHeaderLine('Authorization');
        $token = trim(str_replace('Bearer', '', $token));
        return $token ?: null;
    }
}
