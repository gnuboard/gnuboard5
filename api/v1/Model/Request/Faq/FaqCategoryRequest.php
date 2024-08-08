<?php

namespace API\v1\Model\Request\Faq;

use API\Exceptions\ValidateException;
use API\v1\Traits\SchemaHelperTrait;

/**
 * @OA\Schema(
 *     title="FAQ 카테고리목록 요청",
 * )
 */
class FaqCategoryRequest
{
    use SchemaHelperTrait;

    /**
     * @OA\Property
     */
    public string $fm_id = '';

    /**
     * @OA\Property
     */
    public int $page = 0;

    /**
     * @OA\Property
     */
    public int $per_page = 0;

    public function __construct(array $data)
    {
        $this->mapDataToProperties($this, $data);
        $this->validate();
        
    }

    public function validate(): void
    {
        if ($this->page < 1) {
            $this->page = 1;
        }

        if ($this->per_page < 1) {
            $this->per_page = 10;
        }

        if ($this->per_page > 100) {
            throw new ValidateException('한번에 100개 이상 조회할 수 없습니다.');
        }
    }

}