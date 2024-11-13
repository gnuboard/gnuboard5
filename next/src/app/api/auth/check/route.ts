// src/app/api/auth/check/route.ts
import { NextResponse } from 'next/server';

export async function GET() {
  try {
    const response = await fetch('https://gnuboard.net/api/auth_check.php', {
      credentials: 'include',
      headers: {
        'Cookie': 'PHPSESSID=' + process.env.PHPSESSID || '',
      },
    });

    const data = await response.json();
    
    return NextResponse.json(data);
  } catch (error) {
    console.error('Auth check error:', error);
    return NextResponse.json(
      { isLoggedIn: false, error: 'Auth check failed' },
      { status: 500 }
    );
  }
}