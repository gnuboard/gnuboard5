<?php

use App\Models\Board;
use App\Models\Post;

if (!function_exists('latest')) {
    /**
     * 최신글 추출 함수
     * 
     * @param string $skin 스킨명
     * @param string $boardTable 게시판 테이블명
     * @param int $rows 출력 라인수
     * @param int $subjectLen 제목 길이
     * @return string
     */
    function latest($skin, $boardTable, $rows = 5, $subjectLen = 40)
    {
        $board = Board::find($boardTable);
        if (!$board) {
            return '';
        }
        
        $posts = Post::forBoard($boardTable)
            ->notComment()
            ->orderBy('wr_id', 'desc')
            ->limit($rows)
            ->get();
        
        // 제목 길이 자르기
        $posts->each(function ($post) use ($subjectLen) {
            $post->subject_short = mb_substr($post->wr_subject, 0, $subjectLen);
            if (mb_strlen($post->wr_subject) > $subjectLen) {
                $post->subject_short .= '...';
            }
        });
        
        $viewPath = "latest.{$skin}";
        
        // 뷰 파일이 없으면 기본 스킨 사용
        if (!view()->exists($viewPath)) {
            $viewPath = 'latest.basic';
        }
        
        return view($viewPath, compact('board', 'posts', 'boardTable'))->render();
    }
}

if (!function_exists('cut_str')) {
    /**
     * 문자열 자르기
     * 
     * @param string $str
     * @param int $len
     * @param string $suffix
     * @return string
     */
    function cut_str($str, $len, $suffix = '...')
    {
        $str = strip_tags($str);
        if (mb_strlen($str) <= $len) {
            return $str;
        }
        
        return mb_substr($str, 0, $len) . $suffix;
    }
}

if (!function_exists('get_text')) {
    /**
     * 텍스트 변환
     * 
     * @param string $str
     * @return string
     */
    function get_text($str)
    {
        $str = strip_tags($str);
        $str = htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
        return $str;
    }
}