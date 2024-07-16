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
     * @var int
     * @OA\Property(property="as_id", type="integer")
     */
    public $as_id;

    /**
     * @var string
     * @OA\Property(property="mb_id", type="string")
     */
    public $mb_id;

    /**
     * @var int
     * @OA\Property(property="as_uid", type="integer")
     */
    public $as_uid;

    /**
     * @var string
     * @OA\Property(property="as_subject", type="string"),
     */
    public $as_subject;

    /**
     * @var string
     * @OA\Property(property="as_content", type="string")
     */
    public $as_content;

    /**
     * @var string
     * @OA\Property(property="as_datetime", type="string", format="date-time")
     */
    public $as_datetime;
}