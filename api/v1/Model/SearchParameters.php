<?php

namespace API\v1\Model;


class SearchParameters
{
    /**
     * 정렬 필드
     * @OA\Parameter(name="sst", in="query", @OA\Schema(type="string", default=""))
     */
    public string $sst = '';

    /**
     * 검색 연산자
     * @OA\Parameter(name="sod", in="query", @OA\Schema(type="string", default="and", enum={"and", "or"}))
     */
    public string $sod = 'and';

    /**
     * 검색필드
     * @var string
     * @OA\Parameter(name="sfl", in="query", @OA\Schema(type="string", default="wr_subject||wr_content"))
     */
    public string $sfl = 'wr_subject||wr_content';

    /**
     * 검색어
     * @var string
     * @OA\Parameter(name="stx", in="query", @OA\Schema(type="string", default=""))
     */
    public string $stx = '';

    /**
     * 검색 분류
     * @var string
     * @OA\Parameter(name="sca", in="query", @OA\Schema(type="string", default=""))
     */
    public string $sca = '';

    public function __construct($data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key) && $value) {
                $this->$key = $value;
            }
        }

        // 초기화
        if ($this->stx) {
            $this->stx = trim(stripslashes(strip_tags($this->stx)));
        }
        if ($this->sfl) {
            $this->sfl = trim($this->sfl);
        }
    }
}   
