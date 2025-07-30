@extends('layouts.app')

@section('title', '회원가입약관')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
    <!-- Header with progress indicator -->
    <div class="relative px-4 py-8 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-4xl">
            <div class="text-center">
                <div class="flex items-center justify-center space-x-2 mb-4">
                    <div class="flex items-center justify-center w-8 h-8 bg-blue-600 text-white rounded-full text-sm font-semibold">1</div>
                    <div class="h-px w-16 bg-gray-300"></div>
                    <div class="flex items-center justify-center w-8 h-8 bg-gray-200 text-gray-500 rounded-full text-sm font-semibold">2</div>
                    <div class="h-px w-16 bg-gray-300"></div>
                    <div class="flex items-center justify-center w-8 h-8 bg-gray-200 text-gray-500 rounded-full text-sm font-semibold">3</div>
                </div>
                <p class="text-sm text-gray-600 mb-2">약관동의 → 정보입력 → 가입완료</p>
                <h1 class="text-4xl font-bold tracking-tight text-gray-900 mb-3">
                    서비스 이용약관
                </h1>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    {{ config('app.name') }} 서비스를 이용하시기 위해서는 아래 약관에 동의가 필요합니다
                </p>
            </div>
        </div>
    </div>

    <div class="px-4 pb-12 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-4xl">
            <form method="POST" action="{{ route('register.agree') }}" class="space-y-8" x-data="{ allAgree: false }">
                @csrf
                
                <!-- 전체 동의 -->
                <div class="bg-white/70 backdrop-blur-sm border border-white/20 shadow-xl rounded-2xl p-6">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-xl font-semibold text-gray-900">전체 약관에 동의합니다</h3>
                            <p class="text-sm text-gray-600 mt-1">아래 모든 약관에 한번에 동의할 수 있습니다</p>
                        </div>
                        <div class="flex-shrink-0">
                            <input type="checkbox" 
                                   x-model="allAgree" 
                                   @change="
                                        const checkboxes = document.querySelectorAll('input[name=agree], input[name=agree2]');
                                        checkboxes.forEach(cb => cb.checked = allAgree);
                                   "
                                   class="w-5 h-5 rounded border-2 border-gray-300 text-blue-600 focus:ring-blue-500 focus:ring-2">
                        </div>
                    </div>
                </div>

                <!-- 회원가입약관 -->
                <div class="bg-white/70 backdrop-blur-sm border border-white/20 shadow-xl rounded-2xl overflow-hidden">
                    <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-8 py-6 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">서비스 이용약관</h3>
                                    <p class="text-sm text-gray-600">서비스 이용에 관한 기본 약관입니다</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                필수
                            </span>
                        </div>
                    </div>
                    
                    <div class="p-8">
                        <div class="bg-gray-50 rounded-xl p-6 max-h-80 overflow-y-auto border border-gray-200">
                            <div class="prose prose-sm max-w-none text-gray-700 leading-relaxed">
                                {!! nl2br(e($config->cf_stipulation ?? '제1조(목적)
이 약관은 회사(이하 "회사"라 한다)가 운영하는 사이트에서 제공하는 서비스(이하 "서비스"라 한다)를 이용함에 있어 회사와 이용자의 권리·의무 및 책임사항을 규정함을 목적으로 합니다.

제2조(용어의 정의)
① "사이트"란 회사가 재화 또는 용역을 이용자에게 제공하기 위하여 컴퓨터 등 정보통신설비를 이용하여 재화 또는 용역을 거래할 수 있도록 설정한 가상의 영업장을 말합니다.
② "이용자"란 "사이트"에 접속하여 이 약관에 따라 "사이트"가 제공하는 서비스를 받는 회원 및 비회원을 말합니다.
③ "회원"이라 함은 "사이트"에 개인정보를 제공하여 회원등록을 한 자로서, "사이트"의 정보를 지속적으로 제공받으며, "사이트"가 제공하는 서비스를 계속적으로 이용할 수 있는 자를 말합니다.

제3조(약관의 효력과 변경)
① 이 약관은 이용자에게 공시함으로써 효력을 발생합니다.
② 회사는 필요하다고 인정되는 경우 이 약관을 변경할 수 있으며, 회사가 약관을 변경할 경우에는 적용일자 및 변경사유를 명시하여 현행약관과 함께 사이트의 초기화면에 그 적용일자 7일 이전부터 적용일자 전일까지 공지합니다.

제4조(약관 외 준칙)
이 약관에서 정하지 아니한 사항과 이 약관의 해석에 관하여는 전자상거래 등에서의 소비자보호에 관한 법률, 약관의 규제 등에 관한 법률, 공정거래위원회가 정하는 전자상거래 등에서의 소비자 보호지침 및 관계법령 또는 상관례에 따릅니다.

제5조(회원가입)
① 이용자는 회사가 정한 가입 양식에 따라 회원정보를 기입한 후 이 약관에 동의한다는 의사표시를 함으로서 회원가입을 신청합니다.
② 회사는 제1항과 같이 회원으로 가입할 것을 신청한 이용자 중 다음 각 호에 해당하지 않는 한 회원으로 등록합니다.
  1. 등록 내용에 허위, 기재누락, 오기가 있는 경우
  2. 기타 회원으로 등록하는 것이 "사이트"의 기술상 현저히 지장이 있다고 판단되는 경우
③ 회원가입계약의 성립 시기는 회사의 승낙이 회원에게 도달한 시점으로 합니다.')) !!}
                            </div>
                        </div>
                        
                        <div class="mt-6 flex items-center space-x-3">
                            <input class="w-5 h-5 rounded border-2 border-gray-300 text-blue-600 focus:ring-blue-500 focus:ring-2 @error('agree') border-red-500 @enderror" 
                                   type="checkbox" id="agree" name="agree" value="1">
                            <label for="agree" class="text-sm font-medium text-gray-900 cursor-pointer">
                                서비스 이용약관에 동의합니다
                            </label>
                        </div>
                        @error('agree')
                            <div class="mt-2 flex items-center space-x-2">
                                <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            </div>
                        @enderror
                    </div>
                </div>

                <!-- 개인정보처리방침 -->
                <div class="bg-white/70 backdrop-blur-sm border border-white/20 shadow-xl rounded-2xl overflow-hidden">
                    <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-8 py-6 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">개인정보처리방침</h3>
                                    <p class="text-sm text-gray-600">개인정보 수집 및 처리에 관한 안내입니다</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                필수
                            </span>
                        </div>
                    </div>
                    
                    <div class="p-8">
                        <div class="bg-gray-50 rounded-xl p-6 max-h-80 overflow-y-auto border border-gray-200">
                            <div class="prose prose-sm max-w-none text-gray-700 leading-relaxed">
                                {!! nl2br(e($config->cf_privacy ?? '1. 개인정보의 수집 및 이용목적
회사는 수집한 개인정보를 다음의 목적을 위해 활용합니다.
- 서비스 제공에 관한 계약 이행 및 서비스 제공에 따른 요금정산
- 회원 관리: 회원제 서비스 이용에 따른 본인확인, 개인 식별, 불량회원의 부정 이용 방지와 비인가 사용 방지, 가입 의사 확인, 연령확인
- 마케팅 및 광고에 활용: 이벤트 등 광고성 정보 전달, 접속 빈도 파악 또는 회원의 서비스 이용에 대한 통계

2. 수집하는 개인정보 항목
회사는 회원가입, 상담, 서비스 신청 등을 위해 아래와 같은 개인정보를 수집하고 있습니다.
- 필수항목: 이름, 생년월일, 성별, 로그인ID, 비밀번호, 휴대전화번호, 이메일
- 선택항목: 주소
- 자동수집항목: IP 정보, 쿠키, 방문 일시, 서비스 이용 기록

3. 개인정보의 보유 및 이용기간
원칙적으로, 개인정보 수집 및 이용목적이 달성된 후에는 해당 정보를 지체 없이 파기합니다. 단, 다음의 정보에 대해서는 아래의 이유로 명시한 기간 동안 보존합니다.
- 보존 이유: 회원 탈퇴 시 부정이용 방지
- 보존 기간: 3개월

4. 개인정보의 파기절차 및 방법
회사는 원칙적으로 개인정보 수집 및 이용목적이 달성된 후에는 해당 정보를 지체없이 파기합니다.')) !!}
                            </div>
                        </div>
                        
                        <div class="mt-6 flex items-center space-x-3">
                            <input class="w-5 h-5 rounded border-2 border-gray-300 text-blue-600 focus:ring-blue-500 focus:ring-2 @error('agree2') border-red-500 @enderror" 
                                   type="checkbox" id="agree2" name="agree2" value="1">
                            <label for="agree2" class="text-sm font-medium text-gray-900 cursor-pointer">
                                개인정보처리방침에 동의합니다
                            </label>
                        </div>
                        @error('agree2')
                            <div class="mt-2 flex items-center space-x-2">
                                <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            </div>
                        @enderror
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="bg-white/70 backdrop-blur-sm border border-white/20 shadow-xl rounded-2xl p-8">
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                        <a href="{{ route('home') }}" 
                           class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            취소
                        </a>
                        <button type="submit" 
                                class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl text-sm font-semibold hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105">
                            다음 단계로
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endsection