// src/components/Login.tsx
import { useState } from 'react';
import { useAuth } from '@/context/AuthContext';
import { useRouter } from 'next/router';

export default function Login() {
  const [id, setId] = useState('');
  const [password, setPassword] = useState('');
  const { checkAuth } = useAuth();
  const router = useRouter();

  const handleLogin = async (e: React.FormEvent) => {
    e.preventDefault();

    try {
      const formData = new FormData();
      formData.append('mb_id', id);
      formData.append('mb_password', password);
      
      const response = await fetch('https://gnuboard.net/bbs/login_check.php', {
        method: 'POST',
        body: formData,
        credentials: 'include',
      });

      if (response.ok) {
        await checkAuth();
        // /next로 리다이렉트 (basePath가 자동으로 추가됨)
        router.push('/');
      }
    } catch (error) {
      console.error('Login failed:', error);
    }
  };

  return (
    <div className="flex min-h-screen items-center justify-center">
      <form onSubmit={handleLogin} className="w-full max-w-md space-y-4 p-8">
        <div>
          <label htmlFor="id" className="block text-sm font-medium">
            아이디
          </label>
          <input
            type="text"
            id="id"
            value={id}
            onChange={(e) => setId(e.target.value)}
            className="mt-1 block w-full rounded-md border p-2"
            required
          />
        </div>
        
        <div>
          <label htmlFor="password" className="block text-sm font-medium">
            비밀번호
          </label>
          <input
            type="password"
            id="password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            className="mt-1 block w-full rounded-md border p-2"
            required
          />
        </div>

        <button
          type="submit"
          className="w-full rounded-md bg-blue-500 px-4 py-2 text-white hover:bg-blue-600"
        >
          로그인
        </button>
      </form>
    </div>
  );
}