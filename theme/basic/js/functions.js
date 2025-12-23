// 함수 집합 파일

/**
 * Lazy Loading 함수
 * IntersectionObserver를 사용하여 뷰포트에 진입할 때 이미지를 지연 로드합니다.
 * img 태그와 배경 이미지를 지원하며, 로딩 인디케이터를 표시합니다.
 * @param {Object} options - 설정 옵션
 * @param {string} options.rootMargin - 추가 여백 (기본값: "0px")
 * @param {number} options.threshold - 로드 시점 임계값 (기본값: 0.1)
 * @param {string} options.selector - Lazy Load 대상 선택자 (기본값: '.lazy-load')
 * @param {string} options.indicatorClass - 인디케이터 클래스명 (기본값: 'loading-indicator')
 */
function lazyLoadImages(options = {}) {
    const settings = $.extend(
        {
            rootMargin: "0px", // 추가 여백
            threshold: 0.1, // 10% 보일 때 로드
            selector: '.lazy-load', // Lazy Load 대상
            indicatorClass: 'loading-indicator' // 인디케이터 클래스
        },
        options
    );

    // IntersectionObserver 초기화
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const $img = $(entry.target);

                    // 인디케이터 추가
                    addLoadingIndicator($img, settings.indicatorClass);

                    if ($img.is('img') && $img.data('src')) {
                        $img.on('load', function () {
                            removeLoadingIndicator($img, settings.indicatorClass); // 로드 완료 시 제거
                        });
                        $img.attr('src', $img.data('src')).removeAttr('data-src');
                    } else if ($img.data('src')) {
                        // 배경 이미지인 경우
                        $img.css('background-image', `url(${$img.data('src')})`)
                            .removeAttr('data-src');
                        removeLoadingIndicator($img, settings.indicatorClass); // 배경 이미지는 즉시 제거
                    }

                    observer.unobserve(entry.target); // 더 이상 관찰하지 않음
                }
            });
        }, { rootMargin: settings.rootMargin, threshold: settings.threshold });

        // Lazy Load 대상 요소를 관찰 대상으로 설정
        $(settings.selector).each(function () {
            observer.observe(this);
        });
    } else {
        // IntersectionObserver를 지원하지 않는 경우 폴백
        $(settings.selector).each(function () {
            const $img = $(this);
            if ($img.data('src')) {
                if ($img.is('img')) {
                    $img.attr('src', $img.data('src')).removeAttr('data-src');
                } else {
                    $img.css('background-image', `url(${$img.data('src')})`)
                        .removeAttr('data-src');
                }
            }
        });
    }
}


/**
 * 로딩 인디케이터 추가 함수
 * 이미지 로딩 중임을 표시하는 스피너 인디케이터를 요소에 추가합니다.
 * @param {jQuery|HTMLElement} element - 인디케이터를 추가할 대상 요소
 * @param {string} indicatorClass - 인디케이터에 적용할 CSS 클래스명
 */
function addLoadingIndicator(element, indicatorClass) {
    const $targetElement = $(element); // 변수 이름 변경

    if (!$targetElement.length || $targetElement.find(`.${indicatorClass}`).length > 0) return;

    const $indicator = $('<div>')
        .addClass(indicatorClass)
        .css({
            position: 'absolute',
            top: '50%',
            left: '50%',
            transform: 'translate(-50%, -50%)',
            width: '20px',
            height: '20px',
            border: '3px solid #ccc',
            borderTop: '3px solid #000',
            borderRadius: '50%',
            animation: 'spin 1s linear infinite',
            zIndex: 1000
        });

    // 부모 요소가 relative 스타일을 가져야 하므로 설정
    $targetElement.css('position', 'relative').append($indicator);
}

/**
 * 로딩 인디케이터 제거 함수
 * 요소에서 로딩 인디케이터를 제거합니다.
 * @param {jQuery|HTMLElement} element - 인디케이터를 제거할 대상 요소
 * @param {string} indicatorClass - 제거할 인디케이터의 CSS 클래스명
 */
