// src/app/login/page.tsx
'use client';

import { useState } from 'react';
import { useAuth } from '@/context/AuthContext';
import { useRouter } from 'next/navigation';
import Link from 'next/link';

export default function LoginPage() {
  const [id, setId] = useState('');
  const [password, setPassword] = useState('');
  const { checkAuth } = useAuth();
  const router = useRouter();

  const handleLogin = async (e: React.FormEvent) => {
    e.preventDefault();
    console.log('로그인 시도:', id); // 디버깅용 로그

    try {
      const formData = new FormData();
      formData.append('mb_id', id);
      formData.append('mb_password', password);
      
      console.log('폼 데이터:', Object.fromEntries(formData)); // 디버깅용 로그

      const response = await fetch('https://gnuboard.net/bbs/login_check.php', {
        method: 'POST',
        body: formData,
        credentials: 'include',
        headers: {
          'Accept': '*/*',
        },
      });

      console.log('로그인 응답:', response); // 디버깅용 로그

      if (response.ok) {
        await checkAuth();
        router.push('/');
        router.refresh(); // 페이지 새로고침을 통한 상태 업데이트
      } else {
        alert('로그인에 실패했습니다. 아이디와 비밀번호를 확인해주세요.');
      }
    } catch (error) {
      console.error('Login failed:', error);
      alert('로그인 중 오류가 발생했습니다.');
    }
  };

  return (
    <div className="flex min-h-screen items-center justify-center">
      <form onSubmit={handleLogin} className="w-full max-w-md space-y-4 p-8 bg-white rounded-lg shadow-md">
        <h2 className="text-2xl font-bold mb-6 text-center">로그인</h2>
        
        <div>
          <label htmlFor="id" className="block text-sm font-medium text-gray-700">
            아이디
          </label>
          <input
            type="text"
            id="id"
            value={id}
            onChange={(e) => setId(e.target.value)}
            className="mt-1 block w-full rounded-md border border-gray-300 p-2 
                     focus:border-blue-500 focus:ring-blue-500"
            required
          />
        </div>
        
        <div>
          <label htmlFor="password" className="block text-sm font-medium text-gray-700">
            비밀번호
          </label>
          <input
            type="password"
            id="password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            className="mt-1 block w-full rounded-md border border-gray-300 p-2 
                     focus:border-blue-500 focus:ring-blue-500"
            required
          />
        </div>

        <button
          type="submit"
          className="w-full rounded-md bg-blue-500 px-4 py-2 text-white 
                   hover:bg-blue-600 transition-colors duration-200"
        >
          로그인하기
        </button>

        <div className="text-center mt-4">
          <Link 
            href="/"
            className="text-blue-500 hover:text-blue-600 hover:underline transition-colors duration-200"
          >
            홈으로 돌아가기
          </Link>
        </div>

        <div className="text-center mt-4">
          <a 
            href="/bbs/register.php" 
            className="text-gray-600 hover:text-gray-800 hover:underline transition-colors duration-200"
          >
            회원가입
          </a>
          <span className="mx-2 text-gray-400">|</span>
          <a 
            href="/bbs/password_lost.php" 
            className="text-gray-600 hover:text-gray-800 hover:underline transition-colors duration-200"
          >
            비밀번호 찾기
          </a>
        </div>
      </form>
    </div>
  );
}