@extends('layouts.app')

@section('title', '회원가입')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
    <!-- Header with progress indicator -->
    <div class="relative px-4 py-8 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-4xl">
            <div class="text-center">
                <div class="flex items-center justify-center space-x-2 mb-4">
                    <div class="flex items-center justify-center w-8 h-8 bg-green-600 text-white rounded-full text-sm font-semibold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <div class="h-px w-16 bg-blue-300"></div>
                    <div class="flex items-center justify-center w-8 h-8 bg-blue-600 text-white rounded-full text-sm font-semibold">2</div>
                    <div class="h-px w-16 bg-gray-300"></div>
                    <div class="flex items-center justify-center w-8 h-8 bg-gray-200 text-gray-500 rounded-full text-sm font-semibold">3</div>
                </div>
                <p class="text-sm text-gray-600 mb-2">약관동의 → 정보입력 → 가입완료</p>
                <h1 class="text-4xl font-bold tracking-tight text-gray-900 mb-3">
                    회원정보 입력
                </h1>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    안전하고 편리한 서비스 이용을 위해 정확한 정보를 입력해주세요
                </p>
            </div>
        </div>
    </div>

    <div class="px-4 pb-12 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-4xl">
            <form method="POST" action="{{ route('register.store') }}" class="space-y-8" x-data="{ 
                currentStep: 1, 
                totalSteps: 3,
                showPassword: false,
                showPasswordConfirm: false 
            }" autocomplete="off">
                @csrf

                <!-- 필수 정보 -->
                <div class="bg-white/70 backdrop-blur-sm border border-white/20 shadow-xl rounded-2xl overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-8 py-6 border-b border-gray-200">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-semibold text-gray-900">필수 정보</h3>
                                <p class="text-sm text-gray-600">서비스 이용을 위한 기본 정보를 입력해주세요</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-8 space-y-6">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- 아이디 -->
                            <div class="lg:col-span-2">
                                <label for="mb_id" class="block text-sm font-semibold text-gray-900 mb-2">
                                    아이디 <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="text" 
                                           name="mb_id" 
                                           id="mb_id" 
                                           value="{{ old('mb_id') }}"
                                           class="block w-full rounded-xl border-0 px-4 py-3 text-gray-900 bg-white shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 transition-all duration-200 @error('mb_id') ring-red-500 @enderror"
                                           placeholder="영문, 숫자, _만 사용 가능 (3자 이상)"
                                           required>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                </div>
                                @error('mb_id')
                                    <div class="mt-2 flex items-center space-x-2">
                                        <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                        <p class="text-sm text-red-600">{{ $message }}</p>
                                    </div>
                                @enderror
                            </div>

                            <!-- 비밀번호 -->
                            <div>
                                <label for="mb_password" class="block text-sm font-semibold text-gray-900 mb-2">
                                    비밀번호 <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input :type="showPassword ? 'text' : 'password'" 
                                           name="mb_password" 
                                           id="mb_password" 
                                           class="block w-full rounded-xl border-0 px-4 py-3 text-gray-900 bg-white shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 transition-all duration-200 pr-12 @error('mb_password') ring-red-500 @enderror"
                                           placeholder="최소 3자 이상"
                                           required>
                                    <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 flex items-center pr-3">
                                        <svg x-show="!showPassword" class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        <svg x-show="showPassword" class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L8.464 8.464m1.414 1.414L12 12m-3.122-3.122L7.464 7.464M21 21l-9-9m2.122 2.122L21 21"></path>
                                        </svg>
                                    </button>
                                </div>
                                @error('mb_password')
                                    <div class="mt-2 flex items-center space-x-2">
                                        <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                        <p class="text-sm text-red-600">{{ $message }}</p>
                                    </div>
                                @enderror
                            </div>

                            <!-- 비밀번호 확인 -->
                            <div>
                                <label for="mb_password_confirmation" class="block text-sm font-semibold text-gray-900 mb-2">
                                    비밀번호 확인 <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input :type="showPasswordConfirm ? 'text' : 'password'" 
                                           name="mb_password_confirmation" 
                                           id="mb_password_confirmation" 
                                           class="block w-full rounded-xl border-0 px-4 py-3 text-gray-900 bg-white shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 transition-all duration-200 pr-12"
                                           placeholder="비밀번호를 다시 입력"
                                           required>
                                    <button type="button" @click="showPasswordConfirm = !showPasswordConfirm" class="absolute inset-y-0 right-0 flex items-center pr-3">
                                        <svg x-show="!showPasswordConfirm" class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        <svg x-show="showPasswordConfirm" class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L8.464 8.464m1.414 1.414L12 12m-3.122-3.122L7.464 7.464M21 21l-9-9m2.122 2.122L21 21"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- 이름 -->
                            <div>
                                <label for="mb_name" class="block text-sm font-semibold text-gray-900 mb-2">
                                    이름 <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="text" 
                                           name="mb_name" 
                                           id="mb_name" 
                                           value="{{ old('mb_name') }}"
                                           class="block w-full rounded-xl border-0 px-4 py-3 text-gray-900 bg-white shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 transition-all duration-200 @error('mb_name') ring-red-500 @enderror"
                                           placeholder="실명을 입력해주세요"
                                           required>
                                </div>
                                @error('mb_name')
                                    <div class="mt-2 flex items-center space-x-2">
                                        <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                        <p class="text-sm text-red-600">{{ $message }}</p>
                                    </div>
                                @enderror
                            </div>

                            <!-- 닉네임 -->
                            <div>
                                <label for="mb_nick" class="block text-sm font-semibold text-gray-900 mb-2">
                                    닉네임 <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="text" 
                                           name="mb_nick" 
                                           id="mb_nick" 
                                           value="{{ old('mb_nick') }}"
                                           class="block w-full rounded-xl border-0 px-4 py-3 text-gray-900 bg-white shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 transition-all duration-200 @error('mb_nick') ring-red-500 @enderror"
                                           placeholder="커뮤니티에서 사용할 닉네임"
                                           required>
                                </div>
                                @error('mb_nick')
                                    <div class="mt-2 flex items-center space-x-2">
                                        <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                        <p class="text-sm text-red-600">{{ $message }}</p>
                                    </div>
                                @enderror
                            </div>

                            <!-- 이메일 -->
                            <div>
                                <label for="mb_email" class="block text-sm font-semibold text-gray-900 mb-2">
                                    이메일 <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="email" 
                                           name="mb_email" 
                                           id="mb_email" 
                                           value="{{ old('mb_email') }}"
                                           class="block w-full rounded-xl border-0 px-4 py-3 text-gray-900 bg-white shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 transition-all duration-200 @error('mb_email') ring-red-500 @enderror"
                                           placeholder="example@email.com"
                                           required>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                                        </svg>
                                    </div>
                                </div>
                                @error('mb_email')
                                    <div class="mt-2 flex items-center space-x-2">
                                        <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                        <p class="text-sm text-red-600">{{ $message }}</p>
                                    </div>
                                @enderror
                            </div>

                            <!-- 휴대폰번호 -->
                            <div>
                                <label for="mb_hp" class="block text-sm font-semibold text-gray-900 mb-2">
                                    휴대폰번호 <span class="text-gray-500 text-xs">(선택)</span>
                                </label>
                                <div class="relative">
                                    <input type="text" 
                                           name="mb_hp" 
                                           id="mb_hp" 
                                           value="{{ old('mb_hp') }}"
                                           class="block w-full rounded-xl border-0 px-4 py-3 text-gray-900 bg-white shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 transition-all duration-200 @error('mb_hp') ring-red-500 @enderror"
                                           placeholder="010-0000-0000">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                        </svg>
                                    </div>
                                </div>
                                @error('mb_hp')
                                    <div class="mt-2 flex items-center space-x-2">
                                        <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                        <p class="text-sm text-red-600">{{ $message }}</p>
                                    </div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 주소 정보 (선택사항) -->
                <div class="bg-white/70 backdrop-blur-sm border border-white/20 shadow-xl rounded-2xl overflow-hidden">
                    <div class="bg-gradient-to-r from-green-50 to-emerald-50 px-8 py-6 border-b border-gray-200">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-green-600 rounded-xl flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-semibold text-gray-900">주소 정보</h3>
                                <p class="text-sm text-gray-600">배송이나 서비스 제공에 필요한 경우 입력해주세요 (선택사항)</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-8 space-y-4">
                        <!-- 우편번호 -->
                        <div class="flex gap-3">
                            <input type="text" 
                                   name="mb_zip1" 
                                   id="mb_zip1"
                                   value="{{ old('mb_zip1') }}"
                                   placeholder="우편번호"
                                   class="w-32 rounded-xl border-0 px-4 py-3 text-gray-900 bg-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-green-600 transition-all duration-200"
                                   readonly>
                            <input type="text" 
                                   name="mb_zip2" 
                                   id="mb_zip2"
                                   value="{{ old('mb_zip2') }}"
                                   class="w-32 rounded-xl border-0 px-4 py-3 text-gray-900 bg-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-green-600 transition-all duration-200"
                                   readonly>
                            <button type="button" 
                                    onclick="alert('우편번호 검색 기능은 추후 구현 예정입니다.')"
                                    class="inline-flex items-center px-4 py-3 bg-green-600 text-white rounded-xl text-sm font-semibold hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                                우편번호 검색
                            </button>
                        </div>

                        <!-- 기본주소 -->
                        <input type="text" 
                               name="mb_addr1" 
                               id="mb_addr1"
                               value="{{ old('mb_addr1') }}"
                               placeholder="기본주소"
                               class="block w-full rounded-xl border-0 px-4 py-3 text-gray-900 bg-white shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-green-600 transition-all duration-200">

                        <!-- 상세주소 -->
                        <input type="text" 
                               name="mb_addr2" 
                               id="mb_addr2"
                               value="{{ old('mb_addr2') }}"
                               placeholder="상세주소 (동, 호수 등)"
                               class="block w-full rounded-xl border-0 px-4 py-3 text-gray-900 bg-white shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-green-600 transition-all duration-200">

                        <!-- 참고항목 -->
                        <input type="text" 
                               name="mb_addr3" 
                               id="mb_addr3"
                               value="{{ old('mb_addr3') }}"
                               placeholder="참고항목 (건물명, 근처 랜드마크 등)"
                               class="block w-full rounded-xl border-0 px-4 py-3 text-gray-900 bg-white shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-green-600 transition-all duration-200">
                    </div>
                </div>

                <!-- 추가 설정 -->
                <div class="bg-white/70 backdrop-blur-sm border border-white/20 shadow-xl rounded-2xl overflow-hidden">
                    <div class="bg-gradient-to-r from-purple-50 to-pink-50 px-8 py-6 border-b border-gray-200">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-purple-600 rounded-xl flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-semibold text-gray-900">추가 설정</h3>
                                <p class="text-sm text-gray-600">개인화된 서비스를 위한 선택사항입니다</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-8 space-y-6">
                        <!-- 서명 -->
                        <div>
                            <label for="mb_signature" class="block text-sm font-semibold text-gray-900 mb-2">
                                서명 <span class="text-gray-500 text-xs">(게시글 하단에 표시됩니다)</span>
                            </label>
                            <textarea name="mb_signature" 
                                      id="mb_signature" 
                                      rows="3"
                                      placeholder="게시글 작성 시 자동으로 추가될 서명을 입력하세요"
                                      class="block w-full rounded-xl border-0 px-4 py-3 text-gray-900 bg-white shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-purple-600 transition-all duration-200 resize-none">{{ old('mb_signature') }}</textarea>
                        </div>

                        <!-- 자기소개 -->
                        <div>
                            <label for="mb_profile" class="block text-sm font-semibold text-gray-900 mb-2">
                                자기소개 <span class="text-gray-500 text-xs">(프로필에 표시됩니다)</span>
                            </label>
                            <textarea name="mb_profile" 
                                      id="mb_profile" 
                                      rows="3"
                                      placeholder="다른 회원들에게 소개할 내용을 자유롭게 작성해주세요"
                                      class="block w-full rounded-xl border-0 px-4 py-3 text-gray-900 bg-white shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-purple-600 transition-all duration-200 resize-none">{{ old('mb_profile') }}</textarea>
                        </div>

                        <!-- 알림 설정 -->
                        <div class="space-y-4">
                            <h4 class="text-sm font-semibold text-gray-900">알림 설정</h4>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                                    <div class="flex items-start space-x-3">
                                        <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                                        </svg>
                                        <div>
                                            <label for="mb_mailling" class="text-sm font-medium text-gray-900">이메일 알림</label>
                                            <p class="text-xs text-gray-600">새로운 소식과 공지사항을 이메일로 받아보세요</p>
                                        </div>
                                    </div>
                                    <input type="checkbox" 
                                           name="mb_mailling" 
                                           id="mb_mailling"
                                           value="1"
                                           {{ old('mb_mailling') ? 'checked' : '' }}
                                           class="w-5 h-5 rounded border-2 border-gray-300 text-blue-600 focus:ring-blue-500 focus:ring-2">
                                </div>

                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                                    <div class="flex items-start space-x-3">
                                        <svg class="w-5 h-5 text-green-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                                        </svg>
                                        <div>
                                            <label for="mb_sms" class="text-sm font-medium text-gray-900">SMS 알림</label>
                                            <p class="text-xs text-gray-600">중요한 알림을 문자메시지로 받아보세요</p>
                                        </div>
                                    </div>
                                    <input type="checkbox" 
                                           name="mb_sms" 
                                           id="mb_sms"
                                           value="1"
                                           {{ old('mb_sms') ? 'checked' : '' }}
                                           class="w-5 h-5 rounded border-2 border-gray-300 text-green-600 focus:ring-green-500 focus:ring-2">
                                </div>

                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                                    <div class="flex items-start space-x-3">
                                        <svg class="w-5 h-5 text-purple-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16l2.879-2.879a3 3 0 014.242 0L18 16M8 6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <div>
                                            <label for="mb_open" class="text-sm font-medium text-gray-900">프로필 공개</label>
                                            <p class="text-xs text-gray-600">다른 회원들이 내 프로필을 볼 수 있도록 허용</p>
                                        </div>
                                    </div>
                                    <input type="checkbox" 
                                           name="mb_open" 
                                           id="mb_open"
                                           value="1"
                                           {{ old('mb_open') ? 'checked' : '' }}
                                           class="w-5 h-5 rounded border-2 border-gray-300 text-purple-600 focus:ring-purple-500 focus:ring-2">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="bg-white/70 backdrop-blur-sm border border-white/20 shadow-xl rounded-2xl p-8">
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                        <a href="{{ route('register') }}" 
                           class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            이전 단계로
                        </a>
                        <button type="submit" 
                                class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl text-sm font-semibold hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105">
                                회원가입 완료
                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                        </button>
                    </div>
                    
                    <div class="mt-6 text-center">
                        <p class="text-sm text-gray-600">
                            이미 계정이 있으신가요?
                            <a href="{{ route('login') }}" class="font-semibold text-blue-600 hover:text-blue-500 transition-colors duration-200">
                                로그인하기
                            </a>
                        </p>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endsection

