<?php

namespace API\v1\Model\Response\Write;

use API\v1\Traits\SchemaHelperTrait;

/**
 * @OA\Schema(
 *     type="object",
 *     description="게시판 첨부파일 정보",
 * )
 */
class ImageFile
{
    use SchemaHelperTrait;

    /**
     * 원본 이미지 주소
     * @OA\Property
     */
    public string $original = '';


    /**
     * 썸네일 이미지 주소
     * @OA\Property
     */
    public string $thumbnail = '';


    public function __construct(array $data = [])
    {
        $this->mapDataToProperties($this, $data);
    }
}
