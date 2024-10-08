<?php

namespace API\v1\Controller;

use API\Exceptions\HttpBadRequestException;
use API\Exceptions\HttpForbiddenException;
use API\Service\ConfigService;
use API\Service\MemoService;
use API\Service\PointService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;


class MemoController
{
    private MemoService $memo_service;
    private PointService $point_service;

    public function __construct(MemoService $memoService, PointService $point_service)
    {
        $this->point_service = $point_service;
        $this->memo_service = $memoService;
    }

    /**
     * 쪽지 목록 조회
     * 현재 로그인한 회원의 모든 쪽지 목록을 조회합니다.
     * 각 쪽지에 읽음/않읽음이 표시됩니다.
     * @OA\Get (
     *     path="/api/v1/member/memos",
     *     summary="쪽지 목록 조회",
     *     tags={"쪽지"},
     *     description="현재 로그인한 회원의 모든 쪽지 목록을 조회합니다.",
     *     security={{"Oauth2Password": {}}},
     *     @OA\Parameter (
     *     name="page",
     *     description="페이지 번호",
     *     in="query",
     *     required=false,
     *     @OA\Schema(type="integer", minimum=1)
     *    ),
     *     @OA\Parameter (
     *     name="per_page",
     *     description="페이지당 표시할 목록 수",
     *     in="query",
     *     required=false,
     *     @OA\Schema(type="integer", maximum=100)
     *   ),
     *     @OA\Parameter (
     *     name="me_type",
     *     description="쪽지 타입 (recv: 받은 쪽지, send: 보낸 쪽지)",
     *     in="query",
     *     required=true,
     *     @OA\Schema(type="string")
     *  ),
     *     @OA\Response (
     *     response="200",
     *     description="쪽지 목록 조회 성공",
     *     @OA\JsonContent(ref="#/components/schemas/MemoListResponse")
     *    ),
     *      @OA\Response(response="400", ref="#/components/responses/400"),
     *      @OA\Response(response="403", ref="#/components/responses/403"),
     *      @OA\Response(response="422", ref="#/components/responses/422")
     * )
     *
     *
     */
    public function index(Request $request, Response $response)
    {
        $query_params = $request->getQueryParams();
        $page = isset($query_params['page']) ? (int)$query_params['page'] : 1;
        $per_page = isset($query_params['per_page']) ? (int)$query_params['per_page'] : 10;
        $memo_type = $query_params['me_type'] ?? null;
        $member = $request->getAttribute('member');
        $mb_id = $member['mb_id'];

        if ($page < 1) {
            $page = 1;
        }

        if ($per_page < 1) {
            $per_page = 10;
        }

        if ($per_page > 100) {
            return api_response_json($response, ['message' => '한번에 100개 이상 조회할 수 없습니다.'], 422);
        }

        if ($memo_type == null) {
            return api_response_json($response, ['message' => 'me_type 을 입력하세요'], 422);
        }

        if (!in_array($memo_type, ['recv', 'send'])) {
            return api_response_json($response, ['message' => '올바른 me_type 을 입력하세요'], 400);
        }

        //request 검사 끝

        //메모 리스트 가져오기
        //count
        $total_records = $this->memo_service->fetchTotalCount($memo_type, $mb_id);
        $memo_data = $this->memo_service->getMemos($memo_type, $mb_id, $page, $per_page);

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
     * 현재 로그인 회원이 다른 회원에게 쪽지를 전송합니다.
     * @OA\Post (
     *     path="/api/v1/member/memos",
     *     summary="쪽지 전송",
     *     tags={"쪽지"},
     *     security={{"Oauth2Password": {}}},
     *     @OA\RequestBody (
     *     required=true,
     *     description="쪽지 전송",
     *     @OA\JsonContent(
     *     required={"me_recv_mb_id", "me_memo"},
     *     @OA\Property(
     *     property="me_recv_mb_id",
     *     type="string",
     *     description="받는 회원 ID"
     *   ),
     *     @OA\Property(
     *     property="me_memo",
     *     type="string",
     *     description="쪽지 내용"
     *     )
     *    )
     *   ),
     *     @OA\Response (
     *     response="200",
     *     description="쪽지 전송 성공",
     *     @OA\JsonContent(ref="#/components/schemas/BaseResponse")
     *     ),
     *     @OA\Response(response="400", ref="#/components/responses/400"),
     *     @OA\Response(response="403", ref="#/components/responses/403"),
     *     @OA\Response(response="422", ref="#/components/responses/422")
     * )
     */
    public function send(Request $request, Response $response)
    {
        $member = $request->getAttribute('member');
        $config = ConfigService::getConfig();
        $mb_id = $member['mb_id'];

        $request_data = $request->getParsedBody();


        if (!isset($request_data['me_recv_mb_id'])) {
            return api_response_json($response, ['message' => 'me_recv_mb_id 필드가 필요합니다.'], 422);
        }

        if (!isset($request_data['me_memo'])) {
            return api_response_json($response, ['message' => 'me_memo 필드가 필요합니다.'], 422);
        }

        $receiver_mb_id = $request_data['me_recv_mb_id'];
        $ip = $request->getServerParams()['REMOTE_ADDR'];
        $sended_memo_ids = [];
        try {
            $sended_memo_ids = $this->memo_service->sendMemo($mb_id, $receiver_mb_id, $request_data['me_memo'], $ip);
        } catch (\Exception $e) {
            if ($e->getCode() === 400) {
                throw new HttpBadRequestException($request, $e->getMessage());
            }
        }

        // 쪽지 포인트 차감
        foreach ($sended_memo_ids as $memo_id) {
            $this->point_service->addPoint($mb_id, (int)$config['cf_memo_send_point'] * (-1), $receiver_mb_id . '(' . $receiver_mb_id . ')님께 쪽지 발송', '@memo', $receiver_mb_id,
                $memo_id);
        }
        $this->memo_service->updateNotReadMemoCount($receiver_mb_id);

        run_event('api_send_memo_after', $mb_id, $receiver_mb_id, $request_data);

        return api_response_json($response, ['message' => '쪽지를 전송했습니다.']);
    }

    /**
     * 본인 쪽지 조회
     * @OA\Get (
     *     path="/api/v1/member/memos/{me_id}",
     *     summary="쪽지 조회",
     *     tags={"쪽지"},
     *     security={{"Oauth2Password": {}}},
     *     @OA\Parameter (
     *     name="me_id",
     *     in="path",
     *     description="쪽지 ID",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *     @OA\Response (
     *     response="200",
     *     description="쪽지 조회 성공",
     *     @OA\JsonContent(ref="#/components/schemas/MemoResponse")
     *  ),
     *     @OA\Response(response="400", ref="#/components/responses/400"),
     *     @OA\Response(response="403", ref="#/components/responses/403"),
     *     @OA\Response(response="422", ref="#/components/responses/422")
     * )
     */
    public function show(Request $request, Response $response, $args)
    {
        $member = $request->getAttribute('member');
        $mb_id = $member['mb_id'];

        $memo_id = $args['me_id'];
        if (!is_numeric($memo_id)) {
            return api_response_json($response, ['message' => '숫자만 가능합니다.'], 422);
        }

        try {
            $result = $this->memo_service->fetchMemo($memo_id, $mb_id);
        } catch (\Exception $e) {
            if ($e->getCode() === 403) {
                throw new HttpForbiddenException($request, $e->getMessage());
            }
        }

        $this->memo_service->checkRead($memo_id);

        return api_response_json($response, $result);
    }

    /**
     *  쪽지 삭제
     * @OA\Delete (
     *      path="/api/v1/member/memos/{me_id}",
     *      summary="쪽지 삭제",
     *      tags={"쪽지"},
     *      security={{"Oauth2Password": {}}},
     *      @OA\Parameter (
     *      name="me_id",
     *      in="path",
     *      description="쪽지 ID",
     *      required=true,
     *      @OA\Schema(type="integer")
     *    ),
     *    @OA\Response(response="200", description="쪽지 삭제 완료", @OA\JsonContent(ref="#/components/schemas/BaseResponse")),
     *    @OA\Response(response="400", ref="#/components/responses/400"),
     *    @OA\Response(response="403", ref="#/components/responses/403"),
     *    @OA\Response(response="422", ref="#/components/responses/422")
     * )
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function delete(Request $request, Response $response, $args)
    {
        $member = $request->getAttribute('member');
        $mb_id = $member['mb_id'];
        $memo_id = $args['me_id'];
        if (!is_numeric($memo_id)) {
            return api_response_json($response, ['message' => 'memo_id 는 숫자만 가능합니다.'], 422);
        }
        
        try {
            $this->memo_service->deleteMemoCall($memo_id);
            $this->memo_service->deleteMemo($memo_id, $mb_id);
        } catch (\Exception $e) {
            if ($e->getCode() === 400) {
                throw new HttpBadRequestException($request, $e->getMessage());
            }

            if ($e->getCode() === 403) {
                throw new HttpForbiddenException($request, $e->getMessage());
            }
        }

        return api_response_json($response, ['message' => '삭제되었습니다.']);
    }
}