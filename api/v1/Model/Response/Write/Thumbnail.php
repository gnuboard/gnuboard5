<?php

namespace API\v1\Model\Response\Write;

use API\v1\Traits\SchemaHelperTrait;

/**
 * @OA\Schema(
 *     type="object",
 *     description="썸네일 정보",
 * )
 */
class Thumbnail
{
    use SchemaHelperTrait;

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

    public function __construct(array $data = [])
    {
        $this->mapDataToProperties($this, $data);
    }
}
