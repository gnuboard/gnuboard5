<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

//플러그인 폴더 이름 및 스킨 폴더 이름
define('SMARTEDITOR_UPLOAD_IMG_CHECK', 1);  // 이미지 파일을 썸네일 할수 있는지 여부를 체크합니다. ( 해당 파일이 이미지 파일인지 체크합니다. 1이면 사용, 0이면 사용 안함 )
define('SMARTEDITOR_UPLOAD_RESIZE', 0);  // 스마트에디터 업로드 이미지파일 JPG, PNG 리사이즈 1이면 사용, 0이면 사용안함
define('SMARTEDITOR_UPLOAD_MAX_WIDTH', 1200);  // 스마트에디터 업로드 이미지 리사이즈 제한 width
define('SMARTEDITOR_UPLOAD_MAX_HEIGHT', 2800);  // 스마트에디터 업로드 이미지 리사이즈 제한 height
define('SMARTEDITOR_UPLOAD_SIZE_LIMIT', 20);  // 스마트에디터 업로드 사이즈 제한 ( 기본 20MB )
define('SMARTEDITOR_UPLOAD_IMAGE_QUALITY', 98);  // 썸네일 이미지 JPG, PNG 압축률;