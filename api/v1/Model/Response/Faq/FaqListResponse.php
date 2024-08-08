<?php

namespace API\v1\Model\Response\Faq;


use API\v1\Traits\SchemaHelperTrait;

/**
 * FAQ 특정 카테고리의 목록조회
 * @OA\Schema(
 *     title="Form Model",
 * )
 */
class FaqListResponse
{
    use SchemaHelperTrait;

    /**
     * @OA\Property(
     * example=1
     * )
     */
    public int $fa_id = 0;

    /**
     * Faq 카테고리 id
     * @OA\Property(example="1")
     */
    public int $fm_id = 0;


    /**
     * faq 제목
     * @OA\Property(
     * example="제목"
     * )
     */
    public string $fa_subject = '';

    /**
     * faq 내용
     * @OA\Property(
     * example="string"
     * )
     */
    public string $fa_content = '';

    /**
     * 순서
     * @OA\Property(
     * example=0
     * )
     */
    public int $fa_order = 0;
    
    public function __construct($data)
    {
        $this->mapDataToProperties($this, $data);
    }
}
