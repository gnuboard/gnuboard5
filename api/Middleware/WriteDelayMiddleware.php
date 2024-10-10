<?php

namespace API\Middleware;

use API\Exceptions\HttpBadRequestException;
use API\Exceptions\HttpConflictException;
use API\Service\ThrottleService;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class WriteDelayMiddleware
{
    private string $type;
    private ThrottleService $throttle_service;

    public function __construct($type)
    {
        $this->type = $type;
        $this->throttle_service = new ThrottleService();
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $token = $this->extractToken($request);
        $result = $this->throttle_service->isThrottled($token, $this->type);
        if ($result) {
            throw new HttpConflictException($request, '너무 빠른 시간내에 게시물을 연속해서 올릴 수 없습니다.');
        }

        $response = $handler->handle($request);

        // 글쓰기가 성공했는지 확인 (상태 코드가 201인 경우)
        if ($response->getStatusCode() === 201) {
            $this->throttle_service->upsertToken($token, $this->type);
        }

        return $response;
    }

    private function extractToken(Request $request): string
    {
        $token = $request->getHeaderLine('Authorization');
        $token = trim(str_replace('Bearer', '', $token));

        if (!$token) {
            throw new HttpBadRequestException($request, 'Authorization header not found.');
        }

        return $token;
    }
}