<?php

namespace API\v1\Model\Response\Authentication;

use API\v1\Traits\SchemaHelperTrait;

/**
 * @OA\Schema(
 *      type="object",
 *      description="JWT 생성 응답 모델",
 * )
 */
class GenerateGuestTokenResponse
{
    use SchemaHelperTrait;

    /**
     * 액세스 토큰
     * @OA\Property(example="string")
     */
    public string $access_token = '';

    /**
     * 액세스 토큰 만료 시간
     * @OA\Property(format="date-time")
     */
    public string $access_token_expire_at = '';

    /**
     * 토큰 타입
     * @OA\Property(example="string")
     */
    public string $token_type = '';

    public function __construct(array $data)
    {
        $this->mapDataToProperties($this, $data);
    }
}
