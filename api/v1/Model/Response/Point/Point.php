<?php

namespace API\v1\Model\Response\Point;

use API\v1\Traits\SchemaHelperTrait;

/**
 * @OA\Schema(
 *      type="object",
 *      description="포인트 정보",
 * )
 */
class Point
{
    use SchemaHelperTrait;

    /**
     * 포인트 내용
     * @OA\Property(example="회원가입 포인트 지급")
     */
    public string $po_content = '';

    /**
     * 포인트
     * @OA\Property(example=0)
     */
    public int $po_point = 0;

    /**
     * 연관 테이블
     * @OA\Property(example="")
     */
    public string $po_rel_table = '';

    /**
     * 연관 테이블 ID
     * @OA\Property(example="")
     */
    public string $po_rel_id = '';

    /**
     * 연관 테이블 행동
     * @OA\Property(example="")
     */
    public string $po_rel_action = '';

    public function __construct(array $data = [])
    {
        $this->mapDataToProperties($this, $data);
    }
}
