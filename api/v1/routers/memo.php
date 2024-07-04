<?php

namespace Api\V1\memo;

use API\Middleware\AccessTokenAuthMiddleware;
use API\Service\MemoService;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Slim\Routing\RouteCollectorProxy;
use Slim\App;

/**
 * @var App $app
 */


$app->group('/member/memos', function (RouteCollectorProxy $group) {
    
    /**
     * 쪽지 목록 조회
     * 현재 로그인 회원의 쪽지 목록을 조회합니다.
     */
    $group->get('', function (Request $request, Response $response) {
        $queryParams = $request->getQueryParams();
        $page = isset($queryParams['page']) ? (int)$queryParams['page'] : 1;
        $perPage = isset($queryParams['perPage']) ? (int)$queryParams['perPage'] : 10;
        $meType = isset($queryParams['me_type']) ? $queryParams['me_type'] : 'recv';
        
        $access_token = $request->getHeaderLine('Authorization');
        $access_token = str_replace('Bearer ', '', $access_token);

        // JWT 디코딩
        $access_token_decode = decode_token('access', $access_token);
        //@todo 변경가능성있음.
        $memberId = $access_token_decode->sub;

        $error_data = [];
        
        if($page < 1) {
            $page = 1;
        }
        
        if($perPage < 1) {
            $perPage = 10;
        }

        if ($page > 100 ) {
            $error_data[] = [
                'loc' => ['query', 'page'],
                'msg' => 'ensure this value is less than or equal to 100',
                'type' => 'less_than_equal'
            ];
        }
        
        if(!in_array($meType, ['recv', 'send'])) {
            $meType = 'recv';
        }
        
        if($error_data) {
            return responseValidationError($response, $error_data);
        }
        //request 검사.
        
        //메모 리스트 가져오기
        $memo_service = new MemoService();
        //count
        $totalRecords = $memo_service->fetchTotalRecords($meType, $memberId);
        $memo_data = $memo_service->fetchMemos($meType, $memberId, $page, $perPage);
        
        $responseData = [
            'memos' => [
                $memo_data
            ],
            'total_records' => $totalRecords,
            'total_page' => $page,
        ];
        
        return api_response_json($response, $responseData);

    });
})->add(new AccessTokenAuthMiddleware());


/**
 * FastAPI pydantic 오류 형식 호환 함수
 * @param $response
 * @param $error_data
 * @return mixed
 */
function responseValidationError($response, $error_data)
{
    $errorResponse = [
        'detail' => [
            $error_data
        ]
    ];
    $response->getBody()->write(json_encode($errorResponse));
    return $response->withStatus(422)->withHeader('Content-Type', 'application/json');
    
}