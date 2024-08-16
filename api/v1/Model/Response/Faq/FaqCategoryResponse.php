<?php

namespace API\v1\Model\Response\Faq;


use API\v1\Traits\SchemaHelperTrait;

/**
 * @OA\Schema(
 *     title="Form Model",
 *     description="FAQ 분류 목록 조회"
 * )
 */
class FaqCategoryResponse
{
    use SchemaHelperTrait;

    /**
     * @OA\Property(
     * example=0
     * )
     */
    public int $fm_id = 0;

    /**
     * @OA\Property(
     * example="title"
     * )
     */
    public string $fm_subject = '';


    /**
     * 순서
     * @OA\Property(
     * example=0
     * )
     */
    public int $fm_order = 0;


    /**
     * 상단 HTML
     * @OA\Property(
     * example="string"
     * )
     */
    public string $fm_head_html = '';


    /**
     * 하단 html
     * @OA\Property(
     * example="string"
     * )
     */
    public string $fm_tail_html = '';


    /**
     * 모바일용 상단 html
     * @OA\Property(
     * example="string"
     * )
     */
    public string $fm_mobile_head_html = '';


    /**
     * 모바일용 하단 html
     * @OA\Property(
     * example="string"
     * )
     */
    public string $fm_mobile_tail_html = '';
    

    public function __construct($data)
    {
        $this->mapDataToProperties($this, $data);
    }
}
