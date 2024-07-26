<?php

namespace API\v1\Model\Response\Content;

/**
 * @OA\Schema(
 *     title="콘텐츠 리스트 응답"
 * )
 */
class ContentListResponse
{
    /**
     * @OA\Property (
     *     type="integer",
     *     description="전체 레코드 수"
     * )
     */
    public int $total_records;
    
    /**
     * @OA\Property (
     *     type="integer",
     *     description="전체 페이지 수"
     * )
     */
    public int $total_pages;
    
    /**
     * @OA\Property (
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/ContentResponse")
     * )
     */
    public array $contents;
    
    public function __construct($data)
    {
        $this->total_records = $data['total_records'];
        $this->total_pages = $data['total_pages'];
        $this->contents = $data['contents'];
    }
}