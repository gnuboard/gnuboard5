<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

//------------------------------------------------------------------------------
// 카카오톡 알림 메시지 상수 모음 시작
//------------------------------------------------------------------------------

define('G5_KAKAO5_DIR',             'kakao5');
define('G5_KAKAO5_PATH',            G5_PLUGIN_PATH.'/'.G5_KAKAO5_DIR);
define('G5_KAKAO5_URL',             G5_PLUGIN_URL.'/'.G5_KAKAO5_DIR);

// 연동환경 설정, true-테스트, false-운영(Production), (기본값:false)
define('G5_KAKAO5_IS_TEST',         false);

// 카카오톡 알림 메시지 테이블명
$g5['kakao5_prefix']                  = G5_TABLE_PREFIX.'kakao5_';
$g5['kakao5_preset_table']            = $g5['kakao5_prefix'] . 'preset'; // 알림톡 - 프리셋 관리
$g5['kakao5_preset_history_table']    = $g5['kakao5_prefix'] . 'preset_history'; // 알림톡 - 프리셋 전송내역 관리

//------------------------------------------------------------------------------
// 알림톡 프리셋 버튼 링크 매핑
// - 버튼 템플릿에서 #{주문상세}와 같은 플레이스홀더를 사용할 때, 실제 URL로 치환하기 위한 매핑입니다.
// - URL 내에 {필드명} 형태의 변수가 포함된 경우, 해당 값은 $conditions 배열에 전달되어야 정상적으로 치환됩니다.
//   예시: '#{주문상세}' => ['url' => G5_URL.'/shop/orderinquiryview.php?od_id={od_id}']
//         위의 경우 $conditions['od_id'] 값이 있어야 {od_id}가 실제 주문번호로 치환됩니다.
//------------------------------------------------------------------------------
$kakao5_preset_button_links = [
    // 공통
    '#{홈페이지}' => [
        'url' => G5_URL,
        'description' => '홈페이지 메인으로 이동하는 링크입니다.'
    ],
    '#{로그인}' => [
        'url' => G5_URL.'/bbs/login.php',
        'description' => '로그인 페이지로 이동하는 링크입니다.'
    ],
    '#{마이페이지}' => [
        'url' => G5_URL.'/shop/mypage.php',
        'description' => '마이페이지로 이동하는 링크입니다.'
    ],

    // 게시판
    '#{게시판}' => [
        'url' => G5_URL.'/bbs/board.php?bo_table={bo_table}',
        'description' => '특정 게시판 목록으로 이동하는 링크입니다. {bo_table}은 게시판 아이디로 자동 치환됩니다.'
    ],
    '#{게시글}' => [
        'url' => G5_URL.'/bbs/board.php?bo_table={bo_table}&wr_id={wr_id}',
        'description' => '특정 게시글 상세 페이지로 이동하는 링크입니다. {bo_table}, {wr_id}는 자동 치환됩니다.'
    ],

    // 주문/쇼핑몰
    '#{주문내역}' => [
        'url' => G5_URL.'/shop/orderinquiry.php',
        'description' => '주문내역(리스트) 페이지로 이동하는 링크입니다.'
    ],
    '#{주문상세}' => [
        'url' => G5_URL.'/shop/orderinquiryview.php?od_id={od_id}',
        'description' => '주문상세 페이지로 이동하는 링크입니다. {od_id}는 주문번호로 자동 치환됩니다.'
    ],
    '#{장바구니}' => [
        'url' => G5_URL.'/shop/cart.php',
        'description' => '장바구니 페이지로 이동하는 링크입니다.'
    ],
    '#{상품상세}' => [
        'url' => G5_URL.'/shop/item.php?it_id={it_id}',
        'description' => '재입고된 상품의 상세 페이지로 이동하는 링크입니다. {it_id}는 상품코드로 자동 치환됩니다.'
    ],

    // 1:1 문의
    '#{문의상세}' => [
        'url' => G5_URL.'/bbs/qaview.php?qa_id={qa_id}',
        'description' => '1:1 문의 상세 페이지로 이동하는 링크입니다. {qa_id}는 문의글 ID로 자동 치환됩니다.'
    ],

    // 관리자
    '#{관리자주문내역}' => [
        'url' => G5_ADMIN_URL.'/shop_admin/orderlist.php',
        'description' => '관리자 주문내역(리스트) 페이지로 이동하는 링크입니다.'
    ],
    '#{관리자주문상세}' => [
        'url' => G5_ADMIN_URL.'/shop_admin/orderform.php?od_id={od_id}',
        'description' => '관리자 주문상세 페이지로 이동하는 링크입니다. {od_id}는 주문번호로 자동 치환됩니다.'
    ],
    '#{투표상세}' => [
        'url' => G5_ADMIN_URL.'/poll_form.php?w=u&po_id={po_id}',
        'description' => '관리자 투표 상세 페이지로 이동하는 링크입니다. {po_id}는 투표 ID로 자동 치환됩니다.'
    ],
];

