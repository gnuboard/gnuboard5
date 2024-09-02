<?php

namespace API\v1\Controller;

use API\Service\FaqService;
use API\v1\Model\PageParameters;
use API\v1\Model\Response\Faq\FaqCategoryResponse;
use API\v1\Model\Response\Faq\FaqListResponse;
use Slim\Exception\HttpNotFoundException;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class FaqController
{
    private FaqService $faq_service;

    public function __construct(FaqService $faq_service)
    {
        $this->faq_service = $faq_service;
    }

    /**
     * FAQ 목록 조회
     *
     * @OA\Get(
     *     path="/api/v1/faqs",
     *     summary="FAQ 목록 조회",
     *     tags={"FAQ"},
     *     description="FAQ 목록을 조회합니다.",
     *     @OA\Parameter(
     *     name="page",
     *     in="query",
     *     description="페이지 번호",
     *     required=false,
     *     @OA\Schema(type="integer")
     *  ),
     *     @OA\Parameter(
     *     name="per_page",
     *     in="query",
     *     description="페이지당 조회 개수",
     *     required=false,
     *     @OA\Schema(type="integer")
     * ),
     *     @OA\Parameter(
     *     name="is_mobile",
     *     in="query",
     *     description="모바일 여부",
     *     required=false,
     *     @OA\Schema(type="boolean")
     * ),
     *     @OA\Parameter(
     *     name="use_html",
     *     in="query",
     *     description="HTML 사용 여부",
     *     required=false,
     *     @OA\Schema(type="boolean")
     * ),
     *     @OA\Response(
     *     response=200,
     *     description="FAQ 목록 조회 성공",
     *     @OA\JsonContent(ref="#/components/schemas/FaqCategoryResponse")
     *    ),
     *     @OA\Response(response=404, ref="#/components/responses/404")
     * )
     */

    public function index(Request $request, Response $response)
    {
        $params = $request->getQueryParams() ?? [];
        $config = $request->getAttribute('config');
        $use_html = $request->getQueryParams()['use_html'] ?? false;
        $request_data = new PageParameters($params, $config, 0, 0);

        $page = $request_data->page;
        $per_page = $request_data->per_page;

        if ($use_html) {
            $response_data = $this->faq_service->fetchFaqCategory($page, $per_page);
        } else {
            $response_data = $this->faq_service->fetchFaqCategoryNotHtml($page, $per_page);
        }

        if (!$response_data) {
            throw new HttpNotFoundException($request, 'FAQ가 없습니다.');
        }

        $response_result = [];
        foreach ($response_data as $row) {
            $response_result[] = new FaqCategoryResponse($row);
        }
        return api_response_json($response, $response_result);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/faqs/category/{ca_id}",
     *     summary="FAQ 분류 조회, FAQ 검색",
     *     tags={"FAQ"},
     *     description="FAQ 분류를 조회하거나 검색합니다.",
     *     @OA\Parameter(
     *     name="ca_id",
     *     in="path",
     *     description="FAQ 분류 ID",
     *     required=true,
     *     @OA\Schema(type="integer")
     *    ),
     *     @OA\Parameter(
     *     name="stx",
     *     in="query",
     *     description="검색어",
     *     required=false,
     *     @OA\Schema(type="string")
     *   ),
     *     @OA\Response(
     *     response=200,
     *     description="FAQ 분류 조회 성공",
     *     @OA\JsonContent(ref="#/components/schemas/FaqListResponse")
     *   ),
     *     @OA\Response(response=404, ref="#/components/responses/404")
     * )
     */
    public function show(Request $request, Response $response, $args)
    {
        $faq_ca_id = $args['ca_id'];
        $stx = $request->getQueryParams()['stx'] ?? '';
        $response_data = $this->faq_service->getFaq($faq_ca_id, $stx);
        if (!$response_data) {
            throw new HttpNotFoundException($request, 'FAQ가 없습니다.');
        }

        $response_result = new FaqListResponse($response_data);
        return api_response_json($response, $response_result);
    }
}