<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 에디터(SmartEditor2·CHEditor5) 이미지 업로드 제한 설정 (KVE-2026-3251)
// 값을 바꾸려면 이 파일을 고치거나, 이 파일보다 먼저 읽히는 설정(config.php 등)에서 같은 상수를 정의한다.

// 비회원의 에디터 이미지 업로드 허용 여부. 기본값은 허용하지 않음(false).
// 손님 글쓰기 게시판에서 비회원이 에디터로 이미지를 올려야 하면 true로 바꾼다.
if (!defined('G5_EDITOR_UPLOAD_GUEST')) define('G5_EDITOR_UPLOAD_GUEST', false);

// 파일 하나의 최대 크기(바이트). 0이면 제한하지 않는다(PHP·웹 서버 업로드 상한은 그대로 적용).
if (!defined('G5_EDITOR_UPLOAD_FILE_MAX_SIZE')) define('G5_EDITOR_UPLOAD_FILE_MAX_SIZE', 20 * 1024 * 1024);

// 누적 제한을 세는 기간(초). 이 기간 안의 업로드만 센다.
if (!defined('G5_EDITOR_UPLOAD_WINDOW')) define('G5_EDITOR_UPLOAD_WINDOW', 3600);

// 기간 안에 세션·회원(비회원은 세션·IP)마다 올릴 수 있는 파일 수. 0이면 제한하지 않는다.
if (!defined('G5_EDITOR_UPLOAD_MAX_FILES')) define('G5_EDITOR_UPLOAD_MAX_FILES', 100);

// 기간 안에 세션·회원(비회원은 세션·IP)마다 올릴 수 있는 누적 바이트. 0이면 제한하지 않는다.
if (!defined('G5_EDITOR_UPLOAD_MAX_BYTES')) define('G5_EDITOR_UPLOAD_MAX_BYTES', 200 * 1024 * 1024);

// 최고관리자는 파일 수·누적 제한에서 제외한다(파일당 크기 제한은 적용).
if (!defined('G5_EDITOR_UPLOAD_ADMIN_EXEMPT')) define('G5_EDITOR_UPLOAD_ADMIN_EXEMPT', true);
