<?php

namespace API\v1\Model\Response\Autosave;

/**
 * @OA\Schema(
 *     schema="Autosave"
 * )
 */
class Autosave
{
    /**
     * @OA\Property
     */
    public int $as_id = 0;

    /**
     * @OA\Property
     */
    public string $mb_id;

    /**
     * @OA\Property
     */
    public int $as_uid;

    /**
     * @OA\Property
     */
    public string $as_subject;

    /**
     * @OA\Property
     */
    public string $as_content;

    /**
     * @OA\Property(format="date-time")
     */
    public string $as_datetime;
}