@extends('layouts.app')

@section('title', '비밀글 보기')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">비밀글 보기</h4>
                </div>
                <div class="card-body">
                    <p class="text-muted">이 글은 비밀글입니다. 비밀번호를 입력하여 주세요.</p>
                    
                    <form method="POST" action="{{ route('board.checkPassword', [$board->bo_table, $post->wr_id]) }}">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">비밀번호</label>
                            <input type="password" class="form-control" id="password" name="password" required autofocus>
                        </div>

                        @if(session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">확인</button>
                            <a href="{{ route('board.index', $board->bo_table) }}" class="btn btn-secondary">목록으로</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection