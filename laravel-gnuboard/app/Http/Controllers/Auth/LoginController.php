<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Helpers\PBKDF2;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login-shadcn');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'mb_id' => 'required|string',
            'mb_password' => 'required|string',
        ]);

        $user = User::where('mb_id', $credentials['mb_id'])->first();

        if (!$user) {
            return back()->withErrors([
                'mb_id' => '아이디 또는 비밀번호가 일치하지 않습니다.',
            ])->onlyInput('mb_id');
        }

        // 차단된 회원 체크
        if ($user->mb_intercept_date && $user->mb_intercept_date != '0000-00-00') {
            return back()->withErrors([
                'mb_id' => '차단된 회원입니다.',
            ])->onlyInput('mb_id');
        }

        // 탈퇴한 회원 체크
        if ($user->mb_leave_date && $user->mb_leave_date != '0000-00-00 00:00:00') {
            return back()->withErrors([
                'mb_id' => '탈퇴한 회원입니다.',
            ])->onlyInput('mb_id');
        }

        // 비밀번호 확인 (PBKDF2)
        if (!PBKDF2::verify($credentials['mb_password'], $user->mb_password)) {
            return back()->withErrors([
                'mb_id' => '아이디 또는 비밀번호가 일치하지 않습니다.',
            ])->onlyInput('mb_id');
        }

        // 로그인 처리
        Auth::login($user, $request->boolean('remember'));

        // 로그인 정보 업데이트
        $user->mb_today_login = now();
        $user->mb_login_ip = $request->ip();
        $user->save();

        // 로그인 포인트 지급 (추후 구현)

        return redirect()->intended('/');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}