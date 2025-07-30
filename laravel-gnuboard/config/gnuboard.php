<?php

return [
    // 관리자 ID
    'admin_id' => env('G5_ADMIN_ID', 'admin'),
    
    // 회원 가입시 설정
    'register_level' => env('G5_REGISTER_LEVEL', 2),
    'register_point' => env('G5_REGISTER_POINT', 1000),
    
    // 로그인시 포인트
    'login_point' => env('G5_LOGIN_POINT', 100),
    
    // 글쓰기 포인트
    'write_point' => env('G5_WRITE_POINT', 10),
    'comment_point' => env('G5_COMMENT_POINT', 5),
    'read_point' => env('G5_READ_POINT', -1),
    'download_point' => env('G5_DOWNLOAD_POINT', -20),
    
    // 업로드 설정
    'upload_max_filesize' => env('G5_UPLOAD_MAX_FILESIZE', 1048576), // 1MB
    'upload_extensions' => ['jpg', 'jpeg', 'gif', 'png', 'zip', 'txt', 'pdf'],
    
    // 이미지 설정
    'image_width' => env('G5_IMAGE_WIDTH', 600),
    'image_quality' => env('G5_IMAGE_QUALITY', 90),
    
    // 페이지당 목록 수
    'page_rows' => env('G5_PAGE_ROWS', 15),
    
    // 새글 표시 기간 (일)
    'new_del_days' => env('G5_NEW_DEL_DAYS', 30),
    
    // 쿠키 도메인
    'cookie_domain' => env('G5_COOKIE_DOMAIN', ''),
    
    // 암호화 키
    'encrypt_key' => env('G5_ENCRYPT_KEY', 'gnuboard5laravel'),
    
    // 스킨 경로
    'skin_path' => 'skin',
    'mobile_skin_path' => 'mobile/skin',
    
    // 에디터
    'editor' => env('G5_EDITOR', 'textarea'),
    
    // 캡차 사용
    'use_captcha' => env('G5_USE_CAPTCHA', false),
    
    // 소셜 로그인
    'social_login_use' => env('G5_SOCIAL_LOGIN_USE', false),
];