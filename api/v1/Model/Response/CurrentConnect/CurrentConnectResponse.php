<?php

namespace API\v1\Model\Response\CurrentConnect;

use API\v1\Traits\SchemaHelperTrait;

/**
 * @OA\Schema(
 *     type="object"
 * )
 */
class CurrentConnectResponse
{
    use SchemaHelperTrait;

    /**
     * 회원 id
     * @OA\Property
     */
    public string $mb_id = '';

    /**
     * 접속 위치
     * @OA\Property
     */
    public string $lo_location = '';

    /**
     * 회원 닉네임
     * @OA\Property
     */
    public string $mb_nick = '';
    
    /**
     * 접속 URL
     * @OA\Property
     */
    public string $lo_url = '';

    /**
     * 접속 IP
     * @OA\Property
     */
    public string $lo_ip = '';

    /**
     * 접속 일시
     * @OA\Property
     */
    public string $lo_datetime = '';

    public function __construct($data)
    {
        $this->mapDataToProperties($this, $data);
        $this->lo_ip = preg_replace('/([0-9]+).([0-9]+).([0-9]+).([0-9]+)/', G5_IP_DISPLAY, $this->lo_ip);
    }
}