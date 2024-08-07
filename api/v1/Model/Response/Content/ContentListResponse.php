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
     *     description="전체 레코드 수"
     * )
     */
    public int $total_records = 0;
    
    /**
     * @OA\Property (
     *     description="전체 페이지 수"
     * )
     */
    public int $total_pages = 0;
    
    /**
     * @OA\Property (
     *     @OA\Items(ref="#/components/schemas/ContentResponse")
     * )
     */
    public array $contents = [];
    
    public function __construct($data)
    {
        $this->total_records = $data['total_records'];
        $this->total_pages = $data['total_pages'];
        $this->contents = $data['contents'];
    }
}