<?php

namespace API\v1\Model\Response\Member;

use API\v1\Traits\SchemaHelperTrait;

/**
 * @OA\Schema(
 *      type="object",
 *      description="회원정보 조회 응답 모델",
 * )
 */
class GetMemberResponse
{
    use SchemaHelperTrait;

    /**
     * 회원 아이디
     * @OA\Property(example="test")
     */
    public string $mb_id = '';

    /**
     * 회원 닉네임
     * @OA\Property(example="테스트")
     */
    public string $mb_nick = '';

    /**
     * 회원 이메일
     * @OA\Property(example="test@test.com")
     */
    public string $mb_email = '';

    /**
     * 회원 포인트
     * @OA\Property(example=100)
     */
    public int $mb_point = 0;

    /**
     * 회원 자기소개
     * @OA\Property(example="안녕하세요?")
     */
    public string $mb_profile = '';

    /**
     * 회원 아이콘 경로
     * @OA\Property(example="/member/test/icon.jpg")
     */
    public string $mb_icon_path = '';

    /**
     * 회원 이미지 경로
     * @OA\Property(example="/member/test/image.jpg")
     */
    public string $mb_image_path = '';

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

    public function __construct(array $data)
    {
        $this->mapDataToProperties($this, $data);
    }
}