function removeLoadingIndicator(element, indicatorClass) {
    const $targetElement = $(element); // 변수 이름 변경
    if (!$targetElement.length) return;
    $targetElement.find(`.${indicatorClass}`).remove();
}


// 모든 Swiper를 저장하는 전역 객체 (요소를 키로 사용)
const initializedSwipers = new Map();

/**
 * 자동 Swiper 초기화 함수
 * 페이지의 모든 .swiper 클래스를 가진 요소를 자동으로 찾아서 초기화합니다.
 * IntersectionObserver를 사용하여 뷰포트에 진입할 때만 초기화하고,
 * 벗어나면 슬라이드를 비활성화하여 성능을 최적화합니다.
 * data 속성으로 옵션을 커스터마이징할 수 있습니다.
 * 
 * 사용 예시:
 * <div class="swiper" data-swiper-autoplay="3000" data-swiper-slides="3" data-swiper-effect="fade">
 *     <div class="swiper-wrapper">
 *         <div class="swiper-slide">슬라이드 1</div>
 *     </div>
 *     <div class="swiper-pagination"></div>
 *     <div class="swiper-button-next"></div>
 *     <div class="swiper-button-prev"></div>
 *     <div class="swiper-progress-bar"></div>
 * </div>
 * 
 * data 속성:
 * - data-swiper-autoplay: 자동 재생 딜레이 (ms)
 * - data-swiper-slides: 한 번에 보여줄 슬라이드 수
 * - data-swiper-space: 슬라이드 간격 (px)
 * - data-swiper-loop: 루프 사용 여부 (true/false)
 * - data-swiper-direction: 방향 (horizontal/vertical)
 * - data-swiper-effect: 전환 효과 (slide/fade/cube/coverflow/flip)
 * - data-swiper-speed: 전환 속도 (ms)
 * - data-swiper-centered: 슬라이드 중앙 정렬 (true/false)
 * - data-swiper-pagination-type: 페이지네이션 타입 (bullets/fraction/progressbar)
 */
