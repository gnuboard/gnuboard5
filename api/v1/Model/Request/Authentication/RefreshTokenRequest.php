<?php

namespace API\v1\Model\Request\Authentication;

/**
 * @OA\Schema(
 *      type="object",
 *      description="Access Token 재발급 요청 모델",
 *      required={"refresh_token"},
 * )
 */
class RefreshTokenRequest
{
    /**
     * 리프레시 토큰
     * @var string
     * @OA\Property(example="")
     */
    public string $refresh_token = '';

    public function __construct($data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key) && $value) {
                $this->$key = $value;
            }
        }
    }
}
