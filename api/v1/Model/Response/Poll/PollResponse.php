<?php

namespace API\v1\Model\Response\Poll;

/**
 * @OA\Schema(
 *      type="object",
 *      description="설문조사 > 단일 설문조사 응답"
 * )
 */
class PollResponse
{
    /**
     * 설문조사 번호
     * @var integer
     * @OA\Property(example=1)
     */
    public int $po_id;
    
    /**
     * 설문조사 제목
     * @var string
     * @OA\Property(example="설문조사 제목")
     */
    public string $po_subject;
    
    /**
     * 기타 의견
     * @var string
     * @OA\Property(example="기타 의견")
     */
    public string $po_etc;
    
    /**
     * 설문조사 가능 레벨
     * @var integer
     * @OA\Property(example=1)
     */
    public int $po_level;
    
    /**
     * 설문조사 부여 포인트
     * @var integer
     * @OA\Property(example=100)
     */
    public int $po_point;
    
    /**
     * 설문조사 시작일
     * @var string
     * @OA\Property(example="2024-07-01")
     */
    public string $po_date;
    
    /**
     * 설문조사 열림 여부
     * @var integer
     * @OA\Property(example=1)
     */
    public int $po_use;
}