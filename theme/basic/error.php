<?php
if (!defined('_GNUBOARD_')) {
    define('_GNUBOARD_', true);
    include_once($_SERVER['DOCUMENT_ROOT'].'/common.php');
}

// 에러 코드 가져오기 (REDIRECT_STATUS 또는 쿼리 파라미터)
$error_code = isset($_SERVER['REDIRECT_STATUS']) ? (int)$_SERVER['REDIRECT_STATUS'] : (isset($_GET['code']) ? (int)$_GET['code'] : 500);

// 에러 메시지 정의
$error_messages = [
    400 => [
        'title' => 'Bad Request',
        'message' => '잘못된 요청입니다.',
        'description' => '요청한 내용을 처리할 수 없습니다.'
    ],
    401 => [
        'title' => 'Unauthorized',
        'message' => '인증이 필요합니다.',
        'description' => '이 페이지에 접근하려면 로그인이 필요합니다.'
    ],
    403 => [
        'title' => 'Forbidden',
        'message' => '접근이 거부되었습니다.',
        'description' => '이 페이지에 접근할 권한이 없습니다.'
    ],
    404 => [
        'title' => 'Not Found',
        'message' => '페이지를 찾을 수 없습니다.',
        'description' => '요청하신 페이지가 존재하지 않거나 이동되었을 수 있습니다.'
    ],
    500 => [
        'title' => 'Internal Server Error',
        'message' => '서버 오류가 발생했습니다.',
        'description' => '일시적인 문제가 발생했습니다. 잠시 후 다시 시도해주세요.'
    ],
    503 => [
        'title' => 'Service Unavailable',
        'message' => '준비중입니다.',
        'description' => '서비스 준비 중입니다. 잠시 후 다시 이용해 주세요.'
    ]
];

// 에러 코드에 해당하는 메시지 가져오기 (없으면 500 기본값)
$error_info = isset($error_messages[$error_code]) ? $error_messages[$error_code] : $error_messages[500];

?>
<link rel="stylesheet" href="<?php echo G5_THEME_URL; ?>/css/error.css">

<main id="contents">
    <section class="error-page">
        <div id="logo">
            <a href="/"><img src="<?=G5_THEME_URL?>/img/logo.svg" alt="로고" class="logo"></a>
        </div>
        <div class="error-content">
            <div class="error-code"><?php echo $error_code; ?></div>
            <h1 class="error-title"><?php echo $error_info['title']; ?></h1>
            <p class="error-message"><?php echo $error_info['message']; ?></p>
            <p class="error-description"><?php echo $error_info['description']; ?></p>
            <div class="error-actions">
                <a href="/" class="error-btn">홈으로 이동</a>
                <a href="javascript:history.back();" class="error-btn secondary">이전 페이지</a>
            </div>
        </div>
        <?php if($config['cf_dev_mode'] == 1 && $config['cf_dev_ip'] == '') { ?>
            <a href="<?php echo G5_URL; ?>?dev_pass=1" class="dev-pass-btn"></a>
        <?php } ?>
    </section>
</main>
