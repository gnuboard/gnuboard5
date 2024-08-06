const API_URL = './api_proxy.php';
let conversationHistory = [];
let aiRole = "";

function addMessage(sender, message) {
    const chatContainer = $('#chat-container');
    chatContainer.append(`<div class="message ${sender}">${message}</div>`);
    chatContainer.scrollTop(chatContainer[0].scrollHeight);
}

function setAIRole() {
    aiRole = $('#ai-role').val().trim();
    if (aiRole) {
        conversationHistory = [{ role: 'user', parts: [{ text: `당신은 ${aiRole} 전문가입니다. 이 역할에 맞게 대화해 주세요.` }] }];
        $('#chat-header').text(`${aiRole} 전문가 챗봇`);
        addMessage('bot', `AI의 역할이 "${aiRole} 전문가"로 설정되었습니다. 대화를 시작하세요.`);
        focusInput();
    }
}

function formatResponse(text) {
    const sentenceEndRegex = /[.!?]\s+/g;
    return text.replace(sentenceEndRegex, match => match + '\n');
}

function sendMessage() {
    const userInput = $('#user-input').val().trim();
    if (userInput) {
        addMessage('user', userInput);
        $('#user-input').val('');
        $('#loading').show();

        conversationHistory.push({ role: 'user', parts: [{ text: userInput }] });

        $.ajax({
            url: API_URL,
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                contents: conversationHistory,
                generationConfig: {
                    temperature: 0.9,
                    topK: 1,
                    topP: 1,
                    maxOutputTokens: 2048,
                },
            }),
            success: function(response) {
                $('#loading').hide();
                const data = JSON.parse(response);
                if (data.error) {
                    console.error('API 오류:', data.error);
                    addMessage('bot', '죄송합니다. 오류가 발생했습니다. 나중에 다시 시도해 주세요.');
                } else {
                    const botResponse = data.candidates[0].content.parts[0].text;
                    const formattedResponse = formatResponse(botResponse);
                    addMessage('bot', formattedResponse);
                    conversationHistory.push({ role: 'model', parts: [{ text: botResponse }] });
                }
                focusInput();
            },
            error: function(xhr, status, error) {
                $('#loading').hide();
                console.error('AJAX 오류:', error);
                addMessage('bot', '죄송합니다. 오류가 발생했습니다. 나중에 다시 시도해 주세요.');
                focusInput();
            }
        });
    }
}

function focusInput() {
    $('#user-input').focus();
}

$(document).ready(function() {
    $('#send-button').click(sendMessage);
    $('#set-role-button').click(setAIRole);
    $('#user-input').keypress(function(e) {
        if (e.which == 13) {
            sendMessage();
            return false;
        }
    });

    // 초기 메시지
    addMessage('bot', '안녕하세요! ㅇㅇ 전문가 챗봇입니다.\nAI의 역할을 설정한 후 대화를 시작해 주세요.');
    
    // 페이지 로드 시 입력 필드에 포커스
    focusInput();
});