function autoInitializeSwipers() {
    const swiperElements = document.querySelectorAll('.swiper');
    
    if (swiperElements.length === 0) return;
    
    // 옵저버 지원 여부 확인
    const supportsIntersectionObserver = 'IntersectionObserver' in window;
    
    swiperElements.forEach((element) => {
        // 이미 초기화된 Swiper는 건너뛰기
        if (initializedSwipers.has(element)) return;
        
        // data 속성에서 옵션 읽기
        const autoplayDelay = element.dataset.swiperAutoplay ? parseInt(element.dataset.swiperAutoplay) : null;
        let slidesPerView = element.dataset.swiperSlides ? parseFloat(element.dataset.swiperSlides) : 1;
        const spaceBetween = element.dataset.swiperSpace ? parseInt(element.dataset.swiperSpace) : 0;
        const direction = element.dataset.swiperDirection || 'horizontal';
        const effect = element.dataset.swiperEffect || 'slide';
        const speed = element.dataset.swiperSpeed ? parseInt(element.dataset.swiperSpeed) : 300;
        const centered = element.dataset.swiperCentered === 'true';
        const paginationType = element.dataset.swiperPaginationType || 'bullets';
        const loopEnabled = element.dataset.swiperLoop === 'true';
        
        // fade, cube, flip 효과는 slidesPerView를 1로 강제
        if (['fade', 'cube', 'flip'].includes(effect)) {
            slidesPerView = 1;
        }
        
        // Navigation과 Pagination 요소 찾기
        const paginationEl = element.querySelector('.swiper-pagination');
        const nextEl = element.querySelector('.swiper-button-next');
        const prevEl = element.querySelector('.swiper-button-prev');
        
        // 기본 Swiper 옵션
        const swiperOptions = {
            slidesPerView: slidesPerView,
            spaceBetween: spaceBetween,
            loop: loopEnabled,
            loopAdditionalSlides : loopEnabled ? 1 : 0,
            direction: direction,
            effect: effect,
            speed: speed,
            centeredSlides: centered,
            watchOverflow: true,
        };
        
        // Pagination 설정
        if (paginationEl) {
            swiperOptions.pagination = {
                el: paginationEl,
                clickable: true,
                type: paginationType
            };
        }
        
        // Navigation 설정
        if (nextEl && prevEl) {
            swiperOptions.navigation = {
                nextEl: nextEl,
                prevEl: prevEl
            };
        }
        
        // autoplay 옵션 추가
        if (autoplayDelay) {
            swiperOptions.autoplay = {
                delay: autoplayDelay,
                disableOnInteraction: false
            };
        }
        
        // 프로그레스 바 관련 이벤트
        const progressBar = element.querySelector('.swiper-progress-bar');
        if (progressBar && autoplayDelay) {
            swiperOptions.on = {
                init: function() {
                    startProgressBar(progressBar, autoplayDelay);
                },
                slideChangeTransitionStart: function() {
                    resetProgressBar(progressBar);
                    startProgressBar(progressBar, autoplayDelay);
                },
                autoplayStop: function() {
                    resetProgressBar(progressBar);
                }
            };
        }
        
        
        if (supportsIntersectionObserver) {
            // 옵저버로 슬라이드 보일 때만 초기화
            const observer = new IntersectionObserver((entries, obs) => {
                entries.forEach(entry => {
                    const swiper = initializedSwipers.get(element);
                    
                    if (entry.isIntersecting) {
                        // 슬라이드가 뷰포트 안에 있을 때 활성화
                        if (swiper) {
                            swiper.allowSlideNext = true;
                            swiper.allowSlidePrev = true;
                            if (swiper.autoplay && swiper.autoplay.running === false) {
                                swiper.autoplay.start();
                            }
                        } else {
                            // Swiper 초기화
                            const swiperInstance = new Swiper(element, swiperOptions);
                            initializedSwipers.set(element, swiperInstance);
                            
                            // 프로그레스 바 시작
                            if (progressBar && autoplayDelay) {
                                startProgressBar(progressBar, autoplayDelay);
                            }
                        }
                    } else {
                        // 슬라이드가 뷰포트 밖에 있을 때 비활성화
                        if (swiper) {
                            swiper.allowSlideNext = false;
                            swiper.allowSlidePrev = false;
                            if (swiper.autoplay && swiper.autoplay.running) {
                                swiper.autoplay.stop();
                            }
                        }
                    }
                });
            }, {
                rootMargin: '50px' // 50px 전에 미리 초기화
            });
            
            observer.observe(element);
        } else {
            // 옵저버 미지원 환경에서는 즉시 초기화
            const swiperInstance = new Swiper(element, swiperOptions);
            initializedSwipers.set(element, swiperInstance);
        }
    });
}

/**
 * 프로그레스 바 시작 함수
 * 자동 재생 시간에 맞춰 프로그레스 바를 0에서 100%까지 채웁니다.
 * @param {HTMLElement} progressBar - 프로그레스 바 요소
 * @param {number} duration - 진행 시간 (ms)
 */
function startProgressBar(progressBar, duration) {
    if (!progressBar) return;
    
    progressBar.style.transition = 'none';
    progressBar.style.width = '0%';
    progressBar.dataset.duration = duration;
    
    // 다음 프레임에 애니메이션 시작
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            progressBar.style.transition = `width ${duration}ms linear`;
            progressBar.style.width = '100%';
        });
    });
}

/**
 * 프로그레스 바 리셋 함수
 * @param {HTMLElement} progressBar - 프로그레스 바 요소
 */
function resetProgressBar(progressBar) {
    if (!progressBar) return;
    
    progressBar.style.transition = 'none';
    progressBar.style.width = '0%';
}

/**
 * 프로그레스 바 일시정지 함수
 * @param {HTMLElement} progressBar - 프로그레스 바 요소
 */
function pauseProgressBar(progressBar) {
    if (!progressBar) return;
    
    const computedStyle = window.getComputedStyle(progressBar);
    const currentWidth = computedStyle.width;
    progressBar.style.transition = 'none';
    progressBar.style.width = currentWidth;
}

