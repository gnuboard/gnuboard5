<?php

namespace API\v1\Model\Response\Write;

use API\v1\Traits\SchemaHelperTrait;

/**
 * @OA\Schema(
 *     type="object",
 *     description="댓글 정보",
 * )
 */
class CommentResponse
{
    use SchemaHelperTrait;

    /**
     * @OA\Property (
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/Comment")
     * )
     */
    public array $comments = [];

    public function __construct(array $data)
    {
        $this->mapDataToProperties($this, $data);
    }
}