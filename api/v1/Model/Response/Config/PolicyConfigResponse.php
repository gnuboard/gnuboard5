<?php

namespace API\v1\Model\Response\Config;

use API\v1\Traits\SchemaHelperTrait;

/**
 * @OA\Schema(
 *      type="object",
 *      description="기본환경설정 > 회원가입 약관 조회 응답 모델",
 * )
 */
class PolicyConfigResponse
{
    use SchemaHelperTrait;

    /**
     * 이용약관
     * @OA\Property(example="이용약관 내용...")
     */
    public string $cf_stipulation = "";

    /**
     * 개인정보 처리방침
     * @OA\Property(example="개인정보 처리방침 내용...")
     */
    public string $cf_privacy = "";

    public function __construct(array $config = [])
    {
        $this->mapDataToProperties($this, $config);
    }
}
