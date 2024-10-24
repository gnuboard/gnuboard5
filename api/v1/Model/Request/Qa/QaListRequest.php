<?php

namespace API\v1\Model\Request\Qa;

use API\v1\Traits\SchemaHelperTrait;
/**
 * @OA\Schema(
 *      type="object",
 * )
 */
class QaListRequest
{

    use SchemaHelperTrait;

    /**
     * @OA\Property
     */
    public string $sca;

    /**
     * @OA\Property
     */
    public string $stx;

    /**
     * @OA\Property
     */
    public string $sfl;


    /**
     * @OA\Property
     */
    public int $page;

    /**
     * @OA\Property
     */
    public int $per_page;

    public function __construct($data)
    {
        $this->mapDataToProperties($this, $data);
    }
}