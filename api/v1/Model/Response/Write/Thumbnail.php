<?php

namespace API\v1\Model\Response\Write;

/**
 * @OA\Schema(
 *     type="object",
 *     description="썸네일 정보",
 * )
 */
class Thumbnail
{
    /**
     * 이미지 경로
     * @OA\Property()
     */
    public string $src = "";

    /**
     * 대체 텍스트
     * @OA\Property()
     */
    public string $alt = "";

    /**
     * 이미지 없음 대체 텍스트
     * @OA\Property()
     */
    public string $noimg = "";
}
