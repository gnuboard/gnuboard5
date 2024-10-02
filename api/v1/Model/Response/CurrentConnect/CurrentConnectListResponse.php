<?php

namespace API\v1\Model\Response\CurrentConnect;

use API\v1\Traits\SchemaHelperTrait;

/**
 * @OA\Schema(
 *     type="object"
 * )
 */
class CurrentConnectListResponse
{
    use SchemaHelperTrait;

    /**
     * @OA\Property(type="array", @OA\Items(ref="#/components/schemas/CurrentConnectResponse"))
     */
    public array $logins = [];

    /**
     * 전체
     * @OA\Property
     */
    public int $total_records = 0;

    /**
     * 전체 페이지
     * @OA\Property
     */
    public int $total_pages = 0;


    public function __construct($data)
    {
        $this->mapDataToProperties($this, $data);
    }
}