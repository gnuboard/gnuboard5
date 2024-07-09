<?php

namespace API\v1\Model\Request\Member;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     type="object",
 *     title="ChangeCertificationEmailRequest",
 *     description="인증 이메일 변경 요청 모델",
 * )
 */
class ChangeCertificationEmailRequest
{
    /**
     * 이메일
     * @var string
     * @OA\Property(example="test@test.com")
     */
    public string $email = '';

    /**
     * 비밀번호
     * @var string
     * @OA\Property(example="test1234")
     */
    public string $password = '';


    public function __construct($data = []) {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
}