<?php

namespace API\v1\Controller;


use API\Service\VisitService;
use API\v1\Model\Response\Visit\VisitCountResponse;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class VisitController
{
    private VisitService $visit_service;

    public function __construct(VisitService $visitService)
    {
        $this->visit_service = $visitService;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return ResponseInterface
     * @OA\Get (
     *     path="/api/v1/visit",
     *     summary="방문자 수 조회",
     *     tags={"방문자"},
     *     description="방문자 수를 조회합니다.",
     *     @OA\Response(response="200",
     *     description="방문자 수 조회 성공",
     *     @OA\JsonContent(ref="#/components/schemas/VisitCountResponse")
     *    )
     * )
     */
    public function show(Request $request, Response $response)
    {
        $data = $this->visit_service->fetchCommonVisitCount();
        $response_data = new VisitCountResponse($data);
        return api_response_json($response, $response_data);
    }
}