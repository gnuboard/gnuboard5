<?php

namespace API\v1\Controller;

use API\Service\ContentService;
use API\v1\Model\Response\Content\ContentListResponse;
use API\v1\Model\Response\Content\ContentResponse;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class ContentController
{
    /**
     * 콘텐츠 목록 조회
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     *
     * @OA\Get(
     *     path="/api/v1/content",
     *     tags={"콘텐츠"},
     *     summary="콘텐츠 목록 조회",
     *     description="콘텐츠 목록 조회",
     *     @OA\Parameter(
     *     name="page",
     *     in="query",
     *     description="페이지 번호",
     *     required=false,
     *     @OA\Schema(
     *     type="integer"
     *   )
     * ),
     *     @OA\Parameter(
     *     name="per_page",
     *     in="query",
     *     description="페이지당 레코드 수",
     *     required=false,
     *     @OA\Schema(
     *     type="integer"
     *      )
     *   ),
     *     @OA\Response(
     *     response=200,
     *     description="성공",
     *     @OA\JsonContent(ref="#/components/schemas/ContentListResponse")
     *    ),
     *     @OA\Response(response="404", ref="#/components/responses/404"),
     *     @OA\Response(response="422", ref="#/components/responses/422")
     * )
     *
     */
    public function index(Request $request, Response $response, array $args)
    {
        $page = $request->getQueryParams()['page'] ?? 1;
        $page = (int)$page;
        $page = max($page, 1);
        $per_page = $request->getQueryParams()['per_page'] ?? 10;
        $per_page = (int)$per_page;
        if ($per_page > 100) {
            return api_response_json($response, ['message' => '페이지당 최대 100개까지 조회 가능합니다.'], 422);
        }

        $contents = ContentService::getContentList($page, $per_page);
        if (empty($contents)) {
            return api_response_json($response, ['message' => '콘텐츠가 없습니다.'], 404);
        }
        return api_response_json($response, new ContentListResponse($contents));
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     *
     * @OA\Get(
     *     path="/api/v1/content/{co_id}",
     *     tags={"콘텐츠"},
     *     summary="콘텐츠 상세 조회",
     *     description="콘텐츠 상세 조회",
     *      @OA\Parameter(
     *      name="co_id",
     *      in="path",
     *      description="콘텐츠 ID",
     *      required=true,
     *      @OA\Schema(
     *      type="string"
     *      )
     *     ),
     *     @OA\Response(
     *      response=200,
     *      description="성공",
     *      @OA\JsonContent(ref="#/components/schemas/ContentResponse")
     *     ),
     *     @OA\Response(response="404", ref="#/components/responses/404"),
     *     @OA\Response(response="422", ref="#/components/responses/422")
     * )
     */
    public function show(Request $request, Response $response, array $args)
    {
        if (!isset($args['co_id'])) {
            return api_response_json($response, ['message' => '콘텐츠 ID가 필요합니다.'], 422);
        }

        $content = ContentService::getContent($args['co_id']);
        if (empty($content)) {
            return api_response_json($response, ['message' => '콘텐츠가 없습니다.'], 404);
        }
        $response_data = new ContentResponse($content);
        return api_response_json($response, $response_data);
    }
}