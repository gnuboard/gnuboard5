<?php

namespace API\v1\Controller;


use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

use API\Service\Poll as poll;

/**
 * 최신 투표 1건 조회
 * @param Request $request
 * @param Response $response
 * @return ResponseInterface
 */
class PollController
{

    /**
     * @OA\Get (
     *      path="/api/v1/polls/latest",
     *      summary="설문조사 조회",
     *      tags={"설문조사"},
     *      description="설문조사 정보를 조회합니다.",
     *     @OA\Response(response="200", @OA\JsonContent(ref="#/components/schemas/GetItemResponse")),
     *     @OA\Response(response="404", ref="#/components/responses/404")
     *     @OA\Response(response="422", ref="#/components/responses/422"),
     * )
     */
    public function latest(Request $request, Response $response)
    {
        //@todo cache 추가
        $poll = poll\get_latest_poll();
        if ($poll === null) {
            return api_response_json($response, ['message' => '최신 투표가 없습니다.'], 404);
        }

        return api_response_json($response, $poll);
    }

    /**
     * @OA\Get (
     *      path="/api/v1/polls/{po_id}",
     *      summary="설문조사 조회",
     *      tags={"설문조사"},
     *      description="설문조사 정보를 조회합니다.",
     *      security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="po_id", in="path", description="설문조사 번호", required=true, @OA\Schema(type="integer"), example=1),
     *     @OA\Response(response="200", @OA\JsonContent(ref="#/components/schemas/GetItemResponse")),
     *     @OA\Response(response="403", ref="#/components/responses/403"),
     *     @OA\Response(response="404", ref="#/components/responses/404")
     * )
     */
    public function show(Request $request, Response $response, array $args)
    {
        $po_id = $args['po_id'];

        if (!is_numeric($po_id)) {
            return api_response_json($response, ['message' => '설문조사 번호가 잘못되었습니다.'], 400);
        }

        //@todo cache 추가
        $poll = poll\get_poll($po_id);
        if ($poll === null) {
            return api_response_json($response, ['message' => '설문조사가 없습니다.'], 404);
        }

        return api_response_json($response, $poll);
    }

    /**
     * 설문조사 투표
     * 권한이 없거나 이미 참여한 경우는 투표할 수 없습니다.
     * 포인트 설정이 되어있는 경우, 투표 시 포인트가 지급됩니다.
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return ResponseInterface
     */
    public function vote(Request $request, Response $response, array $args)
    {
        $po_id = $args['po_id'];
        $item_id = $args['item'];

        if (!is_numeric($po_id) || !is_numeric($item_id)) {
            return api_response_json($response, ['message' => '설문조사 번호 또는 항목 번호가 잘못되었습니다.'], 400);
        }

        if (poll\check_already_vote($po_id)) {
            return api_response_json($response, ['message' => '이미 투표하셨습니다.'], 400);
        }

        poll\vote_poll($po_id, $item_id, get_real_client_ip());
        return api_response_json($response, ['message' => '투표가 완료되었습니다.']);
    }

    /**
     * 기타 투표 생성
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return ResponseInterface
     */
    public function etc_create(Request $request, Response $response, array $args)
    {
        $po_id = $args['po_id'];
        $content = $request->getParsedBody()['content'];

        if (!is_numeric($po_id)) {
            return api_response_json($response, ['message' => '설문조사 번호가 잘못되었습니다.'], 400);
        }

        poll\add_etc_poll($po_id, $content);

        //@todo 기타의견 등록 시 최고관리자에게 메일이 발송됩니다. (메일발송 설정 시)

        return api_response_json($response, ['message' => '투표가 완료되었습니다.']);
    }

    /**
     * 회원만 가능.
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return ResponseInterface
     */
    public function etc_delete(Request $request, Response $response, array $args)
    {
        $po_id = $args['po_id'];
        $pc_id = $args['pc_id'];
        $mb_id = $request->getParsedBody()['mb_id'];
        if (!is_numeric($po_id) || !is_numeric($pc_id)) {
            return api_response_json($response, ['message' => '설문조사 번호 또는 항목 번호가 잘못되었습니다.'], 400);
        }

        if (poll\check_auth_etc_poll($pc_id, $mb_id)) {
            return api_response_json($response, ['message' => '권한이 없습니다.'], 403);
        }

        if (poll\delete_etc_poll($po_id, $pc_id)) {
            return api_response_json($response, ['message' => '삭제되었습니다.']);
        }

        return api_response_json($response, ['message' => '삭제 실패했습니다.'], 400);
    }

}