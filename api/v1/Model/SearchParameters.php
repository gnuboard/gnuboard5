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

    /**
     * 검색 시작 위치
     * @OA\Parameter(name="spt", in="query", @OA\Schema(type="int", default=""))
     */
    public int $spt = 0;

    /**
     * 최소 검색 시작 위치 (wr_num)
     */
    public int $min_spt = 0;

    /**
     * 검색 단위 (config Table)
     */
    public int $search_part = 0;

    /**
     * 검색 여부
     */
    public bool $is_search = false;

    public function __construct($data = [], $board_service = null, $config = null)
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
        if ($this->sca || $this->stx || $this->stx === '0') {
            $this->is_search = true;
        }

        if ($this->is_search && $board_service && $config) {
            $this->initializeSearchParameters($board_service, $config);
        }
    }

    /**
     * 검색 단위 파라미터 초기화
     */
    private function initializeSearchParameters($board_service, $config): void
    {
        $this->min_spt = $board_service->getMinimumWriteNumber();
        if (empty($this->spt)) {
            $this->spt = $this->min_spt;
        }
        $this->search_part = (int)$config['cf_search_part'];
    }
}   
