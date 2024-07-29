<?php

namespace API\v1\Controller;

use API\Exceptions\HttpUnprocessableEntityException;
use API\Service\BoardService;
use API\Service\ScrapService;
use API\v1\Model\PageParameters;
use API\v1\Model\Response\Scrap\ScrapsResponse;
use API\v1\Model\Response\Scrap\Scrap;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ScrapController
{
    private ScrapService $scrap_service;

    public function __construct(
        ScrapService $scrap_service
    ) {
        $this->scrap_service = $scrap_service;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/member/scraps",
     *     summary="회원 스크랩 목록 조회",
     *     tags={"스크랩"},
     *     description="회원 스크랩 목록을 조회합니다.",
     *     @OA\Parameter(ref="#/components/parameters/page"),
     *     @OA\Parameter(ref="#/components/parameters/per_page"),
     *     @OA\Response(response="200", description="스크랩 목록 조회 성공", @OA\JsonContent(ref="#/components/schemas/ScrapsResponse")),
     *     @OA\Response(response="401", ref="#/components/responses/401"),
     *     @OA\Response(response="422", ref="#/components/responses/422"),
     *     @OA\Response(response="500", ref="#/components/responses/500")
     * )
     */
    public function getScraps(Request $request, Response $response): Response
    {
        $config = $request->getAttribute('config');
        $member = $request->getAttribute('member');

        try {
            // 검색 조건 및 페이징 처리
            $query_params = $request->getQueryParams();
            $page_params = new PageParameters($query_params, $config);

            $total_records = $this->scrap_service->fetchTotalScrapCount($member['mb_id']);
            $total_page = ceil($total_records / $page_params->per_page);

            // 스크랩 목록 조회
            $fetch_scraps = $this->scrap_service->fetchScraps($member['mb_id'], (array)$page_params);
            $scraps = array_map(function ($scrap) {
                // 게시판 정보 및 게시글 정보 조회
                // TODO: 추후에 조인이 가능하도록 수정해야할 필요가 있음.
                $board_service = new BoardService();
                $board = $board_service->fetchBoardByTable($scrap['bo_table']);
                if ($board) {
                    $board_service->setBoard($board);
                    $write = $board_service->fetchWriteById($scrap['wr_id']);
                    $scrap['bo_subject'] = $board['bo_subject'];
                    $scrap['wr_subject'] = $write['wr_subject'];
                }
                return new Scrap($scrap);
            }, $fetch_scraps);

            $response_data = new ScrapsResponse([
                "total_records" => $total_records,
                "total_pages" => $total_page,
                "scraps" => $scraps
            ]);

            return api_response_json($response, $response_data);
        } catch (Exception $e) {
            if ($e->getCode() === 422) {
                throw new HttpUnprocessableEntityException($request, $e->getMessage());
            } else {
                throw $e;
            }
        }
    }
}