//------------------------------------------------------------------------------
// 알림톡 프리셋 변수 목록 정의 파일
// - 템플릿에서 사용할 수 있는 변수들의 목록과 각 변수의 설명, 실제 데이터베이스 테이블 및 컬럼 정보를 정의합니다.
// - 각 카테고리별로 사용 가능한 변수와 설명, 실제 매핑되는 DB 테이블/컬럼 정보를 배열로 관리합니다.
// - 알림톡 메시지 전송 시, 템플릿 내 #{변수명} 형태로 사용되며, 해당 변수에 맞는 실제 값으로 치환됩니다.
//------------------------------------------------------------------------------
$kakao5_preset_variable_list = [
    [
        'category' => '공통',
        'variables' => [
            [
                'name' => '#{회사명}',
                'description' => '해당 메시지를 발송하는 회사명(브랜드명)이 표시됩니다. (예시 : [마이쇼핑])',
                'column' => 'cf_title',
                'table' => 'config_table',
                'condition_key' => ''
            ],
        ],
    ],
    [
        'category' => '회원',
        'variables' => [
            [
                'name' => '#{이름}',
                'description' => '회원가입 시 입력한 고객님의 이름이 표시됩니다. (예시 : [홍길동])',
                'column' => 'mb_name',
                'table' => 'member_table',
                'condition_key' => 'mb_id'
            ],
            [
                'name' => '#{회원아이디}',
                'description' => '회원가입 시 입력한 아이디가 표시됩니다. (예시 : [user1234])',
                'table' => 'member_table',
                'column' => 'mb_id',
                'condition_key' => 'mb_id'
            ],
            [
                'name' => '#{닉네임}',
                'description' => '회원가입 시 입력한 닉네임이 표시됩니다. (예시 : [길동이])',
                'table' => 'member_table',
                'column' => 'mb_nick',
                'condition_key' => 'mb_id'
            ],
            [
                'name' => '#{이메일}',
                'description' => '회원가입 시 입력한 이메일 주소가 표시됩니다. (예시 : [hong@example.com])',
                'table' => 'member_table',
                'column' => 'mb_email',
                'condition_key' => 'mb_id'
            ],
            [
                'name' => '#{가입일}',
                'description' => '회원가입한 날짜가 표시됩니다. (예시 : [2024-06-20])',
                'column' => 'mb_datetime',
                'table' => 'member_table',
                'condition_key' => 'mb_id'
            ]
        ]
    ],
    [
        'category' => '게시판',
        'variables' => [
            [
                'name' => '#{게시판명}',
                'description' => '게시판의 제목이 표시됩니다. (예시 : [공지사항])',
                'table' => 'board_table',
                'column' => 'bo_subject',
                'condition_key' => 'bo_table'
            ],
            [
                'name' => '#{게시글제목}',
                'description' => '게시글의 제목이 표시됩니다. (예시 : [서비스 점검 안내])',
                'table' => 'write_prefix',
                'table_placeholder' => '{bo_table}',
                'column' => 'wr_subject',
                'condition_key' => 'wr_id'
            ],
            [
                'name' => '#{작성자명}',
                'description' => '게시글 작성자의 이름이 표시됩니다. (예시 : [홍길동])',
                'table' => 'write_prefix',
                'table_placeholder' => '{bo_table}',
                'column' => 'wr_name',
                'condition_key' => 'wr_id'
            ],
            [
                'name' => '#{작성일시}',
                'description' => '게시글이 작성된 일시가 표시됩니다. (예시 : [2024-06-20 14:35:20])',
                'table' => 'write_prefix',
                'table_placeholder' => '{bo_table}',
                'column' => 'wr_datetime',
                'condition_key' => 'wr_id',
            ],
            [
                'name' => '#{댓글작성자}',
                'description' => '댓글 작성자의 이름이 표시됩니다. (예시 : [이몽룡])',
                'column' => 'wr_name_comment',
            ],
        ]
    ],
    [
        'category' => '주문',
        'variables' => [
            [
                'name' => '#{주문자명}',
                'description' => '주문 시 입력한 주문자의 이름이 표시됩니다. (예시 : [홍길동])',
                'column' => 'od_name',
                'table' => 'g5_shop_order_table',
                'condition_key' => 'od_id'
            ],
            [
                'name' => '#{주문번호}',
                'description' => '해당 주문의 주문번호가 표시됩니다. (예시 : [202406190001])',
                'column' => 'od_id',
                'table' => 'g5_shop_order_table',
                'condition_key' => 'od_id'
            ],
            [
                'name' => '#{상품명}',
                'description' => '주문한 또는 처리된 상품명이 표시됩니다. 상품이 여러개인 경우 "외 N건" 형식으로 표시됩니다. (예시: [아메리카노 외 2건])<br>
                                - <b>주문 시</b>: 주문한 상품명이 표시됩니다.<br>
                                - <b>주문취소 / 반품 / 품절 처리 시</b>: 해당 처리 대상 상품명만 표시됩니다.',
                'column' => 'it_name',
                'table' => 'g5_shop_cart_table',
                'condition_key' => 'od_id'
            ],
            [
                'name' => '#{주문금액}',
                'description' => '주문의 총 결제금액이 표시됩니다. (예시 : [120,000])',
                'column' => 'od_receipt_price',
                'table' => 'g5_shop_order_table',
                'condition_key' => 'od_id',
                'is_price' => true
            ],
            [
                'name' => '#{취소사유}',
                'description' => '<b>고객이 주문취소 시</b> 입력한 취소사유가 표시됩니다. (예시: [단순변심])',
                'column' => 'cancel_memo',
            ]
        ]
    ],
    [
        'category' => '주문 - 무통장 입금 관련',
        'variables' => [
            [
                'name' => '#{은행계좌번호}',
                'description' => '무통장 입금 시 제공되는 은행명과 계좌명이 표시됩니다. (예시 : [우리은행 123-456-7890])',
                'column' => 'od_bank_account',
                'table' => 'g5_shop_order_table',
                'condition_key' => 'od_id'
            ],
            [
                'name' => '#{입금자명}',
                'description' => '입금자가 입력한 이름이 표시됩니다. (예시 : [홍길동])',
                'column' => 'od_deposit_name',
                'table' => 'g5_shop_order_table',
                'condition_key' => 'od_id'
            ]
        ]
    ],
    [
        'category' => '배송',
        'variables' => [
            [
                'name' => '#{택배회사}',
                'description' => '배송을 진행하는 택배사 이름이 표시됩니다. (예시 : [CJ대한통운])',
                'table' => 'g5_shop_order_table',
                'column' => 'od_delivery_company',
                'condition_key' => 'od_id'
            ],
            [
                'name' => '#{운송장번호}',
                'description' => '배송 송장번호가 표시됩니다. (예시 : [123456789012])',
                'column' => 'od_invoice',
                'table' => 'g5_shop_order_table',
                'condition_key' => 'od_id'
            ]
        ]
    ],
    [
        'category' => '투표',
        'variables' => [
            [
                'name' => '#{투표제목}',
                'description' => '해당 의견이 속한 투표의 제목입니다. (예시 : [서비스 만족도 조사])',
                'table' => 'poll_table',
                'column' => 'po_subject',
                'condition_key' => 'po_id'
            ],
            [
                'name' => '#{응답자명}',
                'description' => '기타 의견을 작성한 응답자의 이름입니다. (예시 : [홍길동])',
                'table' => 'poll_etc_table',
                'column' => 'pc_name',
                'condition_key' => 'pc_id'
            ],
            [
                'name' => '#{응답일시}',
                'description' => '기타 의견이 작성된 일시입니다. (예시 : [2024-06-20 14:35:20])',
                'table' => 'poll_etc_table',
                'column' => 'pc_datetime',
                'condition_key' => 'pc_id'
            ],
            [
                'name' => '#{응답내용}',
                'description' => '기타 의견으로 작성된 텍스트입니다. (예시 : [서비스가 매우 만족스러웠습니다.])',
                'table' => 'poll_etc_table',
                'column' => 'pc_idea',
                'condition_key' => 'pc_id'
            ],
        ]
    ],
    [
        'category' => '1:1 문의',
        'variables' => [
            [
                'name' => '#{문의제목}',
                'description' => '1:1 문의 제목이 표시됩니다. (예시 : [상품 환불 관련 문의])',
                'table' => 'qa_content_table',
                'column' => 'qa_subject',
                'condition_key' => 'qa_id'
            ],
            [
                'name' => '#{문의자명}',
                'description' => '1:1 문의를 작성한 회원의 이름이 표시됩니다. (예시 : [홍길동])',
                'table' => 'qa_content_table',
                'column' => 'qa_name',
                'condition_key' => 'qa_id'
            ],
            [
                'name' => '#{문의일시}',
                'description' => '회원이 1:1 문의를 작성한 일시가 표시됩니다. (예시 : [2024-06-20 14:35:20])',
                'table' => 'qa_content_table',
                'column' => 'qa_datetime',
                'condition_key' => 'qa_id'
            ],
        ]
    ],
];