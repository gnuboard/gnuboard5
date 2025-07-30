@extends('layouts.admin')

@section('title', '대시보드')

@section('content')
<div class="space-y-6">
    <!-- Stats Grid -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Total Members -->
        <div class="bg-card rounded-lg border p-6 shadow-sm">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="rounded-full bg-blue-100 p-3">
                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-muted-foreground truncate">전체 회원</dt>
                        <dd class="flex items-baseline">
                            <div class="text-2xl font-semibold text-foreground">{{ number_format($stats['total_members']) }}</div>
                            <div class="ml-2 flex items-baseline text-sm font-semibold text-green-600">
                                <svg class="h-5 w-5 flex-shrink-0 self-center text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                                <span class="sr-only">Increased by</span>
                                {{ $stats['today_members'] }}
                            </div>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Today Members -->
        <div class="bg-card rounded-lg border p-6 shadow-sm">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="rounded-full bg-green-100 p-3">
                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-muted-foreground truncate">오늘 가입</dt>
                        <dd class="flex items-baseline">
                            <div class="text-2xl font-semibold text-foreground">{{ number_format($stats['today_members']) }}</div>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Total Posts -->
        <div class="bg-card rounded-lg border p-6 shadow-sm">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="rounded-full bg-purple-100 p-3">
                        <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-muted-foreground truncate">전체 게시글</dt>
                        <dd class="flex items-baseline">
                            <div class="text-2xl font-semibold text-foreground">{{ number_format($stats['total_posts']) }}</div>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Today Posts -->
        <div class="bg-card rounded-lg border p-6 shadow-sm">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="rounded-full bg-yellow-100 p-3">
                        <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-muted-foreground truncate">오늘 게시글</dt>
                        <dd class="flex items-baseline">
                            <div class="text-2xl font-semibold text-foreground">{{ number_format($stats['today_posts']) }}</div>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Members and Posts -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Recent Members -->
        <div class="bg-card rounded-lg border shadow-sm">
            <div class="px-6 py-4 border-b">
                <h3 class="text-lg font-semibold">최근 가입 회원</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-border">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                회원명
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                이메일
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                가입일
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach($recentMembers as $member)
                        <tr class="hover:bg-muted/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8">
                                        <div class="h-8 w-8 rounded-full bg-primary/10 flex items-center justify-center">
                                            <span class="text-xs font-medium text-primary">{{ substr($member->mb_nick, 0, 1) }}</span>
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-foreground">{{ $member->mb_nick }}</div>
                                        <div class="text-xs text-muted-foreground">{{ $member->mb_name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">
                                {{ $member->mb_email }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">
                                {{ \Carbon\Carbon::parse($member->mb_datetime)->format('Y-m-d H:i') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-3 border-t">
                <a href="#" class="text-sm text-primary hover:text-primary/80 transition-colors">
                    전체보기 →
                </a>
            </div>
        </div>

        <!-- Recent Posts -->
        <div class="bg-card rounded-lg border shadow-sm">
            <div class="px-6 py-4 border-b">
                <h3 class="text-lg font-semibold">최근 게시글</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-border">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                제목
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                게시판
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                작성일
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach($recentPosts as $post)
                        <tr class="hover:bg-muted/50 transition-colors">
                            <td class="px-6 py-4 text-sm">
                                <div class="text-sm font-medium text-foreground truncate max-w-xs">
                                    {{ $post->wr_subject }}
                                </div>
                                <div class="text-xs text-muted-foreground">{{ $post->wr_name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary/10 text-primary">
                                    {{ $post->bo_subject }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">
                                {{ \Carbon\Carbon::parse($post->wr_datetime)->format('Y-m-d H:i') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-3 border-t">
                <a href="#" class="text-sm text-primary hover:text-primary/80 transition-colors">
                    전체보기 →
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-card rounded-lg border shadow-sm p-6">
        <h3 class="text-lg font-semibold mb-4">빠른 실행</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="#" class="flex flex-col items-center p-4 rounded-lg border hover:bg-accent hover:text-accent-foreground transition-colors text-center">
                <svg class="h-8 w-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                <span class="text-sm font-medium">새 게시판</span>
            </a>
            <a href="#" class="flex flex-col items-center p-4 rounded-lg border hover:bg-accent hover:text-accent-foreground transition-colors text-center">
                <svg class="h-8 w-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                <span class="text-sm font-medium">회원 추가</span>
            </a>
            <a href="#" class="flex flex-col items-center p-4 rounded-lg border hover:bg-accent hover:text-accent-foreground transition-colors text-center">
                <svg class="h-8 w-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span class="text-sm font-medium">환경 설정</span>
            </a>
            <a href="#" class="flex flex-col items-center p-4 rounded-lg border hover:bg-accent hover:text-accent-foreground transition-colors text-center">
                <svg class="h-8 w-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                </svg>
                <span class="text-sm font-medium">백업 관리</span>
            </a>
        </div>
    </div>
</div>
@endsection