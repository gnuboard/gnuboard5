<?php

namespace API\v1\Model\Response\Autosave;

/**
 * @OA\Schema(
 *     schema="AutosaveListResponse"
 * )
 */
class AutosaveListResponse
{
    /**
     * @var Autosave[]
     * @OA\Property(property="autosaves", type="array", @OA\Items(ref="#/components/schemas/Autosave"))
     */
    public $autosaves;

    /**
     * @OA\Property
     */
    public int $total_records;

    /**
     * @OA\Property
     */
    public int $total_pages;
}