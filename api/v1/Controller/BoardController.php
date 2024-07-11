<?php

namespace API\v1\Controller;

use API\Service\BoardService;
use API\v1\Model\Response\Board\Board;
use API\v1\Model\Response\Board\GetWritesResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;


class BoardController
{
    /**
     * @OA\Get(
     *      path="/api/v1/boards/{bo_table}",
     *      summary="게시판 조회",
     *      tags={"게시판"},
     *      description="게시판 정보 1건을 조회합니다.",
     *      @OA\PathParameter(name="bo_table", description="게시판 코드", @OA\Schema(type="string")),
     *      @OA\Response(response="200", description="게시판 조회 성공", @OA\JsonContent(ref="#/components/schemas/Board")),
     *      @OA\Response(response="404", ref="#/components/responses/404"),
     *      @OA\Response(response="500", ref="#/components/responses/500"),
     * )
     */
    public function getBoard(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $board = $request->getAttribute('board');

        $response_data = new Board($board);

        return api_response_json($response, (array)$response_data);
    }
    /**
     * @OA\Get(
     *      path="/api/v1/boards/{bo_table}/writes",
     *      summary="게시판 글 목록 조회",
     *      tags={"게시판"},
     *      description="게시판 글 목록을 조회합니다.",
     *      @OA\PathParameter(name="bo_table", description="게시판 코드", @OA\Schema(type="string")),
     *      @OA\Parameter(ref="#/components/parameters/sst"),
     *      @OA\Parameter(ref="#/components/parameters/sod"),
     *      @OA\Parameter(ref="#/components/parameters/sfl"),
     *      @OA\Parameter(ref="#/components/parameters/stx"),
     *      @OA\Parameter(ref="#/components/parameters/sca"),
     *      @OA\Parameter(ref="#/components/parameters/page"),
     *      @OA\Parameter(ref="#/components/parameters/per_page"),
     *      @OA\Response(response="200", description="게시판 글 목록 조회 성공", @OA\JsonContent(ref="#/components/schemas/GetWritesResponse")),
     *      @OA\Response(response="400", ref="#/components/responses/400"),
     *      @OA\Response(response="403", ref="#/components/responses/403"),
     *      @OA\Response(response="404", ref="#/components/responses/404"),
     *      @OA\Response(response="422", ref="#/components/responses/422"),
     *      @OA\Response(response="500", ref="#/components/responses/500"),
     * )
     */
    public function getWrites(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $board = $request->getAttribute('board');
        $board_service = new BoardService($board);

        $query_params = $request->getQueryParams();

        $total_records = $board_service->fetchTotalWritesRecords($query_params);

        $response_data = new GetWritesResponse([
            "total_records" => $total_records,
            "board" => new Board($board)
        ]);
        return api_response_json($response, (array)$response_data);
    }
}
