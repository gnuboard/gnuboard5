<?php

namespace API\v1\Model\Response\Qa;

use API\v1\Traits\SchemaHelperTrait;

/**
 *
 * @OA\Schema(
 *   title="QaListResponse",
 *   description="QaListResponse",
 * )
 *
 */
class QaListResponse
{
    use SchemaHelperTrait;

    public function __construct($data)
    {
        $this->mapDataToProperties($this, $data);
    }

    /**
     * @OA\Property
     */
    public int $qa_id;

    /**
     * @OA\Property
     */
    public int $qa_parent;

    /**
     * @OA\Property
     */
    public int $qa_related;

    /**
     * @OA\Property
     */
    public string $mb_id;

    /**
     * @OA\Property
     */
    public string $qa_name;

    /**
     * @OA\Property
     */
    public string $qa_email;

    /**
     * @OA\Property
     */
    public string $qa_hp;

    /**
     * @OA\Property
     */
    public int $qa_type;


    /**
     * @OA\Property
     */
    public string $qa_subject;

    /**
     * @OA\Property
     */
    public string $qa_content;

    /**
     * @OA\Property
     */
    public string $qa_file1;

    /**
     * @OA\Property
     */
    public string $qa_source1;

    /**
     * @OA\Property
     */
    public string $qa_file2;

    /**
     * @OA\Property
     */
    public string $qa_source2;

    /**
     * @OA\Property
     */
    public string $qa_ip;

    /**
     * @OA\Property
     */
    public string $qa_datetime;

    /**
     * @OA\Property
     */
    public int $qa_status;

    /**
     * @OA\Property
     */
    public string $qa_1;

    /**
     * @OA\Property
     */
    public string $qa_2;

    /**
     * @OA\Property
     */
    public string $qa_3;

    /**
     * @OA\Property
     */
    public string $qa_4;

    /**
     * @OA\Property
     */
    public string $qa_5;

}