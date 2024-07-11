<?php

namespace API\v1\Model;


class PageParameters
{
    /**
     * 페이지 번호
     * @OA\Parameter(name="page", in="query", @OA\Schema(type="integer", minimum=1, default=1))
     */
    public int $page = 1;

    /**
     * 페이지 당 결과 수
     * @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", minimum=0, maximum=100, default=0))
     */
    public int $per_page = 0;
}   
