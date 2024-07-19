<?php

namespace API\v1\Model\Response\Memo;

/**
 * @OA\Schema(
 *     schema="MemoListResponse",
 *     title="MemoListResponse",
 *     description="쪽지 목록 조회 응답",
 *     required={"total_records", "total_pages", "memos"},
 *     @OA\Property(
 *         property="memos",
 *         type="array",
 *         description="쪽지 목록",
 *         @OA\Items(
 *             @OA\Property(
 *                 property="me_id",
 *                 type="integer",
 *                 description="쪽지 ID"
 *             ),
 *             @OA\Property(
 *                 property="me_recv_mb_id",
 *                 type="string",
 *                 description="받는 회원 ID"
 *             ),
 *             @OA\Property(
 *                 property="me_send_mb_id",
 *                 type="string",
 *                 description="보내는 회원 ID"
 *             ),
 *             @OA\Property(
 *                 property="me_send_datetime",
 *                 type="string",
 *                 description="보낸 시간"
 *             ),
 *             @OA\Property(
 *                 property="me_read_datetime",
 *                 type="string",
 *                 description="읽은 시간"
 *             ),
 *             @OA\Property(
 *                 property="me_memo",
 *                 type="string",
 *                 description="쪽지 내용"
 *             ),
 *             @OA\Property(
 *                 property="me_send_id",
 *                 type="integer",
 *                 description="보낸 회원 ID"
 *             ),
 *             @OA\Property(
 *                 property="me_type",
 *                 type="string",
 *                 description="쪽지 타입"
 *             ),
 *             @OA\Property(
 *                 property="me_send_ip",
 *                 type="string",
 *                 description="보낸 IP"
 *             )
 *         )
 *     )
 * )
 */
class MemoList
{
    /**
     * @OA\Property(
     *  property="total_records",
     *  type="integer",
     *  description="전체 쪽지 수"
     *  )
     */
    public int $total_records;

    /**
     * @OA\Property(
     *  property="total_pages",
     *  type="integer",
     *  description="전체 페이지 수"
     *  )
     */
    public int $total_pages;
    public array $memos;
}