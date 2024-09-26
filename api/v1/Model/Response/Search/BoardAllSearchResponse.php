<?php

namespace API\v1\Model\Response\Search;

use API\v1\Traits\SchemaHelperTrait;


/**
 * @OA\Schema(
 *      type="object",
 *      description="게시판 통합검색",
 * )
 */
class BoardAllSearchResponse
{
    use SchemaHelperTrait;

    /**
     * @OA\Property(
     *    type="array",
     *    @OA\Items(
     *      type="object",
     *      @OA\Property(property="table", type="string"),
     *      @OA\Property(property="count", type="integer")
     *    )
     * )
     */
    public array $board_list;

    /**
     * @OA\Property(
     *    type="array",
     *    @OA\Items(
     *      type="object",
     *      @OA\Property(property="wr_subject", type="string"),
     *      @OA\Property(property="wr_content", type="string"),
     *      @OA\Property(property="wr_option", type="string"),
     *      @OA\Property(property="wr_name", type="string"),
     *      @OA\Property(property="wr_datetime", type="string"),
     *      @OA\Property(property="wr_id", type="integer"),
     *      @OA\Property(property="bo_table", type="string"),
     *      @OA\Property(property="mb_icon_path", type="string"),
     *      @OA\Property(property="mb_image_path", type="string")
     *    )
     * )
     */
    public array $search_results;

    /**
     * @OA\Property
     */
    public int $page = 0;

    /**
     * @OA\Property
     */
    public int $total_count;

    public function __construct($data)
    {
        $this->board_list = $data['board_list'] ?? [];
        $this->search_results = $data['search_results'] ?? [];
        $this->page = $data['page'] ?? 1;
        $this->total_count = $data['total_count'] ?? 0;
    }
}