@push('styles')
<style>
/* Tailwind CSS 기본 스타일 보완 */
input[type="text"]:focus,
input[type="password"]:focus,
input[type="email"]:focus,
textarea:focus {
    outline: none;
}

/* 유효성 검사 상태 표시 */
input.is-valid {
    @apply ring-green-500 focus:ring-green-600;
}

input.is-invalid {
    @apply ring-red-500 focus:ring-red-600;
}
</style>
@endpush

@push('scripts')
<script>
// 아이디 중복 체크
document.getElementById('mb_id').addEventListener('blur', function() {
    var mbId = this.value;
    if (mbId.length < 3) return;
    
    fetch('{{ route("register.check.mb_id") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ mb_id: mbId })
    })
    .then(response => response.json())
    .then(data => {
        var input = document.getElementById('mb_id');
        var errorMsg = input.parentElement.querySelector('.text-red-600');
        
        if (!errorMsg) {
            errorMsg = document.createElement('p');
            errorMsg.className = 'mt-1 text-sm text-red-600';
            input.parentElement.appendChild(errorMsg);
        }
        
        if (data.available) {
            input.classList.remove('ring-red-500');
            input.classList.add('ring-green-500');
            errorMsg.textContent = '';
        } else {
            input.classList.remove('ring-green-500');
            input.classList.add('ring-red-500');
            errorMsg.textContent = '이미 사용중인 아이디입니다.';
        }
    });
});

