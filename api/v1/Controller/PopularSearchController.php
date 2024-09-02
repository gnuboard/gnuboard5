<?php

namespace API\v1\Controller;

use API\Service\PopularSearch;
use API\v1\Model\Response\Popular\PopularSearchResponse;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * 인기검색어
 *
 */
class PopularSearchController
{
    private $popular_service;

    public function __construct(PopularSearch $popular_search_service)
    {
        $this->popular_service = $popular_search_service;
    }

    /**
     * @OA\Get (
     *     path="/api/v1/populars",
     *     summary="인기검색어 목록 조회",
     *     tags={"인기 검색어"},
     *     description="인기검색어 목록을 조회합니다.",
     *     @OA\Parameter(
     *     name="days",
     *     in="query",
     *     description="몇 일전 검색어부터 조회할지 (기본값 3)",
     *     required=false,
     *     @OA\Schema(type="integer")
     *    ),
     *     @OA\Parameter(
     *      name="limit",
     *      in="query",
     *      description="조회 갯수",
     *      required=false,
     *      @OA\Schema(type="integer")
     *     ),
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
        $days = (int)($request->getQueryParams()['days'] ?? 3);
        $count_limit = (int)($request->getQueryParams()['limit'] ?? 10);

        if ($days < 1) {
            $days = 1;
        }

        if ($count_limit < 1) {
            $count_limit = 0;
        }

        if ($count_limit > 100) {
            return api_response_json($response, ['message' => '한번에 100개 이상 조회할 수 없습니다.'], 404);
        }


        $keywords = $this->popular_service->fetchKeywords($days, $count_limit);
        if (!$keywords) {
            return api_response_json($response, ['message' => '인기 검색어가 없습니다.'], 404);
        }

        $response_data = [];
        foreach ($keywords as $keyword) {
            $response_data[] = new PopularSearchResponse($keyword);
        }
        return api_response_json($response, $response_data);
    }
}