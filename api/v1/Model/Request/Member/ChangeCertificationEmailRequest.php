<?php

namespace API\v1\Model\Request\Member;

use API\v1\Traits\SchemaHelperTrait;

/**
 * @OA\Schema(
 *     type="object",
 *     description="인증 이메일 변경 요청 모델",
 * )
 */
class ChangeCertificationEmailRequest
{
    use SchemaHelperTrait;

    /**
     * 이메일
     * @OA\Property(example="test@test.com")
     */
    public string $email = '';

    /**
     * 비밀번호
     * @OA\Property(example="test1234")
     */
    public string $password = '';

    public function __construct(array $config, array $data = [])
    {
        $this->mapDataToProperties($this, $data);

        $this->validateEmail($config);
        $this->validatePassword();

        $this->email = get_email_address(trim($this->email));
    }

    protected function validateEmail(array $config)
    {
        if (empty(trim($this->email))) {
            $this->throwException('이메일 주소를 입력해주세요.');
        }
        if (!is_valid_email($this->email)) {
            $this->throwException('잘못된 형식의 이메일 주소입니다.');
        }
        if (is_prohibited_email_domain($this->email, $config)) {
            $this->throwException("{$this->email} 메일은 사용할 수 없습니다.");
        }
    }

    protected function validatePassword()
    {
        if (empty(trim($this->password))) {
            $this->throwException('비밀번호를 입력해주세요.');
        }
    }
}
