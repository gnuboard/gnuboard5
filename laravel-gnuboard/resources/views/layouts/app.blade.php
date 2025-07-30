<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', config('app.name'))</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        border: "hsl(214.3 31.8% 91.4%)",
                        input: "hsl(214.3 31.8% 91.4%)",
                        ring: "hsl(222.2 84% 4.9%)",
                        background: "hsl(0 0% 100%)",
                        foreground: "hsl(222.2 84% 4.9%)",
                        primary: {
                            DEFAULT: "hsl(222.2 47.4% 11.2%)",
                            foreground: "hsl(210 40% 98%)",
                        },
                        secondary: {
                            DEFAULT: "hsl(210 40% 96%)",
                            foreground: "hsl(222.2 84% 4.9%)",
                        },
                        destructive: {
                            DEFAULT: "hsl(0 84.2% 60.2%)",
                            foreground: "hsl(210 40% 98%)",
                        },
                        muted: {
                            DEFAULT: "hsl(210 40% 96%)",
                            foreground: "hsl(215.4 16.3% 46.9%)",
                        },
                        accent: {
                            DEFAULT: "hsl(210 40% 96%)",
                            foreground: "hsl(222.2 84% 4.9%)",
                        },
                        popover: {
                            DEFAULT: "hsl(0 0% 100%)",
                            foreground: "hsl(222.2 84% 4.9%)",
                        },
                        card: {
                            DEFAULT: "hsl(0 0% 100%)",
                            foreground: "hsl(222.2 84% 4.9%)",
                        },
                    },
                    borderRadius: {
                        lg: "var(--radius)",
                        md: "calc(var(--radius) - 2px)",
                        sm: "calc(var(--radius) - 4px)",
                    },
                }
            }
        }
    </script>
    <style>
        :root {
            --radius: 0.5rem;
        }
    </style>
    @stack('styles')
</head>
<body class="min-h-screen bg-background font-sans antialiased">
    <!-- Navigation -->
    <header class="sticky top-0 z-50 w-full border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="flex items-center space-x-2">
                        <span class="text-xl font-bold">{{ config('app.name') }}</span>
                    </a>
                </div>
                
                <!-- Desktop Navigation -->
                <nav class="hidden md:flex items-center space-x-6">
                    <a href="{{ route('board.index', 'free') }}" class="text-sm font-medium transition-colors hover:text-primary">
                        자유게시판
                    </a>
                    <a href="{{ route('board.index', 'notice') }}" class="text-sm font-medium transition-colors hover:text-primary">
                        공지사항
                    </a>
                    <a href="{{ route('board.index', 'gallery') }}" class="text-sm font-medium transition-colors hover:text-primary">
                        갤러리
                    </a>
                    <a href="{{ route('board.index', 'qa') }}" class="text-sm font-medium transition-colors hover:text-primary">
                        Q&A
                    </a>
                </nav>

                <!-- User Menu -->
                <div class="flex items-center space-x-4">
                    @guest
                        <a href="{{ route('login') }}" class="text-sm font-medium transition-colors hover:text-primary">
                            로그인
                        </a>
                        <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-9 px-4 py-2">
                            회원가입
                        </a>
                    @else
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center space-x-2 text-sm font-medium transition-colors hover:text-primary">
                                <span>{{ Auth::user()->mb_nick }}</span>
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-56 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                                <div class="py-1">
                                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">마이페이지</a>
                                    <div class="block px-4 py-2 text-sm text-gray-500">포인트: {{ number_format(Auth::user()->mb_point) }}</div>
                                    <hr class="my-1">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100">로그아웃</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endguest
                </div>

                <!-- Mobile menu button -->
                <div class="md:hidden">
                    <button type="button" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500" x-data="{ open: false }" @click="open = !open">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1">
        @if(session('success'))
            <div class="border border-green-200 bg-green-50 text-green-800 px-4 py-3 rounded relative mb-4 mx-4 mt-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="border border-red-200 bg-red-50 text-red-800 px-4 py-3 rounded relative mb-4 mx-4 mt-4">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="border-t bg-muted/50 mt-auto">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h5 class="text-lg font-semibold mb-4">사이트 정보</h5>
                    <div class="text-sm text-muted-foreground space-y-1">
                        <p>회사명 : 회사명 / 대표 : 대표자명</p>
                        <p>주소 : OO도 OO시 OO구 OO동 123-45</p>
                        <p>사업자 등록번호 : 123-45-67890</p>
                        <p>전화 : 02-123-4567 팩스 : 02-123-4568</p>
                        <p>통신판매업신고번호 : 제 OO구 - 123호</p>
                        <p>개인정보관리책임자 : 정보책임자명</p>
                    </div>
                </div>
                <div>
                    <h5 class="text-lg font-semibold mb-4">최신 공지사항</h5>
                    {!! latest('notice', 'notice', 4, 30) !!}
                </div>
                <div>
                    <h5 class="text-lg font-semibold mb-4">빠른 링크</h5>
                    <ul class="text-sm text-muted-foreground space-y-2">
                        <li><a href="#" class="hover:text-foreground transition-colors">회사소개</a></li>
                        <li><a href="#" class="hover:text-foreground transition-colors">개인정보처리방침</a></li>
                        <li><a href="#" class="hover:text-foreground transition-colors">서비스이용약관</a></li>
                        <li><a href="{{ route('board.index', 'qa') }}" class="hover:text-foreground transition-colors">Q&A</a></li>
                        <li><a href="#" class="hover:text-foreground transition-colors">FAQ</a></li>
                    </ul>
                </div>
            </div>
            <hr class="my-8 border-border">
            <div class="text-center">
                <p class="text-sm text-muted-foreground">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            </div>
        </div>
        
        <!-- Top Button -->
        <button type="button" id="top_btn" class="fixed bottom-5 right-5 hidden inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 w-10">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
            </svg>
        </button>
    </footer>

    <!-- Scripts -->
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script>
        // Top Button
        window.addEventListener('scroll', function() {
            var topBtn = document.getElementById('top_btn');
            if (window.pageYOffset > 100) {
                topBtn.classList.remove('hidden');
            } else {
                topBtn.classList.add('hidden');
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