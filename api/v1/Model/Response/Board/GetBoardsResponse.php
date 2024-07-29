<?php

namespace API\v1\Model\Response\Board;

use API\v1\Traits\SchemaHelperTrait;

/**
 * @OA\Schema(
 *      type="object",
 *      description="게시판 목록 응답모델",
 * )
 */
class GetBoardsResponse
{
    use SchemaHelperTrait;

    /**
     * 그룹 정보
     * @OA\Property(
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/Group")
     * )
     */
    public array $group = [];

    /**
     * 게시판 목록
     * @OA\Property(
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/Board")
     * )
     */
    public array $boards = [];

    public function __construct(array $data = [])
    {
        $this->mapDataToProperties($this, $data);
    }
}
