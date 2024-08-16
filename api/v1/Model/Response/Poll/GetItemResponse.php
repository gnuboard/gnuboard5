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
     * @OA\Property(type="object", ref="#/components/schemas/PollResponse")
     */
    public array $poll = [];
    
    /**
     * 총 투표수
     * @OA\Property(type="integer", example=1)
     */
    public int $total_vote = 0;
    
    /**
     * 투표한 항목
     * @OA\Property(type="array", @OA\Items(type="object"))
     */
    public array $items = [];

    /**
     * 기타 의견
     * @OA\Property(type="array", @OA\Items(type="object"))
     */
    public array $etcs = [];
    
    /**
     * 다른 설문조사
     * @OA\Property(type="array", @OA\Items(type="object"))
     */
    public array $other_polls = [];
    
    public function __construct(array $data)
    {
        $this->poll = $data['poll'];
        $this->total_vote = $data['total_vote'];
        $this->items = $data['items'];
        $this->etcs = $data['etcs'];
        $this->other_polls = $data['other_polls'];
    }
}