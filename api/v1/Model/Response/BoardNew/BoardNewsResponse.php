<?php

namespace API\v1\Model\Response\BoardNew;

use API\v1\Traits\SchemaHelperTrait;

/**
 * @OA\Schema(
 *      type="object",
 *      description="최신 게시글 목록 응답 모델",
 * )
 */
class BoardNewsResponse
{
    use SchemaHelperTrait;

    /**
     * 총 레코드 수
     * @OA\Property(example=0)
     */
    public int $total_records = 0;

    /**
     * 총 페이지 수
     * @OA\Property(example=0)
     */
    public int $total_pages = 0;

    /**
     * 현재 페이지
     * @OA\Property
     */
    public int $current_page = 0;

    /**
     * 최신글 목록
     * @OA\Property(
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/BoardNew")
     * )
     * @var \API\v1\Model\Response\BoardNew\BoardNew[]
     */
    public array $board_news = [];

    public function __construct(array $data = [])
    {
        $this->mapDataToProperties($this, $data);
    }
}
