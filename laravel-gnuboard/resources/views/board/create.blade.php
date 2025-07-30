@extends('layouts.app')

@section('title', '글쓰기 - ' . $board->bo_subject)

@section('content')
<div class="container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">홈</a></li>
            <li class="breadcrumb-item"><a href="{{ route('board.index', $board->bo_table) }}">{{ $board->bo_subject }}</a></li>
            <li class="breadcrumb-item active">글쓰기</li>
        </ol>
    </nav>

    <div class="card">
        <div class="card-header">
            <h4 class="mb-0">글쓰기</h4>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('board.store', $board->bo_table) }}" enctype="multipart/form-data">
                @csrf

                @if($board->isUseCategory() && $board->categories)
                <div class="mb-3">
                    <label for="ca_name" class="form-label">분류</label>
                    <select class="form-select" id="ca_name" name="ca_name">
                        <option value="">선택하세요</option>
                        @foreach($board->categories as $category)
                            <option value="{{ $category }}" {{ old('ca_name') == $category ? 'selected' : '' }}>
                                {{ $category }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="mb-3">
                    <label for="wr_subject" class="form-label">제목 <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('wr_subject') is-invalid @enderror" 
                           id="wr_subject" name="wr_subject" value="{{ old('wr_subject') }}" required>
                    @error('wr_subject')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="wr_content" class="form-label">내용 <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('wr_content') is-invalid @enderror" 
                              id="wr_content" name="wr_content" rows="10" required>{{ old('wr_content') }}</textarea>
                    @error('wr_content')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="wr_link1" class="form-label">링크 #1</label>
                    <input type="url" class="form-control @error('wr_link1') is-invalid @enderror" 
                           id="wr_link1" name="wr_link1" value="{{ old('wr_link1') }}" placeholder="http://">
                    @error('wr_link1')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="wr_link2" class="form-label">링크 #2</label>
                    <input type="url" class="form-control @error('wr_link2') is-invalid @enderror" 
                           id="wr_link2" name="wr_link2" value="{{ old('wr_link2') }}" placeholder="http://">
                    @error('wr_link2')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">옵션</label>
                    <div>
                        @if($board->isUseSecret())
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="secret" name="secret" value="1">
                            <label class="form-check-label" for="secret">비밀글</label>
                        </div>
                        @endif
                        @if($board->bo_use_dhtml_editor)
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="html" name="html" value="1" checked>
                            <label class="form-check-label" for="html">HTML</label>
                        </div>
                        @endif
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="mail" name="mail" value="1">
                            <label class="form-check-label" for="mail">답변메일받기</label>
                        </div>
                    </div>
                </div>

                @guest
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="wr_name" class="form-label">이름 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="wr_name" name="wr_name" required>
                    </div>
                    <div class="col-md-6">
                        <label for="wr_password" class="form-label">비밀번호 <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="wr_password" name="wr_password" required>
                    </div>
                </div>
                @endguest

                <div class="d-flex justify-content-between">
                    <a href="{{ route('board.index', $board->bo_table) }}" class="btn btn-secondary">취소</a>
                    <button type="submit" class="btn btn-primary">작성완료</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if($board->bo_use_dhtml_editor)
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
    ClassicEditor
        .create(document.querySelector('#wr_content'))
        .catch(error => {
            console.error(error);
        });
</script>
@endif
@endpush