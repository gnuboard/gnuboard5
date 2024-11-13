// src/components/LoadingSpinner.tsx
'use client';

export default function LoadingSpinner() {
  return (
    <div className="flex min-h-screen items-center justify-center">
      <div className="h-32 w-32 animate-spin rounded-full border-b-2 border-t-2 border-blue-500"></div>
      <span className="ml-4 text-xl font-semibold">로딩중...</span>
    </div>
  );
}