<?php

namespace API\v1\Model\Response\Content;

/**
 * @OA\Schema(
 *     title="콘텐츠 조회"
 * )
 */
class ContentResponse
{

    /**
     * @OA\Property (
     *     description="콘텐츠 ID"
     * )
     */
    public string $co_id = '';

    /**
     * @OA\Property (
     *     description="HTML 사용 여부"
     * )
     */
    public bool $co_html = false;

    /**
     * @OA\Property (
     *     description="콘텐츠 제목"
     * )
     */
    public string $co_subject = '';

    /**
     * @OA\Property (
     *     description="콘텐츠 내용"
     * )
     */
    public string $co_content = '';

    /**
     * @OA\Property (
     *     description="SEO 제목"
     * )
     */
    public string $co_seo_title = '';

    /**
     * @OA\Property (
     *     description="모바일 콘텐츠"
     * )
     */
    public string $co_mobile_content = '';

    /**
     * @OA\Property (
     *     description="조회수"
     * )
     */
    public int $co_hit = 0;

    /**
     * @OA\Property (
     *     description="헤드 이미지"
     * )
     */
    public string $co_image_head = '';

    /**
     * @OA\Property (
     *     description="테일 이미지"
     * )
     */
    public string $co_image_tail = '';

    public function __construct($data)
    {
        $this->co_id = $data['co_id'] ?? '';
        $this->co_html = (bool)($data['co_html'] ?? false);
        $this->co_subject = $data['co_subject'] ?? '';
        $this->co_content = $data['co_content'] ?? '';
        $this->co_seo_title = $data['co_seo_title'] ?? '';
        $this->co_mobile_content = $data['co_mobile_content'] ?? '';
        $this->co_hit = $data['co_hit'] ?? 0;
        $this->co_image_head = $data['co_image_head'] ?? '';
        $this->co_image_tail = $data['co_image_tail'] ?? '';
    }
}