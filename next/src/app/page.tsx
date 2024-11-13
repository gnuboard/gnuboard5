// src/app/page.tsx
'use client';

import { useAuth } from '@/context/AuthContext';
import Link from 'next/link';

export default function Home() {
  const { user, isLoggedIn, isLoading } = useAuth();

  if (isLoading) {
    return (
      <div className="flex min-h-screen items-center justify-center">
        <div className="text-xl">로딩중...</div>
      </div>
    );
  }

  return (
    <div className="min-h-screen flex flex-col items-center justify-center p-8">
      <div className="w-full max-w-4xl">
        <h1 className="mb-8 text-4xl font-bold text-center">
          Next.js + 그누보드 연동
        </h1>
        
        <div className="mb-8 text-center">
          {isLoggedIn ? (
            <div className="space-y-4">
              <p className="text-xl">환영합니다, {user?.nick}님!</p>
              <div className="space-x-4">
                <a
                  href="/bbs/board.php?bo_table=free"
                  className="inline-block rounded bg-blue-500 px-4 py-2 text-white hover:bg-blue-600 transition-colors duration-200"
                >
                  자유게시판
                </a>
                <a
                  href="/bbs/logout.php"
                  className="inline-block rounded bg-gray-500 px-4 py-2 text-white hover:bg-gray-600 transition-colors duration-200"
                >
                  로그아웃
                </a>
              </div>
            </div>
          ) : (
            <div className="space-y-4">
              <p className="text-xl">로그인이 필요합니다</p>
              <Link
                href="/login"
                className="inline-block rounded bg-blue-500 px-4 py-2 text-white hover:bg-blue-600 transition-colors duration-200"
              >
                로그인하기
              </Link>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}