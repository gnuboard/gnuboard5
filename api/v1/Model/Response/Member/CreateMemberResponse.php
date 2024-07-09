<?php

namespace API\v1\Model\Response\Member;

/**
 * @OA\Schema(
 *      type="object",
 *      description="회원가입 응답 모델",
 * )
 */
class CreateMemberResponse
{
    /**
     * @var string 응답 메시지
     * @OA\Property(example="string")
     */
    public $message;

    /**
     * @var string 회원 아이디
     * @OA\Property(example="string")
     */
    public $mb_id;

    /**
     * @var string 회원 이름
     * @OA\Property(example="string")
     */
    public $mb_name;

    /**
     * @var string 회원 닉네임
     * @OA\Property(example="string")
     */
    public $mb_nick;

    /**
     * CreateMemberResponse 생성자.
     *
     * @param string $message 응답 메시지
     * @param string $mb_id 회원 아이디
     * @param string $mb_name 회원 이름
     * @param string $mb_nick 회원 닉네임
     */
    public function __construct(string $message, object $data)
    {
        $this->message = $message;
        $this->mb_id = $data->mb_id;
        $this->mb_name = $data->mb_name;
        $this->mb_nick = $data->mb_nick;
    }

    /**
     * 응답 데이터를 배열로 변환합니다.
     *
     * @return array 응답 데이터 배열
     */
    public function toArray(): array
    {
        return [
            'message' => $this->message,
            'mb_id' => $this->mb_id,
            'mb_name' => $this->mb_name,
            'mb_nick' => $this->mb_nick,
        ];
    }
}