<?php

namespace API\v1\Model\Response\Group;

use API\v1\Traits\SchemaHelperTrait;

/**
 * @OA\Schema(
 *      type="object",
 *      description="게시판 그룹 목록 응답 모델",
 * )
 */
class BoardsResponse
{
    use SchemaHelperTrait;

    /**
     * 게시판 그룹 정보
     * @OA\Property(ref="#/components/schemas/Group")
     */
    public Group $group;

    /**
     * 게시판 그룹 목록
     * @OA\Property(
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/Board")
     * )
     * @var \API\v1\Model\Response\Board\Board[]
     */
    public array $boards = [];

    public function __construct(array $data = [])
    {
        $this->mapDataToProperties($this, $data);
    }
}
