<?php

namespace API\v1\Model\Response\Poll;

use API\v1\Traits\SchemaHelperTrait;

/**
 * @OA\Schema(
 *      type="object",
 *      description="설문조사 > 단일 설문조사 응답"
 * )
 */
class PollResponse
{
    use SchemaHelperTrait;

    /**
     * 설문조사 번호
     * @OA\Property(example=1)
     */
    public int $po_id = 0;

    /**
     * 설문조사 제목
     * @OA\Property(example="설문조사 제목")
     */
    public string $po_subject = '';

    /**
     * 기타 의견
     * @OA\Property(example="기타 의견")
     */
    public string $po_etc = '';

    /**
     * 설문조사 가능 레벨
     * @OA\Property(example=1)
     */
    public int $po_level = 0;

    /**
     * 설문조사 부여 포인트
     * @OA\Property(example=100)
     */
    public int $po_point = 0;

    /**
     * 설문조사 시작일
     * @OA\Property(example="2024-07-01")
     */
    public string $po_date = '';

    /**
     * 설문조사 열림 여부
     * @OA\Property(example=1)
     */
    public int $po_use = 0;

    // 응답 데이터 설정
    public function __construct(array $data)
    {
        $this->mapDataToProperties($this, $data);
    }
}