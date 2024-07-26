<?php

namespace API\v1\Controller;

use API\Exceptions\HttpUnprocessableEntityException;
use API\Service\PointService;
use API\v1\Model\PageParameters;
use API\v1\Model\Response\Point\Point;
use API\v1\Model\Response\Point\PointsResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;

class PointController
{
    private PointService $point_service;

    public function __construct(PointService $point_service) {
        $this->point_service = $point_service;
    }

    /**
     * @OA\Get(
     *      path="/api/v1/member/points",
     *      summary="회원 포인트 내역 목록 조회",
     *      tags={"포인트"},
     *      description="JWT 토큰을 통해 인증된 회원의 포인트 내역을 조회합니다.",
     *      @OA\Parameter(ref="#/components/parameters/page"),
     *      @OA\Parameter(ref="#/components/parameters/per_page"),
     *      @OA\Response(response="200", description="포인트 목록 조회 성공", @OA\JsonContent(ref="#/components/schemas/PointsResponse")),
     *      @OA\Response(response="401", ref="#/components/responses/401"),
     *      @OA\Response(response="422", ref="#/components/responses/422"),
     *      @OA\Response(response="500", ref="#/components/responses/500"),
     * )
     */
    public function getPoints(Request $request, Response $response): Response
    {
        $config = $request->getAttribute('config');
        $member = $request->getAttribute('member');

        try {
            // 검색 조건 및 페이징 처리
            $query_params = $request->getQueryParams();
            $page_params = new PageParameters($query_params, $config);

            $total_records = $this->point_service->fetchTotalPointCount($member['mb_id']);
            $total_page = ceil($total_records / $page_params->per_page);

            // 포인트 목록 조회
            $fetch_points = $this->point_service->fetchPoints($member['mb_id'], (array)$page_params);
            $points = array_map(fn ($point) => new Point($point), $fetch_points);

            $response_data = new PointsResponse([
                "total_records" => $total_records,
                "total_pages" => $total_page,
                "total_points" => $member['mb_point'],
                "page_sum_points" => $this->point_service->calculate_sum($fetch_points),
                "points" => $points
            ]);

            return api_response_json($response, (array)$response_data);
        } catch (Exception $e) {
            if ($e->getCode() === 422) {
                throw new HttpUnprocessableEntityException($request, $e->getMessage());
            } else {
                throw $e;
            }
        }
    }
}