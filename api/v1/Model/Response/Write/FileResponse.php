<?php

namespace API\v1\Model\Response\Write;

use API\v1\Traits\SchemaHelperTrait;

/**
 * @OA\Schema(
 *     type="object",
 *     description="파일 정보",
 * )
 */
class FileResponse
{
    use SchemaHelperTrait;

    public array $files = [];
    /**
     * @param File[][] $data
     */
    public function __construct($data = [])
    {
        foreach ($data as $file) {
            $this->files[] = new File($file);
        }
    }
}
