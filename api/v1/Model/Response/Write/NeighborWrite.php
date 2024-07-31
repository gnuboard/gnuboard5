<?php

namespace API\v1\Model\Response\Write;

use API\v1\Traits\SchemaHelperTrait;

/**
 * @OA\Schema(
 *      type="object",
 *      description="이웃 게시글 정보",
 * )
 */
class NeighborWrite
{
    use SchemaHelperTrait;
    /**
     * 게시글 제목
     * @OA\Property(example="제목")
     */
    public string $wr_subject = "";

    /**
     * 게시글 링크
     * @OA\Property(example="http://example.com/bbs/board.php?bo_table=free&wr_id=1")
     */
    public string $href = "";

    /**
     * 게시글 작성일
     * @OA\Property(example="2021-01-01 00:00:00")
     */
    public string $wr_datetime = "";

    public function __construct(string $bo_table, array $data = [])
    {
        $this->mapDataToProperties($this, $data);

        // TODO: include 제거로 인한 url 처리 오류 해결.
        // get_pretty_url($bo_table, $data['wr_id']);
        $this->href = "";
    }
}