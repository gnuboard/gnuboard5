<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', config('app.name'))</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        border: "hsl(var(--border))",
                        input: "hsl(var(--input))",
                        ring: "hsl(var(--ring))",
                        background: "hsl(var(--background))",
                        foreground: "hsl(var(--foreground))",
                        primary: {
                            DEFAULT: "hsl(var(--primary))",
                            foreground: "hsl(var(--primary-foreground))",
                        },
                        secondary: {
                            DEFAULT: "hsl(var(--secondary))",
                            foreground: "hsl(var(--secondary-foreground))",
                        },
                        destructive: {
                            DEFAULT: "hsl(var(--destructive))",
                            foreground: "hsl(var(--destructive-foreground))",
                        },
                        muted: {
                            DEFAULT: "hsl(var(--muted))",
                            foreground: "hsl(var(--muted-foreground))",
                        },
                        accent: {
                            DEFAULT: "hsl(var(--accent))",
                            foreground: "hsl(var(--accent-foreground))",
                        },
                        popover: {
                            DEFAULT: "hsl(var(--popover))",
                            foreground: "hsl(var(--popover-foreground))",
                        },
                        card: {
                            DEFAULT: "hsl(var(--card))",
                            foreground: "hsl(var(--card-foreground))",
                        },
                    },
                    borderRadius: {
                        lg: "var(--radius)",
                        md: "calc(var(--radius) - 2px)",
                        sm: "calc(var(--radius) - 4px)",
                    },
                    fontFamily: {
                        sans: ['Inter var', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'sans-serif'],
                    },
                    animation: {
                        'accordion-down': 'accordion-down 0.2s ease-out',
                        'accordion-up': 'accordion-up 0.2s ease-out',
                    },
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap');
        
        :root {
            --radius: 0.5rem;
            --background: 0 0% 100%;
            --foreground: 222.2 84% 4.9%;
            --card: 0 0% 100%;
            --card-foreground: 222.2 84% 4.9%;
            --popover: 0 0% 100%;
            --popover-foreground: 222.2 84% 4.9%;
            --primary: 222.2 47.4% 11.2%;
            --primary-foreground: 210 40% 98%;
            --secondary: 210 40% 96.1%;
            --secondary-foreground: 222.2 47.4% 11.2%;
            --muted: 210 40% 96.1%;
            --muted-foreground: 215.4 16.3% 46.9%;
            --accent: 210 40% 96.1%;
            --accent-foreground: 222.2 47.4% 11.2%;
            --destructive: 0 84.2% 60.2%;
            --destructive-foreground: 210 40% 98%;
            --border: 214.3 31.8% 91.4%;
            --input: 214.3 31.8% 91.4%;
            --ring: 222.2 84% 4.9%;
        }
        
        .dark {
            --background: 222.2 84% 4.9%;
            --foreground: 210 40% 98%;
            --card: 222.2 84% 4.9%;
            --card-foreground: 210 40% 98%;
            --popover: 222.2 84% 4.9%;
            --popover-foreground: 210 40% 98%;
            --primary: 210 40% 98%;
            --primary-foreground: 222.2 47.4% 11.2%;
            --secondary: 217.2 32.6% 17.5%;
            --secondary-foreground: 210 40% 98%;
            --muted: 217.2 32.6% 17.5%;
            --muted-foreground: 215 20.2% 65.1%;
            --accent: 217.2 32.6% 17.5%;
            --accent-foreground: 210 40% 98%;
            --destructive: 0 62.8% 30.6%;
            --destructive-foreground: 210 40% 98%;
            --border: 217.2 32.6% 17.5%;
            --input: 217.2 32.6% 17.5%;
            --ring: 212.7 26.8% 83.9%;
        }
    </style>
    @stack('styles')
