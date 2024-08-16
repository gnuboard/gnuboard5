<?php

namespace API\v1\Model\Response\Autosave;

use API\v1\Traits\SchemaHelperTrait;

/**
 * @OA\Schema(
 *     schema="Autosave"
 * )
 */
class Autosave
{

    use SchemaHelperTrait;

    /**
     * @OA\Property
     */
    public int $as_id = 0;

    /**
     * @OA\Property
     */
    public string $mb_id = '';

    /**
     * @OA\Property
     */
    public int $as_uid = 0;

    /**
     * @OA\Property
     */
    public string $as_subject = '';

    /**
     * @OA\Property
     */
    public string $as_content = '';

    /**
     * @OA\Property(format="date-time")
     */
    public string $as_datetime = '';

    public function __construct(array $data)
    {
        $this->mapDataToProperties($this, $data);
    }

}