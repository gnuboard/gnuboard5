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
     * @var int
     * @OA\Property(property="total_records", type="integer")
     */
    public $total_records;

    /**
     * @var int
     * @OA\Property(property="total_pages", type="integer")
     */
    public $total_pages;
}