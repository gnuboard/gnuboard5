<?php

namespace API\v1\Model\Response\Config;

/**
 * @OA\Schema(
 *      type="object",
 *      description="기본환경설정 > 회원가입 약관 조회 응답 모델",
 * )
 */
class PolicyConfigResponse
{
    /**
     * 이용약관
     * @var string
     * @OA\Property(example="이용약관 내용...")
     */
    public string $cf_stipulation = "";

    /**
     * 개인정보 처리방침
     * @var string
     * @OA\Property(example="개인정보 처리방침 내용...")
     */
    public string $cf_privacy = "";

    public function __construct($config = [])
    {
        foreach ($config as $key => $value) {
            if (property_exists($this, $key) && $value) {
                $this->$key = $value;
            }
        }
    }
}
