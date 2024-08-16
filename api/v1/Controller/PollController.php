<?php

namespace API\v1\Controller;

require_once G5_LIB_PATH . '/mailer.lib.php';

use API\Service\MemberService;
use API\Service\PollService;
use API\v1\Model\Response\Poll\PollResponse;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

/**
 * 최신 투표 1건 조회
 * @param Request $request
 * @param Response $response
 * @return ResponseInterface
 */
class PollController
{
    private PollService $poll_service;
    private MemberService $member_service;

    public function __construct(PollService $pollService, MemberService $member_service)
    {
        $this->member_service = $member_service;
        $this->poll_service = $pollService;
    }

    /**
     * @OA\Get (
     *      path="/api/v1/polls/latest",
     *      summary="설문조사 조회",
     *      tags={"설문조사"},
     *      description="설문조사 정보를 조회합니다.",
     *     @OA\Response(response="200",
     *         description="마지막 투표결과 조회 성공",
     *         @OA\JsonContent(ref="#/components/schemas/GetItemResponse")
     *     ),
     *     @OA\Response(response="404", ref="#/components/responses/404")
     * )
     */
    public function showLatest(Request $request, Response $response)
    {
        $poll = $this->poll_service->get_latest_poll();
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
     *      security={{"Oauth2Password": {}}},
     *     @OA\Parameter(name="po_id", in="path", description="설문조사 번호", required=true, @OA\Schema(type="integer"), example=1),
     *     @OA\Response(response="200", description="설문조사 조회 성공",
     *     @OA\JsonContent(ref="#/components/schemas/PollResponse")
     *     ),
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

        $poll = $this->poll_service->fetchPoll($po_id);
        if ($poll === null) {
            return api_response_json($response, ['message' => '설문조사가 없습니다.'], 404);
        }

        $response_data = new PollResponse($poll);
        return api_response_json($response, $response_data);
    }

    /**
     * @OA\Patch(
     *      path="/api/v1/polls/{po_id}/{item}",
     *      summary="설문조사 참여",
     *      description="설문조사 항목에 투표. 권한이 없거나 이미 참여한 경우는 투표할 수 없습니다. 포인트 설정이 되어있는 경우, 투표 시 포인트가 지급됩니다.",
     *      tags={"설문조사"},
     *      @OA\Parameter(
     *          name="po_id",
     *          in="path",
     *          required=true,
     *          description="설문조사 ID",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Parameter(
     *          name="item",
     *          in="path",
     *          required=true,
     *          description="설문항목 번호",
     *          @OA\Schema(
     *              type="integer",
     *              minimum=1,
     *              maximum=9
     *          )
     *      ),
     *     @OA\Response(response="200", description="투표완료", @OA\JsonContent(ref="#/components/schemas/BaseResponse")),
     *     @OA\Response(response=403, ref="#/components/responses/403"),
     *     @OA\Response(response=404, ref="#/components/responses/404"),
     *     @OA\Response(response="409", ref="#/components/responses/409"),
     *     @OA\Response(response="422", ref="#/components/responses/422"),
     *      security={{"Oauth2Password": {}}}
     *  )
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return ResponseInterface
     */
    public function vote(Request $request, Response $response, array $args)
    {
        $po_id = $args['po_id'];
        $item_id = $args['item'];
        /**
         * @var array $member
         */
        $member = $request->getAttribute('member');

        if (!is_numeric($po_id) || !is_numeric($item_id)) {
            return api_response_json($response, ['message' => '설문조사 번호 또는 항목 번호가 잘못되었습니다.'], 400);
        }

        if ($item_id < 1 || $item_id > 9) {
            return api_response_json($response, ['message' => '설문항목 번호가 잘못되었습니다.'], 400);
        }


        $poll = $this->poll_service->fetchPoll($po_id);
        if ($poll === null) {
            return api_response_json($response, ['message' => '설문조사가 없습니다.'], 404);
        }

        if (isset($poll['po_level']) && $poll['po_level'] > $member['mb_level']) {
            return api_response_json($response, ['message' => '투표 권한이 없습니다.'], 403);
        }

        if ($this->poll_service->checkAlreadyVote($po_id, $member, get_real_client_ip())) {
            return api_response_json($response, ['message' => '이미 투표하셨습니다.'], 400);
        }

        $this->poll_service->votePoll($po_id, $item_id, get_real_client_ip());
        return api_response_json($response, ['message' => '투표가 완료되었습니다.']);
    }

