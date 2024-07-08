<?php

namespace API\v1\Controller;

use API\Service\MemoService;
use Slim\Psr7\Request;
use Slim\Psr7\Response;


class MemoController
{
    /**
     * 쪽지 목록 조회
     * 현재 로그인 회원의 쪽지 목록을 조회합니다.
     */
    public function index(Request $request, Response $response)
    {
        $queryParams = $request->getQueryParams();
        $page = isset($queryParams['page']) ? (int)$queryParams['page'] : 1;
        $perPage = isset($queryParams['per_page']) ? (int)$queryParams['per_page'] : 10;
        $meType = isset($queryParams['me_type']) ? $queryParams['me_type'] : 'recv';

        $accessToken = $request->getHeaderLine('Authorization');
        $accessToken = str_replace('Bearer ', '', $accessToken);

        // JWT 디코딩
        $accessTokenDecode = decode_token('access', $accessToken);
        //@todo 변경가능성있음.
        $memberId = $accessTokenDecode->sub;

        $error_data = [];

        if ($page < 1) {
            $page = 1;
        }

        if ($perPage < 1) {
            $perPage = 10;
        }

        if ($page > 100) {
            $error_data[] = [
                'loc' => ['query', 'page'],
                'msg' => 'ensure this value is less than or equal to 100',
                'type' => 'less_than_equal'
            ];
        }

        if (!in_array($meType, ['recv', 'send'])) {
            $meType = 'recv';
        }

        if ($error_data) {
            return $this->responseValidationError($response, $error_data);
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
    }

    /**
     * 쪽지 전송
     * 현재 로그인 회원이 다른 회원에게 쪽지를 전송합니다.
     */
    public function send(Request $request, Response $response)
    {
        $data = $request->getParsedBody();
        $accessToken = $request->getHeaderLine('Authorization');
        $accessToken = str_replace('Bearer ', '', $accessToken);

        // JWT 디코딩
        $accessTokenDecode = decode_token('access', $accessToken);
        $memberId = $accessTokenDecode->sub;

        $request_data = $request->getParsedBody();
        $error_data = [];
        //request data 검사
        //@todo 오류 response 포맷 변경
        if (!isset($data['me_recv_mb_id'])) {
            $error_data[] = [
                'loc' => ['body', 'me_recv_mb_id'],
                'msg' => 'field required',
                'type' => 'value_error.missing'
            ];
        }

        if (!isset($data['me_memo'])) {
            $error_data[] = [
                'loc' => ['body', 'me_memo'],
                'msg' => 'field required',
                'type' => 'value_error.missing'
            ];
        }

        if ($error_data) {
            return $this->responseValidationError($response, $error_data);
        }

        $reciverMemeberId = $request_data['me_recv_mb_id'];
        $memoContent = $request_data['me_memo'];

        $memo_service = new MemoService($request);
        $result = $memo_service->sendMemo($memberId, $reciverMemeberId, $memoContent);
        if (isset($result['error'])) {
            return api_response_json($response, ['message' => $result['error']], 400);
        }


        return api_response_json($response, ['message' => '쪽지를 전송했습니다.']);
    }

    /**
     * 본인 쪽지 조회
     */
    public function show(Request $request, Response $response, $args)
    {
        $data = $request->getParsedBody();
        $accessToken = $request->getHeaderLine('Authorization');
        $accessToken = str_replace('Bearer ', '', $accessToken);

        $memoId = $args['me_id'];
        if (!is_numeric($memoId)) {
            $error_data[] = [
                'loc' => ['body', 'me_id'],
                'msg' => '숫자만 가능합니다.',
                'type' => 'type error'
            ];
            return $this->responseValidationError($response, $error_data);
        }

        // JWT 디코딩
        $accessTokenDecode = decode_token('access', $accessToken);
        $memberId = $accessTokenDecode->sub;

        $memoService = new MemoService($request);
        $result = $memoService->fetchMemo($memoId, $memberId);
        if ($result['error']) {
            return api_response_json($response, ['message' => $result['error']], $result['code']);
        }

        return api_response_json($response, $result);
    }

    public function delete(Request $request, Response $response, $args)
    {
        //        $data = $request->getParsedBody();
        $accessToken = $request->getHeaderLine('Authorization');
        $accessToken = str_replace('Bearer ', '', $accessToken);

        $memoId = $args['me_id'];
        if (!is_numeric($memoId)) {
            $error_data[] = [
                'loc' => ['body', 'me_id'],
                'msg' => '숫자만 가능합니다.',
                'type' => 'type error'
            ];
            return $this->responseValidationError($response, $error_data);
        }

        // JWT 디코딩
        $accessTokenDecode = decode_token('access', $accessToken);
        $memberId = $accessTokenDecode->sub;

        $memoService = new MemoService($request);
        $memoService->deleteMemoCall($memoId);
        $result = $memoService->deleteMemo($memoId, $memberId);
        if ($result['error']) {
            return api_response_json($response, ['message' => $result['error']], $result['code']);
        }

        return api_response_json($response, ['message' => '삭제되었습니다.']);
    }

    /**
     * FastAPI pydantic 오류 형식 호환 함수
     * @param $response
     * @param $error_data
     * @return mixed
     */
    public function responseValidationError($response, $error_data)
    {
        $errorResponse = [
            'detail' => [
                $error_data
            ]
        ];
        $response->getBody()->write(json_encode($errorResponse));
        return $response->withStatus(422)->withHeader('Content-Type', 'application/json');
    }
}