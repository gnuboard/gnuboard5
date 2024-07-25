<?php

namespace API\v1\Model\Request\Member;

use Exception;

/**
 * @OA\Schema(
 *      type="object",
 *      description="회원정보 모델",
 *      required={"mb_id", "mb_password", "mb_password_re", "mb_nick", "mb_name", "mb_email"},
 * )
 */
class CreateMemberRequest
{
    /**
     * 회원 아이디
     * @OA\Property(example="test")
     */
    public string $mb_id = '';

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
    public string $mb_nick_date;

    /**
     * 이름
     * @OA\Property(example="홍길동")
     */
    public string $mb_name = '';

    /**
     * 성별
     * @OA\Property(example="m")
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
     * @OA\Property(example="서울시 강남구")
     */
    public string $mb_addr_jibeon = '';

    /**
     * 기본 주소
     * @OA\Property(example="서울시 강남구 역삼동 123-45")
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
     * 추천인
     * @OA\Property(example="test")
     */
    public string $mb_recommend = '';

    /**
     * 가입일
     * @OA\Property(example="2021-01-01 00:00:00", readOnly=true)
     */
    public string $mb_datetime = '0000-00-00 00:00:00';

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
     * 회원가입 IP
     * @OA\Property(example="127.0.0.1", readOnly=true)
     */
    public string $mb_ip = '';

    /**
     * 회원 레벨
     * @OA\Property(example=2, readOnly=true)
     */
    public int $mb_level = 0;

    /**
     * 정보공개여부
     * @OA\Property(example=1)
     */
    public int $mb_open = 0;

    /**
     * 메일인증 여부
     * @OA\Property(example="2021-01-01 00:00:00", readOnly=true)
     */
    public string $mb_email_certify = '0000-00-00 00:00:00';

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

    public function __construct(array $config, array $data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }

        $this->validateId($config);
        $this->validatePassword();
        $this->validateName();
        $this->validateNickName($config);
        $this->validateEmail($config);
        if ($config['cf_use_recommend']) {
            $this->validateRecommend();
        }
        if ($config['cf_req_hp'] && ($config['cf_use_hp'] || $config['cf_cert_hp'] || $config['cf_cert_simple'])) {
            $this->validateHp();
        }

        $this->mb_id = strtolower($this->mb_id);
        $this->mb_password = get_encrypt_string($this->mb_password);
        $this->mb_nick_date = date('Y-m-d');
        $this->mb_email = get_email_address($this->mb_email);
        $this->mb_recommend = strtolower($this->mb_recommend);
        $this->mb_hp = hyphen_hp_number($this->mb_hp);
        $this->mb_zip1 = substr($this->mb_zip, 0, 3);
        $this->mb_zip2 = substr($this->mb_zip, 4, 3);
        $this->mb_ip = $_SERVER['REMOTE_ADDR'];
        $this->mb_level = $config['cf_register_level'] ?? 1;
        $this->mb_datetime = date('Y-m-d H:i:s');
        if (!$config['cf_use_email_certify']) {
            $this->mb_email_certify = date('Y-m-d H:i:s');
        }

        unset($this->mb_password_re);
        unset($this->mb_zip);
    }

    protected function validateId(array $config)
    {
        if (empty(trim($this->mb_id))) {
            $this->throwException("아이디를 입력해주세요.");
        }
        if (!is_valid_mb_id($this->mb_id)) {
            $this->throwException("회원아이디는 영문자, 숫자, _ 만 입력하세요.");
        }
        $min_length = 3;
        if (!has_min_length($this->mb_id, $min_length)) {
            $this->throwException("회원아이디는 최소 {$min_length}글자 이상 입력하세요.");
        }
        if (is_prohibited_word($this->mb_id, $config)) {
            $this->throwException("이미 예약된 단어로 사용할 수 없는 아이디 입니다.");
        }
    }

    protected function validatePassword()
    {
        if (empty(trim($this->mb_password))) {
            $this->throwException("비밀번호를 입력해주세요.");
        }
        if ($this->mb_password !== $this->mb_password_re) {
            $this->throwException("비밀번호가 일치하지 않습니다.");
        }
    }

    protected function validateName()
    {
        if (empty(trim($this->mb_name))) {
            $this->throwException("이름을 입력해주세요.");
        }
        if (!is_valid_utf8_string($this->mb_name)) {
            $this->throwException("이름을 올바르게 입력해 주십시오.");
        }
    }

    protected function validateNickName(array $config)
    {
        if (empty(trim($this->mb_nick))) {
            $this->throwException("닉네임을 입력해주세요.");
        }
        if (!is_valid_utf8_string($this->mb_nick)) {
            $this->throwException("닉네임을 올바르게 입력해 주십시오.");
        }
        if (!is_valid_mb_nick($this->mb_nick)) {
            $this->throwException("닉네임은 공백없이 한글, 영문, 숫자만 입력 가능합니다.");
        }
        if (is_prohibited_word($this->mb_nick, $config)) {
            $this->throwException("이미 예약된 단어로 사용할 수 없는 닉네임 입니다.");
        }
    }

    protected function validateEmail(array $config)
    {
        if (empty(trim($this->mb_email))) {
            $this->throwException("이메일 주소를 입력해주세요.");
        }
        if (!is_valid_email($this->mb_email)) {
            $this->throwException("잘못된 형식의 이메일 주소입니다.");
        }
        if (is_prohibited_email_domain($this->mb_email, $config)) {
            $this->throwException("{$this->mb_email} 메일은 사용할 수 없습니다.");
        }
    }

    protected function validateRecommend()
    {
        if (strtolower($this->mb_id) == strtolower($this->mb_recommend)) {
            $this->throwException("본인을 추천인으로 등록할 수 없습니다.");
        }
    }

    protected function validateHp()
    {
        if (!is_valid_hp($this->mb_hp)) {
            $this->throwException("휴대폰번호를 올바르게 입력해 주십시오.");
        }
    }

    private function throwException($message)
    {
        throw new Exception($message, 422);
    }
}