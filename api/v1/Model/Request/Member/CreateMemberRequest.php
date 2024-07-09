<?php

namespace API\v1\Model\Request\Member;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Member",
 *     description="회원정보 모델",
 * )
 */
class CreateMemberRequest
{
    /**
     * 회원 아이디
     * @var string
     * @OA\Property(example="test")
     */
    public $mb_id = '';

    /**
     * 비밀번호
     * @var string
     * @OA\Property(example="test1234")
     */
    public $mb_password = '';

    /**
     * 비밀번호 확인
     * @var string
     * @OA\Property(example="test1234")
     */
    public $mb_password_re = '';

    /**
     * 닉네임
     * @var string
     * @OA\Property(example="테스트")
     */
    public $mb_nick = '';

    /**
     * 닉네임 변경일
     * @var datetime
     * @OA\Property(example="2021-01-01", readOnly=true)
     */
    public $mb_nick_date;

    /**
     * 이름
     * @var string
     * @OA\Property(example="홍길동")
     */
    public $mb_name = '';

    /**
     * 성별
     * @var string
     * @OA\Property(example="m")
     */
    public $mb_sex = '';

    /**
     * 생년월일
     * @var string
     * @OA\Property(example="1990-01-01")
     */
    public $mb_email = '';

    /**
     * 홈페이지
     * @var string
     * @OA\Property(example="http://test.com")
     */
    public $mb_homepage = '';

    /**
     * 우편번호
     * @var string
     * @OA\Property(example="12345")
     */
    public $mb_zip = '';

    /**
     * 우편번호1
     * @var string
     * @OA\Property(example="123", readOnly=true)
     */
    public $mb_zip1 = '';

    /**
     * 우편번호2
     * @var string
     * @OA\Property(example="45", readOnly=true)
     */
    public $mb_zip2 = '';

    /**
     * 지번주소
     * @var string
     * @OA\Property(example="서울시 강남구")
     */
    public $mb_addr_jibeon = '';

    /**
     * 기본 주소
     * @var string
     * @OA\Property(example="서울시 강남구 역삼동 123-45")
     */
    public $mb_addr1 = '';

    /**
     * 나머지 주소
     * @var string
     * @OA\Property(example="123호")
     */
    public $mb_addr2 = '';

    /**
     * 기타 주소
     * @var string
     * @OA\Property(example="456호")
     */
    public $mb_addr3 = '';

    /**
     * 전화번호
     * @var string
     * @OA\Property(example="02-1234-5678")
     */
    public $mb_tel = '';

    /**
     * 휴대폰번호
     * @var string
     * @OA\Property(example="010-1234-5678")
     */
    public $mb_hp = '';

    /**
     * 서명
     * @var string
     * @OA\Property(example="test")
     */
    public $mb_signature = '';

    /**
     * 자기소개
     * @var string
     * @OA\Property(example="안녕하세요. 반갑습니다.")
     */
    public $mb_profile = '';

    /**
     * 추천인
     * @var string
     * @OA\Property(example="test")
     */
    public $mb_recommend = '';


    /**
     * 가입일
     * @var datetime
     * @OA\Property(example="2021-01-01 00:00:00", readOnly=true)
     */
    public $mb_datetime = '0000-00-00 00:00:00';

    /**
     * 메일 수신여부
     * @var int
     * @OA\Property(example=1)
     */
    public $mb_mailling = 0;

    /**
     * SMS 수신여부
     * @var int
     * @OA\Property(example=1)
     */
    public $mb_sms = 0;

    /**
     * 회원가입 IP
     * @var string
     * @OA\Property(example="127.0.0.1", readOnly=true)
     */
    public $mb_ip = '';

    /**
     * 회원 레벨
     * @var int
     * @OA\Property(example=2, readOnly=true)
     */
    public $mb_level = 0;

    /**
     * 정보공개여부
     * @var int
     * @OA\Property(example=1)
     */
    public $mb_open = 0;

    /**
     * 메일인증 여부
     * @var datetime
     * @OA\Property(example="2021-01-01 00:00:00", readOnly=true)
     */
    public $mb_email_certify = '0000-00-00 00:00:00';

    /**
     * 여분필드1
     * @var string
     * @OA\Property(example="test")
     */
    public $mb_1 = '';

    /**
     * 여분필드2
     * @var string
     * @OA\Property(example="test")
     */
    public $mb_2 = '';

    /**
     * 여분필드3
     * @var string
     * @OA\Property(example="test")
     */
    public $mb_3 = '';

    /**
     * 여분필드4
     * @var string
     * @OA\Property(example="test")
     */
    public $mb_4 = '';

    /**
     * 여분필드5
     * @var string
     * @OA\Property(example="test")
     */
    public $mb_5 = '';

    /**
     * 여분필드6
     * @var string
     * @OA\Property(example="test")
     */
    public $mb_6 = '';

    /**
     * 여분필드7
     * @var string
     * @OA\Property(example="test")
     */
    public $mb_7 = '';

    /**
     * 여분필드8
     * @var string
     * @OA\Property(example="test")
     */
    public $mb_8 = '';

    /**
     * 여분필드9
     * @var string
     * @OA\Property(example="test")
     */
    public $mb_9 = '';

    /**
     * 여분필드10
     * @var string
     * @OA\Property(example="test")
     */
    public $mb_10 = '';


    public function __construct($data = []) {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
}
