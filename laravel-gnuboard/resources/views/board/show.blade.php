@extends('layouts.app')

@section('title', $post->wr_subject . ' - ' . $board->bo_subject)

@section('content')
<div class="container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">홈</a></li>
            <li class="breadcrumb-item"><a href="{{ route('board.index', $board->bo_table) }}">{{ $board->bo_subject }}</a></li>
            <li class="breadcrumb-item active">{{ $post->wr_subject }}</li>
        </ol>
    </nav>

    <div class="card">
        <div class="card-header">
            <h2 class="h4 mb-0">{{ $post->wr_subject }}</h2>
            <div class="mt-2 text-muted small">
                <span>작성자: {{ $post->wr_name }}</span>
                <span class="mx-2">|</span>
                <span>작성일: {{ $post->wr_datetime->format('Y-m-d H:i') }}</span>
                <span class="mx-2">|</span>
                <span>조회: {{ number_format($post->wr_hit) }}</span>
                @if($board->isUseGood())
                    <span class="mx-2">|</span>
                    <span>추천: {{ number_format($post->wr_good) }}</span>
                @endif
            </div>
        </div>
        <div class="card-body">
            @if($post->isHtml())
                {!! $post->wr_content !!}
            @else
                {!! nl2br(e($post->wr_content)) !!}
            @endif

            @if($post->wr_link1 || $post->wr_link2)
                <div class="mt-4 p-3 bg-light">
                    <h6>링크</h6>
                    @if($post->wr_link1)
                        <a href="{{ $post->wr_link1 }}" target="_blank" class="d-block">
                            <i class="bi bi-link-45deg"></i> {{ $post->wr_link1 }}
                        </a>
                    @endif
                    @if($post->wr_link2)
                        <a href="{{ $post->wr_link2 }}" target="_blank" class="d-block">
                            <i class="bi bi-link-45deg"></i> {{ $post->wr_link2 }}
                        </a>
                    @endif
                </div>
            @endif
        </div>
        
        @if($board->isUseGood() || $board->isUseNogood())
        <div class="card-footer text-center">
            @if($board->isUseGood())
                <button class="btn btn-outline-primary btn-sm" onclick="alert('추천 기능은 추후 구현 예정입니다.')">
                    <i class="bi bi-hand-thumbs-up"></i> 추천 {{ number_format($post->wr_good) }}
                </button>
            @endif
            @if($board->isUseNogood())
                <button class="btn btn-outline-danger btn-sm" onclick="alert('비추천 기능은 추후 구현 예정입니다.')">
                    <i class="bi bi-hand-thumbs-down"></i> 비추천 {{ number_format($post->wr_nogood) }}
                </button>
            @endif
        </div>
        @endif
    </div>

    <div class="mt-3 d-flex justify-content-between">
        <div>
            <a href="{{ route('board.index', $board->bo_table) }}" class="btn btn-secondary">목록</a>
        </div>
        <div>
            @auth
                @if(Auth::user()->mb_id === $post->mb_id || Auth::user()->isAdmin())
                    <a href="{{ route('board.edit', [$board->bo_table, $post->wr_id]) }}" class="btn btn-primary">수정</a>
                    <form method="POST" action="{{ route('board.destroy', [$board->bo_table, $post->wr_id]) }}" class="d-inline" 
                          onsubmit="return confirm('정말 삭제하시겠습니까?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">삭제</button>
                    </form>
                @endif
                @if(Auth::user()->canWriteBoard($board))
                    <a href="{{ route('board.create', $board->bo_table) }}" class="btn btn-success">글쓰기</a>
                @endif
            @endauth
        </div>
    </div>

    <!-- 댓글 영역 -->
    @if($board->bo_use_comment)
    <div class="mt-5">
        <h5>댓글 {{ count($comments) }}개</h5>
        
        @foreach($comments as $comment)
        <div class="card mb-2">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <h6 class="card-subtitle mb-2 text-muted">
                        {{ $comment->wr_name }} 
                        <small>{{ $comment->wr_datetime->format('Y-m-d H:i') }}</small>
                    </h6>
                </div>
                <p class="card-text">{{ $comment->wr_content }}</p>
            </div>
        </div>
        @endforeach

        @auth
            @if(Auth::user()->mb_level >= $board->bo_comment_level)
            <form method="POST" action="#" class="mt-3">
                @csrf
                <div class="mb-3">
                    <textarea class="form-control" name="wr_content" rows="3" placeholder="댓글을 입력하세요" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">댓글 작성</button>
            </form>
            @endif
        @endauth
    </div>
    @endif
</div>
@endsection