<?php

namespace API\v1\Model\Response\Visit;

use API\v1\Traits\SchemaHelperTrait;

/**
 * @OA\Schema(
 *      type="object",
 *      description="방문자  > 방문자 통계"
 * )
 */
class VisitCountResponse
{
    use SchemaHelperTrait;

    /**
     * @OA\Property (example="50")
     */
    public int $today = 0;

    /**
     * @OA\Property (example="50")
     */
    public int $yesterday = 0;

    /**
     * @OA\Property (example="777")
     */
    public int $max = 0;

    /**
     * @OA\Property (example="10000")
     */
    public int $total = 0;


    public function __construct(array $data)
    {
        $this->mapDataToProperties($this, $data);
    }
}