<?php

namespace API\v1\Model;

/**
 * 검색 파라미터 클래스
 */
class SearchParameters
{
    /**
     * 정렬 필드
     * @OA\Parameter(name="sst", in="query", @OA\Schema(type="string", default=""))
     */
    public string $sst = '';

    /**
     * 검색 연산자
     * @OA\Parameter(name="sod", in="query", @OA\Schema(type="string", default="and", enum={"and", "or"}))
     */
    public string $sod = 'and';

    /**
     * 검색필드
     * @OA\Parameter(name="sfl", in="query", @OA\Schema(type="string", default="wr_subject||wr_content"))
     */
    public string $sfl = 'wr_subject||wr_content';

    /**
     * 검색어
     * @OA\Parameter(name="stx", in="query", @OA\Schema(type="string", default=""))
     */
    public string $stx = '';

    /**
     * 검색 분류
     * @OA\Parameter(name="sca", in="query", @OA\Schema(type="string", default=""))
     */
    public string $sca = '';

    /**
     * 검색 시작 위치
     * @OA\Parameter(name="spt", in="query", @OA\Schema(type="integer", default=0))
     */
    public int $spt = 0;

    /**
     * 게시판 그룹 ID
     * @OA\Parameter(name="gr_id", in="query", @OA\Schema(type="string", default=""))
     */
    public string $gr_id = '';

    /**
     * 게시글 검색 유형(게시글/댓글)
     * @OA\Parameter(name="view", in="query", @OA\Schema(type="string", default="", enum={"write", "comment"}))
     */
    public string $view = '';

    /**
     * 회원 ID
     * @OA\Parameter(name="mb_id", in="query", @OA\Schema(type="string", default=""))
     */
    public string $mb_id = '';
}
