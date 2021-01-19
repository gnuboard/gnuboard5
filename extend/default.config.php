<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 유저 사이드뷰에서 아이콘 지정 안했을시 기본 no 프로필 이미지
define('G5_NO_PROFILE_IMG', '<span class="profile_img"><img src="'.G5_IMG_URL.'/no_profile.gif" alt="no_profile" width="'.$config['cf_member_icon_width'].'" height="'.$config['cf_member_icon_height'].'"></span>');

define('G5_USE_MEMBER_IMAGE_FILETIME', TRUE);

// 썸네일 처리 방식, 비율유지 하지 않고 썸네일을 생성하려면 주석을 풀고 값은 false 입력합니다. ( true 또는 주석으로 된 경우에는 비율 유지합니다. )
//define('G5_USE_THUMB_RATIO', false);