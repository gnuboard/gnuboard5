<?php

namespace API\Middleware;

use API\Auth\JwtTokenManager;
use API\Service\MemberService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class OptionalAccessTokenAuthMiddleware
{
    private JwtTokenManager $token_manager;
    private MemberService $member_service;
    private $default_member = array('mb_id' => '', 'mb_level' => 1, 'mb_name' => '', 'mb_point' => 0, 'mb_certify' => '', 'mb_email' => '', 'mb_open' => '', 'mb_homepage' => '', 'mb_tel' => '', 'mb_hp' => '', 'mb_zip1' => '', 'mb_zip2' => '', 'mb_addr1' => '', 'mb_addr2' => '', 'mb_addr3' => '', 'mb_addr_jibeon' => '', 'mb_signature' => '', 'mb_profile' => '');

    public function __construct(JwtTokenManager $token_manager, MemberService $member_service)
    {
        $this->token_manager = $token_manager;
        $this->member_service = $member_service;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $token = $this->extract_token($request);
        if ($token) {
            $decode = $this->token_manager->decode_token('access', $token);
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
