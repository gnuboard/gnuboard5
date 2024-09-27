<?php

namespace API\v1\Controller;

use API\Service\SearchService;
use API\v1\Model\Request\Board\BoardAllSearchRequest;
use API\v1\Model\Response\Search\BoardAllSearchResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class SearchController
{
    private SearchService $search_service;

    function __construct(SearchService $searchService)
    {
        $this->search_service = $searchService;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/search",
     *     summary="게시판 검색",
     *     tags={"검색"},
     *     description="검색어로 조회합니다.",
     *     security={{"Oauth2Password": {}}},
     *     @OA\Parameter(ref="#/components/parameters/sst"),
     *     @OA\Parameter(ref="#/components/parameters/sod"),
     *     @OA\Parameter(ref="#/components/parameters/sfl"),
     *     @OA\Parameter(ref="#/components/parameters/stx"),
     *     @OA\Parameter(ref="#/components/parameters/sca"),
     *     @OA\Parameter(ref="#/components/parameters/page"),
     *     @OA\Parameter(ref="#/components/parameters/per_page"),
     *     @OA\Parameter(ref="#/components/parameters/is_mobile"),
     *     @OA\Parameter(
     *      name="onetable",
     *      in="query",
     *      description="단일테이블",
     *      required=false,
     *       @OA\Schema(
     *       type="string"
     *       )
     *     ),
     *     @OA\Response(response="200", description="검색어 조회 성공", @OA\JsonContent(ref="#/components/schemas/BoardAllSearchResponse")),
     *     @OA\Response(response="404", ref="#/components/responses/404"),
     *     @OA\Response(response="500", ref="#/components/responses/500")
     * )
     */
    public function searchBoard(Request $request, Response $response, $args)
    {
        $member = $request->getAttribute('member');
        $search_param = new BoardAllSearchRequest($request->getQueryParams());
        $result = $this->search_service->getSearchResults($search_param, $member);

        run_event('api_search_after', $search_param);
        
        $data = new BoardAllSearchResponse($result);
        return api_response_json($response, $data);
        
    }

}