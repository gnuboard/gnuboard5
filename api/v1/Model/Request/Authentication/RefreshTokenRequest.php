<?php

namespace API\v1\Model\Request\Authentication;

use Exception;

/**
 * @OA\Schema(
 *      type="object",
 *      description="JWT 재발급 요청 모델",
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

    /**
     * @param array $data 요청 데이터
     * @throws Exception 유효성 검사 실패 시 예외 발생
     * @return void
     */
    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key) && $value) {
                $this->$key = $value;
            }
        }

        $this->validate();
    }

    /**
     * 유효성 검사
     */
    private function validate(): void
    {
        if (empty($this->refresh_token)) {
            throw new Exception('리프레시 토큰이 입력되지 않았습니다.');
        }
    }
}
