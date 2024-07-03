<?php

namespace API\Middleware;

use API\Auth\JwtTokenManager;
use API\EnvironmentConfig;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
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
    private $algorithm;
    private $secretKey;

    public function __construct()
    {
        $token_manager = new JwtTokenManager(new EnvironmentConfig());

        $this->secretKey = $token_manager->secret_key();
        $this->algorithm = $token_manager->algorithm;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $token = $this->extract_token($request);
        $decode = JWT::decode($token, new Key($this->secretKey, $this->algorithm));
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
        global $g5;

        $sql = "SELECT * FROM {$g5['member_table']} WHERE mb_id = '{$mb_id}'";
        $member = sql_fetch($sql);

        if (!$member) {
            throw new HttpNotFoundException($request, 'Member not found.');
        }

        return $member;
    }
}
