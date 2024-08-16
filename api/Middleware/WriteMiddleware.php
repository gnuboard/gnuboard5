<?php

namespace API\Middleware;

use API\Exceptions\HttpNotFoundException;
use API\Service\WriteService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Routing\RouteContext;

/**
 * Write Middleware
 */
class WriteMiddleware
{
    private WriteService $write_service;

    public function __construct(WriteService $write_service)
    {
        $this->write_service = $write_service;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        // RouteContext를 사용하여 경로 매개변수 가져오기
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();
        $routeArguments = $route->getArguments();
        $wr_id = $routeArguments['wr_id'] ?? null;

        $write = $this->write_service->fetchWrite((int)$wr_id);

        if (!$write) {
            throw new HttpNotFoundException($request, '존재하지 않는 게시글입니다.');
        }

        $request = $request->withAttribute('write', $write);

        return $handler->handle($request);
    }
}
