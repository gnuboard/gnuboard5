<?php

namespace API\v1\Model\Request\Authentication;

use API\Exceptions\ValidateException;
use API\v1\Traits\SchemaHelperTrait;

/**
 * @OA\Schema(
 *      type="object",
 *      description="JWT 발급 요청 모델",
 *      required={"username", "password"},
 * )
 */
class GenerateTokenRequest
{
    use SchemaHelperTrait;

    /**
     * 사용자 이름
     * @OA\Property(example="")
     */
    public string $username = '';

    /**
     * 비밀번호
     * @OA\Property(example="")
     */
    public string $password = '';

    /**
     * @param array $data 요청 데이터
     * @throws ValidateException 유효성 검사 실패 시 예외 발생
     * @return void
     */
    public function __construct(array $data = [])
    {
        $this->mapDataToProperties($this, $data);

        $this->validate();
    }

    /**
     * 유효성 검사
     */
    private function validate(): void
    {
        if (empty($this->username) || empty($this->password)) {
            $this->throwException('아이디 또는 비밀번호가 입력되지 않았습니다.');
        }
    }
}
