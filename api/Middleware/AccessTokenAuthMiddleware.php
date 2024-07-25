<?php

namespace API\Middleware;

use API\Auth\JwtTokenManager;
use API\Service\MemberService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Exception\HttpUnauthorizedException;
use Slim\Exception\HttpNotFoundException;

/**
 * Access Token Authentication Middleware
 * 
 * Add this middleware to routes that require access token authentication
 * Append member information to the request as an attribute
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
        $token = $this->extract_token($request);
        $decode = $this->token_manager->decode_token('access', $token);
        $request = $request->withAttribute('member', $this->get_member($request, $decode->sub));

        return $handler->handle($request);
    }

    private function extract_token(Request $request): string
    {
        $token = $request->getHeaderLine('Authorization');
        $token = trim(str_replace('Bearer', '', $token));

        if (!$token) {
            throw new HttpUnauthorizedException($request, 'Authorization header not found.');
        }

        return $token;
    }

    private function get_member(Request $request, string $mb_id): array
    {
        $member = $this->member_service->fetchMemberById($mb_id);

        if (!$member) {
            throw new HttpNotFoundException($request, 'Member not found.');
        }

        return $member;
    }
}
