@extends('layouts.app')

@section('title', '메인')

@section('content')
<div class="min-h-screen bg-gradient-to-b from-background to-muted/20">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h2 class="sr-only">최신글</h2>
        
        <!-- Hero Section -->
        <div class="mb-16">
            <div class="text-center space-y-4">
                <h1 class="text-5xl sm:text-6xl font-bold bg-gradient-to-r from-foreground to-foreground/70 bg-clip-text text-transparent">
                    {{ config('app.name') }}
                </h1>
                <p class="text-xl text-muted-foreground max-w-2xl mx-auto">
                    다양한 게시판에서 정보를 공유하고 소통해보세요
                </p>
                <div class="flex justify-center gap-4 pt-4">
                    <a href="/bbs/board/free" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-6">
                        게시판 둘러보기
                    </a>
                    <a href="/register" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-6">
                        회원가입
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Latest Posts Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-12">
            <div class="group relative overflow-hidden rounded-xl border bg-card p-6 shadow-sm transition-all hover:shadow-lg hover:-translate-y-1">
                <div class="absolute inset-0 bg-gradient-to-br from-primary/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-card-foreground">자유게시판</h3>
                        <svg class="w-5 h-5 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                        </svg>
                    </div>
                    {!! latest('pic_list', 'free', 4, 23) !!}
                </div>
            </div>
            
            <div class="group relative overflow-hidden rounded-xl border bg-card p-6 shadow-sm transition-all hover:shadow-lg hover:-translate-y-1">
                <div class="absolute inset-0 bg-gradient-to-br from-primary/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-card-foreground">Q&A</h3>
                        <svg class="w-5 h-5 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    {!! latest('pic_list', 'qa', 4, 23) !!}
                </div>
            </div>
            
            <div class="group relative overflow-hidden rounded-xl border bg-card p-6 shadow-sm transition-all hover:shadow-lg hover:-translate-y-1">
                <div class="absolute inset-0 bg-gradient-to-br from-primary/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-card-foreground">공지사항</h3>
                        <svg class="w-5 h-5 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path>
                        </svg>
                    </div>
                    {!! latest('pic_list', 'notice', 4, 23) !!}
                </div>
            </div>
        </div>
        
        <!-- Gallery Section -->
        <div class="mb-12">
            <div class="rounded-xl border bg-card p-8 shadow-sm">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-2xl font-semibold text-card-foreground">갤러리</h3>
                    <a href="/bbs/board?bo_table=gallery" class="text-sm text-muted-foreground hover:text-primary transition-colors">
                        전체보기 →
                    </a>
                </div>
                <div class="gallery-grid">
                    {!! latest('pic_block', 'gallery', 4, 23) !!}
                </div>
            </div>
        </div>
        
        <!-- Other Latest Posts -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($otherLatestPosts as $boardTable => $data)
                <div class="rounded-xl border bg-card/50 backdrop-blur p-6 shadow-sm transition-all hover:bg-card hover:shadow-md">
                    <h3 class="text-lg font-semibold mb-4 text-card-foreground">{{ $data['title'] ?? $boardTable }}</h3>
                    {!! latest('basic', $boardTable, 6, 24) !!}
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}

/* Latest post styling with shadcn/ui theme */
.latest-list {
    @apply space-y-2;
}

.latest-item {
    @apply flex items-center justify-between py-2.5 px-1 rounded-md hover:bg-muted/50 transition-colors border-b last:border-b-0 border-border/50;
}

.latest-title {
    @apply text-sm font-medium hover:text-primary transition-colors flex-1 truncate;
}

.latest-date {
    @apply text-xs text-muted-foreground ml-3 shrink-0;
}

.latest-count {
    @apply text-xs text-muted-foreground bg-muted px-2 py-0.5 rounded-full;
}

/* Gallery grid styling */
.gallery-grid {
    @apply grid grid-cols-2 md:grid-cols-4 gap-4;
}

.gallery-grid .gallery-item {
    @apply relative aspect-square rounded-lg overflow-hidden bg-muted;
}

.gallery-grid img {
    @apply w-full h-full object-cover transition-transform duration-300 hover:scale-110;
}

/* Card hover effects */
.group:hover .latest-item {
    @apply hover:bg-accent/50;
}

/* Button styles matching shadcn/ui */
.inline-flex {
    @apply transition-all duration-200;
}

/* Responsive adjustments */
@media (max-width: 640px) {
    .latest-title {
        @apply text-xs;
    }
    
    .latest-date {
        @apply text-[10px];
    }
}
</style>
@endpush