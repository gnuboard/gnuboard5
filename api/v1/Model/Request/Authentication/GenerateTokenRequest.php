<?php

namespace API\v1\Model\Request\Authentication;

use Exception;

/**
 * @OA\Schema(
 *      type="object",
 *      description="JWT 발급 요청 모델",
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
        if (empty($this->username) || empty($this->password)) {
            throw new Exception('아이디 또는 비밀번호가 입력되지 않았습니다.');
        }
    }
}
