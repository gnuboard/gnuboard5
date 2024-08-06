<?php

namespace API\v1\Model\Response\Board;

/**
 * @OA\Schema(
 *      type="object",
 *      description="게시글 작성 결과 응답모델",
 * )
 */
class CreateWriteResponse
{
    /**
     * 게시글 작성 결과
     * @OA\Property
     */
    public string $result = "";

    /**
     * 게시글 아이디
     * @OA\Property
     */
    public int $wr_id = 0;

    public function __construct(string $result, int $wr_id)
    {
        $this->result = $result;
        $this->wr_id = $wr_id;
    }
}
