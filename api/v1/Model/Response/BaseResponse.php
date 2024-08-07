<?php

namespace API\v1\Model\Response;

/**
 * @OA\Schema(
 *      type="object",
 *      description="응답 메시지를 포함하는 기본 모델 클래스",
 * )
 */
class BaseResponse
{
    /**
     * 응답 메시지
     * @OA\Property(example="string")
     */
    public string $message = '';

    /**
     * @param string $message 응답 메시지
     */
    public function __construct($message) {
        $this->message = $message;
    }
}