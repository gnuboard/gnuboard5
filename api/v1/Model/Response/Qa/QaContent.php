<?php

namespace API\v1\Model\Response\Qa;

use API\v1\Traits\SchemaHelperTrait;

/**
 *
 * @OA\Schema(
 *   title="QaContent",
 *   description="QaContent",
 * )
 *
 */
class QaContent
{
    use SchemaHelperTrait;

    public function __construct($data)
    {
        $this->mapDataToProperties($this, $data);
    }

    public int $qa_id;
    public int $qa_parent;
    public int $qa_related;

    public string $mb_id;

    public string $qa_name;
    public int $qa_type;
    public string $qa_email;
    public string $qa_hp;
    public int $qa_email_recv;
    public int $qa_sms_recv;
    public string $qa_subject;
    public string $qa_content;
    public int $qa_status;
    public string $qa_file1;
    public string $qa_source1; 
    public string $qa_file2;
    public string $qa_source2;
    public string $qa_ip;

    public string $qa_datetime;
    public string $qa_1;
    public string $qa_2;
    public string $qa_3;
    public string $qa_4;
    public string $qa_5;

}