    /**
     * 기타 의견 추가
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return ResponseInterface
     * @OA\Post (
     *     path="/api/v1/polls/{po_id}/etc",
     *     tags={"설문조사"},
     *     description="설문조사 기타의견을 추가합니다. 비회원가능",
     *     @OA\Parameter (
     *     name="po_id",
     *     in="path",
     *     description="설문조사 번호",
     *     required=true,
     *     @OA\Schema(type="integer")
     *    ),
     *     @OA\RequestBody (
     *     required=true,
     *     @OA\JsonContent (
     *     required={"content"},
     *     @OA\Property(property="content", type="string", description="기타의견", example="기타의견 내용")
     *     )
     *    ),
     *      security={{"Oauth2Password": {}}},
     *     @OA\Response(response="200", description="투표완료", @OA\JsonContent(ref="#/components/schemas/BaseResponse")),
     *     @OA\Response(response=400, ref="#/components/responses/400"),
     *     @OA\Response(response=403, ref="#/components/responses/403"),
     *     @OA\Response(response=404, ref="#/components/responses/404"),
     *     @OA\Response(response=422, ref="#/components/responses/422")
     * )
     */
    public function createEtc(Request $request, Response $response, array $args)
    {
        $po_id = $args['po_id'];
        $content = $request->getParsedBody()['content'] ?? '';
        $pc_name = $request->getParsedBody()['pc_name'] ?? '';
        $config = $request->getAttribute('config');
        $member = $request->getAttribute('member');

        if (!$content) {
            return api_response_json($response, ['message' => '의견을 입력해주세요.'], 400);
        }

        if (!is_numeric($po_id)) {
            return api_response_json($response, ['message' => '설문조사 번호가 잘못되었습니다.'], 400);
        }
        
        $is_guest = empty($member['mb_id']) == '';
        if ($is_guest) {
            $mb_id = '';
        } else {
            $mb_id = $member['mb_id'];
        }

        $this->poll_service->addEtcPoll($po_id, $pc_name, $content, $mb_id);

        // 관리자에게 메일 보내기
        if ($config['cf_email_po_super_admin']) {
            $poll = $this->poll_service->fetchPoll($po_id);
            $subject = $poll['po_subject'];

            ob_start();
            include_once(G5_BBS_PATH . '/poll_etc_update_mail.php');
            $content = ob_get_clean();

            $admin_id = $config['cf_admin'];
            $admin_member = $this->member_service->fetchMemberById($admin_id);
            $from_email = $member['mb_email'] ?: $admin_member['mb_email'];
            if ($is_guest) {
                $name = 'guest';
            } else {
                $name = $member['mb_nick'];
            }
            mailer($name, $from_email, $admin_member['mb_email'], " [{$config['cf_title']}] 설문조사 기타의견 메일", $content, 1);
        }

        return api_response_json($response, ['message' => '기타의견이 등록되었습니다']);
    }

    /**
     * 회원, 관리자만 가능
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return ResponseInterface
     *
     * @OA\Delete (
     *     path="/api/v1/polls/{po_id}/etc/{pc_id}",
     *     tags={"설문조사"},
     *     description="설문조사 기타의견을 삭제합니다. 회원, 관리자만 가능",
     *     @OA\Parameter (
     *     name="po_id",
     *     in="path",
     *     description="설문조사 번호",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *    @OA\Parameter (
     *     name="pc_id",
     *     in="path",
     *     description="기타 의견 번호",
     *     required=true,
     *     @OA\Schema(type="integer")
     *  ),
     *     security={{"Oauth2Password": {}}},
     *     @OA\Response(response="200", description="삭제 되었습니다.", @OA\JsonContent(ref="#/components/schemas/BaseResponse")),
     *     @OA\Response(response=400, ref="#/components/responses/400"),
     *     @OA\Response(response=403, ref="#/components/responses/403")
     * )
     */
    public function deleteEtc(Request $request, Response $response, array $args)
    {
        $po_id = $args['po_id'];
        $pc_id = $args['pc_id'];
        $mb_id = $request->getParsedBody()['mb_id'];
        if (!is_numeric($po_id) || !is_numeric($pc_id)) {
            return api_response_json($response, ['message' => '설문조사 번호 또는 항목 번호가 잘못되었습니다.'], 400);
        }

        if ($this->poll_service->checkAuthEtcPoll($pc_id, $mb_id)) {
            return api_response_json($response, ['message' => '권한이 없습니다.'], 403);
        }

        if ($this->poll_service->deleteEtcPoll($po_id, $pc_id)) {
            return api_response_json($response, ['message' => '삭제되었습니다.']);
        }

        return api_response_json($response, ['message' => '삭제 실패했습니다.'], 400);
    }

}