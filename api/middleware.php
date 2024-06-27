<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

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
        $token = $request->getHeaderLine('Authorization');

        if (empty($token)) {
            return api_response_json($handler->handle($request), array(
                'error' => 'No token provided'
            ), 401);
        }

        $token = str_replace('Bearer ', '', $token);

        try {
            $decode = JWT::decode($token, new Key($this->secretKey, $this->algorithm));
            // mb_id를 request attribute에 추가
            $request = $request->withAttribute('mb_id', $decode->sub);

        } catch (\Exception $e) {
            return api_response_json($handler->handle($request), array(
                'error' => 'Invalid token'
            ), 401);
        }

        return $handler->handle($request);
    }
}


/**
 * Custom Middleware
 */
$config_mw = function (Request $request, RequestHandler $handler) {
    global $g5;

    $sql = "SELECT * FROM {$g5['config_table']}";
    $config = sql_fetch($sql);

    $request = $request->withAttribute('config', $config);

    return $handler->handle($request);
};

$get_member_mw = function (Request $request, RequestHandler $handler) {
    global $g5;
    // $auth = new AccessTokenAuthMiddleware();

    // $mb_id = $request->getAttribute('mb_id');

    // $sql = "SELECT * FROM {$g5['member_table']} WHERE mb_id = '{$mb_id}'";
    // $member = sql_fetch($sql);

    // $request = $request->withAttribute('member', $member);

    return $handler->handle($request);
};
