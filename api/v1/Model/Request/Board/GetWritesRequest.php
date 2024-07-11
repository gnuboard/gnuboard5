<?php

namespace API\v1\Model\Request\Board;

/**
 * @OA\Schema(
 *     type="object",
 *     description="게시판 글 목록 조회 요청 모델"
 * )
 */
class GetWritesRequest
{
    /**
     * 정렬필드
     * @var string
     * @OA\Property(example="")
     */
    public string $sst = '';
}