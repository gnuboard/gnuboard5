<?php

namespace API\v1\Model\Request\BoardNew;

use API\v1\Model\SearchParameters;
use API\v1\Traits\SchemaHelperTrait;

/**
 * 최신글 목록 검색 파라미터 클래스
 */
class SearchRequest extends SearchParameters
{
    use SchemaHelperTrait;

    public function __construct(array $data = [])
    {
        $this->mapDataToProperties($this, $data);
    }
}
