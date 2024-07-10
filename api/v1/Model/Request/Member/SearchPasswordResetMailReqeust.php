<?php

namespace API\v1\Model\Request\Member;

/**
 * @OA\Schema(
 *     type="object",
 *     description="임시비밀번호 메일 발송 요청 모델",
 * )
 */
class SearchPasswordResetMailReqeust
{
    /**
     * 이메일
     * @var string
     * @OA\Property(example="test@test.com")
     */
    public string $mb_email = '';


    public function __construct($data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key) && $value) {
                $this->$key = $value;
            }
        }
    }
}
