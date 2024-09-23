<?php

namespace API\v1\Model\Request\Member;

use API\v1\Traits\SchemaHelperTrait;

/**
 * @OA\Schema(
 *     type="object",
 *     description="회원정보 갱신 모델",
 * )
 */
class UpdateMemberRequest
{
    use SchemaHelperTrait;

    /**
     * 비밀번호
     * @OA\Property(example="test1234")
     */
    public string $mb_password = '';

    /**
     * 비밀번호 확인
     * @OA\Property(example="test1234")
     */
    public string $mb_password_re = '';

    /**
     * 닉네임
     * @OA\Property(example="테스트")
     */
    public string $mb_nick = '';

    /**
     * 닉네임 변경일
     * @OA\Property(example="2021-01-01", readOnly=true)
     */
    public string $mb_nick_date = '';

    /**
     * 성별
     * @OA\Property(example=" 'w'  or 'm' ")
     */
    public string $mb_sex = '';

    /**
     * 이메일
     * @OA\Property(example="test@test.com")
     */
    public string $mb_email = '';

    /**
     * 홈페이지
     * @OA\Property(example="http://test.com")
     */
    public string $mb_homepage = '';

    /**
     * 우편번호
     * @OA\Property(example="12345")
     */
    public string $mb_zip = '';

    /**
     * 우편번호1
     * @OA\Property(example="123", readOnly=true)
     */
    public string $mb_zip1 = '';

    /**
     * 우편번호2
     * @OA\Property(example="45", readOnly=true)
     */
    public string $mb_zip2 = '';

    /**
     * 지번주소
     * @OA\Property(example="ㅇㅇ도 ㅇㅇ시 ㅇㅇ구")
     */
    public string $mb_addr_jibeon = '';

    /**
     * 기본 주소
     * @OA\Property(example="ㅇㅇ도 ㅇㅇ시 ㅇㅇ구 ㅇㅇ동 123-45")
     */
    public string $mb_addr1 = '';

    /**
     * 나머지 주소
     * @OA\Property(example="123호")
     */
    public string $mb_addr2 = '';

    /**
     * 기타 주소
     * @OA\Property(example="456호")
     */
    public string $mb_addr3 = '';

    /**
     * 전화번호
     * @OA\Property(example="02-1234-5678")
     */
    public string $mb_tel = '';

    /**
     * 휴대폰번호
     * @OA\Property(example="010-1234-5678")
     */
    public string $mb_hp = '';

    /**
     * 서명
     * @OA\Property(example="test")
     */
    public string $mb_signature = '';

    /**
     * 자기소개
     * @OA\Property(example="안녕하세요. 반갑습니다.")
     */
    public string $mb_profile = '';

    /**
     * 메일 수신여부
     * @OA\Property(example=1)
     */
    public int $mb_mailling = 0;

    /**
     * SMS 수신여부
     * @OA\Property(example=1)
     */
    public int $mb_sms = 0;

    /**
     * 메일 수신여부
     * @OA\Property(example="127.0.0.1", readOnly=true)
     */
    public string $mb_ip = '';

    /**
     * 정보공개여부
     * @OA\Property(example=1)
     */
    public int $mb_open = 0;

    /**
     * 여분필드1
     * @OA\Property(example="test")
     */
    public string $mb_1 = '';

    /**
     * 여분필드2
     * @OA\Property(example="test")
     */
    public string $mb_2 = '';

    /**
     * 여분필드3
     * @OA\Property(example="test")
     */
    public string $mb_3 = '';

    /**
     * 여분필드4
     * @OA\Property(example="test")
     */
    public string $mb_4 = '';

    /**
     * 여분필드5
     * @OA\Property(example="test")
     */
    public string $mb_5 = '';

    /**
     * 여분필드6
     * @OA\Property(example="test")
     */
    public string $mb_6 = '';

    /**
     * 여분필드7
     * @OA\Property(example="test")
     */
    public string $mb_7 = '';

    /**
     * 여분필드8
     * @OA\Property(example="test")
     */
    public string $mb_8 = '';

    /**
     * 여분필드9
     * @OA\Property(example="test")
     */
    public string $mb_9 = '';

    /**
     * 여분필드10
     * @OA\Property(example="test")
     */
    public string $mb_10 = '';

    public function __construct(array $config, array $member, array $data = [])
    {
        $this->mapDataToProperties($this, $data);

        $this->validatePassword();

        if ($member['mb_nick'] !== $this->mb_nick) {
            $this->validateNickName($config);
            $this->processNickDate($config, $member);
        }
        if ($member['mb_email'] !== $this->mb_email) {
            $this->validateEmail($config);
            $this->mb_email = get_email_address($this->mb_email);
        }

        $this->processPassword();
        $this->processZipCode();
    }

    protected function validatePassword()
    {
        if ($this->mb_password !== '') {
            if ($this->mb_password !== $this->mb_password_re) {
                $this->throwException('비밀번호가 일치하지 않습니다.');
            }
        }
    }

    protected function validateNickName(array $config)
    {
        if (empty(trim($this->mb_nick))) {
            $this->throwException('닉네임을 입력해주세요.');
        }
        if (!is_valid_utf8_string($this->mb_nick)) {
            $this->throwException('닉네임을 올바르게 입력해 주십시오.');
        }
        if (!is_valid_mb_nick($this->mb_nick)) {
            $this->throwException('닉네임은 공백없이 한글, 영문, 숫자만 입력 가능합니다.');
        }
        if (is_prohibited_word($this->mb_nick, $config)) {
            $this->throwException('이미 예약된 단어로 사용할 수 없는 닉네임 입니다.');
        }
    }

    protected function validateEmail(array $config)
    {
        if (empty(trim($this->mb_email))) {
            $this->throwException('이메일 주소를 입력해주세요.');
        }
        if (!is_valid_email($this->mb_email)) {
            $this->throwException('잘못된 형식의 이메일 주소입니다.');
        }
        if (is_prohibited_email_domain($this->mb_email, $config)) {
            $this->throwException("{$this->mb_email} 메일은 사용할 수 없습니다.");
        }
    }

    protected function processPassword()
    {
        if ($this->mb_password !== '') {
            $this->mb_password = get_encrypt_string($this->mb_password);
        } else {
            unset($this->mb_password);
        }
        unset($this->mb_password_re);
    }

    protected function processNickDate(array $config, array $member)
    {
        if ($member['mb_nick_date'] < date('Y-m-d', time() - ($config['cf_nick_modify'] * 86400))) {
            $this->mb_nick_date = date('Y-m-d');
        } else {
            unset($this->mb_nick, $this->mb_nick_date);
        }
    }

    protected function processZipCode()
    {
        $this->mb_zip1 = substr($this->mb_zip, 0, 3);
        $this->mb_zip2 = substr($this->mb_zip, 4, 3);
        unset($this->mb_zip);
    }
}
