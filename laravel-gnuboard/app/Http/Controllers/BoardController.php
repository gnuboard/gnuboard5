<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BoardController extends Controller
{
    public function index($boardTable)
    {
        $board = Board::findOrFail($boardTable);
        
        // 접근 권한 체크
        if (Auth::check() && !Auth::user()->canAccessBoard($board)) {
            abort(403, '접근 권한이 없습니다.');
        }

        $query = Post::forBoard($boardTable)
                    ->notComment()
                    ->orderBy('wr_num', 'asc')
                    ->orderBy('wr_reply', 'asc');

        // 카테고리 필터
        if ($category = request('ca_name')) {
            $query->where('ca_name', $category);
        }

        // 검색
        if ($search = request('stx')) {
            $searchField = request('sfl', 'wr_subject,wr_content');
            $fields = explode(',', $searchField);
            
            $query->where(function($q) use ($fields, $search) {
                foreach ($fields as $field) {
                    $q->orWhere($field, 'like', '%' . $search . '%');
                }
            });
        }

        $posts = $query->paginate($board->bo_page_rows ?: 20);

        return view('board.index', compact('board', 'posts'));
    }

    public function show($boardTable, $id)
    {
        $board = Board::findOrFail($boardTable);
        $post = Post::forBoard($boardTable)->findOrFail($id);

        // 읽기 권한 체크
        if (Auth::check() && !Auth::user()->canReadPost($board)) {
            abort(403, '읽기 권한이 없습니다.');
        }

        // 비밀글 체크
        if ($post->isSecret() && (!Auth::check() || 
            (Auth::user()->mb_id !== $post->mb_id && !Auth::user()->isAdmin()))) {
            return redirect()->route('board.password', [$boardTable, $id]);
        }

        // 조회수 증가
        $post->incrementHit();

        // 댓글 가져오기
        $comments = $post->comments()->get();

        return view('board.show', compact('board', 'post', 'comments'));
    }

    public function create($boardTable)
    {
        $board = Board::findOrFail($boardTable);

        // 쓰기 권한 체크
        if (!Auth::check() || !Auth::user()->canWriteBoard($board)) {
            abort(403, '쓰기 권한이 없습니다.');
        }

        return view('board.create', compact('board'));
    }

    public function store(Request $request, $boardTable)
    {
        $board = Board::findOrFail($boardTable);

        // 쓰기 권한 체크
        if (!Auth::check() || !Auth::user()->canWriteBoard($board)) {
            abort(403, '쓰기 권한이 없습니다.');
        }

        $validated = $request->validate([
            'ca_name' => 'nullable|string|max:255',
            'wr_subject' => 'required|string|max:255',
            'wr_content' => 'required|string',
            'wr_link1' => 'nullable|url',
            'wr_link2' => 'nullable|url',
        ]);

        $post = Post::forBoard($boardTable);
        $post->fill($validated);
        
        if (Auth::check()) {
            $post->mb_id = Auth::user()->mb_id;
            $post->wr_name = Auth::user()->mb_nick;
            $post->wr_email = Auth::user()->mb_email;
        }
        
        $post->wr_password = bcrypt($request->wr_password ?? '');
        $post->wr_num = Post::forBoard($boardTable)->min('wr_num') - 1;
        $post->wr_reply = '';
        $post->wr_datetime = now();
        $post->wr_last = now()->format('Y-m-d H:i:s');
        $post->wr_ip = $request->ip();
        
        // 옵션 처리
        $options = [];
        if ($request->has('secret')) $options[] = 'secret';
        if ($request->has('html')) $options[] = 'html1';
        if ($request->has('mail')) $options[] = 'mail';
        $post->wr_option = implode(',', $options);
        
        $post->save();

        // 게시판 글 수 증가
        $board->increment('bo_count_write');

        return redirect()->route('board.show', [$boardTable, $post->wr_id]);
    }

    public function edit($boardTable, $id)
    {
        $board = Board::findOrFail($boardTable);
        $post = Post::forBoard($boardTable)->findOrFail($id);

        // 수정 권한 체크
        if (!Auth::check() || 
            (Auth::user()->mb_id !== $post->mb_id && !Auth::user()->isAdmin())) {
            abort(403, '수정 권한이 없습니다.');
        }

        return view('board.edit', compact('board', 'post'));
    }

    public function update(Request $request, $boardTable, $id)
    {
        $board = Board::findOrFail($boardTable);
        $post = Post::forBoard($boardTable)->findOrFail($id);

        // 수정 권한 체크
        if (!Auth::check() || 
            (Auth::user()->mb_id !== $post->mb_id && !Auth::user()->isAdmin())) {
            abort(403, '수정 권한이 없습니다.');
        }

        $validated = $request->validate([
            'ca_name' => 'nullable|string|max:255',
            'wr_subject' => 'required|string|max:255',
            'wr_content' => 'required|string',
            'wr_link1' => 'nullable|url',
            'wr_link2' => 'nullable|url',
        ]);

        $post->fill($validated);
        $post->wr_last = now()->format('Y-m-d H:i:s');
        
        // 옵션 처리
        $options = [];
        if ($request->has('secret')) $options[] = 'secret';
        if ($request->has('html')) $options[] = 'html1';
        if ($request->has('mail')) $options[] = 'mail';
        $post->wr_option = implode(',', $options);
        
        $post->save();

        return redirect()->route('board.show', [$boardTable, $post->wr_id]);
    }

    public function destroy($boardTable, $id)
    {
        $board = Board::findOrFail($boardTable);
        $post = Post::forBoard($boardTable)->findOrFail($id);

        // 삭제 권한 체크
        if (!Auth::check() || 
            (Auth::user()->mb_id !== $post->mb_id && !Auth::user()->isAdmin())) {
            abort(403, '삭제 권한이 없습니다.');
        }

        // 댓글이 있는지 확인
        if ($post->wr_comment > 0) {
            return back()->with('error', '댓글이 있는 글은 삭제할 수 없습니다.');
        }

        $post->delete();

        // 게시판 글 수 감소
        $board->decrement('bo_count_write');

        return redirect()->route('board.index', $boardTable);
    }

    public function password($boardTable, $id)
    {
        $board = Board::findOrFail($boardTable);
        $post = Post::forBoard($boardTable)->findOrFail($id);

        return view('board.password', compact('board', 'post'));
    }

    public function checkPassword(Request $request, $boardTable, $id)
    {
        $post = Post::forBoard($boardTable)->findOrFail($id);

        if (password_verify($request->password, $post->wr_password)) {
            session()->put('board_password_' . $boardTable . '_' . $id, true);
            return redirect()->route('board.show', [$boardTable, $id]);
        }

        return back()->with('error', '비밀번호가 일치하지 않습니다.');
    }
}