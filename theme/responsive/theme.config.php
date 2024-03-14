<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 테마가 지원하는 장치 설정 pc, mobile
// 선언하지 않거나 값을 지정하지 않으면 그누보드5의 설정을 따른다.
// G5_SET_DEVICE 상수 설정 보다 우선 적용됨
if(! defined('G5_THEME_DEVICE')) define('G5_THEME_DEVICE', '');

$theme_config = array();

// 갤러리 이미지 수 등의 설정을 지정하시면 게시판관리에서 해당 값을
// 가져오기 기능을 통해 게시판 설정의 해당 필드에 바로 적용할 수 있습니다.
// 사용하지 않는 스킨 설정은 값을 비워두시면 됩니다.

// 테마에서 커뮤니티 지원여부 설정
// 커뮤니티 사용없이 쇼핑몰이 초기화면이라면 false로 설정
// false 설정이면 게시판 head, tail 은 쇼핑몰의 그것이 적용됨
if(! defined('G5_COMMUNITY_USE')) define('G5_COMMUNITY_USE', true);

// 갤러리 이미지 수 등의 설정을 지정하시면 게시판관리에서 해당 값을
// 가져오기 기능을 통해 게시판 설정의 해당 필드에 바로 적용할 수 있습니다.
// 사용하지 않는 스킨 설정은 값을 비워두시면 됩니다.
$theme_config = array(
    'set_default_skin'              => false,   // 기본환경설정의 최근게시물 등의 기본스킨 변경여부 true, false
    'preview_board_skin'            => 'basic', // 테마 미리보기 때 적용될 기본 게시판 스킨
    'preview_mobile_board_skin'     => 'basic', // 테마 미리보기 때 적용될 기본 모바일 게시판 스킨
    'cf_member_skin'                => 'basic', // 회원 스킨
    'cf_mobile_member_skin'         => 'basic', // 모바일 회원 스킨
    'cf_new_skin'                   => 'basic', // 최근게시물 스킨
    'cf_mobile_new_skin'            => 'basic', // 모바일 최근게시물 스킨
    'cf_search_skin'                => 'basic', // 검색 스킨
    'cf_mobile_search_skin'         => 'basic', // 모바일 검색 스킨
    'cf_connect_skin'               => 'basic', // 접속자 스킨
    'cf_mobile_connect_skin'        => 'basic', // 모바일 접속자 스킨
    'cf_faq_skin'                   => 'basic', // FAQ 스킨
    'cf_mobile_faq_skin'            => 'basic', // 모바일 FAQ 스킨
    'bo_gallery_cols'               => 4,       // 갤러리 이미지 수
    'bo_gallery_width'              => 215,     // 갤러리 이미지 폭
    'bo_gallery_height'             => 215,     // 갤러리 이미지 높이
    'bo_mobile_gallery_width'       => 250,     // 모바일 갤러리 이미지 폭
    'bo_mobile_gallery_height'      => 200,     // 모바일 갤러리 이미지 높이
    'bo_image_width'                => 900,     // 게시판 뷰 이미지 폭
    'qa_skin'                       => 'basic', // 1:1문의 스킨
    'qa_mobile_skin'                => 'basic', // 1:1문의 모바일 스킨
    'de_shop_skin'                  => 'basic', // 쇼핑몰 PC 기본스킨
    'de_shop_mobile_skin'           => 'basic', // 쇼핑몰 모바일 기본스킨
    'de_type1_list_use'             => 1,       // PC 히트상품 출력
    'de_type1_list_skin'            => 'main.10.skin.php', // PC 히트상품 출력스킨
    'de_type1_list_mod'             => 5,       // PC 히트상품 출력 1줄당 이미지 수
    'de_type1_list_row'             => 2,       // PC 히트상품 출력 출력 줄 수
    'de_type1_img_width'            => 160,     // PC 히트상품 이미지 폭
    'de_type1_img_height'           => 160,     // PC 히트상품 이미지 높이
    'de_type2_list_use'             => 1,       // PC 추천상품 출력
    'de_type2_list_skin'            => 'main.20.skin.php', // PC 추천상품 출력스킨
    'de_type2_list_mod'             => 4,       // PC 추천상품 출력 1줄당 이미지 수
    'de_type2_list_row'             => 2,       // PC 추천상품 출력 출력 줄 수
    'de_type2_img_width'            => 215,     // PC 추천상품 이미지 폭
    'de_type2_img_height'           => 215,     // PC 추천상품 이미지 높이
    'de_type3_list_use'             => 1,       // PC 최신상품 출력
    'de_type3_list_skin'            => 'main.40.skin.php', // PC 최신상품 출력스킨
    'de_type3_list_mod'             => 4,       // PC 최신상품 출력 1줄당 이미지 수
    'de_type3_list_row'             => 1,       // PC 최신상품 출력 출력 줄 수
    'de_type3_img_width'            => 215,     // PC 최신상품 이미지 폭
    'de_type3_img_height'           => 215,     // PC 최신상품 이미지 높이
    'de_type4_list_use'             => 1,       // PC 인기상품 출력
    'de_type4_list_skin'            => 'main.50.skin.php', // PC 인기상품 출력스킨
    'de_type4_list_mod'             => 5,       // PC 인기상품 출력 1줄당 이미지 수
    'de_type4_list_row'             => 1,       // PC 인기상품 출력 출력 줄 수
    'de_type4_img_width'            => 215,     // PC 인기상품 이미지 폭
    'de_type4_img_height'           => 215,     // PC 인기상품 이미지 높이
    'de_type5_list_use'             => 1,       // PC 할인상품 출력
    'de_type5_list_skin'            => 'main.30.skin.php', // PC 할인상품 출력스킨
    'de_type5_list_mod'             => 4,       // PC 할인상품 출력 1줄당 이미지 수
    'de_type5_list_row'             => 1,       // PC 할인상품 출력 출력 줄 수
    'de_type5_img_width'            => 215,     // PC 할인상품 이미지 폭
    'de_type5_img_height'           => 215,     // PC 할인상품 이미지 높이
    'de_mobile_type1_list_use'      => 1,       // 모바일 히트상품 출력
    'de_mobile_type1_list_skin'     => 'main.30.skin.php', // 모바일 히트상품 출력스킨
    'de_mobile_type1_list_mod'      => 2,       // 모바일 히트상품 출력 1줄당 이미지 수
    'de_mobile_type1_list_row'      => 4,       // 모바일 히트상품 출력 출력 줄 수
    'de_mobile_type1_img_width'     => 230,     // 모바일 히트상품 이미지 폭
    'de_mobile_type1_img_height'    => 230,     // 모바일 히트상품 이미지 높이
    'de_mobile_type2_list_use'      => 1,       // 모바일 추천상품 출력
    'de_mobile_type2_list_skin'     => 'main.10.skin.php', // 모바일 추천상품 출력스킨
    'de_mobile_type2_list_mod'      => 2,       // 모바일 추천상품 출력 1줄당 이미지 수
    'de_mobile_type2_list_row'      => 2,       // 모바일 추천상품 출력 출력 줄 수
    'de_mobile_type2_img_width'     => 300,     // 모바일 추천상품 이미지 폭
    'de_mobile_type2_img_height'    => 300,     // 모바일 추천상품 이미지 높이
    'de_mobile_type3_list_use'      => 1,       // 모바일 최신상품 출력
    'de_mobile_type3_list_skin'     => 'main.10.skin.php', // 모바일 최신상품 출력스킨
    'de_mobile_type3_list_mod'      => 2,       // 모바일 최신상품 출력 1줄당 이미지 수
    'de_mobile_type3_list_row'      => 4,       // 모바일 최신상품 출력 출력 줄 수
    'de_mobile_type3_img_width'     => 300,     // 모바일 최신상품 이미지 폭
    'de_mobile_type3_img_height'    => 300,     // 모바일 최신상품 이미지 높이
    'de_mobile_type4_list_use'      => 1,       // 모바일 인기상품 출력
    'de_mobile_type4_list_skin'     => 'main.20.skin.php', // 모바일 인기상품 출력스킨
    'de_mobile_type4_list_mod'      => 2,       // 모바일 인기상품 출력 1줄당 이미지 수
    'de_mobile_type4_list_row'      => 2,       // 모바일 인기상품 출력 출력 줄 수
    'de_mobile_type4_img_width'     => 80,     // 모바일 인기상품 이미지 폭
    'de_mobile_type4_img_height'    => 80,     // 모바일 인기상품 이미지 높이
    'de_mobile_type5_list_use'      => 1,       // 모바일 할인상품 출력
    'de_mobile_type5_list_skin'     => 'main.10.skin.php', // 모바일 할인상품 출력스킨
    'de_mobile_type5_list_mod'      => 2,       // 모바일 할인상품 출력 1줄당 이미지 수
    'de_mobile_type5_list_row'      => 2,       // 모바일 할인상품 출력 출력 줄 수
    'de_mobile_type5_img_width'     => 230,     // 모바일 할인상품 이미지 폭
    'de_mobile_type5_img_height'    => 230,     // 모바일 할인상품 이미지 높이
    'de_rel_list_use'               => 1,       // 관련상품 출력
    'de_rel_list_skin'              => 'relation.10.skin.php',  // 관련상품 출력 스킨
    'de_rel_list_mod'               => 5,       // 관련상품 1줄당 이미지 수
    'de_rel_img_width'              => 215,     // 관련상품 이미지 폭
    'de_rel_img_height'             => 215,     // 관련상품 이미지 높이
    'de_mobile_rel_list_use'        => 1,       // 모바일 관련상품 출력
    'de_mobile_rel_list_skin'       => 'relation.10.skin.php',  // 모바일 관련상품 출력 스킨
    'de_mobile_rel_list_mod'        => 3,       // 모바일 관련상품 1줄당 이미지 수
    'de_mobile_rel_img_width'       => 230,     // 모바일 관련상품 이미지 폭
    'de_mobile_rel_img_height'      => 230,     // 모바일 관련상품 이미지 높이
    'de_search_list_skin'           => 'list.10.skin.php',  // 검색상품 출력 스킨
    'de_search_list_mod'            => 5,       // 검색상품 1줄당 이미지 수
    'de_search_list_row'            => 5,       // 검색상품 출력 줄 수
    'de_search_img_width'           => 225,     // 검색상품 이미지 폭
    'de_search_img_height'          => 225,     // 검색상품 이미지 높이
    'de_mobile_search_list_skin'    => 'list.10.skin.php',  // 모바일 검색상품 출력 스킨
    'de_mobile_search_list_mod'     => 2,       // 모바일 검색상품 1줄당 이미지 수
    'de_mobile_search_list_row'     => 5,       // 모바일 검색상품 출력 줄 수
    'de_mobile_search_img_width'    => 230,     // 모바일 관련상품 이미지 폭
    'de_mobile_search_img_height'   => 230,     // 모바일 관련상품 이미지 높이
    'de_mimg_width'                 => 400,     // 상품상세 이미지 Width
    'de_mimg_height'                => 400,     // 상품상세 이미지 Height
    'ca_skin'                       => 'list.10.skin.php',  // 분류 리스트 스킨
    'ca_img_width'                  => 225,     // 분류 리스트 이미지 폭
    'ca_img_height'                 => 225,     // 분류 리스트 이미지 높이
    'ca_list_mod'                   => 5,       // 분류 리스트 1줄당 이미지 수
    'ca_list_row'                   => 5,       // 분류 리스트 이미지 줄 수
    'ca_mobile_skin'                => 'list.10.skin.php',  // 모바일 분류 리스트 스킨
    'ca_mobile_img_width'           => 230,     // 모바일 분류 리스트 이미지 폭
    'ca_mobile_img_height'          => 230,     // 모바일 분류 리스트 이미지 높이
    'ca_mobile_list_mod'            => 2,       // 모바일 분류 리스트 1줄당 이미지 수
    'ca_mobile_list_row'            => 5,       // 모바일 분류 리스트 이미지 줄 수
    'ev_skin'                       => 'list.10.skin.php',  // 이벤트 출력 스킨
    'ev_img_width'                  => 225,     // 이벤트 리스트 이미지 폭
    'ev_img_height'                 => 225,     // 이벤트 리스트 이미지 높이
    'ev_list_mod'                   => 5,       // 이벤트 리스트 1줄당 이미지 수
    'ev_list_row'                   => 5,       // 이벤트 리스트 이미지 줄 수
    'ev_mobile_skin'                => 'list.10.skin.php',  // 모바일 이벤트 출력 스킨
    'ev_mobile_img_width'           => 230,     // 모바일 이벤트 리스트 이미지 폭
    'ev_mobile_img_height'          => 230,     // 모바일 이벤트 리스트 이미지 높이
    'ev_mobile_list_mod'            => 2,       // 모바일 이벤트 1줄당 이미지 수
    'ev_mobile_list_row'            => 5,       // 모바일 이벤트 이미지 줄 수
    'ca_mobile_list_best_mod'       => 2,       // 모바일 상품리스트 베스트상품 1줄당 이미지 수
    'ca_mobile_list_best_row'       => 3,       // 모바일 상품리스트 베스트상품 이미지 줄 수
);