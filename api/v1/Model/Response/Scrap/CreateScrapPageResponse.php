<?php
namespace API\v1\Model\Response\Scrap;

/**
 * @OA\Schema(
 *      type="object",
 *      description="스크랩 등록페이지 응답 모델",
 * )
 */
class CreateScrapPageResponse
{
    /**
     * 게시판 정보
     * @OA\Property(
     *      type="object",
     *      @OA\Property(property="bo_table", type="string"),
     *      @OA\Property(property="bo_subject", type="string"),
     * )
     */
    public array $board = [];

    /**
     * 게시글 정보
     * @OA\Property(
     *      type="object",
     *      @OA\Property(property="wr_id", type="integer"),
     *      @OA\Property(property="wr_subject", type="string")
     * )
     */
    public array $write = [];

    public function __construct(array $data = [])
    {
        $this->board = $this->filterData($data['board'] ?? [], ['bo_table', 'bo_subject']);
        $this->write = $this->filterData($data['write'] ?? [], ['wr_id', 'wr_subject']);
    }

    private function filterData(array $data, array $allowedKeys): array
    {
        return array_intersect_key($data, array_flip($allowedKeys));
    }
}
