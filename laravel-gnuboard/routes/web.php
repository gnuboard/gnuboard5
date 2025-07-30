<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MyPageController;
use App\Http\Controllers\Admin\AdminController;

// 메인 페이지
Route::get('/', [HomeController::class, 'index'])->name('home');

// 인증 라우트
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// 약관 동의
Route::get('/register', [RegisterController::class, 'showTerms'])->name('register');
Route::post('/register/agree', [RegisterController::class, 'agreeTerms'])->name('register.agree');
Route::get('/register/form', [RegisterController::class, 'showRegistrationForm'])->name('register.form');
Route::post('/register', [RegisterController::class, 'register'])->name('register.store');

// AJAX 중복 체크
Route::post('/register/check-mb-id', [RegisterController::class, 'checkMbId'])->name('register.check.mb_id');
Route::post('/register/check-mb-nick', [RegisterController::class, 'checkMbNick'])->name('register.check.mb_nick');
Route::post('/register/check-mb-email', [RegisterController::class, 'checkMbEmail'])->name('register.check.mb_email');

// 마이페이지 라우트
Route::middleware('auth')->prefix('mypage')->group(function () {
    Route::get('/', [MyPageController::class, 'index'])->name('mypage.index');
    Route::get('/edit', [MyPageController::class, 'edit'])->name('mypage.edit');
    Route::put('/update', [MyPageController::class, 'update'])->name('mypage.update');
    Route::put('/password', [MyPageController::class, 'updatePassword'])->name('mypage.password');
});

// 게시판 라우트
Route::prefix('board/{board}')->group(function () {
    Route::get('/', [BoardController::class, 'index'])->name('board.index');
    Route::get('/write', [BoardController::class, 'create'])->name('board.create');
    Route::post('/write', [BoardController::class, 'store'])->name('board.store');
    Route::get('/{id}', [BoardController::class, 'show'])->name('board.show');
    Route::get('/{id}/edit', [BoardController::class, 'edit'])->name('board.edit');
    Route::put('/{id}', [BoardController::class, 'update'])->name('board.update');
    Route::delete('/{id}', [BoardController::class, 'destroy'])->name('board.destroy');
    Route::get('/{id}/password', [BoardController::class, 'password'])->name('board.password');
    Route::post('/{id}/password', [BoardController::class, 'checkPassword'])->name('board.checkPassword');
});

// 관리자 라우트
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/config', [\App\Http\Controllers\Admin\ConfigController::class, 'index'])->name('config');
    Route::post('/config', [\App\Http\Controllers\Admin\ConfigController::class, 'update'])->name('config.update');
});