// 닉네임 중복 체크
document.getElementById('mb_nick').addEventListener('blur', function() {
    var mbNick = this.value;
    if (mbNick.length < 2) return;
    
    fetch('{{ route("register.check.mb_nick") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ mb_nick: mbNick })
    })
    .then(response => response.json())
    .then(data => {
        var input = document.getElementById('mb_nick');
        var errorMsg = input.parentElement.querySelector('.text-red-600');
        
        if (!errorMsg) {
            errorMsg = document.createElement('p');
            errorMsg.className = 'mt-1 text-sm text-red-600';
            input.parentElement.appendChild(errorMsg);
        }
        
        if (data.available) {
            input.classList.remove('ring-red-500');
            input.classList.add('ring-green-500');
            errorMsg.textContent = '';
        } else {
            input.classList.remove('ring-green-500');
            input.classList.add('ring-red-500');
            errorMsg.textContent = '이미 사용중인 닉네임입니다.';
        }
    });
});

// 이메일 중복 체크
document.getElementById('mb_email').addEventListener('blur', function() {
    var mbEmail = this.value;
    if (!mbEmail || !mbEmail.includes('@')) return;
    
    fetch('{{ route("register.check.mb_email") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ mb_email: mbEmail })
    })
    .then(response => response.json())
    .then(data => {
        var input = document.getElementById('mb_email');
        var errorMsg = input.parentElement.querySelector('.text-red-600');
        
        if (!errorMsg) {
            errorMsg = document.createElement('p');
            errorMsg.className = 'mt-1 text-sm text-red-600';
            input.parentElement.appendChild(errorMsg);
        }
        
        if (data.available) {
            input.classList.remove('ring-red-500');
            input.classList.add('ring-green-500');
            errorMsg.textContent = '';
        } else {
            input.classList.remove('ring-green-500');
            input.classList.add('ring-red-500');
            errorMsg.textContent = '이미 사용중인 이메일입니다.';
        }
    });
});
</script>
@endpush