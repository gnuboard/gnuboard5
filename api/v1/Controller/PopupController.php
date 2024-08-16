<?php

namespace API\v1\Controller;

use API\Service\PopupService;
use API\v1\Model\Response\Layer\PopupResponse;

use Slim\Psr7\Request;
use Slim\Psr7\Response;

/**
 * 팝업 컨트롤러.
 *
 */
class PopupController
{
    private $popup_service;

    public function __construct(PopupService $popup_service)
    {
        $this->popup_service = $popup_service;
    }

    /**
     * @OA\Get (
     *     path="/api/v1/newwins",
     *     summary="팝업 창 목록 조회",
     *     tags={"팝업 레이어"},
     *     description="팝업 목록들을 조회합니다.",
     *     @OA\Parameter(
     *      name="device",
     *      in="query",
     *      description="접속 기기",
     *      required=false,
     *     @OA\Schema(type="string", default="both (둘다)", enum={"pc", "mobile", "both"})),
     *     @OA\Parameter(
     *     name="except_ids",
     *     in="query",
     *     description="제외할 팝업 ID",
     *     required=false,
     *     @OA\Schema(type="string")
     *    ),
     *     @OA\Response(response="200",
     *     description="팝업 조회 성공",
     *     @OA\JsonContent(
     *          type="array",
     *          @OA\Items(ref="#/components/schemas/PopupResponse")
     *     )
     *   ),
     *   @OA\Response(response="404", ref="#/components/responses/404", description="팝업이 없습니다.")
     * )
     * @param Request $request
     * @param Response $response
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function show(Request $request, Response $response)
    {
        $device = $request->getAttribute('device') ?? 'both';
        $except_ids = $request->getQueryParams()['except_ids'] ?? '';
        // @todo cache
        $data = $this->popup_service->fetch_popup($device);
        if (!$data || (is_countable($data) && count($data) === 0)) {
            return api_response_json($response, ['message' => '팝업이 없습니다.'], 404);
        }

        if ($except_ids) {
            $data = $this->popup_service->except_popup($data, $except_ids);
            if (count($data) === 0) {
                return api_response_json($response, ['message' => '팝업이 없습니다.'], 404);
            }
        }
        $response_data = [];
        foreach ($data as $popup) {
            $response_data[] = new PopupResponse($popup);
        }
        return api_response_json($response, $response_data);
    }
}