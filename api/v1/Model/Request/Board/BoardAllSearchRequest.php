<?php

namespace API\v1\Model\Request\Board;

use API\v1\Traits\SchemaHelperTrait;
use API\v1\Model\SearchParameters;

/**
 * 검색 파라미터 클래스
 */
class BoardAllSearchRequest extends SearchParameters
{
    use SchemaHelperTrait;

    public int $per_page = 0;
    public int $page = 0;
    public string $sop = '';
    public string $onetable = '';
    public string $gr_id = '';

    /**
     * 검색 여부
     */
    public bool $is_search = false;

    /**
     * @param array $data 요청 데이터
     */
    public function __construct(array $data)
    {
        $this->mapDataToProperties($this, $data);

        $this->sanitizeParameters();
        $this->checkIfSearch();
        $this->validatePerPage();
        $this->validateSop();
        
        if($this->page < 1) {
            $this->page = 1;
        }
    }

    /**
     * 파라미터 정리
     */
    private function sanitizeParameters(): void
    {
        $this->stx = trim(stripslashes(strip_tags($this->stx)));
        $this->sfl = trim($this->sfl);
        
        if(mb_strlen($this->stx) < 2) {
            $this->throwException('검색어는 두글자 이상입력하세요');
        }
    }

    /**
     * 검색 여부 확인
     */
    private function checkIfSearch(): void
    {
        if ($this->sca || $this->stx || $this->stx === '0') {
            $this->is_search = true;
        } else {
            $this->throwException('검색어를 입력하세요');
        }
    }

    private function validatePerPage()
    {
        if ($this->per_page < 0) {
            $this->per_page = 1;
        }

        if ($this->per_page > 100) {
            $this->per_page = 100;
        }
    }

    private function validateSop()
    {
        $sop = strtolower($this->sop);
        if (!$sop || !($sop === 'and' || $sop === 'or')) {
            $sop = 'and';
        }

        $this->sop = $sop;
    }
}
