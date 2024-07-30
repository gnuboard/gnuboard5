<?php

namespace API\v1\Model\Response\BoardNew;

use API\v1\Traits\SchemaHelperTrait;

/**
 * @OA\Schema(
 *      type="object",
 *      description="최신글 정보",
 * )
 */
class BoardNew
{
    use SchemaHelperTrait;

    /**
     * 글 번호
     * @OA\Property()
     */
    public int $num = 0;

    /**
     * 그룹 명
     * @OA\Property()
     */
    public string $gr_subject = '';

    /**
     * 게시판 명
     * @OA\Property()
     */
    public string $bo_subject = '';

    /**
     * 게시글 제목
     * @OA\Property()
     */
    public string $wr_subject = '';

    /**
     * 게시판 ID
     * @OA\Property()
     */
    public string $bo_table = '';

    /**
     * 게시글 ID
     * @OA\Property()
     */
    public int $wr_id = 0;

    /**
     * 게시글 부모 ID
     * @OA\Property()
     */
    public int $wr_parent = 0;

    /**
     * 댓글 여부
     * @OA\Property()
     */
    public int $wr_is_comment = 0;

    /**
     * 등록일
     * @OA\Property(example="2024-07-12T18:15:18")
     */
    public string $bn_datetime = '';

    /**
     * 회원 ID
     * @OA\Property()
     */
    public string $mb_id = '';

    /**
     * 회원 이름
     * @OA\Property()
     */
    public string $mb_name = '';

    public function __construct(array $data = [])
    {
        $this->mapDataToProperties($this, $data);
    }
}
