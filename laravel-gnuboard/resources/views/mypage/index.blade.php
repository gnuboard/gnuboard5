@extends('layouts.app')

@section('title', '마이페이지')

@section('content')
<div class="min-h-screen bg-gradient-to-b from-muted/30 to-background">
    <div class="container mx-auto px-4 py-8 max-w-7xl">
        <!-- 헤더 섹션 -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold bg-gradient-to-r from-primary to-primary/60 bg-clip-text text-transparent mb-2">마이페이지</h1>
            <p class="text-muted-foreground">{{ $user->mb_name }}님, 안녕하세요!</p>
        </div>
        
        <!-- 프로필 헤더 카드 -->
        <div class="rounded-xl border bg-gradient-to-r from-primary/10 via-primary/5 to-transparent p-8 mb-8 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-primary/5 rounded-full -mr-32 -mt-32"></div>
            <div class="absolute bottom-0 left-0 w-48 h-48 bg-primary/5 rounded-full -ml-24 -mb-24"></div>
            
            <div class="relative z-10 flex flex-col md:flex-row items-center gap-8">
                <!-- 프로필 아바타 -->
                <div class="relative">
                    <div class="w-32 h-32 rounded-full bg-gradient-to-br from-primary to-primary/80 flex items-center justify-center text-white shadow-xl">
                        <span class="text-4xl font-bold">{{ mb_substr($user->mb_name, 0, 1) }}</span>
                    </div>
                    <div class="absolute bottom-0 right-0 w-8 h-8 bg-green-500 rounded-full border-4 border-white"></div>
                </div>
                
                <!-- 기본 정보 -->
                <div class="flex-1 text-center md:text-left">
                    <h2 class="text-3xl font-bold mb-2">{{ $user->mb_nick }}</h2>
                    <p class="text-muted-foreground mb-4">{{ $user->mb_email }}</p>
                    <div class="flex flex-wrap gap-3 justify-center md:justify-start">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium bg-primary/10 text-primary">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            {{ $user->mb_id }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium bg-blue-500/10 text-blue-600">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Level {{ $user->mb_level }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium bg-green-500/10 text-green-600">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            가입일: {{ \Carbon\Carbon::parse($user->mb_datetime)->format('Y.m.d') }}
                        </span>
                    </div>
                </div>
                
                <!-- 액션 버튼 -->
                <div>
                    <a href="{{ route('mypage.edit') }}" class="inline-flex items-center justify-center gap-2 rounded-lg text-sm font-medium transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-11 px-6 shadow-lg hover:shadow-xl hover:-translate-y-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        프로필 수정
                    </a>
                </div>
            </div>
        </div>
        
        <!-- 통계 카드 그리드 -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <!-- 포인트 카드 -->
            <div class="rounded-xl border bg-card text-card-foreground shadow-sm hover:shadow-lg transition-all hover:-translate-y-1">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-lg bg-yellow-500/10 flex items-center justify-center">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <span class="text-xs font-medium text-muted-foreground bg-muted px-2 py-1 rounded">포인트</span>
                    </div>
                    <div class="text-3xl font-bold mb-1">{{ number_format($user->mb_point) }}</div>
                    <p class="text-xs text-muted-foreground">보유 포인트</p>
                </div>
            </div>
            
            <!-- 게시글 카드 -->
            <div class="rounded-xl border bg-card text-card-foreground shadow-sm hover:shadow-lg transition-all hover:-translate-y-1">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-lg bg-blue-500/10 flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <span class="text-xs font-medium text-muted-foreground bg-muted px-2 py-1 rounded">게시글</span>
                    </div>
                    <div class="text-3xl font-bold mb-1">0</div>
                    <p class="text-xs text-muted-foreground">작성한 글</p>
                </div>
            </div>
            
            <!-- 댓글 카드 -->
            <div class="rounded-xl border bg-card text-card-foreground shadow-sm hover:shadow-lg transition-all hover:-translate-y-1">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-lg bg-green-500/10 flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                        </div>
                        <span class="text-xs font-medium text-muted-foreground bg-muted px-2 py-1 rounded">댓글</span>
                    </div>
                    <div class="text-3xl font-bold mb-1">0</div>
                    <p class="text-xs text-muted-foreground">작성한 댓글</p>
                </div>
            </div>
            
            <!-- 스크랩 카드 -->
            <div class="rounded-xl border bg-card text-card-foreground shadow-sm hover:shadow-lg transition-all hover:-translate-y-1">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-lg bg-purple-500/10 flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                            </svg>
                        </div>
                        <span class="text-xs font-medium text-muted-foreground bg-muted px-2 py-1 rounded">스크랩</span>
                    </div>
                    <div class="text-3xl font-bold mb-1">{{ $user->mb_scrap_cnt ?? 0 }}</div>
                    <p class="text-xs text-muted-foreground">스크랩 수</p>
                </div>
            </div>
        </div>
        
        <!-- 상세 정보 그리드 -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- 기본 정보 카드 -->
            <div class="rounded-xl border bg-card text-card-foreground shadow-sm">
                <div class="flex items-center justify-between p-6 pb-4">
                    <div>
                        <h3 class="text-lg font-semibold">기본 정보</h3>
                        <p class="text-sm text-muted-foreground">회원님의 기본 정보입니다</p>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                </div>
                <div class="px-6 pb-6">
                    <dl class="space-y-3">
                        <div class="flex justify-between py-2 border-b border-border/50">
                            <dt class="text-sm font-medium text-muted-foreground">아이디</dt>
                            <dd class="text-sm font-medium">{{ $user->mb_id }}</dd>
                        </div>
                        <div class="flex justify-between py-2 border-b border-border/50">
                            <dt class="text-sm font-medium text-muted-foreground">이름</dt>
                            <dd class="text-sm font-medium">{{ $user->mb_name }}</dd>
                        </div>
                        <div class="flex justify-between py-2 border-b border-border/50">
                            <dt class="text-sm font-medium text-muted-foreground">닉네임</dt>
                            <dd class="text-sm font-medium">{{ $user->mb_nick }}</dd>
                        </div>
                        <div class="flex justify-between py-2 border-b border-border/50">
                            <dt class="text-sm font-medium text-muted-foreground">이메일</dt>
                            <dd class="text-sm font-medium">{{ $user->mb_email }}</dd>
                        </div>
                        <div class="flex justify-between py-2">
                            <dt class="text-sm font-medium text-muted-foreground">휴대폰</dt>
                            <dd class="text-sm font-medium">{{ $user->mb_hp ?: '-' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
            
            <!-- 연락처 정보 카드 -->
            <div class="rounded-xl border bg-card text-card-foreground shadow-sm">
                <div class="flex items-center justify-between p-6 pb-4">
                    <div>
                        <h3 class="text-lg font-semibold">연락처 정보</h3>
                        <p class="text-sm text-muted-foreground">등록된 연락처 정보입니다</p>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="px-6 pb-6">
                    @if($user->mb_zip || $user->mb_addr1 || $user->mb_addr2)
                        <dl class="space-y-3">
                            @if($user->mb_zip)
                            <div class="flex justify-between py-2 border-b border-border/50">
                                <dt class="text-sm font-medium text-muted-foreground">우편번호</dt>
                                <dd class="text-sm font-medium">{{ $user->mb_zip }}</dd>
                            </div>
                            @endif
                            @if($user->mb_addr1)
                            <div class="py-2 border-b border-border/50">
                                <dt class="text-sm font-medium text-muted-foreground mb-1">기본주소</dt>
                                <dd class="text-sm">{{ $user->mb_addr1 }}</dd>
                            </div>
                            @endif
                            @if($user->mb_addr2)
                            <div class="py-2">
                                <dt class="text-sm font-medium text-muted-foreground mb-1">상세주소</dt>
                                <dd class="text-sm">{{ $user->mb_addr2 }}</dd>
                            </div>
                            @endif
                        </dl>
                    @else
                        <div class="text-center py-8">
                            <svg class="w-12 h-12 text-muted-foreground/30 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <p class="text-sm text-muted-foreground">등록된 주소가 없습니다</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- 빠른 메뉴 -->
        <div class="mt-8 rounded-xl border bg-card/50 backdrop-blur p-6">
            <h3 class="text-lg font-semibold mb-4">빠른 메뉴</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <a href="{{ route('mypage.edit') }}" class="flex flex-col items-center gap-2 p-4 rounded-lg hover:bg-muted transition-colors group">
                    <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center group-hover:bg-primary/20 transition-colors">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </div>
                    <span class="text-sm font-medium">정보 수정</span>
                </a>
                <a href="#" class="flex flex-col items-center gap-2 p-4 rounded-lg hover:bg-muted transition-colors group">
                    <div class="w-12 h-12 rounded-full bg-blue-500/10 flex items-center justify-center group-hover:bg-blue-500/20 transition-colors">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <span class="text-sm font-medium">내가 쓴 글</span>
                </a>
                <a href="#" class="flex flex-col items-center gap-2 p-4 rounded-lg hover:bg-muted transition-colors group">
                    <div class="w-12 h-12 rounded-full bg-green-500/10 flex items-center justify-center group-hover:bg-green-500/20 transition-colors">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                    </div>
                    <span class="text-sm font-medium">내 댓글</span>
                </a>
                <a href="#" class="flex flex-col items-center gap-2 p-4 rounded-lg hover:bg-muted transition-colors group">
                    <div class="w-12 h-12 rounded-full bg-purple-500/10 flex items-center justify-center group-hover:bg-purple-500/20 transition-colors">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                        </svg>
                    </div>
                    <span class="text-sm font-medium">스크랩</span>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection