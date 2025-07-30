<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        // 최신글 데이터 준비
        $latestPosts = [];
        
        // 상단 최신글 (pic_list 스킨)
        $latestPosts['free'] = $this->getLatestPosts('free', 4);
        $latestPosts['qa'] = $this->getLatestPosts('qa', 4);
        $latestPosts['notice'] = $this->getLatestPosts('notice', 4);
        
        // 갤러리 최신글 (pic_block 스킨)
        $latestPosts['gallery'] = $this->getLatestPosts('gallery', 4);
        
        // 기타 게시판 최신글 (basic 스킨)
        $otherBoards = Board::where('bo_device', '!=', 'mobile')
            ->whereNotIn('bo_table', ['notice', 'gallery'])
            ->orderBy('gr_id')
            ->orderBy('bo_order')
            ->get();
        
        $otherLatestPosts = [];
        foreach ($otherBoards as $board) {
            if (!auth()->check() && $board->bo_use_cert) {
                continue;
            }
            $otherLatestPosts[$board->bo_table] = [
                'board' => $board,
                'posts' => $this->getLatestPosts($board->bo_table, 6)
            ];
        }
        
        return view('index', compact('latestPosts', 'otherLatestPosts'));
    }
    
    private function getLatestPosts($boardTable, $limit = 5)
    {
        $board = Board::find($boardTable);
        if (!$board) {
            return collect();
        }
        
        return Post::forBoard($boardTable)
            ->notComment()
            ->orderBy('wr_id', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($post) use ($board) {
                $post->board = $board;
                return $post;
            });
    }
}