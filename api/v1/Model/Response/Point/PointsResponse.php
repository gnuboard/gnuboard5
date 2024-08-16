<?php

namespace API\v1\Model\Response\Point;

use API\v1\Traits\SchemaHelperTrait;

/**
 * @OA\Schema(
 *      type="object",
 *      description="포인트 목록 조회 응답",
 * )
 */
class PointsResponse
{
    use SchemaHelperTrait;

    /**
     * 총 레코드 수
     * @OA\Property(example=0)
     */
    public int $total_records = 0;

    /**
     * 총 페이지 수
     * @OA\Property(example=0)
     */
    public int $total_pages = 0;

    /**
     * 총 포인트 수
     * @OA\Property(example=0)
     */
    public int $total_points = 0;

    /**
     * 페이지 합계 포인트
     * @OA\Property(
     *     type="object",
     *     @OA\Property(property="negative", type="integer", example=0),
     *     @OA\Property(property="positive", type="integer", example=0)
     * )
     */
    public array $page_sum_points = [];

    /**
     * 포인트 목록
     * @OA\Property(
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/Point")
     * )
     * @var \API\v1\Model\Response\Point\Point[]
     */
    public array $points = [];

    public function __construct(array $data = [])
    {
        $this->page_sum_points = [
            'negative' => 0,
            'positive' => 0,
        ];

        $this->mapDataToProperties($this, $data);
    }

    // private function calculatePageSumPoints(): void
    // {
    //     $this->page_sum_points = array_reduce($this->points, function ($carry, $point) {
    //         if ($point->po_point < 0) {
    //             $carry['negative'] += $point->po_point;
    //         } else {
    //             $carry['positive'] += $point->po_point;
    //         }

    //         return $carry;
    //     }, $this->page_sum_points);
    // }

    // private function calculateTotalPoints(): void
    // {
    //     $this->total_points = array_reduce($this->points, fn ($carry, $point) => $carry + $point->po_point, 0);
    // }
}
