<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', config('app.name'))</title>
    
    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding-top: 70px; }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">{{ config('app.name') }}</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('board.index', 'free') }}">자유게시판</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('board.index', 'notice') }}">공지사항</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('board.index', 'gallery') }}">갤러리</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('board.index', 'qa') }}">Q&A</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    @guest
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">로그인</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">회원가입</a>
                        </li>
                    @else
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                {{ Auth::user()->mb_nick }}
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#">마이페이지</a></li>
                                <li><a class="dropdown-item" href="#">포인트: {{ number_format(Auth::user()->mb_point) }}</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item">로그아웃</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer id="ft" class="bg-light py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>사이트 정보</h5>
                    <p class="small text-muted">
                        회사명 : 회사명 / 대표 : 대표자명<br>
                        주소 : OO도 OO시 OO구 OO동 123-45<br>
                        사업자 등록번호 : 123-45-67890<br>
                        전화 : 02-123-4567 팩스 : 02-123-4568<br>
                        통신판매업신고번호 : 제 OO구 - 123호<br>
                        개인정보관리책임자 : 정보책임자명
                    </p>
                </div>
                <div class="col-md-4">
                    <h5>최신 공지사항</h5>
                    {!! latest('notice', 'notice', 4, 30) !!}
                </div>
                <div class="col-md-4">
                    <h5>빠른 링크</h5>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-muted">회사소개</a></li>
                        <li><a href="#" class="text-muted">개인정보처리방침</a></li>
                        <li><a href="#" class="text-muted">서비스이용약관</a></li>
                        <li><a href="{{ route('board.index', 'qa') }}" class="text-muted">Q&A</a></li>
                        <li><a href="#" class="text-muted">FAQ</a></li>
                    </ul>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <p class="mb-0">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            </div>
        </div>
        
        <!-- Top Button -->
        <button type="button" id="top_btn" class="btn btn-secondary position-fixed" style="bottom: 20px; right: 20px; display: none;">
            <i class="bi bi-arrow-up"></i>
        </button>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Top Button
        window.addEventListener('scroll', function() {
            var topBtn = document.getElementById('top_btn');
            if (window.pageYOffset > 100) {
                topBtn.style.display = 'block';
            } else {
                topBtn.style.display = 'none';
            }
        });
        
        document.getElementById('top_btn').addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    </script>
    @stack('scripts')
</body>
</html>