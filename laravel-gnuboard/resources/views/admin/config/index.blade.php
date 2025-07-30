@extends('layouts.admin')

@section('title', '환경설정')

@section('content')
<div class="space-y-6" x-data="{ activeTab: '{{ session('active_tab', request('tab', 'basic')) }}' }">
    <!-- Header -->
    <div class="bg-card rounded-lg border shadow-sm p-6">
        <h2 class="text-2xl font-bold">환경설정</h2>
        <p class="text-muted-foreground mt-2">사이트의 기본 환경을 설정합니다.</p>
    </div>

    <!-- Tabs -->
    <div class="bg-card rounded-lg border shadow-sm">
        <nav class="flex flex-wrap border-b">
            <button @click="activeTab = 'basic'" 
                    :class="activeTab === 'basic' ? 'border-b-2 border-primary text-primary' : 'text-muted-foreground hover:text-foreground'"
                    class="px-6 py-3 text-sm font-medium transition-colors">
                기본환경
            </button>
            <button @click="activeTab = 'board'" 
                    :class="activeTab === 'board' ? 'border-b-2 border-primary text-primary' : 'text-muted-foreground hover:text-foreground'"
                    class="px-6 py-3 text-sm font-medium transition-colors">
                게시판기본
            </button>
            <button @click="activeTab = 'member'" 
                    :class="activeTab === 'member' ? 'border-b-2 border-primary text-primary' : 'text-muted-foreground hover:text-foreground'"
                    class="px-6 py-3 text-sm font-medium transition-colors">
                회원가입
            </button>
            <button @click="activeTab = 'cert'" 
                    :class="activeTab === 'cert' ? 'border-b-2 border-primary text-primary' : 'text-muted-foreground hover:text-foreground'"
                    class="px-6 py-3 text-sm font-medium transition-colors">
                본인확인
            </button>
            <button @click="activeTab = 'shorturl'" 
                    :class="activeTab === 'shorturl' ? 'border-b-2 border-primary text-primary' : 'text-muted-foreground hover:text-foreground'"
                    class="px-6 py-3 text-sm font-medium transition-colors">
                짧은주소
            </button>
            <button @click="activeTab = 'mail'" 
                    :class="activeTab === 'mail' ? 'border-b-2 border-primary text-primary' : 'text-muted-foreground hover:text-foreground'"
                    class="px-6 py-3 text-sm font-medium transition-colors">
                메일설정
            </button>
            <button @click="activeTab = 'sns'" 
                    :class="activeTab === 'sns' ? 'border-b-2 border-primary text-primary' : 'text-muted-foreground hover:text-foreground'"
                    class="px-6 py-3 text-sm font-medium transition-colors">
                SNS
            </button>
            <button @click="activeTab = 'layout'" 
                    :class="activeTab === 'layout' ? 'border-b-2 border-primary text-primary' : 'text-muted-foreground hover:text-foreground'"
                    class="px-6 py-3 text-sm font-medium transition-colors">
                레이아웃 추가설정
            </button>
            <button @click="activeTab = 'sms'" 
                    :class="activeTab === 'sms' ? 'border-b-2 border-primary text-primary' : 'text-muted-foreground hover:text-foreground'"
                    class="px-6 py-3 text-sm font-medium transition-colors">
                SMS
            </button>
            <button @click="activeTab = 'extra'" 
                    :class="activeTab === 'extra' ? 'border-b-2 border-primary text-primary' : 'text-muted-foreground hover:text-foreground'"
                    class="px-6 py-3 text-sm font-medium transition-colors">
                여분필드
            </button>
        </nav>

        <form action="{{ route('admin.config.update') }}" method="POST" class="p-6" id="fconfigform">
            @csrf
            <input type="hidden" name="active_tab" :value="activeTab">
            
            <!-- Basic Settings -->
            <div x-show="activeTab === 'basic'" x-transition>
                <h3 class="text-lg font-semibold mb-6">홈페이지 기본환경 설정</h3>
                
                <div class="space-y-6">
                    <!-- Site Title -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label for="cf_title" class="text-sm font-medium">
                                홈페이지 제목 <span class="text-destructive">*</span>
                            </label>
                            <input type="text" 
                                   name="cf_title" 
                                   id="cf_title" 
                                   value="{{ old('cf_title', $config->cf_title) }}" 
                                   required
                                   class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>

                        <div class="space-y-2">
                            <label for="cf_admin" class="text-sm font-medium">
                                최고관리자 <span class="text-destructive">*</span>
                            </label>
                            <input type="text" 
                                   name="cf_admin" 
                                   id="cf_admin" 
                                   value="{{ old('cf_admin', $config->cf_admin) }}" 
                                   required
                                   class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                    </div>

                    <!-- Admin Email -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label for="cf_admin_email" class="text-sm font-medium">
                                관리자 메일 주소 <span class="text-destructive">*</span>
                            </label>
                            <input type="email" 
                                   name="cf_admin_email" 
                                   id="cf_admin_email" 
                                   value="{{ old('cf_admin_email', $config->cf_admin_email) }}" 
                                   required
                                   class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            <p class="text-xs text-muted-foreground">관리자가 보내고 받는 용도로 사용하는 메일 주소를 입력합니다.</p>
                        </div>

                        <div class="space-y-2">
                            <label for="cf_admin_email_name" class="text-sm font-medium">
                                관리자 메일 발송이름 <span class="text-destructive">*</span>
                            </label>
                            <input type="text" 
                                   name="cf_admin_email_name" 
                                   id="cf_admin_email_name" 
                                   value="{{ old('cf_admin_email_name', $config->cf_admin_email_name) }}" 
                                   required
                                   class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                    </div>

                    <!-- Point Settings -->
                    <div class="border-t pt-6">
                        <h4 class="font-medium mb-4">포인트 설정</h4>
                        
                        <div class="space-y-4">
                            <div class="flex items-center gap-2">
                                <input type="checkbox" 
                                       name="cf_use_point" 
                                       id="cf_use_point" 
                                       value="1" 
                                       {{ $config->cf_use_point ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-primary focus:ring-primary">
                                <label for="cf_use_point" class="text-sm font-medium">포인트 사용</label>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label for="cf_login_point" class="text-sm font-medium">
                                        로그인시 포인트 <span class="text-destructive">*</span>
                                    </label>
                                    <div class="flex items-center gap-2">
                                        <input type="number" 
                                               name="cf_login_point" 
                                               id="cf_login_point" 
                                               value="{{ old('cf_login_point', $config->cf_login_point) }}" 
                                               required
                                               class="px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                        <span class="text-sm">점</span>
                                    </div>
                                    <p class="text-xs text-muted-foreground">회원이 로그인시 하루에 한번만 적립</p>
                                </div>

                                <div class="space-y-2">
                                    <label for="cf_memo_send_point" class="text-sm font-medium">
                                        쪽지보낼시 차감 포인트 <span class="text-destructive">*</span>
                                    </label>
                                    <div class="flex items-center gap-2">
                                        <input type="number" 
                                               name="cf_memo_send_point" 
                                               id="cf_memo_send_point" 
                                               value="{{ old('cf_memo_send_point', $config->cf_memo_send_point) }}" 
                                               required
                                               class="px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                        <span class="text-sm">점</span>
                                    </div>
                                    <p class="text-xs text-muted-foreground">양수로 입력하십시오. 0점은 쪽지 보낼시 포인트를 차감하지 않습니다.</p>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label for="cf_point_term" class="text-sm font-medium">
                                    포인트 유효기간 <span class="text-destructive">*</span>
                                </label>
                                <div class="flex items-center gap-2">
                                    <input type="number" 
                                           name="cf_point_term" 
                                           id="cf_point_term" 
                                           value="{{ old('cf_point_term', $config->cf_point_term) }}" 
                                           required
                                           class="px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <span class="text-sm">일</span>
                                </div>
                                <p class="text-xs text-muted-foreground">기간을 0으로 설정시 포인트 유효기간이 적용되지 않습니다.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Display Settings -->
                    <div class="border-t pt-6">
                        <h4 class="font-medium mb-4">표시 설정</h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label for="cf_cut_name" class="text-sm font-medium">
                                    이름(닉네임) 표시 <span class="text-destructive">*</span>
                                </label>
                                <div class="flex items-center gap-2">
                                    <input type="number" 
                                           name="cf_cut_name" 
                                           id="cf_cut_name" 
                                           value="{{ old('cf_cut_name', $config->cf_cut_name) }}" 
                                           required
                                           class="px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <span class="text-sm">자리만 표시</span>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label for="cf_nick_modify" class="text-sm font-medium">
                                    닉네임 수정 <span class="text-destructive">*</span>
                                </label>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm">수정하면</span>
                                    <input type="number" 
                                           name="cf_nick_modify" 
                                           id="cf_nick_modify" 
                                           value="{{ old('cf_nick_modify', $config->cf_nick_modify) }}" 
                                           required
                                           class="px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <span class="text-sm">일 동안 바꿀 수 없음</span>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label for="cf_open_modify" class="text-sm font-medium">
                                    정보공개 수정 <span class="text-destructive">*</span>
                                </label>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm">수정하면</span>
                                    <input type="number" 
                                           name="cf_open_modify" 
                                           id="cf_open_modify" 
                                           value="{{ old('cf_open_modify', $config->cf_open_modify) }}" 
                                           required
                                           class="px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <span class="text-sm">일 동안 바꿀 수 없음</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Delete Settings -->
                    <div class="border-t pt-6">
                        <h4 class="font-medium mb-4">자동 삭제 설정</h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label for="cf_new_del" class="text-sm font-medium">
                                    최근게시물 삭제 <span class="text-destructive">*</span>
                                </label>
                                <div class="flex items-center gap-2">
                                    <input type="number" 
                                           name="cf_new_del" 
                                           id="cf_new_del" 
                                           value="{{ old('cf_new_del', $config->cf_new_del) }}" 
                                           required
                                           class="px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <span class="text-sm">일</span>
                                </div>
                                <p class="text-xs text-muted-foreground">설정일이 지난 최근게시물 자동 삭제</p>
                            </div>

                            <div class="space-y-2">
                                <label for="cf_memo_del" class="text-sm font-medium">
                                    쪽지 삭제 <span class="text-destructive">*</span>
                                </label>
                                <div class="flex items-center gap-2">
                                    <input type="number" 
                                           name="cf_memo_del" 
                                           id="cf_memo_del" 
                                           value="{{ old('cf_memo_del', $config->cf_memo_del) }}" 
                                           required
                                           class="px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <span class="text-sm">일</span>
                                </div>
                                <p class="text-xs text-muted-foreground">설정일이 지난 쪽지 자동 삭제</p>
                            </div>

                            <div class="space-y-2">
                                <label for="cf_visit_del" class="text-sm font-medium">
                                    접속자로그 삭제 <span class="text-destructive">*</span>
                                </label>
                                <div class="flex items-center gap-2">
                                    <input type="number" 
                                           name="cf_visit_del" 
                                           id="cf_visit_del" 
                                           value="{{ old('cf_visit_del', $config->cf_visit_del) }}" 
                                           required
                                           class="px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <span class="text-sm">일</span>
                                </div>
                                <p class="text-xs text-muted-foreground">설정일이 지난 접속자 로그 자동 삭제</p>
                            </div>

                            <div class="space-y-2">
                                <label for="cf_popular_del" class="text-sm font-medium">
                                    인기검색어 삭제 <span class="text-destructive">*</span>
                                </label>
                                <div class="flex items-center gap-2">
                                    <input type="number" 
                                           name="cf_popular_del" 
                                           id="cf_popular_del" 
                                           value="{{ old('cf_popular_del', $config->cf_popular_del) }}" 
                                           required
                                           class="px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <span class="text-sm">일</span>
                                </div>
                                <p class="text-xs text-muted-foreground">설정일이 지난 인기검색어 자동 삭제</p>
                            </div>
                        </div>
                    </div>

                    <!-- Page Settings -->
                    <div class="border-t pt-6">
                        <h4 class="font-medium mb-4">페이지 표시 설정</h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label for="cf_login_minutes" class="text-sm font-medium">
                                    현재 접속자 <span class="text-destructive">*</span>
                                </label>
                                <div class="flex items-center gap-2">
                                    <input type="number" 
                                           name="cf_login_minutes" 
                                           id="cf_login_minutes" 
                                           value="{{ old('cf_login_minutes', $config->cf_login_minutes) }}" 
                                           required
                                           class="px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <span class="text-sm">분</span>
                                </div>
                                <p class="text-xs text-muted-foreground">설정값 이내의 접속자를 현재 접속자로 인정</p>
                            </div>

                            <div class="space-y-2">
                                <label for="cf_new_rows" class="text-sm font-medium">
                                    최근게시물 라인수 <span class="text-destructive">*</span>
                                </label>
                                <div class="flex items-center gap-2">
                                    <input type="number" 
                                           name="cf_new_rows" 
                                           id="cf_new_rows" 
                                           value="{{ old('cf_new_rows', $config->cf_new_rows) }}" 
                                           required
                                           class="px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <span class="text-sm">라인</span>
                                </div>
                                <p class="text-xs text-muted-foreground">목록 한페이지당 라인수</p>
                            </div>

                            <div class="space-y-2">
                                <label for="cf_page_rows" class="text-sm font-medium">
                                    한페이지당 라인수 <span class="text-destructive">*</span>
                                </label>
                                <div class="flex items-center gap-2">
                                    <input type="number" 
                                           name="cf_page_rows" 
                                           id="cf_page_rows" 
                                           value="{{ old('cf_page_rows', $config->cf_page_rows) }}" 
                                           required
                                           class="px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <span class="text-sm">라인</span>
                                </div>
                                <p class="text-xs text-muted-foreground">목록(리스트) 한페이지당 라인수</p>
                            </div>

                            <div class="space-y-2">
                                <label for="cf_mobile_page_rows" class="text-sm font-medium">
                                    모바일 한페이지당 라인수 <span class="text-destructive">*</span>
                                </label>
                                <div class="flex items-center gap-2">
                                    <input type="number" 
                                           name="cf_mobile_page_rows" 
                                           id="cf_mobile_page_rows" 
                                           value="{{ old('cf_mobile_page_rows', $config->cf_mobile_page_rows) }}" 
                                           required
                                           class="px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <span class="text-sm">라인</span>
                                </div>
                                <p class="text-xs text-muted-foreground">모바일 목록 한페이지당 라인수</p>
                            </div>

                            <div class="space-y-2">
                                <label for="cf_write_pages" class="text-sm font-medium">
                                    페이지 표시 수 <span class="text-destructive">*</span>
                                </label>
                                <div class="flex items-center gap-2">
                                    <input type="number" 
                                           name="cf_write_pages" 
                                           id="cf_write_pages" 
                                           value="{{ old('cf_write_pages', $config->cf_write_pages) }}" 
                                           required
                                           class="px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <span class="text-sm">페이지씩 표시</span>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label for="cf_mobile_pages" class="text-sm font-medium">
                                    모바일 페이지 표시 수 <span class="text-destructive">*</span>
                                </label>
                                <div class="flex items-center gap-2">
                                    <input type="number" 
                                           name="cf_mobile_pages" 
                                           id="cf_mobile_pages" 
                                           value="{{ old('cf_mobile_pages', $config->cf_mobile_pages) }}" 
                                           required
                                           class="px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <span class="text-sm">페이지씩 표시</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Skin Settings -->
                    <div class="border-t pt-6">
                        <h4 class="font-medium mb-4">스킨 설정</h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label for="cf_new_skin" class="text-sm font-medium">
                                    최근게시물 스킨 <span class="text-destructive">*</span>
                                </label>
                                <input type="text" 
                                       name="cf_new_skin" 
                                       id="cf_new_skin" 
                                       value="{{ old('cf_new_skin', $config->cf_new_skin) }}" 
                                       required
                                       placeholder="basic"
                                       class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>

                            <div class="space-y-2">
                                <label for="cf_mobile_new_skin" class="text-sm font-medium">
                                    모바일 최근게시물 스킨 <span class="text-destructive">*</span>
                                </label>
                                <input type="text" 
                                       name="cf_mobile_new_skin" 
                                       id="cf_mobile_new_skin" 
                                       value="{{ old('cf_mobile_new_skin', $config->cf_mobile_new_skin) }}" 
                                       required
                                       placeholder="basic"
                                       class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>

                            <div class="space-y-2">
                                <label for="cf_search_skin" class="text-sm font-medium">
                                    검색 스킨 <span class="text-destructive">*</span>
                                </label>
                                <input type="text" 
                                       name="cf_search_skin" 
                                       id="cf_search_skin" 
                                       value="{{ old('cf_search_skin', $config->cf_search_skin) }}" 
                                       required
                                       placeholder="basic"
                                       class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>

                            <div class="space-y-2">
                                <label for="cf_mobile_search_skin" class="text-sm font-medium">
                                    모바일 검색 스킨 <span class="text-destructive">*</span>
                                </label>
                                <input type="text" 
                                       name="cf_mobile_search_skin" 
                                       id="cf_mobile_search_skin" 
                                       value="{{ old('cf_mobile_search_skin', $config->cf_mobile_search_skin) }}" 
                                       required
                                       placeholder="basic"
                                       class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>

                            <div class="space-y-2">
                                <label for="cf_faq_skin" class="text-sm font-medium">
                                    FAQ 스킨 <span class="text-destructive">*</span>
                                </label>
                                <input type="text" 
                                       name="cf_faq_skin" 
                                       id="cf_faq_skin" 
                                       value="{{ old('cf_faq_skin', $config->cf_faq_skin) }}" 
                                       required
                                       placeholder="basic"
                                       class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>

                            <div class="space-y-2">
                                <label for="cf_mobile_faq_skin" class="text-sm font-medium">
                                    모바일 FAQ 스킨 <span class="text-destructive">*</span>
                                </label>
                                <input type="text" 
                                       name="cf_mobile_faq_skin" 
                                       id="cf_mobile_faq_skin" 
                                       value="{{ old('cf_mobile_faq_skin', $config->cf_mobile_faq_skin) }}" 
                                       required
                                       placeholder="basic"
                                       class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                        </div>
                    </div>

                    <!-- Other Settings -->
                    <div class="border-t pt-6">
                        <h4 class="font-medium mb-4">기타 설정</h4>
                        
                        <div class="space-y-4">
                            <div class="flex items-center gap-2">
                                <input type="checkbox" 
                                       name="cf_use_copy_log" 
                                       id="cf_use_copy_log" 
                                       value="1" 
                                       {{ $config->cf_use_copy_log ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-primary focus:ring-primary">
                                <label for="cf_use_copy_log" class="text-sm font-medium">복사, 이동시 로그 남기기</label>
                                <span class="text-xs text-muted-foreground ml-2">(게시물 아래에 누구로 부터 복사, 이동됨 표시)</span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                                <div class="space-y-2">
                                    <label for="cf_analytics" class="text-sm font-medium">방문자분석 스크립트</label>
                                    <textarea name="cf_analytics" 
                                              id="cf_analytics" 
                                              rows="5"
                                              placeholder="구글 애널리틱스 등의 스크립트 코드를 입력합니다."
                                              class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">{{ old('cf_analytics', $config->cf_analytics) }}</textarea>
                                    <p class="text-xs text-muted-foreground">방문자분석 스크립트 코드를 입력합니다. 관리자 페이지에서는 이 코드를 사용하지 않습니다.</p>
                                </div>

                                <div class="space-y-2">
                                    <label for="cf_add_meta" class="text-sm font-medium">추가 메타태그</label>
                                    <textarea name="cf_add_meta" 
                                              id="cf_add_meta" 
                                              rows="5"
                                              placeholder="추가로 사용하실 meta 태그를 입력합니다."
                                              class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">{{ old('cf_add_meta', $config->cf_add_meta) }}</textarea>
                                    <p class="text-xs text-muted-foreground">추가로 사용하실 meta 태그를 입력합니다. 관리자 페이지에서는 이 코드를 사용하지 않습니다.</p>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label for="cf_add_script" class="text-sm font-medium">추가 스크립트</label>
                                <textarea name="cf_add_script" 
                                          id="cf_add_script" 
                                          rows="5"
                                          placeholder="추가로 사용하실 스크립트를 입력합니다."
                                          class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">{{ old('cf_add_script', $config->cf_add_script) }}</textarea>
                                <p class="text-xs text-muted-foreground">body 태그 끝나기 전에 출력하는 스크립트입니다. 관리자 페이지에서는 이 코드를 사용하지 않습니다.</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                                <div class="space-y-2">
                                    <label for="cf_syndi_token" class="text-sm font-medium">네이버 신디케이션 연동키</label>
                                    <input type="text" 
                                           name="cf_syndi_token" 
                                           id="cf_syndi_token" 
                                           value="{{ old('cf_syndi_token', $config->cf_syndi_token) }}" 
                                           class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <p class="text-xs text-muted-foreground">네이버 신디케이션 연동키(token)를 입력하면 네이버 신디케이션을 사용할 수 있습니다.</p>
                                </div>

                                <div class="space-y-2">
                                    <label for="cf_syndi_except" class="text-sm font-medium">네이버 신디케이션 제외게시판</label>
                                    <input type="text" 
                                           name="cf_syndi_except" 
                                           id="cf_syndi_except" 
                                           value="{{ old('cf_syndi_except', $config->cf_syndi_except) }}" 
                                           placeholder="notice|adult"
                                           class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <p class="text-xs text-muted-foreground">네이버 신디케이션 수집에서 제외할 게시판 아이디를 | 로 구분하여 입력하십시오.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Board Settings -->
            <div x-show="activeTab === 'board'" x-transition>
                <h3 class="text-lg font-semibold mb-6">게시판 기본 설정</h3>
                <p class="text-sm text-muted-foreground mb-6">각 게시판 관리에서 개별적으로 설정 가능합니다.</p>
                
                <div class="space-y-6">
                    <!-- 글쓰기 및 링크 설정 -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label for="cf_delay_sec" class="text-sm font-medium">
                                글쓰기 간격 <span class="text-destructive">*</span>
                            </label>
                            <div class="flex items-center gap-2">
                                <input type="number" 
                                       name="cf_delay_sec" 
                                       id="cf_delay_sec" 
                                       value="{{ old('cf_delay_sec', $config->cf_delay_sec) }}" 
                                       required
                                       class="px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                <span class="text-sm">초 지난후 가능</span>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label for="cf_link_target" class="text-sm font-medium">
                                새창 링크 <span class="text-destructive">*</span>
                            </label>
                            <select name="cf_link_target" 
                                    id="cf_link_target"
                                    required
                                    class="px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="_blank" {{ $config->cf_link_target == '_blank' ? 'selected' : '' }}>_blank</option>
                                <option value="_self" {{ $config->cf_link_target == '_self' ? 'selected' : '' }}>_self</option>
                                <option value="_top" {{ $config->cf_link_target == '_top' ? 'selected' : '' }}>_top</option>
                                <option value="_new" {{ $config->cf_link_target == '_new' ? 'selected' : '' }}>_new</option>
                            </select>
                            <p class="text-xs text-muted-foreground">글내용중 자동 링크되는 타겟을 지정합니다.</p>
                        </div>
                    </div>

                    <!-- 포인트 설정 -->
                    <div class="border-t pt-6">
                        <h4 class="font-medium mb-4">포인트 설정</h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label for="cf_read_point" class="text-sm font-medium">
                                    글읽기 포인트 <span class="text-destructive">*</span>
                                </label>
                                <div class="flex items-center gap-2">
                                    <input type="number" 
                                           name="cf_read_point" 
                                           id="cf_read_point" 
                                           value="{{ old('cf_read_point', $config->cf_read_point) }}" 
                                           required
                                           class="px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <span class="text-sm">점</span>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label for="cf_write_point" class="text-sm font-medium">
                                    글쓰기 포인트 <span class="text-destructive">*</span>
                                </label>
                                <div class="flex items-center gap-2">
                                    <input type="number" 
                                           name="cf_write_point" 
                                           id="cf_write_point" 
                                           value="{{ old('cf_write_point', $config->cf_write_point) }}" 
                                           required
                                           class="px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <span class="text-sm">점</span>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label for="cf_comment_point" class="text-sm font-medium">
                                    댓글쓰기 포인트 <span class="text-destructive">*</span>
                                </label>
                                <div class="flex items-center gap-2">
                                    <input type="number" 
                                           name="cf_comment_point" 
                                           id="cf_comment_point" 
                                           value="{{ old('cf_comment_point', $config->cf_comment_point) }}" 
                                           required
                                           class="px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <span class="text-sm">점</span>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label for="cf_download_point" class="text-sm font-medium">
                                    다운로드 포인트 <span class="text-destructive">*</span>
                                </label>
                                <div class="flex items-center gap-2">
                                    <input type="number" 
                                           name="cf_download_point" 
                                           id="cf_download_point" 
                                           value="{{ old('cf_download_point', $config->cf_download_point) }}" 
                                           required
                                           class="px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <span class="text-sm">점</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 검색 설정 -->
                    <div class="border-t pt-6">
                        <div class="space-y-2">
                            <label for="cf_search_part" class="text-sm font-medium">
                                검색 단위 <span class="text-destructive">*</span>
                            </label>
                            <div class="flex items-center gap-2">
                                <input type="number" 
                                       name="cf_search_part" 
                                       id="cf_search_part" 
                                       value="{{ old('cf_search_part', $config->cf_search_part) }}" 
                                       required
                                       class="px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                <span class="text-sm">건 단위로 검색</span>
                            </div>
                        </div>
                    </div>

                    <!-- 파일 업로드 설정 -->
                    <div class="border-t pt-6">
                        <h4 class="font-medium mb-4">파일 업로드 설정</h4>
                        
                        <div class="space-y-6">
                            <div class="space-y-2">
                                <label for="cf_image_extension" class="text-sm font-medium">이미지 업로드 확장자</label>
                                <input type="text" 
                                       name="cf_image_extension" 
                                       id="cf_image_extension" 
                                       value="{{ old('cf_image_extension', $config->cf_image_extension) }}" 
                                       placeholder="jpg|jpeg|gif|png|webp"
                                       class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                <p class="text-xs text-muted-foreground">게시판 글작성시 이미지 파일 업로드 가능 확장자. | 로 구분</p>
                            </div>

                            <div class="space-y-2">
                                <label for="cf_flash_extension" class="text-sm font-medium">플래쉬 업로드 확장자</label>
                                <input type="text" 
                                       name="cf_flash_extension" 
                                       id="cf_flash_extension" 
                                       value="{{ old('cf_flash_extension', $config->cf_flash_extension) }}" 
                                       placeholder="swf"
                                       class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                <p class="text-xs text-muted-foreground">게시판 글작성시 플래쉬 파일 업로드 가능 확장자. | 로 구분</p>
                            </div>

                            <div class="space-y-2">
                                <label for="cf_movie_extension" class="text-sm font-medium">동영상 업로드 확장자</label>
                                <input type="text" 
                                       name="cf_movie_extension" 
                                       id="cf_movie_extension" 
                                       value="{{ old('cf_movie_extension', $config->cf_movie_extension) }}" 
                                       placeholder="avi|mp4|wmv|webm|mpg|mpeg|mov|flv|mkv"
                                       class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                <p class="text-xs text-muted-foreground">게시판 글작성시 동영상 파일 업로드 가능 확장자. | 로 구분</p>
                            </div>
                        </div>
                    </div>

                    <!-- 필터링 설정 -->
                    <div class="border-t pt-6">
                        <h4 class="font-medium mb-4">필터링 설정</h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label for="cf_filter" class="text-sm font-medium">단어 필터링</label>
                                <textarea name="cf_filter" 
                                          id="cf_filter" 
                                          rows="7"
                                          class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">{{ old('cf_filter', $config->cf_filter) }}</textarea>
                                <p class="text-xs text-muted-foreground">입력된 단어가 포함된 내용은 게시할 수 없습니다. 단어와 단어 사이는 ,로 구분하세요.</p>
                            </div>

                            <div class="space-y-6">
                                <div class="space-y-2">
                                    <label for="cf_possible_ip" class="text-sm font-medium">접근가능 IP</label>
                                    <textarea name="cf_possible_ip" 
                                              id="cf_possible_ip" 
                                              rows="3"
                                              class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">{{ old('cf_possible_ip', $config->cf_possible_ip) }}</textarea>
                                    <p class="text-xs text-muted-foreground">입력된 IP의 컴퓨터만 접근할 수 있습니다. 123.123.+ 도 입력 가능. (엔터로 구분)</p>
                                </div>

                                <div class="space-y-2">
                                    <label for="cf_intercept_ip" class="text-sm font-medium">접근차단 IP</label>
                                    <textarea name="cf_intercept_ip" 
                                              id="cf_intercept_ip" 
                                              rows="3"
                                              class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">{{ old('cf_intercept_ip', $config->cf_intercept_ip) }}</textarea>
                                    <p class="text-xs text-muted-foreground">입력된 IP의 컴퓨터는 접근할 수 없습니다. 123.123.+ 도 입력 가능. (엔터로 구분)</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Member Settings -->
            <div x-show="activeTab === 'member'" x-transition>
                <h3 class="text-lg font-semibold mb-6">회원가입 설정</h3>
                
                <div class="space-y-6">
                    <!-- 회원가입 정보 설정 -->
                    <div>
                        <h4 class="font-medium mb-4">회원가입 정보</h4>
                        <table class="w-full border rounded-lg">
                            <tbody class="divide-y">
                                <tr>
                                    <th scope="row" class="px-4 py-3 bg-muted/50 text-left text-sm font-medium w-1/4">홈페이지 입력</th>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-4">
                                            <label class="flex items-center gap-2">
                                                <input type="checkbox" name="cf_use_homepage" value="1" id="cf_use_homepage" 
                                                       {{ $config->cf_use_homepage ? 'checked' : '' }}
                                                       class="rounded border-gray-300 text-primary focus:ring-primary">
                                                <span class="text-sm">보이기</span>
                                            </label>
                                            <label class="flex items-center gap-2">
                                                <input type="checkbox" name="cf_req_homepage" value="1" id="cf_req_homepage" 
                                                       {{ $config->cf_req_homepage ? 'checked' : '' }}
                                                       class="rounded border-gray-300 text-primary focus:ring-primary">
                                                <span class="text-sm">필수입력</span>
                                            </label>
                                        </div>
                                    </td>
                                    <th scope="row" class="px-4 py-3 bg-muted/50 text-left text-sm font-medium w-1/4">주소 입력</th>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-4">
                                            <label class="flex items-center gap-2">
                                                <input type="checkbox" name="cf_use_addr" value="1" id="cf_use_addr" 
                                                       {{ $config->cf_use_addr ? 'checked' : '' }}
                                                       class="rounded border-gray-300 text-primary focus:ring-primary">
                                                <span class="text-sm">보이기</span>
                                            </label>
                                            <label class="flex items-center gap-2">
                                                <input type="checkbox" name="cf_req_addr" value="1" id="cf_req_addr" 
                                                       {{ $config->cf_req_addr ? 'checked' : '' }}
                                                       class="rounded border-gray-300 text-primary focus:ring-primary">
                                                <span class="text-sm">필수입력</span>
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row" class="px-4 py-3 bg-muted/50 text-left text-sm font-medium">전화번호 입력</th>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-4">
                                            <label class="flex items-center gap-2">
                                                <input type="checkbox" name="cf_use_tel" value="1" id="cf_use_tel" 
                                                       {{ $config->cf_use_tel ? 'checked' : '' }}
                                                       class="rounded border-gray-300 text-primary focus:ring-primary">
                                                <span class="text-sm">보이기</span>
                                            </label>
                                            <label class="flex items-center gap-2">
                                                <input type="checkbox" name="cf_req_tel" value="1" id="cf_req_tel" 
                                                       {{ $config->cf_req_tel ? 'checked' : '' }}
                                                       class="rounded border-gray-300 text-primary focus:ring-primary">
                                                <span class="text-sm">필수입력</span>
                                            </label>
                                        </div>
                                    </td>
                                    <th scope="row" class="px-4 py-3 bg-muted/50 text-left text-sm font-medium">휴대폰번호 입력</th>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-4">
                                            <label class="flex items-center gap-2">
                                                <input type="checkbox" name="cf_use_hp" value="1" id="cf_use_hp" 
                                                       {{ $config->cf_use_hp ? 'checked' : '' }}
                                                       class="rounded border-gray-300 text-primary focus:ring-primary">
                                                <span class="text-sm">보이기</span>
                                            </label>
                                            <label class="flex items-center gap-2">
                                                <input type="checkbox" name="cf_req_hp" value="1" id="cf_req_hp" 
                                                       {{ $config->cf_req_hp ? 'checked' : '' }}
                                                       class="rounded border-gray-300 text-primary focus:ring-primary">
                                                <span class="text-sm">필수입력</span>
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row" class="px-4 py-3 bg-muted/50 text-left text-sm font-medium">서명 입력</th>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-4">
                                            <label class="flex items-center gap-2">
                                                <input type="checkbox" name="cf_use_signature" value="1" id="cf_use_signature" 
                                                       {{ $config->cf_use_signature ? 'checked' : '' }}
                                                       class="rounded border-gray-300 text-primary focus:ring-primary">
                                                <span class="text-sm">보이기</span>
                                            </label>
                                            <label class="flex items-center gap-2">
                                                <input type="checkbox" name="cf_req_signature" value="1" id="cf_req_signature" 
                                                       {{ $config->cf_req_signature ? 'checked' : '' }}
                                                       class="rounded border-gray-300 text-primary focus:ring-primary">
                                                <span class="text-sm">필수입력</span>
                                            </label>
                                        </div>
                                    </td>
                                    <th scope="row" class="px-4 py-3 bg-muted/50 text-left text-sm font-medium">자기소개 입력</th>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-4">
                                            <label class="flex items-center gap-2">
                                                <input type="checkbox" name="cf_use_profile" value="1" id="cf_use_profile" 
                                                       {{ $config->cf_use_profile ? 'checked' : '' }}
                                                       class="rounded border-gray-300 text-primary focus:ring-primary">
                                                <span class="text-sm">보이기</span>
                                            </label>
                                            <label class="flex items-center gap-2">
                                                <input type="checkbox" name="cf_req_profile" value="1" id="cf_req_profile" 
                                                       {{ $config->cf_req_profile ? 'checked' : '' }}
                                                       class="rounded border-gray-300 text-primary focus:ring-primary">
                                                <span class="text-sm">필수입력</span>
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Register Settings -->
                    <div class="border-t pt-6">
                        <h4 class="font-medium mb-4">회원가입 설정</h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label for="cf_register_level" class="text-sm font-medium">
                                    회원가입시 권한 <span class="text-destructive">*</span>
                                </label>
                                <input type="number" 
                                       name="cf_register_level" 
                                       id="cf_register_level" 
                                       value="{{ old('cf_register_level', $config->cf_register_level) }}" 
                                       min="1"
                                       max="10"
                                       required
                                       class="px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>

                            <div class="space-y-2">
                                <label for="cf_register_point" class="text-sm font-medium">
                                    회원가입시 포인트 <span class="text-destructive">*</span>
                                </label>
                                <div class="flex items-center gap-2">
                                    <input type="number" 
                                           name="cf_register_point" 
                                           id="cf_register_point" 
                                           value="{{ old('cf_register_point', $config->cf_register_point) }}" 
                                           required
                                           class="px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <span class="text-sm">점</span>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label for="cf_leave_day" class="text-sm font-medium">
                                    회원탈퇴후 자료보존일 <span class="text-destructive">*</span>
                                </label>
                                <div class="flex items-center gap-2">
                                    <input type="number" 
                                           name="cf_leave_day" 
                                           id="cf_leave_day" 
                                           value="{{ old('cf_leave_day', $config->cf_leave_day) }}" 
                                           required
                                           class="px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <span class="text-sm">일</span>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label for="cf_recommend_point" class="text-sm font-medium">추천인 포인트</label>
                                <div class="flex items-center gap-2">
                                    <input type="number" 
                                           name="cf_recommend_point" 
                                           id="cf_recommend_point" 
                                           value="{{ old('cf_recommend_point', $config->cf_recommend_point) }}" 
                                           class="px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <span class="text-sm">점</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Icon Settings -->
                    <div class="border-t pt-6">
                        <h4 class="font-medium mb-4">회원아이콘 설정</h4>
                        
                        <div class="space-y-4">
                            <div class="space-y-2">
                                <label for="cf_use_member_icon" class="text-sm font-medium">
                                    회원아이콘 사용 <span class="text-destructive">*</span>
                                </label>
                                <select name="cf_use_member_icon" 
                                        id="cf_use_member_icon"
                                        required
                                        class="px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <option value="0" {{ $config->cf_use_member_icon == 0 ? 'selected' : '' }}>미사용</option>
                                    <option value="1" {{ $config->cf_use_member_icon == 1 ? 'selected' : '' }}>아이콘만 표시</option>
                                    <option value="2" {{ $config->cf_use_member_icon == 2 ? 'selected' : '' }}>아이콘+이름 표시</option>
                                </select>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="space-y-2">
                                    <label for="cf_icon_level" class="text-sm font-medium">
                                        아이콘 업로드 권한 <span class="text-destructive">*</span>
                                    </label>
                                    <input type="number" 
                                           name="cf_icon_level" 
                                           id="cf_icon_level" 
                                           value="{{ old('cf_icon_level', $config->cf_icon_level) }}" 
                                           min="1"
                                           max="10"
                                           required
                                           class="px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                </div>

                                <div class="space-y-2">
                                    <label for="cf_member_icon_size" class="text-sm font-medium">
                                        회원아이콘 용량 <span class="text-destructive">*</span>
                                    </label>
                                    <div class="flex items-center gap-2">
                                        <input type="number" 
                                               name="cf_member_icon_size" 
                                               id="cf_member_icon_size" 
                                               value="{{ old('cf_member_icon_size', $config->cf_member_icon_size) }}" 
                                               required
                                               class="px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                        <span class="text-sm">바이트</span>
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <label class="text-sm font-medium">
                                        회원아이콘 크기 <span class="text-destructive">*</span>
                                    </label>
                                    <div class="flex items-center gap-2">
                                        <input type="number" 
                                               name="cf_member_icon_width" 
                                               value="{{ old('cf_member_icon_width', $config->cf_member_icon_width) }}" 
                                               placeholder="가로"
                                               required
                                               class="w-20 px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                        <span class="text-sm">x</span>
                                        <input type="number" 
                                               name="cf_member_icon_height" 
                                               value="{{ old('cf_member_icon_height', $config->cf_member_icon_height) }}" 
                                               placeholder="세로"
                                               required
                                               class="w-20 px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                        <span class="text-sm">픽셀</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Member Image Settings -->
                    <div class="border-t pt-6">
                        <h4 class="font-medium mb-4">회원이미지 설정</h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label for="cf_member_img_size" class="text-sm font-medium">
                                    회원이미지 용량 <span class="text-destructive">*</span>
                                </label>
                                <div class="flex items-center gap-2">
                                    <input type="number" 
                                           name="cf_member_img_size" 
                                           id="cf_member_img_size" 
                                           value="{{ old('cf_member_img_size', $config->cf_member_img_size) }}" 
                                           required
                                           class="px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <span class="text-sm">바이트</span>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="text-sm font-medium">
                                    회원이미지 크기 <span class="text-destructive">*</span>
                                </label>
                                <div class="flex items-center gap-2">
                                    <input type="number" 
                                           name="cf_member_img_width" 
                                           value="{{ old('cf_member_img_width', $config->cf_member_img_width) }}" 
                                           placeholder="가로"
                                           required
                                           class="w-20 px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <span class="text-sm">x</span>
                                    <input type="number" 
                                           name="cf_member_img_height" 
                                           value="{{ old('cf_member_img_height', $config->cf_member_img_height) }}" 
                                           placeholder="세로"
                                           required
                                           class="w-20 px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <span class="text-sm">픽셀</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Prohibit Settings -->
                    <div class="border-t pt-6">
                        <h4 class="font-medium mb-4">금지 설정</h4>
                        
                        <div class="space-y-6">
                            <div class="space-y-2">
                                <label for="cf_prohibit_id" class="text-sm font-medium">아이디 금지단어</label>
                                <textarea name="cf_prohibit_id" 
                                          id="cf_prohibit_id" 
                                          rows="5"
                                          class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">{{ old('cf_prohibit_id', $config->cf_prohibit_id) }}</textarea>
                                <p class="text-xs text-muted-foreground">회원아이디로 사용할 수 없는 단어를 정합니다. 쉼표(,)로 구분</p>
                            </div>

                            <div class="space-y-2">
                                <label for="cf_prohibit_email" class="text-sm font-medium">입력 금지 메일</label>
                                <textarea name="cf_prohibit_email" 
                                          id="cf_prohibit_email" 
                                          rows="5"
                                          class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">{{ old('cf_prohibit_email', $config->cf_prohibit_email) }}</textarea>
                                <p class="text-xs text-muted-foreground">입력 받지 않을 도메인을 지정합니다. 엔터로 구분 ex) hotmail.com</p>
                            </div>
                        </div>
                    </div>

                    <!-- Terms Settings -->
                    <div class="border-t pt-6">
                        <h4 class="font-medium mb-4">약관 설정</h4>
                        
                        <div class="space-y-6">
                            <div class="space-y-2">
                                <label for="cf_stipulation" class="text-sm font-medium">회원가입약관</label>
                                <textarea name="cf_stipulation" 
                                          id="cf_stipulation" 
                                          rows="10"
                                          class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent font-mono text-xs">{{ old('cf_stipulation', $config->cf_stipulation) }}</textarea>
                            </div>

                            <div class="space-y-2">
                                <label for="cf_privacy" class="text-sm font-medium">개인정보처리방침</label>
                                <textarea name="cf_privacy" 
                                          id="cf_privacy" 
                                          rows="10"
                                          class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent font-mono text-xs">{{ old('cf_privacy', $config->cf_privacy) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mail Settings -->
            <div x-show="activeTab === 'mail'" x-transition>
                <h3 class="text-lg font-semibold mb-6">메일 설정</h3>
                
                <div class="space-y-6">
                    <!-- Basic Mail Settings -->
                    <div>
                        <h4 class="font-medium mb-4">기본 메일 환경</h4>
                        
                        <div class="space-y-6">
                            <div>
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" 
                                           name="cf_email_use" 
                                           id="cf_email_use" 
                                           value="1" 
                                           {{ $config->cf_email_use ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-primary focus:ring-primary">
                                    <label for="cf_email_use" class="text-sm font-medium">메일발송 사용</label>
                                </div>
                                <p class="text-xs text-muted-foreground ml-6 mt-1">체크하지 않으면 메일발송을 아예 사용하지 않습니다. 메일 테스트도 불가합니다.</p>
                            </div>

                            <div>
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" 
                                           name="cf_use_email_certify" 
                                           id="cf_use_email_certify" 
                                           value="1" 
                                           {{ $config->cf_use_email_certify ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-primary focus:ring-primary">
                                    <label for="cf_use_email_certify" class="text-sm font-medium">메일인증 사용</label>
                                </div>
                                <p class="text-xs text-muted-foreground ml-6 mt-1">
                                    메일에 배달된 인증 주소를 클릭하여야 회원으로 인정합니다.<br>
                                    (SNS를 이용한 소셜로그인 한 회원은 회원메일인증을 하지 않습니다. 일반회원에게만 해당됩니다.)
                                </p>
                            </div>

                            <div>
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" 
                                           name="cf_formmail_is_member" 
                                           id="cf_formmail_is_member" 
                                           value="1" 
                                           {{ $config->cf_formmail_is_member ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-primary focus:ring-primary">
                                    <label for="cf_formmail_is_member" class="text-sm font-medium">폼메일 사용 여부</label>
                                </div>
                                <p class="text-xs text-muted-foreground ml-6 mt-1">체크하지 않으면 비회원도 사용 할 수 있습니다.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Write Mail Settings -->
                    <div class="border-t pt-6">
                        <h4 class="font-medium mb-4">게시판 글 작성시 메일 발송</h4>

                        <p class="text-xs text-muted-foreground mb-4">게시판 글 작성시 체크한 사람들에게 모두 메일을 발송합니다.</p>
                        
                        <div class="space-y-4">
                            <div>
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" 
                                           name="cf_email_wr_super_admin" 
                                           id="cf_email_wr_super_admin" 
                                           value="1" 
                                           {{ $config->cf_email_wr_super_admin ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-primary focus:ring-primary">
                                    <label for="cf_email_wr_super_admin" class="text-sm font-medium">최고관리자</label>
                                </div>
                                <p class="text-xs text-muted-foreground ml-6 mt-1">최고관리자에게 메일을 발송합니다.</p>
                            </div>

                            <div>
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" 
                                           name="cf_email_wr_group_admin" 
                                           id="cf_email_wr_group_admin" 
                                           value="1" 
                                           {{ $config->cf_email_wr_group_admin ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-primary focus:ring-primary">
                                    <label for="cf_email_wr_group_admin" class="text-sm font-medium">그룹관리자</label>
                                </div>
                                <p class="text-xs text-muted-foreground ml-6 mt-1">그룹관리자에게 메일을 발송합니다.</p>
                            </div>

                            <div>
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" 
                                           name="cf_email_wr_board_admin" 
                                           id="cf_email_wr_board_admin" 
                                           value="1" 
                                           {{ $config->cf_email_wr_board_admin ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-primary focus:ring-primary">
                                    <label for="cf_email_wr_board_admin" class="text-sm font-medium">게시판관리자</label>
                                </div>
                                <p class="text-xs text-muted-foreground ml-6 mt-1">게시판관리자에게 메일을 발송합니다.</p>
                            </div>

                            <div>
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" 
                                           name="cf_email_wr_write" 
                                           id="cf_email_wr_write" 
                                           value="1" 
                                           {{ $config->cf_email_wr_write ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-primary focus:ring-primary">
                                    <label for="cf_email_wr_write" class="text-sm font-medium">원글작성자</label>
                                </div>
                                <p class="text-xs text-muted-foreground ml-6 mt-1">게시자님께 메일을 발송합니다.</p>
                            </div>

                            <div>
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" 
                                           name="cf_email_wr_comment_all" 
                                           id="cf_email_wr_comment_all" 
                                           value="1" 
                                           {{ $config->cf_email_wr_comment_all ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-primary focus:ring-primary">
                                    <label for="cf_email_wr_comment_all" class="text-sm font-medium">댓글작성자</label>
                                </div>
                                <p class="text-xs text-muted-foreground ml-6 mt-1">원글에 댓글이 올라오는 경우 댓글 쓴 모든 분들께 메일을 발송합니다.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Member Mail Settings -->
                    <div class="border-t pt-6">
                        <h4 class="font-medium mb-4">회원가입 시 메일 설정</h4>
                        
                        <div class="space-y-3">
                            <div class="flex items-center gap-2">
                                <input type="checkbox" 
                                       name="cf_email_mb_super_admin" 
                                       id="cf_email_mb_super_admin" 
                                       value="1" 
                                       {{ $config->cf_email_mb_super_admin ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-primary focus:ring-primary">
                                <label for="cf_email_mb_super_admin" class="text-sm">최고관리자 메일발송</label>
                            </div>

                            <div class="flex items-center gap-2">
                                <input type="checkbox" 
                                       name="cf_email_mb_member" 
                                       id="cf_email_mb_member" 
                                       value="1" 
                                       {{ $config->cf_email_mb_member ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-primary focus:ring-primary">
                                <label for="cf_email_mb_member" class="text-sm">회원님께 메일발송</label>
                            </div>
                        </div>
                    </div>

                    <!-- Poll Mail Settings -->
                    <div class="border-t pt-6">
                        <h4 class="font-medium mb-4">투표 기타의견 작성시 메일 발송</h4>
                        <p class="text-sm text-muted-foreground mb-4">체크하신 경우 해당되는 사람에게 메일을 발송합니다.</p>
                        
                        <div class="flex items-center gap-2">
                            <input type="checkbox" 
                                   name="cf_email_po_super_admin" 
                                   id="cf_email_po_super_admin" 
                                   value="1" 
                                   {{ $config->cf_email_po_super_admin ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-primary focus:ring-primary">
                            <label for="cf_email_po_super_admin" class="text-sm">최고관리자</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SNS Settings -->
            <div x-show="activeTab === 'sns'" x-transition>
                <h3 class="text-lg font-semibold mb-6">SNS 설정</h3>
                
                <div class="space-y-6">
                    <!-- Captcha Settings -->
                    <div class="pb-6">
                        <h4 class="font-medium mb-4">캡차 설정</h4>
                        
                        <div class="space-y-4">
                            <div class="space-y-2">
                                <label for="cf_captcha" class="text-sm font-medium">
                                    캡차 사용 <span class="text-destructive">*</span>
                                </label>
                                <select name="cf_captcha" 
                                        id="cf_captcha"
                                        required
                                        class="px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <option value="kcaptcha" {{ $config->cf_captcha == 'kcaptcha' ? 'selected' : '' }}>Kcaptcha</option>
                                    <option value="recaptcha" {{ $config->cf_captcha == 'recaptcha' ? 'selected' : '' }}>reCAPTCHA V2</option>
                                    <option value="recaptcha_inv" {{ $config->cf_captcha == 'recaptcha_inv' ? 'selected' : '' }}>Invisible reCAPTCHA</option>
                                </select>
                                <p class="text-xs text-muted-foreground">
                                    1) Kcaptcha 는 그누보드5의 기본캡챠입니다. ( 문자입력 )<br>
                                    2) reCAPTCHA V2 는 구글에서 서비스하는 원클릭 형식의 간편한 캡챠입니다. ( 모바일 친화적 UI )<br>
                                    3) Invisible reCAPTCHA 는 구글에서 서비스하는 안보이는 형식의 캡챠입니다. ( 간혹 퀴즈를 풀어야 합니다. )
                                </p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label for="cf_recaptcha_site_key" class="text-sm font-medium">구글 reCAPTCHA Site key</label>
                                    <input type="text" 
                                           name="cf_recaptcha_site_key" 
                                           id="cf_recaptcha_site_key" 
                                           value="{{ old('cf_recaptcha_site_key', $config->cf_recaptcha_site_key) }}" 
                                           class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <p class="text-xs text-muted-foreground">reCAPTCHA V2와 Invisible reCAPTCHA 캡챠의 sitekey 와 secret 키는 동일하지 않고, 서로 발급받는 키가 다릅니다.</p>
                                </div>

                                <div class="space-y-2">
                                    <label for="cf_recaptcha_secret_key" class="text-sm font-medium">구글 reCAPTCHA Secret key</label>
                                    <input type="text" 
                                           name="cf_recaptcha_secret_key" 
                                           id="cf_recaptcha_secret_key" 
                                           value="{{ old('cf_recaptcha_secret_key', $config->cf_recaptcha_secret_key) }}" 
                                           class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Social Login Settings -->
                    <div class="border-t pt-6">
                        <h4 class="font-medium mb-4">소셜 로그인 설정</h4>
                        
                        <div class="space-y-4">
                            <div class="flex items-center gap-2">
                                <input type="checkbox" 
                                       name="cf_social_login_use" 
                                       id="cf_social_login_use" 
                                       value="1" 
                                       {{ $config->cf_social_login_use ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-primary focus:ring-primary">
                                <label for="cf_social_login_use" class="text-sm font-medium">소셜 로그인 사용</label>
                            </div>

                            <!-- Facebook -->
                            <div class="space-y-4">
                                <h5 class="text-sm font-medium">Facebook</h5>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pl-4">
                                    <div class="space-y-2">
                                        <label for="cf_facebook_appid" class="text-sm">App ID</label>
                                        <input type="text" 
                                               name="cf_facebook_appid" 
                                               id="cf_facebook_appid" 
                                               value="{{ old('cf_facebook_appid', $config->cf_facebook_appid) }}" 
                                               class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    </div>
                                    <div class="space-y-2">
                                        <label for="cf_facebook_secret" class="text-sm">App Secret</label>
                                        <input type="text" 
                                               name="cf_facebook_secret" 
                                               id="cf_facebook_secret" 
                                               value="{{ old('cf_facebook_secret', $config->cf_facebook_secret) }}" 
                                               class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    </div>
                                </div>
                            </div>

                            <!-- Google -->
                            <div class="space-y-4">
                                <h5 class="text-sm font-medium">Google</h5>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pl-4">
                                    <div class="space-y-2">
                                        <label for="cf_google_clientid" class="text-sm">Client ID</label>
                                        <input type="text" 
                                               name="cf_google_clientid" 
                                               id="cf_google_clientid" 
                                               value="{{ old('cf_google_clientid', $config->cf_google_clientid) }}" 
                                               class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    </div>
                                    <div class="space-y-2">
                                        <label for="cf_google_secret" class="text-sm">Client Secret</label>
                                        <input type="text" 
                                               name="cf_google_secret" 
                                               id="cf_google_secret" 
                                               value="{{ old('cf_google_secret', $config->cf_google_secret) }}" 
                                               class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    </div>
                                </div>
                            </div>

                            <!-- Naver -->
                            <div class="space-y-4">
                                <h5 class="text-sm font-medium">Naver</h5>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pl-4">
                                    <div class="space-y-2">
                                        <label for="cf_naver_clientid" class="text-sm">Client ID</label>
                                        <input type="text" 
                                               name="cf_naver_clientid" 
                                               id="cf_naver_clientid" 
                                               value="{{ old('cf_naver_clientid', $config->cf_naver_clientid) }}" 
                                               class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    </div>
                                    <div class="space-y-2">
                                        <label for="cf_naver_secret" class="text-sm">Client Secret</label>
                                        <input type="text" 
                                               name="cf_naver_secret" 
                                               id="cf_naver_secret" 
                                               value="{{ old('cf_naver_secret', $config->cf_naver_secret) }}" 
                                               class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    </div>
                                </div>
                            </div>

                            <!-- Kakao -->
                            <div class="space-y-4">
                                <h5 class="text-sm font-medium">Kakao</h5>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pl-4">
                                    <div class="space-y-2">
                                        <label for="cf_kakao_rest_key" class="text-sm">REST API Key</label>
                                        <input type="text" 
                                               name="cf_kakao_rest_key" 
                                               id="cf_kakao_rest_key" 
                                               value="{{ old('cf_kakao_rest_key', $config->cf_kakao_rest_key) }}" 
                                               class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    </div>
                                    <div class="space-y-2">
                                        <label for="cf_kakao_client_secret" class="text-sm">Client Secret</label>
                                        <input type="text" 
                                               name="cf_kakao_client_secret" 
                                               id="cf_kakao_client_secret" 
                                               value="{{ old('cf_kakao_client_secret', $config->cf_kakao_client_secret) }}" 
                                               class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    </div>
                                    <div class="space-y-2">
                                        <label for="cf_kakao_js_apikey" class="text-sm">JavaScript Key</label>
                                        <input type="text" 
                                               name="cf_kakao_js_apikey" 
                                               id="cf_kakao_js_apikey" 
                                               value="{{ old('cf_kakao_js_apikey', $config->cf_kakao_js_apikey) }}" 
                                               class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    </div>
                                </div>
                            </div>

                            <!-- Twitter -->
                            <div class="space-y-4">
                                <h5 class="text-sm font-medium">Twitter</h5>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pl-4">
                                    <div class="space-y-2">
                                        <label for="cf_twitter_key" class="text-sm">Consumer Key</label>
                                        <input type="text" 
                                               name="cf_twitter_key" 
                                               id="cf_twitter_key" 
                                               value="{{ old('cf_twitter_key', $config->cf_twitter_key) }}" 
                                               class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    </div>
                                    <div class="space-y-2">
                                        <label for="cf_twitter_secret" class="text-sm">Consumer Secret</label>
                                        <input type="text" 
                                               name="cf_twitter_secret" 
                                               id="cf_twitter_secret" 
                                               value="{{ old('cf_twitter_secret', $config->cf_twitter_secret) }}" 
                                               class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Certification Settings -->
            <div x-show="activeTab === 'cert'" x-transition>
                <h3 class="text-lg font-semibold mb-6">본인확인 설정</h3>
                
                <div class="space-y-6">
                    <!-- 본인확인 사용 -->
                    <div>
                        <h4 class="font-medium mb-4">본인확인 서비스</h4>
                        
                        <div class="space-y-4">
                            <div class="flex items-center gap-2">
                                <input type="checkbox" 
                                       name="cf_cert_use" 
                                       id="cf_cert_use" 
                                       value="1" 
                                       {{ $config->cf_cert_use ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-primary focus:ring-primary">
                                <label for="cf_cert_use" class="text-sm font-medium">본인확인 사용</label>
                            </div>

                            <div class="flex items-center gap-2">
                                <input type="checkbox" 
                                       name="cf_cert_find" 
                                       id="cf_cert_find" 
                                       value="1" 
                                       {{ $config->cf_cert_find ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-primary focus:ring-primary">
                                <label for="cf_cert_find" class="text-sm font-medium">아이디/비밀번호 찾기에 사용</label>
                            </div>

                            <div class="flex items-center gap-2">
                                <input type="checkbox" 
                                       name="cf_cert_req" 
                                       id="cf_cert_req" 
                                       value="1" 
                                       {{ $config->cf_cert_req ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-primary focus:ring-primary">
                                <label for="cf_cert_req" class="text-sm font-medium">회원가입시 본인확인 필수</label>
                            </div>
                        </div>
                    </div>

                    <!-- 본인확인 서비스 선택 -->
                    <div class="border-t pt-6">
                        <h4 class="font-medium mb-4">본인확인 서비스 선택</h4>
                        
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label for="cf_cert_ipin" class="text-sm font-medium">아이핀</label>
                                    <select name="cf_cert_ipin" 
                                            id="cf_cert_ipin"
                                            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                        <option value="">사용안함</option>
                                        <option value="kcb" {{ $config->cf_cert_ipin == 'kcb' ? 'selected' : '' }}>코리아크레딧뷰로(KCB)</option>
                                    </select>
                                </div>

                                <div class="space-y-2">
                                    <label for="cf_cert_hp" class="text-sm font-medium">휴대폰</label>
                                    <select name="cf_cert_hp" 
                                            id="cf_cert_hp"
                                            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                        <option value="">사용안함</option>
                                        <option value="kcp" {{ $config->cf_cert_hp == 'kcp' ? 'selected' : '' }}>NHN KCP</option>
                                        <option value="lg" {{ $config->cf_cert_hp == 'lg' ? 'selected' : '' }}>LG유플러스</option>
                                        <option value="inicis" {{ $config->cf_cert_hp == 'inicis' ? 'selected' : '' }}>KG이니시스</option>
                                    </select>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label for="cf_cert_limit" class="text-sm font-medium">
                                    본인확인 제한 <span class="text-destructive">*</span>
                                </label>
                                <div class="flex items-center gap-2">
                                    <input type="number" 
                                           name="cf_cert_limit" 
                                           id="cf_cert_limit" 
                                           value="{{ old('cf_cert_limit', $config->cf_cert_limit) }}" 
                                           required
                                           class="px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <span class="text-sm">회 (하루동안 가능한 본인확인 회수)</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- KCP 설정 -->
                    <div class="border-t pt-6">
                        <h4 class="font-medium mb-4">NHN KCP 설정</h4>
                        
                        <div class="space-y-4">
                            <div class="space-y-2">
                                <label for="cf_cert_kcp_cd" class="text-sm font-medium">KCP 사이트코드</label>
                                <input type="text" 
                                       name="cf_cert_kcp_cd" 
                                       id="cf_cert_kcp_cd" 
                                       value="{{ old('cf_cert_kcp_cd', $config->cf_cert_kcp_cd) }}" 
                                       class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                        </div>
                    </div>

                    <!-- LG U+ 설정 -->
                    <div class="border-t pt-6">
                        <h4 class="font-medium mb-4">LG유플러스 설정</h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label for="cf_lg_mid" class="text-sm font-medium">LG유플러스 상점아이디</label>
                                <input type="text" 
                                       name="cf_lg_mid" 
                                       id="cf_lg_mid" 
                                       value="{{ old('cf_lg_mid', $config->cf_lg_mid) }}" 
                                       class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>

                            <div class="space-y-2">
                                <label for="cf_lg_mert_key" class="text-sm font-medium">LG유플러스 MERT KEY</label>
                                <input type="text" 
                                       name="cf_lg_mert_key" 
                                       id="cf_lg_mert_key" 
                                       value="{{ old('cf_lg_mert_key', $config->cf_lg_mert_key) }}" 
                                       class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Short URL Settings -->
            <div x-show="activeTab === 'shorturl'" x-transition>
                <h3 class="text-lg font-semibold mb-6">짧은주소 설정</h3>
                
                <div class="space-y-6">
                    <div class="space-y-2">
                        <label for="cf_googl_shorturl_apikey" class="text-sm font-medium">구글 짧은주소 API Key</label>
                        <input type="text" 
                               name="cf_googl_shorturl_apikey" 
                               id="cf_googl_shorturl_apikey" 
                               value="{{ old('cf_googl_shorturl_apikey', $config->cf_googl_shorturl_apikey) }}" 
                               class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        <p class="text-xs text-muted-foreground">
                            구글 짧은주소 API Key를 입력하시면, 게시판 글쓰기시 구글 짧은주소를 만들 수 있습니다.<br>
                            <a href="https://developers.google.com/url-shortener/v1/getting_started?hl=ko#APIKey" target="_blank" class="text-primary hover:underline">
                                구글 짧은주소 API Key 발급받기
                            </a>
                        </p>
                    </div>
                </div>
            </div>


            <!-- Layout Settings -->
            <div x-show="activeTab === 'layout'" x-transition>
                <h3 class="text-lg font-semibold mb-6">레이아웃 추가설정</h3>
                
                <div class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label for="cf_member_skin" class="text-sm font-medium">회원 스킨</label>
                            <input type="text" 
                                   name="cf_member_skin" 
                                   id="cf_member_skin" 
                                   value="{{ old('cf_member_skin', $config->cf_member_skin) }}" 
                                   placeholder="basic"
                                   class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>

                        <div class="space-y-2">
                            <label for="cf_mobile_member_skin" class="text-sm font-medium">모바일 회원 스킨</label>
                            <input type="text" 
                                   name="cf_mobile_member_skin" 
                                   id="cf_mobile_member_skin" 
                                   value="{{ old('cf_mobile_member_skin', $config->cf_mobile_member_skin) }}" 
                                   placeholder="basic"
                                   class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>

                        <div class="space-y-2">
                            <label for="cf_connect_skin" class="text-sm font-medium">접속자 스킨</label>
                            <input type="text" 
                                   name="cf_connect_skin" 
                                   id="cf_connect_skin" 
                                   value="{{ old('cf_connect_skin', $config->cf_connect_skin) }}" 
                                   placeholder="basic"
                                   class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>

                        <div class="space-y-2">
                            <label for="cf_mobile_connect_skin" class="text-sm font-medium">모바일 접속자 스킨</label>
                            <input type="text" 
                                   name="cf_mobile_connect_skin" 
                                   id="cf_mobile_connect_skin" 
                                   value="{{ old('cf_mobile_connect_skin', $config->cf_mobile_connect_skin) }}" 
                                   placeholder="basic"
                                   class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                    </div>
                </div>
            </div>

            <!-- SMS Settings -->
            <div x-show="activeTab === 'sms'" x-transition>
                <h3 class="text-lg font-semibold mb-6">SMS 설정</h3>
                
                <div class="space-y-6">
                    <!-- SMS 사용 -->
                    <div>
                        <h4 class="font-medium mb-4">SMS 서비스</h4>
                        
                        <div class="space-y-4">
                            <div class="space-y-2">
                                <label for="cf_sms_use" class="text-sm font-medium">SMS 사용</label>
                                <select name="cf_sms_use" 
                                        id="cf_sms_use"
                                        class="px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <option value="">사용안함</option>
                                    <option value="icode" {{ $config->cf_sms_use == 'icode' ? 'selected' : '' }}>아이코드</option>
                                </select>
                            </div>

                            <div class="space-y-2">
                                <label for="cf_sms_type" class="text-sm font-medium">SMS 전송유형</label>
                                <input type="text" 
                                       name="cf_sms_type" 
                                       id="cf_sms_type" 
                                       value="{{ old('cf_sms_type', $config->cf_sms_type) }}" 
                                       class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                        </div>
                    </div>

                    <!-- 아이코드 설정 -->
                    <div class="border-t pt-6">
                        <h4 class="font-medium mb-4">아이코드 설정</h4>
                        
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label for="cf_icode_id" class="text-sm font-medium">아이코드 회원아이디</label>
                                    <input type="text" 
                                           name="cf_icode_id" 
                                           id="cf_icode_id" 
                                           value="{{ old('cf_icode_id', $config->cf_icode_id) }}" 
                                           class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                </div>

                                <div class="space-y-2">
                                    <label for="cf_icode_pw" class="text-sm font-medium">아이코드 비밀번호</label>
                                    <input type="password" 
                                           name="cf_icode_pw" 
                                           id="cf_icode_pw" 
                                           value="{{ old('cf_icode_pw', $config->cf_icode_pw) }}" 
                                           class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                </div>

                                <div class="space-y-2">
                                    <label for="cf_icode_server_ip" class="text-sm font-medium">아이코드 SMS 서버 IP</label>
                                    <input type="text" 
                                           name="cf_icode_server_ip" 
                                           id="cf_icode_server_ip" 
                                           value="{{ old('cf_icode_server_ip', $config->cf_icode_server_ip) }}" 
                                           class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                </div>

                                <div class="space-y-2">
                                    <label for="cf_icode_server_port" class="text-sm font-medium">아이코드 SMS 서버 포트</label>
                                    <input type="text" 
                                           name="cf_icode_server_port" 
                                           id="cf_icode_server_port" 
                                           value="{{ old('cf_icode_server_port', $config->cf_icode_server_port) }}" 
                                           class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label for="cf_icode_token_key" class="text-sm font-medium">아이코드 토큰키</label>
                                <input type="text" 
                                       name="cf_icode_token_key" 
                                       id="cf_icode_token_key" 
                                       value="{{ old('cf_icode_token_key', $config->cf_icode_token_key) }}" 
                                       class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                <p class="text-xs text-muted-foreground">
                                    아이코드 SMS 신규 플랫폼 사용시 토큰키를 입력해 주세요.<br>
                                    (<a href="http://icodeservice.com/info/public_new_api_intro" target="_blank" class="text-primary hover:underline">아이코드 SMS 신규 플랫폼 API 안내</a>)
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Extra Fields -->
            <div x-show="activeTab === 'extra'" x-transition>
                <h3 class="text-lg font-semibold mb-6">여분필드 설정</h3>
                
                <div class="space-y-6">
                    <p class="text-sm text-muted-foreground">각 필드의 제목과 값을 설정할 수 있습니다. 값은 게시판 및 그룹관리에서 사용할 수 있습니다.</p>
                    
                    @for ($i = 1; $i <= 10; $i++)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-end">
                        <div class="space-y-2">
                            <label for="cf_{{ $i }}_subj" class="text-sm font-medium">여분필드 {{ $i }} 제목</label>
                            <input type="text" 
                                   name="cf_{{ $i }}_subj" 
                                   id="cf_{{ $i }}_subj" 
                                   value="{{ old('cf_' . $i . '_subj', $config->{'cf_' . $i . '_subj'}) }}" 
                                   class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div class="space-y-2">
                            <label for="cf_{{ $i }}" class="text-sm font-medium">여분필드 {{ $i }} 값</label>
                            <input type="text" 
                                   name="cf_{{ $i }}" 
                                   id="cf_{{ $i }}" 
                                   value="{{ old('cf_' . $i, $config->{'cf_' . $i}) }}" 
                                   class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                    </div>
                    @endfor
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end pt-6 border-t">
                <button type="submit" 
                        class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-6">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    저장하기
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // 폼 제출 시 빈 값 처리
    document.getElementById('fconfigform')?.addEventListener('submit', function(e) {
        // 숫자 필드들의 빈 값을 0으로 변경
        const numberInputs = this.querySelectorAll('input[type="number"]');
        numberInputs.forEach(input => {
            if (input.value === '') {
                input.value = '0';
            }
        });
    });
</script>
@endpush