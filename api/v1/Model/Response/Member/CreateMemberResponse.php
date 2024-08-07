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
     * 응답 메시지
     * @OA\Property(example="string")
     */
    public string $message = '';

    /**
     * 아이디
     * @OA\Property(example="string")
     */
    public string $mb_id = '';

    /**
     * 이름
     * @OA\Property(example="string")
     */
    public string $mb_name = '';

    /**
     * 닉네임
     * @OA\Property(example="string")
     */
    public string $mb_nick = '';

    /**
     * CreateMemberResponse 생성자.
     *
     * @param string $message 응답 메시지
     * @param object $data
     */
    public function __construct(string $message, object $data)
    {
        $this->message = $message;
        $this->mb_id = $data->mb_id;
        $this->mb_name = $data->mb_name;
        $this->mb_nick = $data->mb_nick;
    }
}
