<?php

namespace API\Middleware;

use API\Auth\JwtTokenManager;
use API\Exceptions\HttpNotFoundException;
use API\Exceptions\HttpUnauthorizedException;
use API\Service\MemberService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

/**
 * Access Token Authentication Middleware
 * 
 * 토큰에 저장된 id 를 기준으로 member 를 request attribute 에 추가합니다.
 */
class AccessTokenAuthMiddleware
{
    private JwtTokenManager $token_manager;
    private MemberService $member_service;

    public function __construct(JwtTokenManager $token_manager, MemberService $member_service)
    {
        $this->token_manager = $token_manager;
        $this->member_service = $member_service;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $token = $this->extractToken($request);
        $decode = $this->token_manager->decodeToken('access', $token);
        $request = $request->withAttribute('member', $this->getMember($request, $decode->sub));

        return $handler->handle($request);
    }

    private function extractToken(Request $request): string
    {
        $token = $request->getHeaderLine('Authorization');
        $token = trim(str_replace('Bearer', '', $token));

        if (!$token) {
            throw new HttpUnauthorizedException($request, 'Authorization header not found.');
        }

        return $token;
    }

    /**
     * mb_id 로 회원정보 가져오기
     * @param Request $request
     * @param string $mb_id
     * @return array
     */
    private function getMember(Request $request, string $mb_id): array
    {
        $member = $this->member_service->fetchMemberById($mb_id);

        if (!$member) {
            throw new HttpNotFoundException($request, 'Member not found.');
        }

        return $member;
    }
}
