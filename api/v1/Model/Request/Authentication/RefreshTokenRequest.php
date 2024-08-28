<?php

namespace API\v1\Model\Request\Authentication;

use API\Exceptions\ValidateException;
use API\v1\Traits\SchemaHelperTrait;

/**
 * @OA\Schema(
 *      type="object",
 *      description="JWT 재발급 요청 모델",
 *      required={"refresh_token"},
 * )
 */
class RefreshTokenRequest
{
    use SchemaHelperTrait;

    /**
     * 리프레시 토큰
     * @OA\Property(example="")
     */
    public string $refresh_token = '';

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
        if (empty($this->refresh_token)) {
            $this->throwException('리프레시 토큰이 입력되지 않았습니다.');
        }
    }
}
