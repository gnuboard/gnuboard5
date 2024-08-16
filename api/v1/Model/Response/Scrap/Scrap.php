<?php

namespace API\v1\Model\Response\Scrap;

use API\v1\Traits\SchemaHelperTrait;

/**
 * @OA\Schema(
 *      type="object",
 *      description="스크랩 정보",
 * )
 */
class Scrap
{
    use SchemaHelperTrait;

    /**
     * 스크랩 ID
     * @OA\Property
     */
    public int $ms_id = 0;

    /**
     * 회원 ID
     * @OA\Property(example="test")
     */
    public string $mb_id = "";

    /**
     * 게시판 ID
     * @OA\Property(example="test")
     */
    public string $bo_table = "";

    /**
     * 게시글 ID
     * @OA\Property(example=0)
     */
    public int $wr_id = 0;

    /**
     * 스크랩 등록일
     * @OA\Property(example="2024-07-29T05:39:23.484Z")
     */
    public string $ms_datetime = '';
    
    /**
     * 게시글 제목
     * @OA\Property(example="게시글 제목")
     */
    public string $wr_subject = '';
    
    /**
     * 게시판 제목
     * @OA\Property(example="게시판 제목")
     */
    public string $bo_subject = '';

    public function __construct(array $data = [])
    {
        $this->mapDataToProperties($this, $data);
    }
}