/**
 * 프로그레스 바 재개 함수
 * @param {HTMLElement} progressBar - 프로그레스 바 요소
 */
function resumeProgressBar(progressBar) {
    if (!progressBar) return;
    
    const currentWidth = parseFloat(progressBar.style.width) || 0;
    const remainingPercent = 100 - currentWidth;
    const totalDuration = parseInt(progressBar.dataset.duration) || 3000;
    const remainingDuration = (remainingPercent / 100) * totalDuration;
    
    progressBar.style.transition = `width ${remainingDuration}ms linear`;
    progressBar.style.width = '100%';
}

/**
 * 네비게이션 스와이프 함수
 * 마우스 드래그로 가로 스크롤 가능한 요소를 구현합니다.
 * 드래그 중에는 클릭 이벤트를 방지하여 의도치 않은 링크 이동을 막습니다.
 * @param {string} selector - 드래그 스크롤을 적용할 요소 선택자
 */
function navSwipe(selector) {
    // if (!isPC()) return; // PC 환경이 아니면 함수 종료

    const $sliders = $(selector);
    
    // 각 요소별로 독립적인 드래그 상태 관리
    $sliders.each(function() {
        const $slider = $(this);
        let isDragging = false;
        let startX = 0;
        let scrollLeftAtStart = 0;
        let hasDragged = false; // 드래그 여부를 추적

        // 커서 상태 초기화
        $slider.css('cursor', 'default');

        // 마우스 다운 (드래그 시작)
        $slider.on('mousedown', function (event) {
            // input, button, select, textarea 등의 요소에서는 드래그를 시작하지 않음 (a 태그는 제외)
            if ($(event.target).is('input, button, select, textarea')) {
                return;
            }

            isDragging = true;
            hasDragged = false; // 드래그 상태 초기화
            startX = event.pageX;
            scrollLeftAtStart = $slider.scrollLeft();

            // 잡은 상태(grabbing)
            document.body.style.cursor = 'grabbing';
            $slider.css('cursor', 'grabbing');
            $slider.addClass('dragging'); // 드래그 상태 클래스 추가

            event.preventDefault();
        });

        // 마우스 무브 (드래그)
        $(document).on('mousemove', function (event) {
            if (!isDragging) return;

            const distanceMoved = event.pageX - startX;
            $slider.scrollLeft(scrollLeftAtStart - distanceMoved);
            
            // 일정 거리 이상 움직였으면 드래그로 간주
            if (Math.abs(distanceMoved) > 5) {
                hasDragged = true;
            }
        });

        // 마우스 업 (드래그 종료)
        $(document).on('mouseup', function () {
            if (isDragging) {
                isDragging = false;

                // 커서 상태를 원복
                document.body.style.cursor = 'auto';
                $slider.css('cursor', 'default');
                $slider.removeClass('dragging'); // 드래그 상태 클래스 제거
                
                // 모든 하위 요소의 커서도 원복
                $slider.find('*').css('cursor', '');
                
                // 드래그가 발생했다면 잠시 후 클릭 이벤트 방지
                if (hasDragged) {
                    setTimeout(() => {
                        hasDragged = false;
                    }, 100);
                }
            }
        });

        // 마우스가 요소 밖으로 나가도 드래그 상태 유지
        $slider.on('mouseleave', function () {
            if (isDragging) {
                document.body.style.cursor = 'grabbing';
                $(this).css('cursor', 'grabbing');
            }
        });

        // 마우스가 다시 요소 안으로 들어왔을 때
        $slider.on('mouseenter', function () {
            if (!isDragging) {
                $(this).css('cursor', 'default');
            }
        });

        // 드래깅 중에는 모든 하위 요소에서 grabbing 커서 유지
        $slider.on('mouseover', '*', function () {
            if (isDragging) {
                $(this).css('cursor', 'grabbing');
            }
        });

        // 드래그 후 클릭 이벤트 방지
        $slider.on('click', 'a', function (event) {
            if (hasDragged) {
                event.preventDefault();
                event.stopPropagation();
            }
        });
    });
}

