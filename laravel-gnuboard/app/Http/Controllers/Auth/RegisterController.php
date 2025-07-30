<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Helpers\PBKDF2;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        // 로그인 중인 경우 홈으로 리다이렉트
        if (Auth::check()) {
            return redirect('/');
        }
        
        // 약관 동의 확인
        if (!session('agree_terms')) {
            return redirect()->route('register');
        }
        
        return view('auth.register-shadcn');
    }
    
    public function showTerms()
    {
        // 로그인 중인 경우 홈으로 리다이렉트
        if (Auth::check()) {
            return redirect('/');
        }
        
        $config = DB::table('g5_config')->first();
        
        return view('auth.terms', compact('config'));
    }
    
    public function agreeTerms(Request $request)
    {
        $request->validate([
            'agree' => 'required|accepted',
            'agree2' => 'required|accepted',
        ], [
            'agree.required' => '회원가입약관의 내용에 동의하셔야 회원가입 하실 수 있습니다.',
            'agree.accepted' => '회원가입약관의 내용에 동의하셔야 회원가입 하실 수 있습니다.',
            'agree2.required' => '개인정보처리방침안내의 내용에 동의하셔야 회원가입 하실 수 있습니다.',
            'agree2.accepted' => '개인정보처리방침안내의 내용에 동의하셔야 회원가입 하실 수 있습니다.',
        ]);
        
        // 세션에 동의 여부 저장
        session(['agree_terms' => true]);
        
        return redirect()->route('register.form');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'mb_id' => 'required|string|min:3|max:20|unique:g5_member,mb_id|regex:/^[a-zA-Z0-9_]+$/',
            'mb_password' => 'required|string|min:3|confirmed',
            'mb_name' => 'required|string|max:255',
            'mb_nick' => 'required|string|max:255|unique:g5_member,mb_nick',
            'mb_email' => 'required|string|email|max:255|unique:g5_member,mb_email',
            'mb_hp' => 'nullable|string|max:255',
            'mb_zip1' => 'nullable|string|max:3',
            'mb_zip2' => 'nullable|string|max:3',
            'mb_addr1' => 'nullable|string|max:255',
            'mb_addr2' => 'nullable|string|max:255',
            'mb_addr3' => 'nullable|string|max:255',
            'mb_signature' => 'nullable|string',
            'mb_profile' => 'nullable|string',
            'mb_mailling' => 'boolean',
            'mb_sms' => 'boolean',
            'mb_open' => 'boolean',
        ]);

        // 회원 생성
        $user = new User();
        $user->mb_id = $validated['mb_id'];
        $user->mb_password = PBKDF2::hash($validated['mb_password']);
        $user->mb_name = $validated['mb_name'];
        $user->mb_nick = $validated['mb_nick'];
        $user->mb_nick_date = now();
        $user->mb_email = $validated['mb_email'];
        $user->mb_hp = $validated['mb_hp'] ?? '';
        $user->mb_zip1 = $validated['mb_zip1'] ?? '';
        $user->mb_zip2 = $validated['mb_zip2'] ?? '';
        $user->mb_addr1 = $validated['mb_addr1'] ?? '';
        $user->mb_addr2 = $validated['mb_addr2'] ?? '';
        $user->mb_addr3 = $validated['mb_addr3'] ?? '';
        $user->mb_signature = $validated['mb_signature'] ?? '';
        $user->mb_profile = $validated['mb_profile'] ?? '';
        $user->mb_mailling = $validated['mb_mailling'] ?? 0;
        $user->mb_sms = $validated['mb_sms'] ?? 0;
        $user->mb_open = $validated['mb_open'] ?? 0;
        $user->mb_open_date = $user->mb_open ? now()->format('Y-m-d') : '0000-00-00';
        $user->mb_datetime = now();
        $user->mb_ip = $request->ip();
        $user->mb_level = config('gnuboard.register_level', 2);
        $user->mb_point = config('gnuboard.register_point', 0);
        $user->mb_today_login = now();
        $user->mb_login_ip = $request->ip();
        
        // 추가 필수 필드 기본값 설정
        $user->mb_homepage = '';
        $user->mb_tel = '';
        $user->mb_recommend = '';
        $user->mb_memo = '';
        $user->mb_lost_certify = '';
        $user->mb_memo_cnt = 0;
        $user->mb_scrap_cnt = 0;
        $user->mb_adult = 0;
        $user->mb_birth = '';
        $user->mb_sex = 0;
        $user->mb_certify = 0;
        $user->mb_dupinfo = '';
        $user->mb_addr_jibeon = '';
        $user->mb_leave_date = '';
        $user->mb_intercept_date = '';
        $user->mb_email_certify = '0000-00-00 00:00:00';
        $user->mb_email_certify2 = '';
        $user->mb_memo_call = '';
        $user->mb_1 = '';
        $user->mb_2 = '';
        $user->mb_3 = '';
        $user->mb_4 = '';
        $user->mb_5 = '';
        $user->mb_6 = '';
        $user->mb_7 = '';
        $user->mb_8 = '';
        $user->mb_9 = '';
        $user->mb_10 = '';
        
        $user->save();

        // 회원가입 포인트 지급 (추후 구현)

        // 자동 로그인
        Auth::login($user);

        return redirect('/')->with('success', '회원가입이 완료되었습니다.');
    }

    public function checkMbId(Request $request)
    {
        $exists = User::where('mb_id', $request->mb_id)->exists();
        
        return response()->json([
            'available' => !$exists
        ]);
    }

    public function checkMbNick(Request $request)
    {
        $exists = User::where('mb_nick', $request->mb_nick)->exists();
        
        return response()->json([
            'available' => !$exists
        ]);
    }

    public function checkMbEmail(Request $request)
    {
        $exists = User::where('mb_email', $request->mb_email)->exists();
        
        return response()->json([
            'available' => !$exists
        ]);
    }
}