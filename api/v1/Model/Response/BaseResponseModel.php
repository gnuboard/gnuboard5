<?php

namespace API\v1\Model\Response;

/**
 * @OA\Schema(
 *      type="object",
 *      description="응답 메시지를 포함하는 기본 모델 클래스",
 * )
 */
class BaseResponseModel
{
    /**
     * @var string 응답 메시지
     * @OA\Property(example="string")
     */
    public $message;

    /**
     * 생성자
     * 
     * @param string $message 응답 메시지
     */
    public function __construct($message) {
        $this->message = $message;
    }
}