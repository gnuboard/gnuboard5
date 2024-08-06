<?php

namespace API\v1\Model\Response\Visit;

/**
 * @OA\Schema(
 *      type="object",
 *      description="방문자  > 방문자 통계"
 * )
 */
class VisitCountResponse
{
    /**
     * @OA\Property (example="50")
     */
    public int $today;

    /**
     * @OA\Property (example="50")
     */
    public int $yesterday;

    /**
     * @OA\Property (example="777")
     */
    public int $max;

    /**
     * @OA\Property (example="10000")
     */
    public int $total;


    public function __construct(array $data)
    {
        $this->today = $data['today'];
        $this->yesterday = $data['yesterday'];
        $this->max = $data['max'];
        $this->total = $data['total'];
    }
}