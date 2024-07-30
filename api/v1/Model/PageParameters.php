<?php

namespace API\v1\Model;

use API\v1\Traits\SchemaHelperTrait;

class PageParameters
{
    use SchemaHelperTrait;

    /**
     * 페이지 번호
     * @OA\Parameter(name="page", in="query", @OA\Schema(type="integer", minimum=1, default=1))
     */
    public int $page = 1;

    /**
     * 페이지 당 결과 수 (0이면 설정된 기본값을 사용)
     * @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", minimum=0, maximum=100, default=0))
     */
    public int $per_page = 0;

    /**
     * 모바일 여부
     * @OA\Parameter(name="is_mobile", in="query", @OA\Schema(type="boolean", default=false))
     */
    public bool $is_mobile = false;

    /**
     * 시작 위치
     */
    public int $offset = 0;

    /**
     * @param array $data 요청 데이터
     * @param array $config 기본환경설정 (결과 수 기본값 설정)
     * @param int $page_rows 표시할 페이지당 결과 수
     * @param int $mobile_page_rows 모바일에서 표시할 페이지당 결과 수
     */
    public function __construct(array $data, array $config, int $page_rows = 0, int $mobile_page_rows = 0)
    {
        $this->mapDataToProperties($this, $data);

        // per_page값이 없을 경우 게시판 설정값 반영
        if ($this->per_page <= 0) {
            if ($this->is_mobile) {
                $this->per_page = $mobile_page_rows ?? (int)$config['cf_mobile_page_rows'];
            } else {
                $this->per_page = $page_rows ?? (int)$config['cf_page_rows'];
            }
        }

        // 시작 위치 초기화
        $this->offset = ($this->page - 1) * $this->per_page;
    }
}
