<?php

namespace API\Middleware;

use API\Database\Db;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Exception\HttpNotFoundException;
use Slim\Routing\RouteContext;

/**
 * Write Middleware
 */
class WriteMiddleware
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        global $g5;

        // RouteContext를 사용하여 경로 매개변수 가져오기
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();

        $routeArguments = $route->getArguments();
        $bo_table = $routeArguments['bo_table'] ?? null;
        $wr_id = $routeArguments['wr_id'] ?? null;
        $table = $g5['write_prefix'] . $bo_table;

        $query = "SELECT * FROM {$table} where wr_id = :wr_id";
        $stmt = Db::getInstance()->run($query, ['wr_id' => $wr_id]);
        $write = $stmt->fetch();

        if (!$write) {
            throw new HttpNotFoundException($request, '존재하지 않는 게시글입니다.');
        }

        $request = $request->withAttribute('write', $write);

        return $handler->handle($request);
    }
}
