<?php

namespace API\v1\Model\Response\Member;

/**
 * @OA\Schema(
 *      type="object",
 *      description="현재 로그인 회원 (내 프로필) 조회 응답 모델",
 * )
 */
class GetMemberMeResponse extends GetMemberResponse
{
    /**
     * 회원 이름
     * @OA\Property(example="홍길동")
     */
    public string $mb_name = '';

    /**
     * 회원 레벨
     * @OA\Property(example=2)
     */
    public int $mb_level = 1;

    /**
     * 서명
     * @OA\Property(example="test")
     */
    public string $mb_signature = '';

    /**
     * 회원 메모 갯수
     * @OA\Property(example=10)
     */
    public int $mb_memo_cnt = 0;

    /**
     * 회원 스크랩 갯수
     * @OA\Property(example=10)
     */
    public int $mb_scrap_cnt = 0;

    /**
     * 열람 허용 여부
     * @OA\Property(example=0)
     */
    public int $mb_open = 0;

    /**
     * SMS 수신여부
     * @OA\Property(example=1)
     */
    public int $mb_sms = 0;

    /**
     * 메일 수신여부
     * @OA\Property(example=1)
     */
    public int $mb_mailling = 0;

    /**
     * 이메일
     * @OA\Property(example="te@domain.com")
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
     * 전화번호
     * @OA\Property(example="02-1234-5678")
     */
    public string $mb_tel = '';

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


    public function __construct($data)
    {
        parent::__construct($data);
    }
}
