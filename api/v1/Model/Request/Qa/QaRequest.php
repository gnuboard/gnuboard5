<?php

namespace API\v1\Model\Request\Qa;

use API\v1\Traits\SchemaHelperTrait;

/**
 * create QaRequest
 *
 * @OA\Schema(
 *   title="QaRequest",
 *   description="QaRequest",
 * )
 *
 */
class QaRequest
{

    use SchemaHelperTrait;

    /**
     * @OA\Property
     */
    public int $qa_id = 0;

    /**
     * @OA\Property
     */
    public int $qa_parent = 0;

    /**
     * @OA\Property
     */
    public int $qa_related = 0;


    public string $qa_name = '';
    /**
     * @OA\Property
     */
    public string $qa_email = '';

    /**
     * @OA\Property
     */
    public string $qa_hp = '';

    /**
     * @OA\Property
     */
    public int $qa_type = 0;

    /**
     * @OA\Property
     */
    public string $qa_category = '';

    /**
     * @OA\Property
     */
    public int $qa_email_recv = 0;

    /**
     * @OA\Property
     */
    public int $qa_sms_recv = 0;

    /**
     * @OA\Property
     */
    public int $qa_html = 0;


    /**
     * @OA\Property
     */
    public string $qa_subject = '';

    /**
     * @OA\Property
     */
    public string $qa_content = '';

    /**
     * @OA\Property
     */
    public string $qa_ip = '';

    /**
     * @OA\Property
     */
    public string $qa_datetime = '';

    /**
     * @OA\Property
     */
    public string $qa_1 = '';

    /**
     * @OA\Property
     */
    public string $qa_2 = '';

    /**
     * @OA\Property
     */
    public string $qa_3 = '';

    /**
     * @OA\Property
     */
    public string $qa_4 = '';

    /**
     * @OA\Property
     */
    public string $qa_5 = '';

    public function __construct(array $data)
    {
        $this->mapDataToProperties($this, $data);
    }

}