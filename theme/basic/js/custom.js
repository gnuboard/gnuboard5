// 기능들 연결
jQuery(function($) {
    if(g5_lang_type) {
        // 언어 선택 변경 이벤트
        $("#lang_select").on('change', function(){
            var lang = $(this).val();
            if (!lang) return;
            
            $.ajax({
                url: g5_theme_url +'/ajax/lang_change.php',
                type: 'POST',
                data: { lang: lang },
                dataType: 'json',
                success: function(response) {
                    if (response.error) {
                        console.log(response.message);
                        alert(response.message);
                        // 이전 값으로 복원
                        $("#lang_select").val(g5_lang);
                    } else {
                        // 성공 시 페이지 리로드
                        location.reload();
                    }
                },
                error: function(xhr, status, error) {
                    var errorMsg = '언어 변경 중 오류가 발생했습니다.';
                    if (xhr.responseText) {
                        try {
                            var response = JSON.parse(xhr.responseText);
                            if (response.message) {
                                errorMsg = response.message;
                            }
                        } catch(e) {
                            // JSON 파싱 실패 시 기본 메시지 사용
                        }
                    }
                    alert(errorMsg);
                    // 이전 값으로 복원
                    $("#lang_select").val(g5_lang);
                }
            });
        });
    }

    if(g5_view_mode) {
        // 다크모드 초기화 및 토글
        (function() {
            var DARK_MODE_KEY = 'g5_dark_mode';
            var $html = $('html');
            var $toggle = $("#dark_mode_toggle");
            
            // 다크모드 적용 함수
            function applyDarkMode(isDark) {
                if (isDark) {
                    $html.addClass('dark-mode');
                    $toggle.addClass('dark');
                } else {
                    $html.removeClass('dark-mode');
                    $toggle.removeClass('dark');
                }
            }
            
            // 다크모드 상태 가져오기 (사용자 선택 > 시스템 설정)
            function getDarkModePreference() {
                // 1순위: 사용자 선택 (localStorage)
                var userPreference = localStorage.getItem(DARK_MODE_KEY);
                if (userPreference !== null) {
                    return userPreference === 'true';
                }
                
                // 2순위: 시스템 설정
                if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    return true;
                }
                
                return false;
            }
            
            // 초기 로드 시 다크모드 적용 (head.sub.php에서 이미 적용했지만, 토글 버튼 상태 동기화)
            var isDark = getDarkModePreference();
            applyDarkMode(isDark);
            
            // 토글 이벤트
            $toggle.on('click', function(){
                var currentDark = $html.hasClass('dark-mode');
                var newDark = !currentDark;
                
                // 사용자 선택 저장
                localStorage.setItem(DARK_MODE_KEY, newDark);
                
                // 다크모드 적용
                applyDarkMode(newDark);
            });
            
            // 시스템 설정 변경 감지 (선택사항)
            if (window.matchMedia) {
                window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
                    // 사용자 선택이 없을 때만 시스템 설정 반영
                    if (localStorage.getItem(DARK_MODE_KEY) === null) {
                        applyDarkMode(e.matches);
                    }
                });
            }
        })();
    }
});