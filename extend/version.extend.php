<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

define('G5_VERSION', '그누보드5');
define('G5_GNUBOARD_VER', '5.4.7');
// 그누보드5.4.5.5 버전과 영카트5.4.5.5.1 버전을 합쳐서 그누보드5.4.6 버전에서 시작함 (kagla-210617)
// G5_YOUNGCART_VER 이 상수를 사용하는 곳이 있으므로 주석 처리 해제함
define('G5_YOUNGCART_VER', '5.4.5.5.1');

// 자바스크립트와 CSS 파일을 새로 다운로드 하도록 파일의 끝에 년월일 지정
// 예) https://도메인/css/default.css?ver=210618
// 예) https://도메인/js/common.js?ver=210618
define('G5_CSS_VER', '210618');
define('G5_JS_VER',  '210618');
