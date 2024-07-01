<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Exception\HttpUnauthorizedException;
use Slim\Exception\HttpNotFoundException;

/**
 * JSON Body Parser Middleware
 * 
 * This middleware parses JSON request body and attaches it to the request as a `parsedBody` attribute
 * 
 * @see https://www.slimframework.com/docs/v4/objects/request.html#the-request-body
 */
class JsonBodyParserMiddleware implements MiddlewareInterface
{
    /**
     * Parse JSON request body and attach it to the request as a `parsedBody` attribute
     *
     * @param Request $request PSR-7 request
     * @param RequestHandler $handler PSR-15 request handler
     *
     * @return Response
     */
    public function process(Request $request, RequestHandler $handler): Response
    {
        $contentType = $request->getHeaderLine('Content-Type');

        if (strstr($contentType, 'application/json')) {
            $contents = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $request = $request->withParsedBody($contents);
            }
        }

        return $handler->handle($request);
    }
}

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
        $token_manager = new JwtTokenManager();

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
        $token = str_replace('Bearer', '', $token);

        if (!$token) {
            throw new HttpUnauthorizedException($request, 'Authorization header not found.');
        }

        return trim($token);
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

/**
 * Config Middleware
 */
$config_mw = function (Request $request, RequestHandler $handler) {
    global $g5;

    $sql = "SELECT * FROM {$g5['config_table']}";
    $config = sql_fetch($sql);

    if (!$config) {
        throw new HttpNotFoundException($request, 'Config not found.');
    }

    $request = $request->withAttribute('config', $config);

    return $handler->handle($request);
};
