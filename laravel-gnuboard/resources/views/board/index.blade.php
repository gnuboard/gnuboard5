@extends('layouts.app')

@section('title', $board->bo_subject)

@section('content')
<div class="container">
    <div class="row">
        <div class="col-lg-12">
            <h2 class="mb-4">{{ $board->bo_subject }}</h2>

            <!-- 카테고리 -->
            @if($board->isUseCategory() && $board->categories)
                <div class="mb-3">
                    <a href="{{ route('board.index', $board->bo_table) }}" 
                       class="btn btn-sm {{ !request('ca_name') ? 'btn-primary' : 'btn-outline-primary' }}">
                        전체
                    </a>
                    @foreach($board->categories as $category)
                        <a href="{{ route('board.index', [$board->bo_table, 'ca_name' => $category]) }}" 
                           class="btn btn-sm {{ request('ca_name') == $category ? 'btn-primary' : 'btn-outline-primary' }}">
                            {{ $category }}
                        </a>
                    @endforeach
                </div>
            @endif

            <!-- 검색 -->
            <form method="GET" action="{{ route('board.index', $board->bo_table) }}" class="mb-3">
                <div class="input-group">
                    <select name="sfl" class="form-select" style="max-width: 150px;">
                        <option value="wr_subject,wr_content">제목+내용</option>
                        <option value="wr_subject">제목</option>
                        <option value="wr_content">내용</option>
                        <option value="mb_id,wr_name">회원아이디,이름</option>
                    </select>
                    <input type="text" name="stx" value="{{ request('stx') }}" class="form-control" placeholder="검색어">
                    <button class="btn btn-outline-secondary" type="submit">검색</button>
                </div>
            </form>

            <!-- 게시글 목록 -->
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th width="60">번호</th>
                        <th>제목</th>
                        <th width="120">글쓴이</th>
                        <th width="100">날짜</th>
                        <th width="60">조회</th>
                        @if($board->isUseGood())
                            <th width="60">추천</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($posts as $post)
                        <tr>
                            <td>{{ $post->wr_id }}</td>
                            <td>
                                @if($board->isUseCategory() && $post->ca_name)
                                    <span class="badge bg-secondary">{{ $post->ca_name }}</span>
                                @endif
                                <a href="{{ route('board.show', [$board->bo_table, $post->wr_id]) }}">
                                    {{ $post->wr_subject }}
                                    @if($post->wr_comment > 0)
                                        <span class="text-danger">[{{ $post->wr_comment }}]</span>
                                    @endif
                                    @if($post->isSecret())
                                        <i class="bi bi-lock"></i>
                                    @endif
                                </a>
                            </td>
                            <td>{{ $post->wr_name }}</td>
                            <td>{{ $post->wr_datetime->format('m-d') }}</td>
                            <td>{{ $post->wr_hit }}</td>
                            @if($board->isUseGood())
                                <td>{{ $post->wr_good }}</td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $board->isUseGood() ? 6 : 5 }}" class="text-center py-5">
                                게시글이 없습니다.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- 페이지네이션 -->
            <div class="d-flex justify-content-between align-items-center">
                {{ $posts->links('pagination::bootstrap-5') }}
                
                @auth
                    @if(Auth::user()->canWriteBoard($board))
                        <a href="{{ route('board.create', $board->bo_table) }}" class="btn btn-primary">글쓰기</a>
                    @endif
                @endauth
            </div>
        </div>
    </div>
</div>
@endsection