<?php

namespace API\v1\Model\Response\Poll;

/**
 * @OA\Schema(
 *      type="object",
 *      description="설문조사 > 설문조사 응답 모델"
 * )
 */
class GetItemResponse
{
    /**
     * 설문조사
     * @OA\Property(property="poll", type="object", ref="#/components/schemas/PollResponse")
     */
    public array $poll;
    
    /**
     * 총 투표수
     * @OA\Property(property="total_vote", type="integer", example=1)
     */
    public int $total_vote;
    
    /**
     * 투표한 항목
     * @OA\Property(property="items", type="array", @OA\Items(type="object"))
     */
    public array $items;

    /**
     * 기타 의견
     * @OA\Property(property="etcs", type="array", @OA\Items(type="object"))
     */
    public array $etcs;
    
    /**
     * 다른 설문조사
     * @OA\Property(property="other_polls", type="array", @OA\Items(type="object"))
     */
    public array $other_polls;
}