</head>
<body class="min-h-screen bg-background font-sans antialiased">
    <!-- Navigation -->
    <header class="sticky top-0 z-50 w-full border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <div class="flex items-center gap-6 lg:gap-10">
                    <a href="{{ route('home') }}" class="flex items-center space-x-2 group">
                        <div class="rounded-lg bg-primary p-1.5 transition-transform group-hover:scale-110">
                            <svg class="h-5 w-5 text-primary-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                        </div>
                        <span class="text-xl font-bold bg-gradient-to-r from-foreground to-foreground/70 bg-clip-text text-transparent">
                            {{ config('app.name') }}
                        </span>
                    </a>
                    
                    <!-- Desktop Navigation -->
                    <nav class="hidden md:flex items-center gap-1">
                        <a href="{{ route('board.index', 'free') }}" class="px-3 py-2 text-sm font-medium rounded-md transition-all hover:bg-accent hover:text-accent-foreground">
                            자유게시판
                        </a>
                        <a href="{{ route('board.index', 'notice') }}" class="px-3 py-2 text-sm font-medium rounded-md transition-all hover:bg-accent hover:text-accent-foreground">
                            공지사항
                        </a>
                        <a href="{{ route('board.index', 'gallery') }}" class="px-3 py-2 text-sm font-medium rounded-md transition-all hover:bg-accent hover:text-accent-foreground">
                            갤러리
                        </a>
                        <a href="{{ route('board.index', 'qa') }}" class="px-3 py-2 text-sm font-medium rounded-md transition-all hover:bg-accent hover:text-accent-foreground">
                            Q&A
                        </a>
                    </nav>
                </div>

                <!-- User Menu -->
                <div class="flex items-center gap-4">
                    @guest
                        <a href="{{ route('login') }}" class="text-sm font-medium transition-colors hover:text-primary hidden sm:block">
                            로그인
                        </a>
                        <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-9 px-4 py-2">
                            회원가입
                        </a>
                    @else
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium transition-all hover:bg-accent">
                                <div class="h-8 w-8 rounded-full bg-primary/10 flex items-center justify-center">
                                    <span class="text-xs font-semibold text-primary">{{ substr(Auth::user()->mb_nick, 0, 1) }}</span>
                                </div>
                                <span class="hidden sm:block">{{ Auth::user()->mb_nick }}</span>
                                <svg class="h-4 w-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div x-show="open" 
                                 @click.away="open = false" 
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-56 origin-top-right rounded-md border bg-popover p-1 shadow-md">
                                <div class="px-2 py-1.5">
                                    <p class="text-sm font-medium">{{ Auth::user()->mb_nick }}</p>
                                    <p class="text-xs text-muted-foreground">{{ Auth::user()->mb_email }}</p>
                                </div>
                                <div class="h-px bg-border my-1"></div>
                                <a href="{{ route('mypage.index') }}" class="flex items-center gap-2 rounded-sm px-2 py-1.5 text-sm hover:bg-accent hover:text-accent-foreground transition-colors">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    마이페이지
                                </a>
                                <div class="flex items-center gap-2 rounded-sm px-2 py-1.5 text-sm">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span class="text-muted-foreground">포인트: <span class="font-medium text-foreground">{{ number_format(Auth::user()->mb_point) }}</span></span>
                                </div>
                                <div class="h-px bg-border my-1"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="flex w-full items-center gap-2 rounded-sm px-2 py-1.5 text-sm hover:bg-accent hover:text-accent-foreground transition-colors">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                        </svg>
                                        로그아웃
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endguest

                    <!-- Mobile menu button -->
                    <button type="button" 
                            @click="mobileMenuOpen = !mobileMenuOpen" 
                            class="md:hidden inline-flex items-center justify-center p-2 rounded-md hover:bg-accent hover:text-accent-foreground transition-colors">
                        <svg x-show="!mobileMenuOpen" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                        <svg x-show="mobileMenuOpen" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Mobile Navigation -->
        <div x-show="mobileMenuOpen" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-1"
             class="md:hidden border-b bg-background/95 backdrop-blur">
            <nav class="container mx-auto px-4 py-4 space-y-1">
                <a href="{{ route('board.index', 'free') }}" class="block px-3 py-2 text-sm font-medium rounded-md hover:bg-accent hover:text-accent-foreground transition-colors">
                    자유게시판
                </a>
                <a href="{{ route('board.index', 'notice') }}" class="block px-3 py-2 text-sm font-medium rounded-md hover:bg-accent hover:text-accent-foreground transition-colors">
                    공지사항
                </a>
                <a href="{{ route('board.index', 'gallery') }}" class="block px-3 py-2 text-sm font-medium rounded-md hover:bg-accent hover:text-accent-foreground transition-colors">
                    갤러리
                </a>
                <a href="{{ route('board.index', 'qa') }}" class="block px-3 py-2 text-sm font-medium rounded-md hover:bg-accent hover:text-accent-foreground transition-colors">
                    Q&A
                </a>
                @guest
                    <div class="pt-2 space-y-1 border-t">
                        <a href="{{ route('login') }}" class="block px-3 py-2 text-sm font-medium rounded-md hover:bg-accent hover:text-accent-foreground transition-colors">
                            로그인
                        </a>
                        <a href="{{ route('register') }}" class="block px-3 py-2 text-sm font-medium rounded-md hover:bg-accent hover:text-accent-foreground transition-colors">
                            회원가입
                        </a>
                    </div>
                @endguest
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1" x-data="{ mobileMenuOpen: false }">
        @if(session('success'))
            <div class="mx-auto max-w-7xl px-4 pt-4">
                <div class="rounded-lg border border-green-200 bg-green-50/50 p-4 flex items-start gap-3">
                    <svg class="h-5 w-5 text-green-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-sm text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mx-auto max-w-7xl px-4 pt-4">
                <div class="rounded-lg border border-red-200 bg-red-50/50 p-4 flex items-start gap-3">
                    <svg class="h-5 w-5 text-red-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-sm text-red-800">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="border-t bg-muted/30 mt-auto">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="lg:col-span-1">
                    <a href="{{ route('home') }}" class="flex items-center space-x-2 mb-4">
                        <div class="rounded-lg bg-primary p-1.5">
                            <svg class="h-5 w-5 text-primary-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                        </div>
                        <span class="text-lg font-bold">{{ config('app.name') }}</span>
                    </a>
                    <p class="text-sm text-muted-foreground leading-relaxed">
                        커뮤니티의 중심, {{ config('app.name') }}에서<br>
                        다양한 정보와 의견을 나누세요.
                    </p>
                </div>
                
                <div>
                    <h5 class="font-semibold mb-4">최신 공지사항</h5>
                    <div class="space-y-2">
                        {!! latest('footer', 'notice', 4, 30) !!}
                    </div>
                </div>
                
                <div>
                    <h5 class="font-semibold mb-4">사이트 정보</h5>
                    <ul class="text-sm text-muted-foreground space-y-2">
                        <li class="flex items-center gap-2">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            회사명 / 대표: 대표자명
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            서울시 강남구 테헤란로 123
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            02-123-4567
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            info@example.com
                        </li>
                    </ul>
                </div>
                
                <div>
                    <h5 class="font-semibold mb-4">빠른 링크</h5>
                    <ul class="text-sm space-y-2">
                        <li><a href="#" class="text-muted-foreground hover:text-foreground transition-colors inline-flex items-center gap-1">
                            회사소개 <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </a></li>
                        <li><a href="#" class="text-muted-foreground hover:text-foreground transition-colors inline-flex items-center gap-1">
                            개인정보처리방침 <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </a></li>
                        <li><a href="#" class="text-muted-foreground hover:text-foreground transition-colors inline-flex items-center gap-1">
                            서비스이용약관 <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </a></li>
                        <li><a href="{{ route('board.index', 'qa') }}" class="text-muted-foreground hover:text-foreground transition-colors inline-flex items-center gap-1">
                            Q&A <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </a></li>
                        <li><a href="#" class="text-muted-foreground hover:text-foreground transition-colors inline-flex items-center gap-1">
                            FAQ <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </a></li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t mt-12 pt-8">
                <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                    <p class="text-sm text-muted-foreground">
                        &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                    </p>
                    <div class="flex items-center gap-4">
                        <a href="#" class="text-muted-foreground hover:text-foreground transition-colors">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </a>
                        <a href="#" class="text-muted-foreground hover:text-foreground transition-colors">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                            </svg>
                        </a>
                        <a href="#" class="text-muted-foreground hover:text-foreground transition-colors">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zM5.838 12a6.162 6.162 0 1112.324 0 6.162 6.162 0 01-12.324 0zM12 16a4 4 0 110-8 4 4 0 010 8zm4.965-10.405a1.44 1.44 0 112.881.001 1.44 1.44 0 01-2.881-.001z"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Top Button -->
        <button type="button" 
                id="top_btn" 
                class="fixed bottom-5 right-5 hidden inline-flex items-center justify-center rounded-full text-sm font-medium ring-offset-background transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-12 w-12 shadow-lg hover:shadow-xl hover:scale-110">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"></path>
            </svg>
        </button>
    </footer>

    <!-- Scripts -->
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script>
        // Top Button with fade animation
        let lastScrollTop = 0;
        window.addEventListener('scroll', function() {
            const topBtn = document.getElementById('top_btn');
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            
            if (scrollTop > 300) {
                topBtn.classList.remove('hidden');
                topBtn.classList.add('animate-fade-in');
            } else {
                topBtn.classList.add('hidden');
                topBtn.classList.remove('animate-fade-in');
            }
            
            lastScrollTop = scrollTop;
        });
        
        document.getElementById('top_btn').addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
        
        // Add fade animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fade-in {
                from { opacity: 0; transform: translateY(10px); }
                to { opacity: 1; transform: translateY(0); }
            }
            .animate-fade-in {
                animation: fade-in 0.3s ease-out;
            }
        `;
        document.head.appendChild(style);
    </script>
    @stack('scripts')
</body>
</html>