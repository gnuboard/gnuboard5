<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Member;
use App\Models\Board;
use App\Models\Write;

class AdminController extends Controller
{
    public function dashboard()
    {
        // 통계 데이터 수집
        $stats = [
            'total_members' => Member::count(),
            'today_members' => Member::whereDate('mb_datetime', today())->count(),
            'total_posts' => $this->getTotalPosts(),
            'today_posts' => $this->getTodayPosts(),
        ];

        // 최근 회원
        $recentMembers = Member::latest('mb_datetime')
            ->take(5)
            ->get();

        // 최근 게시글
        $recentPosts = $this->getRecentPosts();

        return view('admin.dashboard', compact('stats', 'recentMembers', 'recentPosts'));
    }

    private function getTotalPosts()
    {
        $total = 0;
        $boards = Board::all();
        
        foreach ($boards as $board) {
            $tableName = 'g5_write_' . $board->bo_table;
            if (DB::getSchemaBuilder()->hasTable($tableName)) {
                $total += DB::table($tableName)->count();
            }
        }
        
        return $total;
    }

    private function getTodayPosts()
    {
        $total = 0;
        $boards = Board::all();
        
        foreach ($boards as $board) {
            $tableName = 'g5_write_' . $board->bo_table;
            if (DB::getSchemaBuilder()->hasTable($tableName)) {
                $total += DB::table($tableName)
                    ->whereDate('wr_datetime', today())
                    ->count();
            }
        }
        
        return $total;
    }

    private function getRecentPosts()
    {
        $posts = collect();
        $boards = Board::all();
        
        foreach ($boards as $board) {
            $tableName = 'g5_write_' . $board->bo_table;
            if (DB::getSchemaBuilder()->hasTable($tableName)) {
                $boardPosts = DB::table($tableName)
                    ->select('wr_id', 'wr_subject', 'wr_name', 'wr_datetime', DB::raw("'{$board->bo_table}' as bo_table"), DB::raw("'{$board->bo_subject}' as bo_subject"))
                    ->orderBy('wr_datetime', 'desc')
                    ->take(5)
                    ->get();
                
                $posts = $posts->merge($boardPosts);
            }
        }
        
        return $posts->sortByDesc('wr_datetime')->take(10);
    }
}