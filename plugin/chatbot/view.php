<?php
if (!defined('_GNUBOARD_')) exit;
add_stylesheet('<link rel="stylesheet" href="'.G5_CHATBOT_URL.'/style.css">', 0);
?>

<div class="chat-container">
    <div class="chat-header" id="chat-header">
        ㅇㅇ 전문가 챗봇
    </div>
    <div class="role-setting">
        <input type="text" id="ai-role" placeholder="AI의 역할을 입력하세요">
        <button id="set-role-button">역할 설정</button>
    </div>
    <div class="chat-messages" id="chat-container"></div>
    <div class="loading" id="loading">
        <div class="loading-spinner"></div>
        <div class="loading-text">응답 생성 중<span class="loading-dots"></span></div>
    </div>
    <div class="chat-input">
        <input type="text" id="user-input" placeholder="메시지를 입력하세요...">
        <button id="send-button">전송</button>
    </div>
</div>

<script src="<?php echo G5_CHATBOT_URL; ?>/script.js"></script>