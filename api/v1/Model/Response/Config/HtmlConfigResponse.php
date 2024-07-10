<?php

namespace API\v1\Model\Response\Config;

/**
 * @OA\Schema(
 *      type="object",
 *      description="기본환경설정 > HTML 설정 조회 응답 모델",
 * )
 */
class HtmlConfigResponse
{
    /**
     * 홈페이지 제목
     * @var string
     * @OA\Property(example="그누보드5")
     */
    public string $cf_title = "";

    /**
     * 추가 메타태그
     * @var string
     * @OA\Property(example="<meta name='description' content='그누보드5'>")
     */
    public string $cf_add_meta = "";

    /**
     * 추가 스크립트
     * @var string
     * @OA\Property(example="<script src='http://example.com/script.js'></script>")
     */
    public string $cf_add_script = "";

    /**
     * 분석코드
     * @var string
     * @OA\Property(example="UA-12345678-1")
     */
    public string $cf_analytics = "";

    public function __construct($config = [])
    {
        foreach ($config as $key => $value) {
            if (property_exists($this, $key) && $value) {
                $this->$key = $value;
            }
        }
    }
}
