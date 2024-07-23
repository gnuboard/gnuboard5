<?php

namespace API\v1\Model;


class PageParameters
{
    /**
     * 페이지 번호
     * @OA\Parameter(name="page", in="query", @OA\Schema(type="integer", minimum=1, default=1))
     */
    public int $page = 1;

    /**
     * 페이지 당 결과 수
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
     * @param array $config 기본환경설정
     * @param array|null $data 요청 데이터
     * @param array|null $board 게시판 설정
     */
    public function __construct(array $config, array $data = [], array $board = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key) && $value) {
                if (gettype($this->$key) == 'boolean') {
                    $this->$key = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                } else {
                    $this->$key = $value;
                }
            }
        }

        // per_page값이 없을 경우 게시판 설정값 반영
        if ($this->per_page <= 0) {
            if ($this->is_mobile) {
                $this->per_page = $board['bo_mobile_page_rows'] ?? $config['cf_mobile_page_rows'];
            } else {
                $this->per_page = $board['bo_page_rows'] ?? $config['cf_page_rows'];
            }
        }

        // 시작 위치 초기화
        $this->offset = ($this->page - 1) * $this->per_page;
    }
}
