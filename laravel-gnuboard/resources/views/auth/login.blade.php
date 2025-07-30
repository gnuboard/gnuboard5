@extends('layouts.app')

@section('title', '로그인')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="text-center text-3xl font-bold tracking-tight text-gray-900">
                로그인
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                계정에 로그인하여 서비스를 이용하세요
            </p>
        </div>
        
        <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl p-6 md:p-8">
            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf

                <div>
                    <label for="mb_id" class="block text-sm font-medium leading-6 text-gray-900">
                        아이디
                    </label>
                    <div class="mt-2">
                        <input type="text" 
                               name="mb_id" 
                               id="mb_id" 
                               value="{{ old('mb_id') }}"
                               class="block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6 @error('mb_id') ring-red-500 @enderror"
                               required 
                               autofocus>
                        @error('mb_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="mb_password" class="block text-sm font-medium leading-6 text-gray-900">
                        비밀번호
                    </label>
                    <div class="mt-2">
                        <input type="password" 
                               name="mb_password" 
                               id="mb_password"
                               class="block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6 @error('mb_password') ring-red-500 @enderror"
                               required>
                        @error('mb_password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input type="checkbox" 
                               name="remember" 
                               id="remember"
                               {{ old('remember') ? 'checked' : '' }}
                               class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-600">
                        <label for="remember" class="ml-3 block text-sm leading-6 text-gray-900">
                            자동로그인
                        </label>
                    </div>

                    <div class="text-sm leading-6">
                        <a href="#" class="font-semibold text-blue-600 hover:text-blue-500">
                            아이디/비밀번호 찾기
                        </a>
                    </div>
                </div>

                <div>
                    <button type="submit" 
                            class="flex w-full justify-center rounded-md bg-blue-600 px-3 py-1.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                        로그인
                    </button>
                </div>

                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm font-medium leading-6">
                        <span class="bg-white px-6 text-gray-900">또는</span>
                    </div>
                </div>

                <div class="text-center">
                    <a href="{{ route('register') }}" 
                       class="font-semibold text-blue-600 hover:text-blue-500">
                        회원가입하기
                    </a>
                </div>
            </form>
        </div>

        @if(config('gnuboard.social_login_use'))
        <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">소셜 로그인</h3>
            <div class="space-y-3">
                <button class="flex w-full justify-center items-center gap-3 rounded-md bg-yellow-400 px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm hover:bg-yellow-300 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-yellow-400">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 3c2.755 0 5.455.232 8.083.678 2.537.43 4.573 2.407 5.096 4.944C25.768 11.545 26 14.245 26 17s-.232 5.455-.821 8.378c-.523 2.537-2.559 4.514-5.096 4.944C17.455 30.768 14.755 31 12 31s-5.455-.232-8.083-.678c-2.537-.43-4.573-2.407-5.096-4.944C-1.768 22.455-2 19.755-2 17s.232-5.455.821-8.378c.523-2.537 2.559-4.514 5.096-4.944C6.545 3.232 9.245 3 12 3z"/>
                    </svg>
                    카카오 로그인
                </button>
                <button class="flex w-full justify-center items-center gap-3 rounded-md bg-green-500 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-400 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-500">
                    네이버 로그인
                </button>
                <button class="flex w-full justify-center items-center gap-3 rounded-md bg-white border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm hover:bg-gray-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-500">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                    </svg>
                    구글 로그인
                </button>
                <button class="flex w-full justify-center items-center gap-3 rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                    페이스북 로그인
                </button>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection