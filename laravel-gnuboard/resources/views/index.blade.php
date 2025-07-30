@extends('layouts.app')

@section('title', '메인')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h2 class="sr-only">최신글</h2>
    
    <!-- Hero Section -->
    <div class="mb-12">
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">{{ config('app.name') }}에 오신 것을 환영합니다</h1>
            <p class="text-xl text-gray-600">다양한 게시판에서 정보를 공유하고 소통해보세요</p>
        </div>
    </div>
    
    <!-- Latest Posts Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
        <div class="bg-card rounded-lg border p-6 shadow-sm">
            <h3 class="text-lg font-semibold mb-4 text-card-foreground">자유게시판</h3>
            {!! latest('pic_list', 'free', 4, 23) !!}
        </div>
        <div class="bg-card rounded-lg border p-6 shadow-sm">
            <h3 class="text-lg font-semibold mb-4 text-card-foreground">Q&A</h3>
            {!! latest('pic_list', 'qa', 4, 23) !!}
        </div>
        <div class="bg-card rounded-lg border p-6 shadow-sm">
            <h3 class="text-lg font-semibold mb-4 text-card-foreground">공지사항</h3>
            {!! latest('pic_list', 'notice', 4, 23) !!}
        </div>
    </div>
    
    <!-- Gallery Section -->
    <div class="mb-12">
        <div class="bg-card rounded-lg border p-6 shadow-sm">
            <h3 class="text-2xl font-semibold mb-6 text-card-foreground">갤러리</h3>
            {!! latest('pic_block', 'gallery', 4, 23) !!}
        </div>
    </div>
    
    <!-- Other Latest Posts -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        @foreach($otherLatestPosts as $boardTable => $data)
            <div class="bg-card rounded-lg border p-6 shadow-sm">
                <h3 class="text-lg font-semibold mb-4 text-card-foreground">{{ $data['title'] ?? $boardTable }}</h3>
                {!! latest('basic', $boardTable, 6, 24) !!}
            </div>
        @endforeach
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

/* Latest post styling */
.latest-list {
    @apply space-y-3;
}

.latest-item {
    @apply flex items-center justify-between py-2 border-b last:border-b-0 border-gray-100;
}

.latest-title {
    @apply text-sm hover:text-primary transition-colors flex-1;
}

.latest-date {
    @apply text-xs text-muted-foreground ml-2;
}

.latest-count {
    @apply text-xs text-muted-foreground;
}
</style>
@endpush