<?php

namespace API\v1\Model\Response\Write;

use API\v1\Traits\SchemaHelperTrait;

class GetCommentsResponse
{
    use SchemaHelperTrait;

    /**
     * @OA\Property
     */
    public int $total_records = 0;

    /**
     * @OA\Property
     */
    public int $total_pages = 0;

    /**
     * @OA\Property
     */
    public int $current_page = 0;

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