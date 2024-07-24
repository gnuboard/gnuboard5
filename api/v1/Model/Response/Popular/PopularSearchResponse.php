<?php

namespace API\v1\Model\Response\Popular;

/**
 * @OA\Schema(
 *      type="object",
 *      description="인기 검색어 응답",
 * )
 */
class PopularSearchResponse
{
    /**
     * @OA\Property (
     *     type="string",
     *     description="인기 검색어"
     * )
     */
    public string $pp_word;

    /**
     * @OA\Property (
     *     type="integer",
     *     description="검색어 갯수"
     * )
     */
    public int $count;

    public function __construct($data)
    {
        $this->pp_word = $data['pp_word'] ?? '';
        $this->count = $data['count'] ?? 0;
    }
}