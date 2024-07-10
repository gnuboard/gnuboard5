<?php

namespace API\v1\Model\Request\Authentication;

/**
 * @OA\Schema(
 *      type="object",
 *      description="JWT 생성 요청 모델",
 *      required={"username", "password"},
 * )
 */
class GenerateTokenRequest
{
    /**
     * 사용자 이름
     * @var string
     * @OA\Property(example="")
     */
    public string $username = '';

    /**
     * 비밀번호
     * @var string
     * @OA\Property(example="")
     */
    public string $password = '';

    public function __construct($data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key) && $value) {
                $this->$key = $value;
            }
        }
    }
}
