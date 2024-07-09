<?php

namespace API\v1\Model\Response\Member;

/**
 * @OA\Schema(
 *      type="object",
 *      description="회원정보 조회 응답 모델",
 * )
 */
class GetMemberResponse
{
    /**
     * 회원 아이디
     * @var string
     * @OA\Property(example="test")
     */
    public $mb_id;

    /**
     * 회원 닉네임
     * @var string
     * @OA\Property(example="테스트")
     */
    public $mb_nick;

    /**
     * 회원 이메일
     * @var string
     * @OA\Property(example="test@test.com")
     */
    public $mb_email;

    /**
     * 회원 포인트
     * @var integer
     * @OA\Property(example=100)
     */
    public $mb_point;

    /**
     * 회원 자기소개
     * @var string
     * @OA\Property(example="안녕하세요?")
     */
    public $mb_profile;

    /**
     * 회원 아이콘 경로
     * @var string
     * @OA\Property(example="/member/test/icon.jpg")
     */
    public $mb_icon_path;

    /**
     * 회원 이미지 경로
     * @var string
     * @OA\Property(example="/member/test/image.jpg")
     */
    public $mb_image_path;

    /**
     * 여분필드1
     * @var string
     * @OA\Property(example="test")
     */
    public $mb_1;

    /**
     * 여분필드2
     * @var string
     * @OA\Property(example="test")
     */
    public $mb_2;

    /**
     * 여분필드3
     * @var string
     * @OA\Property(example="test")
     */
    public $mb_3;

    /**
     * 여분필드4
     * @var string
     * @OA\Property(example="test")
     */
    public $mb_4;

    /**
     * 여분필드5
     * @var string
     * @OA\Property(example="test")
     */
    public $mb_5;

    /**
     * 여분필드6
     * @var string
     * @OA\Property(example="test")
     */
    public $mb_6;

    /**
     * 여분필드7
     * @var string
     * @OA\Property(example="test")
     */
    public $mb_7;

    /**
     * 여분필드8
     * @var string
     * @OA\Property(example="test")
     */
    public $mb_8;

    /**
     * 여분필드9
     * @var string
     * @OA\Property(example="test")
     */
    public $mb_9;

    /**
     * 여분필드10
     * @var string
     * @OA\Property(example="test")
     */
    public $mb_10;

    public function __construct($data)
    {
        $this->mb_id = $data['mb_id'];
        $this->mb_nick = $data['mb_nick'];
        $this->mb_email = $data['mb_email'];
        $this->mb_point = $data['mb_point'];
        $this->mb_profile = $data['mb_profile'];
        $this->mb_icon_path = $data['mb_icon_path'];
        $this->mb_image_path = $data['mb_image_path'];
        $this->mb_1 = $data['mb_1'];
        $this->mb_2 = $data['mb_2'];
        $this->mb_3 = $data['mb_3'];
        $this->mb_4 = $data['mb_4'];
        $this->mb_5 = $data['mb_5'];
        $this->mb_6 = $data['mb_6'];
        $this->mb_7 = $data['mb_7'];
        $this->mb_8 = $data['mb_8'];
        $this->mb_9 = $data['mb_9'];
        $this->mb_10 = $data['mb_10'];
    }

    public function toArray()
    {
        return (array) $this;
    }
}
