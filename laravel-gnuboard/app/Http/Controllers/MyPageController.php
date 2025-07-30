<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;

class MyPageController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();
        return view('mypage.index', compact('user'));
    }

    public function edit()
    {
        $user = Auth::user();
        return view('mypage.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'mb_name' => 'required|string|max:255',
            'mb_nick' => 'required|string|max:255|unique:g5_member,mb_nick,' . $user->mb_id . ',mb_id',
            'mb_email' => 'required|email|max:255|unique:g5_member,mb_email,' . $user->mb_id . ',mb_id',
            'mb_hp' => 'nullable|string|max:255',
            'mb_zip' => 'nullable|string|max:10',
            'mb_addr1' => 'nullable|string|max:255',
            'mb_addr2' => 'nullable|string|max:255',
        ]);
        
        $user->fill($validated);
        $user->save();
        
        return redirect()->route('mypage.index')->with('success', '프로필이 업데이트되었습니다.');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);
        
        $user = Auth::user();
        
        if (!Hash::check($validated['current_password'], $user->mb_password)) {
            return back()->withErrors(['current_password' => '현재 비밀번호가 일치하지 않습니다.']);
        }
        
        $user->mb_password = Hash::make($validated['password']);
        $user->save();
        
        return redirect()->route('mypage.index')->with('success', '비밀번호가 변경되었습니다.');
    }
}
