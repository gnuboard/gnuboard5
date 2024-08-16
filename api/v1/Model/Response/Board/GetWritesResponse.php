<?php

namespace API\v1\Model\Response\Board;

use API\v1\Traits\SchemaHelperTrait;

/**
 * @OA\Schema(
 *      type="object",
 *      description="게시판 정보 응답 모델",
 * )
 */
class GetWritesResponse
{
    use SchemaHelperTrait;

    /**
     * 총 레코드 수
     * @OA\Property
     */
    public int $total_records = 0;

    /**
     * 총 페이지 수
     * @OA\Property
     */
    public int $total_pages = 0;

    /**
     * 현재 페이지
     * @OA\Property
     */
    public int $current_page = 0;

    /**
     * 모바일 여부
     * @OA\Property
     */
    public bool $is_mobile = false;

    /**
     * 카테고리 목록
     * @OA\Property(type="array", @OA\Items(type="string"))
     */
    public array $categories = [];

    /**
     * 게시판 정보
     * @OA\Property(ref="#/components/schemas/Board")
     */
    public Board $board;

    /**
     * 공지 글 목록
     * @OA\Property(
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/Write")
     * )
     *
     * @var \API\v1\Model\Response\Board\Write[]
     */
    public array $notice_writes = [];

    /**
     * 게시글 목록
     * @OA\Property(
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/Write")
     * )
     *
     * @var \API\v1\Model\Response\Board\Write[]
     */
    public array $writes = [];

    /**
     * 이전검색 포인터
     * @OA\Property
     */
    public int $prev_spt = 0;

    /**
     * 다음검색 포인터
     * @OA\Property
     */
    public int $next_spt = 0;

    public function __construct(array $data = [])
    {
        $this->mapDataToProperties($this, $data);
    }
}
