<?php

namespace API\v1\Model\Response\Config;

/**
 * @OA\Schema(
 *      type="object",
 *      description="기본환경설정 > 쪽지발송 포인트 조회 응답 모델",
 * )
 */
class MemoConfigResponse
{
    /**
     * 쪽지발송 1건당 소진 포인트
     * @var integer
     * @OA\Property(example=500)
     */
    public int $cf_memo_send_point = 0;

    public function __construct($config = [])
    {
        foreach ($config as $key => $value) {
            if (property_exists($this, $key) && $value) {
                $this->$key = $value;
            }
        }
    }
}