/**
 * 알림 DOM 생성 함수
 * 성공/경고/닫기 타입의 알림 메시지를 화면에 표시하고 3초 후 자동으로 제거합니다.
 * @param {string} msg - 표시할 메시지
 * @param {number} status - 알림 타입 (1: 성공, 2: 경고, 3: 닫기)
 */
function alertDom(msg, status) {
    var imgSrc, typeClass;
    switch(status) {
        case 1:
            imgSrc = g5_url + '/plugin/9f_rental/img/alertDom1.png';
            typeClass = 'success';
            break;
        case 2:
            imgSrc = g5_url + '/plugin/9f_rental/img/alertDom2.png';
            typeClass = 'warning';
            break;
        case 3:
            imgSrc = g5_url + '/plugin/9f_rental/img/alertDom2.png';
            typeClass = 'close';
            break;
    }

    const dom = `<aside id="alertDom" class="${typeClass}">
                    <div class="ad_wrap">
                        <span class="ad_img"><img src="${imgSrc}"></span>
                        <span class="ad_txt">${msg}</span>
                    </div>
                 </aside>`;

    $('.container_wr').append(dom);

    setTimeout(() => {
        $('#alertDom').remove();
    }, 3000);
}

/**
 * 숫자 검증 함수
 * 입력 필드에서 숫자 외의 문자를 제거하고, 음수값이 입력되면 0으로 설정합니다.
 * @param {HTMLInputElement} input - 검증할 입력 필드 요소
 */
function validateNumber(input) {
    // 숫자 외의 문자는 제거
    input.value = input.value.replace(/[^0-9]/g, '');

    // 음수값이 입력되지 않도록
    if (parseFloat(input.value) < 0) {
        input.value = '0'; // 음수일 경우 0으로 설정
    }
}

/**
 * 활성 항목 보이기 함수
 * 가로 스크롤 가능한 메뉴에서 활성화된 항목(.act)이 화면 중앙에 보이도록 스크롤합니다.
 * @param {string} selector - 스크롤할 컨테이너 선택자
 */
function ensureActVisible(selector) {
    const $subMenu = $(selector);
    const $actItem = $subMenu.find(".act");

    if (!$subMenu.length || !$actItem.length) return;

    // 부모 요소의 너비와 subMenu의 실제 너비를 비교
    const $parent = $subMenu.parent();
    const parentWidth = $parent.length ? $parent.width() : $(window).width();
    const subMenuWidth = $subMenu.get(0).scrollWidth;
    const subMenuVisibleWidth = $subMenu.width();

    console.log('부모 너비:', parentWidth, 'subMenu 전체 너비:', subMenuWidth, 'subMenu 보이는 너비:', subMenuVisibleWidth);

    // subMenu가 부모보다 크거나, act 요소가 현재 보이는 영역 밖에 있을 때 스크롤 처리
    if (subMenuWidth > subMenuVisibleWidth) {
        const actItemLeft = $actItem.position().left;
        const actItemWidth = $actItem.outerWidth(true);
        const scrollOffset = actItemLeft - subMenuVisibleWidth / 2 + actItemWidth / 2;

        console.log('actItem 위치:', actItemLeft, '스크롤 오프셋:', scrollOffset);

        // 스크롤로 이동
        $subMenu.animate({ scrollLeft: scrollOffset }, 400); // 부드러운 스크롤
    }
}

/**
 * 전화번호 포맷 함수
 * 입력된 숫자를 전화번호 형식(010-1234-5678)으로 자동 변환합니다.
 * @param {HTMLInputElement} input - 포맷할 입력 필드 요소
 */
function formatPhone(input) {
    // 입력된 값에서 숫자만 추출
    const numbers = input.value.replace(/[^0-9]/g, "");

    // 형식에 맞게 변환
    let formatted = "";
    if (numbers.length <= 3) {
        formatted = numbers;
    } else if (numbers.length <= 7) {
        formatted = `${numbers.slice(0, 3)}-${numbers.slice(3)}`;
    } else {
        formatted = `${numbers.slice(0, 3)}-${numbers.slice(3, 7)}-${numbers.slice(7, 11)}`;
    }

    // 입력 필드에 업데이트
    input.value = formatted;
}

