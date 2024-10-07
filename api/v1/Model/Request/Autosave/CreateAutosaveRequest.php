<?php

namespace API\v1\Model\Request\Autosave;

use API\v1\Traits\SchemaHelperTrait;

/**
 * @OA\Schema(
 *     type="object",
 *     description="자동 임시저장 생성",
 * )
 */
class CreateAutosaveRequest
{
    use SchemaHelperTrait;
    

    public string $as_subject = '';

    /**
     * @OA\Property(
     *     description="내용",
     * )
     */

    public string $as_content = '';
    public int $as_uid = 0;

    public function __construct(array $data = [])
    {
        $this->mapDataToProperties($this, $data);
        $this->validate();
    }

    public function validate()
    {
        if (empty($this->as_subject)) {
            $this->throwException('제목을 입력해주세요.');
        }
        if (empty($this->as_content)) {
            $this->throwException('내용을 입력해주세요.');
        }
        if (empty($this->as_uid)) {
            $this->throwException('uid 를 입력해주세요.');
        }
    }

}