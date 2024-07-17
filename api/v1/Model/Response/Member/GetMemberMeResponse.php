<?php

namespace API\v1\Model\Response\Member;

use API\v1\Model\Response\Member\GetMemberResponse;

/**
 * @OA\Schema(
 *      type="object",
 *      description="현재 로그인 회원정보 조회 응답 모델",
 * )
 */
class GetMemberMeResponse extends GetMemberResponse
{
    /**
     * 회원 이름
     * @OA\Property(example="홍길동")
     */
    public string $mb_name;

    /**
     * 회원 레벨
     * @OA\Property(example=2)
     */
    public int $mb_level;

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


    public function __construct($data)
    {
        parent::__construct($data);
        $this->mb_name = $data['mb_name'];
        $this->mb_level = $data['mb_level'];
        $this->mb_memo_cnt = $data['mb_memo_cnt'];
        $this->mb_scrap_cnt = $data['mb_scrap_cnt'];
    }
}
