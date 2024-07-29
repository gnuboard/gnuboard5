<?php

namespace API\v1\Model\Response\Scrap;

use API\v1\Traits\SchemaHelperTrait;

/**
 * @OA\Schema(
 *      type="object",
 *      description="스크랩 목록 응답 모델",
 * )
 */
class ScrapsResponse
{
    use SchemaHelperTrait;

    /**
     * 총 레코드 수
     * @OA\Property()
     */
    public int $total_records = 0;

    /**
     * 총 페이지 수
     * @OA\Property()
     */
    public int $total_pages = 0;

    /**
     * 게시글 목록
     * @OA\Property(
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/Scrap")
     * )
     */
    public array $scraps = [];

    public function __construct(array $data = [])
    {
        $this->mapDataToProperties($this, $data);
    }
}
