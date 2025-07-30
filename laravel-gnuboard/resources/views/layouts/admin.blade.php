<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', '관리자') - {{ config('app.name') }}</title>
    
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
        
        /* Scrollbar 스타일 */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: hsl(var(--muted));
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb {
            background: hsl(var(--muted-foreground) / 0.3);
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: hsl(var(--muted-foreground) / 0.5);
        }
    </style>
    @stack('styles')
</head>
<body class="min-h-screen bg-background font-sans antialiased" x-data="{ sidebarOpen: true, mobileMenuOpen: false }">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="flex">
            <!-- Sidebar for desktop -->
            <div :class="sidebarOpen ? 'w-64' : 'w-16'" 
                 class="hidden md:flex md:flex-col transition-all duration-300 ease-in-out">
                <div class="flex flex-col flex-grow border-r bg-card pt-5 pb-4 overflow-y-auto">
                    <div class="flex items-center flex-shrink-0 px-4">
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center">
                            <div class="rounded-lg bg-primary p-2">
                                <svg class="h-6 w-6 text-primary-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <span x-show="sidebarOpen" x-transition class="ml-3 text-xl font-bold">관리자</span>
                        </a>
                    </div>
                    
                    <!-- Navigation -->
                    <nav class="mt-8 flex-1 px-2 space-y-1" x-data="{ openMenus: {} }">
                        <!-- Dashboard -->
                        <a href="{{ route('admin.dashboard') }}" 
                           class="group flex items-center px-2 py-2 text-sm font-medium rounded-md hover:bg-accent hover:text-accent-foreground transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-accent text-accent-foreground' : '' }}">
                            <svg class="flex-shrink-0 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            <span x-show="sidebarOpen" x-transition class="ml-3">대시보드</span>
                        </a>

                        <!-- 회원 관리 -->
                        <div>
                            <button @click="openMenus.members = !openMenus.members" 
                                    class="group w-full flex items-center px-2 py-2 text-sm font-medium rounded-md hover:bg-accent hover:text-accent-foreground transition-colors">
                                <svg class="flex-shrink-0 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                <span x-show="sidebarOpen" x-transition class="ml-3 flex-1 text-left">회원 관리</span>
                                <svg x-show="sidebarOpen" 
                                     :class="openMenus.members ? 'rotate-90' : ''" 
                                     class="ml-auto h-4 w-4 transform transition-transform duration-200" 
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                            <div x-show="openMenus.members && sidebarOpen" 
                                 x-transition 
                                 class="mt-1 space-y-1">
                                <a href="#" class="group flex items-center pl-11 pr-2 py-2 text-sm font-medium rounded-md hover:bg-accent hover:text-accent-foreground transition-colors">
                                    회원 목록
                                </a>
                                <a href="#" class="group flex items-center pl-11 pr-2 py-2 text-sm font-medium rounded-md hover:bg-accent hover:text-accent-foreground transition-colors">
                                    회원 등급
                                </a>
                                <a href="#" class="group flex items-center pl-11 pr-2 py-2 text-sm font-medium rounded-md hover:bg-accent hover:text-accent-foreground transition-colors">
                                    포인트 관리
                                </a>
                            </div>
                        </div>

                        <!-- 게시판 관리 -->
                        <div>
                            <button @click="openMenus.boards = !openMenus.boards" 
                                    class="group w-full flex items-center px-2 py-2 text-sm font-medium rounded-md hover:bg-accent hover:text-accent-foreground transition-colors">
                                <svg class="flex-shrink-0 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                </svg>
                                <span x-show="sidebarOpen" x-transition class="ml-3 flex-1 text-left">게시판 관리</span>
                                <svg x-show="sidebarOpen" 
                                     :class="openMenus.boards ? 'rotate-90' : ''" 
                                     class="ml-auto h-4 w-4 transform transition-transform duration-200" 
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                            <div x-show="openMenus.boards && sidebarOpen" 
                                 x-transition 
                                 class="mt-1 space-y-1">
                                <a href="#" class="group flex items-center pl-11 pr-2 py-2 text-sm font-medium rounded-md hover:bg-accent hover:text-accent-foreground transition-colors">
                                    게시판 목록
                                </a>
                                <a href="#" class="group flex items-center pl-11 pr-2 py-2 text-sm font-medium rounded-md hover:bg-accent hover:text-accent-foreground transition-colors">
                                    게시판 그룹
                                </a>
                                <a href="#" class="group flex items-center pl-11 pr-2 py-2 text-sm font-medium rounded-md hover:bg-accent hover:text-accent-foreground transition-colors">
                                    게시글 관리
                                </a>
                            </div>
                        </div>

                        <!-- 환경 설정 -->
                        <div>
                            <button @click="openMenus.settings = !openMenus.settings" 
                                    class="group w-full flex items-center px-2 py-2 text-sm font-medium rounded-md hover:bg-accent hover:text-accent-foreground transition-colors">
                                <svg class="flex-shrink-0 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span x-show="sidebarOpen" x-transition class="ml-3 flex-1 text-left">환경 설정</span>
                                <svg x-show="sidebarOpen" 
                                     :class="openMenus.settings ? 'rotate-90' : ''" 
                                     class="ml-auto h-4 w-4 transform transition-transform duration-200" 
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                            <div x-show="openMenus.settings && sidebarOpen" 
                                 x-transition 
                                 class="mt-1 space-y-1">
                                <a href="{{ route('admin.config') }}" class="group flex items-center pl-11 pr-2 py-2 text-sm font-medium rounded-md hover:bg-accent hover:text-accent-foreground transition-colors {{ request()->routeIs('admin.config') ? 'bg-accent text-accent-foreground' : '' }}">
                                    기본 설정
                                </a>
                                <a href="#" class="group flex items-center pl-11 pr-2 py-2 text-sm font-medium rounded-md hover:bg-accent hover:text-accent-foreground transition-colors">
                                    메타 태그
                                </a>
                                <a href="#" class="group flex items-center pl-11 pr-2 py-2 text-sm font-medium rounded-md hover:bg-accent hover:text-accent-foreground transition-colors">
                                    메일 설정
                                </a>
                            </div>
                        </div>
                    </nav>

                    <!-- Sidebar Toggle Button -->
                    <div class="flex-shrink-0 px-4 py-4">
                        <button @click="sidebarOpen = !sidebarOpen" 
                                class="group flex items-center px-2 py-2 text-sm font-medium rounded-md hover:bg-accent hover:text-accent-foreground transition-colors w-full">
                            <svg :class="sidebarOpen ? '' : 'rotate-180'" 
                                 class="h-5 w-5 transform transition-transform duration-200" 
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                            </svg>
                            <span x-show="sidebarOpen" x-transition class="ml-3">메뉴 접기</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile sidebar -->
            <div x-show="mobileMenuOpen" 
                 @click.away="mobileMenuOpen = false"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="-translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition ease-in duration-300"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="-translate-x-full"
                 class="fixed inset-y-0 left-0 z-50 w-64 bg-card border-r md:hidden">
                <div class="flex flex-col h-full pt-5 pb-4 overflow-y-auto">
                    <div class="flex items-center justify-between flex-shrink-0 px-4">
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center">
                            <div class="rounded-lg bg-primary p-2">
                                <svg class="h-6 w-6 text-primary-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <span class="ml-3 text-xl font-bold">관리자</span>
                        </a>
                        <button @click="mobileMenuOpen = false" class="rounded-md hover:bg-accent p-1">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Mobile Navigation (same as desktop) -->
                    <nav class="mt-8 flex-1 px-2 space-y-1">
                        <!-- Copy navigation items from desktop here -->
                    </nav>
                </div>
            </div>
        </aside>

        <!-- Main content -->
        <div class="flex flex-col flex-1 overflow-hidden">
            <!-- Top header -->
            <header class="bg-card border-b">
                <div class="flex items-center justify-between px-4 py-4">
                    <div class="flex items-center">
                        <!-- Mobile menu button -->
                        <button @click="mobileMenuOpen = true" 
                                class="md:hidden rounded-md hover:bg-accent p-2 mr-2">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        
                        <h1 class="text-xl font-semibold">@yield('title', '대시보드')</h1>
                    </div>

                    <div class="flex items-center gap-4">
                        <!-- Visit Site -->
                        <a href="{{ route('home') }}" 
                           target="_blank"
                           class="text-sm text-muted-foreground hover:text-foreground transition-colors">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </a>

                        <!-- User Menu -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" 
                                    class="flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium hover:bg-accent">
                                <div class="h-8 w-8 rounded-full bg-primary/10 flex items-center justify-center">
                                    <span class="text-xs font-semibold text-primary">{{ substr(Auth::user()->mb_nick, 0, 1) }}</span>
                                </div>
                                <span class="hidden sm:block">{{ Auth::user()->mb_nick }}</span>
                                <svg class="h-4 w-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="open" 
                                 @click.away="open = false" 
                                 x-transition
                                 class="absolute right-0 mt-2 w-56 origin-top-right rounded-md border bg-popover p-1 shadow-md">
                                <a href="{{ route('mypage.index') }}" 
                                   class="flex items-center gap-2 rounded-sm px-2 py-1.5 text-sm hover:bg-accent hover:text-accent-foreground transition-colors">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    내 정보
                                </a>
                                <div class="h-px bg-border my-1"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" 
                                            class="flex w-full items-center gap-2 rounded-sm px-2 py-1.5 text-sm hover:bg-accent hover:text-accent-foreground transition-colors">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                        </svg>
                                        로그아웃
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto bg-muted/30">
                <div class="p-6">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script>
        // 페이지 로드 시 현재 메뉴 열기
        document.addEventListener('alpine:init', () => {
            Alpine.data('sidebarData', () => ({
                init() {
                    // 현재 경로에 따라 메뉴 열기
                    const path = window.location.pathname;
                    if (path.includes('/admin/members')) {
                        this.openMenus.members = true;
                    } else if (path.includes('/admin/boards')) {
                        this.openMenus.boards = true;
                    } else if (path.includes('/admin/settings')) {
                        this.openMenus.settings = true;
                    }
                }
            }));
        });
    </script>
    @stack('scripts')
</body>
</html>