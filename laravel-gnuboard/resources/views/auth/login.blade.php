@extends('layouts.app')

@section('title', '로그인')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">로그인</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="mb_id" class="form-label">아이디</label>
                            <input type="text" class="form-control @error('mb_id') is-invalid @enderror" 
                                   id="mb_id" name="mb_id" value="{{ old('mb_id') }}" required autofocus>
                            @error('mb_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="mb_password" class="form-label">비밀번호</label>
                            <input type="password" class="form-control @error('mb_password') is-invalid @enderror" 
                                   id="mb_password" name="mb_password" required>
                            @error('mb_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember" 
                                       {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label" for="remember">
                                    자동로그인
                                </label>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">로그인</button>
                        </div>

                        <hr>

                        <div class="text-center">
                            <a href="{{ route('register') }}" class="btn btn-link">회원가입</a>
                            <a href="#" class="btn btn-link">아이디/비밀번호 찾기</a>
                        </div>
                    </form>
                </div>
            </div>

            @if(config('gnuboard.social_login_use'))
            <div class="card mt-3">
                <div class="card-body">
                    <h5 class="card-title">소셜 로그인</h5>
                    <div class="d-grid gap-2">
                        <button class="btn btn-warning">카카오 로그인</button>
                        <button class="btn btn-success">네이버 로그인</button>
                        <button class="btn btn-danger">구글 로그인</button>
                        <button class="btn btn-primary">페이스북 로그인</button>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection