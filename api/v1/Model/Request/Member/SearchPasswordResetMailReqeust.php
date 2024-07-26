<?php

namespace API\v1\Model\Request\Member;

use API\v1\Traits\SchemaHelperTrait;

/**
 * @OA\Schema(
 *     type="object",
 *     description="임시비밀번호 메일 발송 요청 모델",
 * )
 */
class SearchPasswordResetMailReqeust
{
    use SchemaHelperTrait;

    /**
     * 이메일
     * @OA\Property(example="test@test.com")
     */
    public string $mb_email = '';

    public function __construct(array $data = [])
    {
        $this->mapDataToProperties($this, $data);

        $this->validateEmail();
        $this->mb_email = get_email_address($this->mb_email);
    }

    protected function validateEmail()
    {
        if (empty(trim($this->mb_email))) {
            $this->throwException("이메일 주소를 입력해주세요.");
        }
        if (!is_valid_email($this->mb_email)) {
            $this->throwException("잘못된 형식의 이메일 주소입니다.");
        }
    }
}
