<?php

namespace API\v1\Model\Request\Member;

use API\Service\MemberService;
use API\v1\Traits\SchemaHelperTrait;

/**
 * @OA\Schema(
 *      type="object",
 *      description="회원정보 모델",
 *      required={"mb_nick"},
 * )
 */
class CreateSocialMemberRequest
{
    use SchemaHelperTrait;

    public string $mb_password = '';

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
     * @OA\Property(example="")
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
     * @OA\Property(example="서명")
     */
    public string $mb_signature = '';

    /**
     * 자기소개
     * @OA\Property(example="안녕하세요. 반갑습니다.")
     */
    public string $mb_profile = '';

    /**
     * 가입일
     * @OA\Property(example="2024-01-01 00:00:00", readOnly=true)
     */
    public string $mb_datetime = '0000-00-00 00:00:00'; // default value

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
     * @OA\Property(example="")
     */
    public string $mb_1 = '';

    /**
     * 여분필드2
     * @OA\Property(example="")
     */
    public string $mb_2 = '';

    /**
     * 여분필드3
     * @OA\Property(example="")
     */
    public string $mb_3 = '';

    /**
     * 여분필드4
     * @OA\Property(example="")
     */
    public string $mb_4 = '';

    /**
     * 여분필드5
     * @OA\Property(example="")
     */
    public string $mb_5 = '';

    /**
     * 여분필드6
     * @OA\Property(example="")
     */
    public string $mb_6 = '';

    /**
     * 여분필드7
     * @OA\Property(example="")
     */
    public string $mb_7 = '';

    /**
     * 여분필드8
     * @OA\Property(example="")
     */
    public string $mb_8 = '';

    /**
     * 여분필드9
     * @OA\Property(example="")
     */
    public string $mb_9 = '';

    /**
     * 여분필드10
     * @OA\Property(example="")
     */
    public string $mb_10 = '';

    public function __construct(array $config, array $data = [])
    {
        $this->mapDataToProperties($this, $data);

        $this->validateNickName($config);
        $this->validateEmailNullable($config);
        if ($config['cf_req_hp'] && ($config['cf_use_hp'] || $config['cf_cert_hp'] || $config['cf_cert_simple'])) {
            if (!empty($this->mb_hp)) {
                $this->validateHp();
            }
        }

        $this->mb_password = $this->randomPasswordGenerator();
        $this->mb_email = get_email_address($this->mb_email);
        $this->mb_nick_date = date('Y-m-d');
        $this->mb_hp = hyphen_hp_number($this->mb_hp);
        $this->mb_ip = $_SERVER['REMOTE_ADDR'];
        $this->mb_level = $config['cf_register_level'] ?? 2; // 2 member 기본 레벨.
        $this->mb_datetime = date('Y-m-d H:i:s');

        // 소셜가입은 메일인증 사용안함
        $this->processZipCode();
    }

    protected function validateNameNullable()
    {
        if (empty(trim($this->mb_name))) {
            return '';
        }
        
        if (!is_valid_utf8_string($this->mb_name)) {
            $this->throwException('이름을 올바르게 입력해 주십시오.');
        }
    }

    /**
     * 소셜가입은 닉네임 선택조건.
     * @param array $config
     * @return string|void
     */
    protected function validateNickName(array $config)
    {
        if (empty(trim($this->mb_nick))) {
            return '';
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
        $member_service = new MemberService();
        // 모든 회원의 닉네임 중복 검사를 위해 mb_id 를 공백으로 구분.
        if($member_service->existsMemberByNick($this->mb_nick, ' ')) {
            $this->throwException('이미 사용중인 닉네임 입니다.');
        }
    }

    protected function validateEmailNullable(array $config)
    {
        if (empty(trim($this->mb_email))) {
            return '';
        }
        
        if (!is_valid_email($this->mb_email)) {
            $this->throwException('잘못된 형식의 이메일 주소입니다.');
        }
        if (is_prohibited_email_domain($this->mb_email, $config)) {
            $this->throwException("{$this->mb_email} 메일은 사용할 수 없습니다.");
        }
    }

    protected function randomPasswordGenerator()
    {
        try {
            return hash('sha256', bin2hex(random_bytes(32) . microtime(true)));
        } catch (\Exception $e) {
            return hash('sha256', bin2hex(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789') . microtime(true)));
        }
    }

    protected function validateHp()
    {
        if (!is_valid_hp($this->mb_hp)) {
            $this->throwException('휴대폰번호를 올바르게 입력해 주십시오.');
        }
    }

    protected function processZipCode()
    {
        $this->mb_zip1 = substr($this->mb_zip, 0, 3);
        $this->mb_zip2 = substr($this->mb_zip, 4, 3);
        unset($this->mb_zip);
    }
}