/**
 * 확인 모달 함수
 * 커스텀 확인/취소 모달 다이얼로그를 생성하고 표시합니다.
 * 확인 버튼 클릭 시 콜백 함수를 실행합니다.
 * @param {string} title - 모달 제목
 * @param {string} message - 모달 메시지
 * @param {Function} onConfirm - 확인 버튼 클릭 시 실행할 콜백 함수
 */
function ifConfirm(title, message, onConfirm) {
    $('.modal-overlay').remove();
    
    const $modal = $(`
        <div class="modal-overlay">
            <div class="modal-content">
                <div class="modal-title">${title}</div>
                <div class="modal-message">${message}</div>
                <div class="modal-buttons">
                    <button class="modal-button cancel-button button btn-full btn-line2 btn-lg">취소</button>
                    <button class="modal-button confirm-button button btn-full btn-fill btn-lg">확인</button>
                </div>
            </div>
        </div>
    `);

    $modal.find('.confirm-button').on('click', function() {
        onConfirm();
        $modal.remove();
    });

    $modal.find('.cancel-button').on('click', function() {
        $modal.remove();
    });

    $modal.on('click', function(e) {
        if ($(e.target).is('.modal-overlay')) {
            $modal.remove();
        }
    });

    $('body').append($modal);

}


/**
 * 체크박스 토글 함수
 * 전체 선택 체크박스와 개별 체크박스 간의 연동을 처리합니다.
 * 전체 선택 시 모든 개별 체크박스가 선택/해제되고, 개별 체크박스 상태에 따라 전체 선택 상태가 자동 업데이트됩니다.
 */
function toggleCheckboxes() {
    const $allChk = $('.all_chk input[type="checkbox"]'); // 전체 선택 체크박스
    const $chkInputs = $('.chk_input'); // 개별 체크박스들

    // 전체 선택 체크박스 클릭 이벤트
    $allChk.on('change', function () {
        const isChecked = $allChk.prop('checked');
        $chkInputs.prop('checked', isChecked);
    });

    // 개별 체크박스 상태 변경 이벤트
    $chkInputs.on('change', function () {
        // 모든 개별 체크박스가 체크되었는지 확인
        const allChecked = $chkInputs.length === $chkInputs.filter(':checked').length;
        $allChk.prop('checked', allChecked);
    });
}

/**
 * 삭제 확인 함수
 * 링크 클릭 시 삭제 확인 다이얼로그를 표시하고, 확인 시에만 링크로 이동합니다.
 * @param {HTMLAnchorElement} anchor - 삭제 링크 요소
 * @returns {boolean} 항상 false를 반환하여 기본 링크 동작을 차단
 */
function del_confirm(anchor) {
  if (confirm('정말 삭제하시겠습니까?')) {
    window.location.href = anchor.href;
  }
  return false; // 항상 링크 이동 기본 동작 차단
}


// 리사이즈 이벤트
$(window).on("resize", function(){
    ensureActVisible('#sub-menu');
    ensureActVisible('#roomNavi');
    ensureActVisible('#mypage_tabs');
    ensureActVisible('.club-info #bo_cate ul');
});

// 문서 준비 완료 시 이벤트
$(document).ready(function () {
    lazyLoadImages({
        rootMargin: "50px", // 여백 설정
        threshold: 0.2, // 20% 보일 때 로드
        selector: '.lazy-load', // Lazy Load 대상
        indicatorClass: 'loading-indicator' // 인디케이터 클래스
    });
    navSwipe('.tbl-wrap, #sub-menu, #roomNavi');
    ensureActVisible('#sub-menu');
    ensureActVisible('#roomNavi');
    ensureActVisible('#mypage_tabs');
    ensureActVisible('.club-info #bo_cate ul');
    toggleCheckboxes();
    // 모든 Swiper 자동 초기화
    autoInitializeSwipers();
});
