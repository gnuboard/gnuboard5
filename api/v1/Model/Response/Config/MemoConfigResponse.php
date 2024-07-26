<?php

namespace API\v1\Model\Response\Config;

use API\v1\Traits\SchemaHelperTrait;

/**
 * @OA\Schema(
 *      type="object",
 *      description="기본환경설정 > 쪽지발송 포인트 조회 응답 모델",
 * )
 */
class MemoConfigResponse
{
    use SchemaHelperTrait;

    /**
     * 쪽지발송 1건당 소진 포인트
     * @OA\Property(example=500)
     */
    public int $cf_memo_send_point = 0;

    public function __construct($config = [])
    {
        $this->mapDataToProperties($this, $config);
    }
}
