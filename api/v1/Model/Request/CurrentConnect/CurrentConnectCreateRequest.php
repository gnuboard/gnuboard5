<?php

namespace API\v1\Model\Request\CurrentConnect;

use API\v1\Traits\SchemaHelperTrait;

/**
 * @OA\Schema(
 *     type="object",
 *     description="현재 접속자 정보 생성 모델",
 * )
 */
class CurrentConnectCreateRequest
{
    use SchemaHelperTrait;

    /**
     * 접속 url 제목
     * @OA\Property
     */
    public string $lo_location = '';

    /**
     * 접속 url
     * @OA\Property
     */
    public string $lo_url = '';

    public function __construct($data)
    {
        $this->mapDataToProperties($this, $data);
    }
}