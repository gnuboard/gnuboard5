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
        $query_params = $request->getQueryParams();
        $page = isset($query_params['page']) ? (int)$query_params['page'] : 1;
        $per_page = isset($query_params['per_page']) ? (int)$query_params['per_page'] : 10;
        $me_type = isset($query_params['me_type']) ? $query_params['me_type'] : 'recv';

        $access_token = $request->getHeaderLine('Authorization');
        $access_token = str_replace('Bearer ', '', $access_token);

        // JWT 디코딩
        $access_token_decode = decode_token('access', $access_token);
        //@todo 변경가능성있음.
        $mb_id = $access_token_decode->sub;

        if ($page < 1) {
            $page = 1;
        }

        if ($per_page < 1) {
            $per_page = 10;
        }

        if ($per_page > 100) {
            return api_response_json($response, ['message' => '한번에 100개 이상 조회할 수 없습니다.'], 422);
        }

        if ($page > 100) {
            return api_response_json($response, ['message' => '100페이지 이상 조회할 수 없습니다.'], 422);
        }

        if (!in_array($me_type, ['recv', 'send'])) {
            $me_type = 'recv';
        }

        //request 검사.

        //메모 리스트 가져오기
        $memo_service = new MemoService($request);
        //count
        $total_records = $memo_service->fetch_total_records($me_type, $mb_id);
        $memo_data = $memo_service->fetch_memos($me_type, $mb_id, $page, $per_page);


        $response_data = [
            'memos' => [
                $memo_data
            ],
            'total_records' => $total_records,
            'total_page' => ceil($total_records / $per_page)
        ];

        return api_response_json($response, $response_data);
    }

    /**
     * 쪽지 전송
     * 현재 로그인 회원이 다른 회원에게 쪽지를 전송합니다.
     */
    public function send(Request $request, Response $response)
    {
        $access_token = $request->getHeaderLine('Authorization');
        $access_token = str_replace('Bearer ', '', $access_token);

        // JWT 디코딩
        $access_token_decode = decode_token('access', $access_token);
        $mb_id = $access_token_decode->sub;

        $request_data = $request->getParsedBody();

        if (!isset($request_data['me_recv_mb_id'])) {
            return api_response_json($response, ['message' => 'me_recv_mb_id 필드가 필요합니다.'], 422);
        }

        if (!isset($request_data['me_memo'])) {
            return api_response_json($response, ['message' => 'me_memo 필드가 필요합니다.'], 422);
        }

        $reciver_mb_id = $request_data['me_recv_mb_id'];
        $mem_content = $request_data['me_memo'];

        $memo_service = new MemoService($request);
        $result = $memo_service->send_memo($mb_id, $reciver_mb_id, $mem_content);
        if (isset($result['error'])) {
            return api_response_json($response, ['message' => $result['error']], 400);
        }

        $memo_service->update_memo_count($reciver_mb_id);

        return api_response_json($response, ['message' => '쪽지를 전송했습니다.']);
    }

    /**
     * 본인 쪽지 조회
     */
    public function show(Request $request, Response $response, $args)
    {
        /*$data = $request->getParsedBody();*/
        $access_token = $request->getHeaderLine('Authorization');
        $access_token = str_replace('Bearer ', '', $access_token);

        // JWT 디코딩
        $access_token_decode = decode_token('access', $access_token);
        $mb_id = $access_token_decode->sub;

        $memo_id = $args['me_id'];
        if (!is_numeric($memo_id)) {
            return api_response_json($response, ['message' => '숫자만 가능합니다.'], 422);
        }

        $memo_service = new MemoService($request);
        $result = $memo_service->fetch_memo($memo_id, $mb_id);
        if (isset($result['error'])) {
            return api_response_json($response, ['message' => $result['error']], $result['code']);
        }

        $memo_service->read_check($memo_id);

        return api_response_json($response, $result);
    }

    /**
     * 쪽지 삭제
     */
    public function delete(Request $request, Response $response, $args)
    {
        //        $data = $request->getParsedBody();
        $access_token = $request->getHeaderLine('Authorization');
        $access_token = str_replace('Bearer ', '', $access_token);

        // JWT 디코딩
        $access_token_decode = decode_token('access', $access_token);
        $mb_id = $access_token_decode->sub;

        $memo_id = $args['me_id'];
        if (!is_numeric($memo_id)) {
            return api_response_json($response, ['message' => 'memo_id 는 숫자만 가능합니다.'], 422);
        }

        $memo_service = new MemoService($request);

        $result = $memo_service->delete_memo_call($memo_id);
        if (isset($result['error'])) {
            return api_response_json($response, ['message' => $result['error']], $result['code']);
        }

        $result = $memo_service->delete_memo($memo_id, $mb_id);
        if (isset($result['error'])) {
            return api_response_json($response, ['message' => $result['error']], $result['code']);
        }


        return api_response_json($response, ['message' => '삭제되었습니다.']);
    }
}