<?php

namespace API\v1\Model\Response\Write;

use API\v1\Model\Response\BaseResponse;
use API\v1\Traits\SchemaHelperTrait;

/**
 * @OA\Schema(
 *      type="object",
 *      description="추천/비추천 처리결과 모델",
 * )
 */
class GoodWriteResponse extends BaseResponse
{
    use SchemaHelperTrait;

    /**
     * 게시글 추천 수
     * @OA\Property
     */
    public int $good = 0;

    /**
     * 게시글 비추천 수
     * @OA\Property
     */
    public int $nogood = 0;

    public function __construct(array $data = [])
    {
        parent::__construct($data['message'] ?? '');
        $this->mapDataToProperties($this, $data);
    }
}