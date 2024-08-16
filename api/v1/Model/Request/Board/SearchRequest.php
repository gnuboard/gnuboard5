<?php

namespace API\v1\Model\Request\Board;

use API\Service\WriteService;
use API\v1\Traits\SchemaHelperTrait;
use API\v1\Model\SearchParameters;

/**
 * 검색 파라미터 클래스
 */
class SearchRequest extends SearchParameters
{
    use SchemaHelperTrait;

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
     * @param WriteService $write_service 게시글 서비스 객체
     * @param array $data 요청 데이터
     * @param array|null $config 설정 데이터
     */
    public function __construct(WriteService $write_service, array $config, array $data = [])
    {
        $this->mapDataToProperties($this, $data);

        $this->sanitizeParameters();
        $this->checkIfSearch();
        $this->initializeSearchPartParameters($write_service, $config);
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
    private function initializeSearchPartParameters(WriteService $write_service, array $config): void
    {
        if (!$this->is_search) {
            return;
        }

        $this->min_spt = $write_service->fetchMinimumWriteNumber();
        if (empty($this->spt)) {
            $this->spt = $this->min_spt;
        }
        $this->search_part = (int)$config['cf_search_part'];
    }
}
