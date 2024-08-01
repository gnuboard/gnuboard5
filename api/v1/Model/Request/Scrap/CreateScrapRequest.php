<?php

namespace API\v1\Model\Request\Scrap;

use API\v1\Traits\SchemaHelperTrait;

/**
 * @OA\Schema(
 *     type="object",
 *     description="스크랩 추가 요청 모델",
 * )
 */
class CreateScrapRequest
{
    use SchemaHelperTrait;

    /**
     * 댓글내용
     * @OA\Property(example="댓글")
     */
    public string $wr_content = '';

    public function __construct(array $data = [])
    {
        $this->mapDataToProperties($this, $data);

        $this->validateContent();
    }

    /**
     * 댓글내용 유효성 검사
     */
    protected function validateContent(): void
    {
        $this->wr_content = sanitize_input($this->wr_content, 65536);

        if (substr_count($this->wr_content, '&#') > 50) {
            $this->throwException('내용에 올바르지 않은 코드가 다수 포함되어 있습니다.');
        }
    }
}
