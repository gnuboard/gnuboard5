<?php

namespace API\v1\Controller;

use API\v1\Model\Response\Board\GetBoardResponse;
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
     *      @OA\Parameter(name="bo_table", in="path", description="게시판 코드", required=true, @OA\Schema(type="string")),
     *      @OA\Response(response="200", description="게시판 조회 성공", @OA\JsonContent(ref="#/components/schemas/GetBoardResponse")),
     *      @OA\Response(response="404", ref="#/components/responses/404"),
     *      @OA\Response(response="500", ref="#/components/responses/500"),
     * )
     */
    public function getBoard(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $board = $request->getAttribute('board');

        $response_data = new GetBoardResponse($board);

        return api_response_json($response, (array)$response_data);
    }

    /**
     * TODO: Query Parameter를 Request Model을 통해 일괄로 변경하는 방법을 찾아야함/
     * @OA\Get(
     *      path="/api/v1/boards/{bo_table}/writes",
     *      summary="게시판 글 목록 조회",
     *      tags={"게시판"},
     *      description="게시판 글 목록을 조회합니다.",
     *      @OA\Parameter(name="bo_table", in="path", description="게시판 코드", required=true, @OA\Schema(type="string")),
     *      @OA\Response(response="200", description="게시판 글 목록 조회 성공", @OA\JsonContent(ref="#/components/schemas/GetWritesResponse")),
     *      @OA\Response(response="404", ref="#/components/responses/404"),
     *      @OA\Response(response="500", ref="#/components/responses/500"),
     * )
     */
    public function getWrites(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $board = $request->getAttribute('board');

        $response_data = new GetWritesResponse($board);

        return api_response_json($response, (array)$response_data);
    }
}
