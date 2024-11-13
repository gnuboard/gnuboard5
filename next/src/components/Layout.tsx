// src/components/Layout.tsx
import { useAuth } from '@/context/AuthContext';
import Link from 'next/link';

export default function Layout({ children }: { children: React.ReactNode }) {
  const { user, isLoggedIn, isLoading } = useAuth();

  return (
    <div>
      <header className="bg-white shadow">
        <nav className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <div className="flex h-16 justify-between items-center">
            <div className="flex">
              <Link href="/" className="text-xl font-bold">
                Next.js + 그누보드
              </Link>
            </div>
            
            <div className="flex items-center">
              {isLoading ? (
                <div>로딩중...</div>
              ) : isLoggedIn ? (
                <div className="flex items-center space-x-4">
                  <span>{user?.nick}님 환영합니다</span>
                  <a
                    href="/bbs/logout.php"
                    className="text-gray-600 hover:text-gray-900"
                  >
                    로그아웃
                  </a>
                </div>
              ) : (
                <Link
                  href="/login"
                  className="text-gray-600 hover:text-gray-900"
                >
                  로그인
                </Link>
              )}
            </div>
          </div>
        </nav>
      </header>

      <main className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
        {children}
      </main>
    </div>
  );
}