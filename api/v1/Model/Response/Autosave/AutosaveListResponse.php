<?php

namespace API\v1\Model\Response\Autosave;

use API\v1\Traits\SchemaHelperTrait;

/**
 * @OA\Schema(
 *     schema="AutosaveListResponse"
 * )
 */
class AutosaveListResponse
{
    use SchemaHelperTrait;

    /**
     * @var Autosave[]
     * @OA\Property(type="array", @OA\Items(ref="#/components/schemas/Autosave"))
     */
    public $autosaves = [];

    /**
     * @OA\Property
     */
    public int $total_records = 0;

    /**
     * @OA\Property
     */
    public int $total_pages = 0;

    public function __construct(array $data)
    {
        $this->mapDataToProperties($this, $data);
    }
}