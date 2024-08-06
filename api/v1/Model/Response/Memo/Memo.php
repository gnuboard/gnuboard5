<?php

namespace API\v1\Model\Response\Memo;

/**
 * @OA\Schema(
 *     schema="MemoResponse",
 *     title="MemoResponse",
 *     description="쪽지 조회 응답",
 * )
 */
class Memo
{
    /**
     * 
     * @OA\Property(
     *          property="memos",
     *          type="array",
     *          description="쪽지 목록",
     *          @OA\Items(
     *              @OA\Property(
     *                  property="me_id",
     *                  description="쪽지 ID"
     *              ),
     *              @OA\Property(
     *                  property="me_recv_mb_id",
     *                  description="받는 회원 ID"
     *              ),
     *              @OA\Property(
     *                  property="me_send_mb_id",
     *                  description="보내는 회원 ID"
     *              ),
     *              @OA\Property(
     *                  property="me_send_datetime",
     *                  description="보낸 시간"
     *              ),
     *              @OA\Property(
     *                  property="me_read_datetime",
     *                  description="읽은 시간"
     *              ),
     *              @OA\Property(
     *                  property="me_memo",
     *                  description="쪽지 내용"
     *              ),
     *              @OA\Property(
     *                  property="me_send_id",
     *                  description="보낸 회원 ID"
     *              ),
     *              @OA\Property(
     *                  property="me_type",
     *                  description="쪽지 타입"
     *              ),
     *              @OA\Property(
     *                  property="me_send_ip",
     *                  description="보낸 IP"
     *              )
     *          )
     *      )
     */
    public array $memos;
}