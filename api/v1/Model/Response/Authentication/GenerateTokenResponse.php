<?php

namespace API\v1\Model\Response\Authentication;

/**
 * @OA\Schema(
 *      type="object",
 *      description="JWT 생성 응답 모델",
 * )
 */
class GenerateTokenResponse
{
    /**
     * 액세스 토큰
     * @var string
     * @OA\Property(example="string")
     */
    public string $access_token = '';

    /**
     * 액세스 토큰 만료 시간
     * @var string
     * @OA\Property(format="date-time")
     */
    public string $access_token_expire_at = '';

    /**
     * 리프레시 토큰
     * @var string
     * @OA\Property(example="string")
     */
    public string $refresh_token = '';

    /**
     * 리프레시 토큰 만료 시간
     * @var string
     * @OA\Property(format="date-time")
     */
    public string $refresh_token_expire_at = '';

    /**
     * 토큰 타입
     * @var string
     * @OA\Property(example="string")
     */
    public string $token_type = '';

    public function __construct($data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key) && $value) {
                $this->$key = $value;
            }
        }
    }
}
