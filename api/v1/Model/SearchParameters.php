<?php

namespace API\v1\Model;

use API\Service\BoardService;

/**
 * 검색 파라미터 클래스
 */
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
     * @OA\Parameter(name="sfl", in="query", @OA\Schema(type="string", default="wr_subject||wr_content"))
     */
    public string $sfl = 'wr_subject||wr_content';

    /**
     * 검색어
     * @OA\Parameter(name="stx", in="query", @OA\Schema(type="string", default=""))
     */
    public string $stx = '';

    /**
     * 검색 분류
     * @OA\Parameter(name="sca", in="query", @OA\Schema(type="string", default=""))
     */
    public string $sca = '';

    /**
     * 검색 시작 위치
     * @OA\Parameter(name="spt", in="query", @OA\Schema(type="integer", default=0))
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

    /**
     * @param BoardService $board_service 게시판 서비스 객체
     * @param array $data 요청 데이터
     * @param array|null $config 설정 데이터
     */
    public function __construct(BoardService $board_service, array $config, array $data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key) && !is_null($value)) {
                $this->$key = $value;
            }
        }

        $this->sanitizeParameters();
        $this->checkIfSearch();
        $this->initializeSearchPartParameters($board_service, $config);
    }

    /**
     * 파라미터 정리
     */
    private function sanitizeParameters(): void
    {
        $this->stx = trim(stripslashes(strip_tags($this->stx)));
        $this->sfl = trim($this->sfl);
    }

    /**
     * 검색 여부 확인
     */
    private function checkIfSearch(): void
    {
        if ($this->sca || $this->stx || $this->stx === '0') {
            $this->is_search = true;
        }
    }

    /**
     * 검색 단위 파라미터 초기화
     */
    private function initializeSearchPartParameters(BoardService $board_service, array $config): void
    {
        if (!$this->is_search) {
            return;
        }

        $this->min_spt = $board_service->getMinimumWriteNumber();
        if (empty($this->spt)) {
            $this->spt = $this->min_spt;
        }
        $this->search_part = (int)$config['cf_search_part'];
    }
}
