<?php
if (!defined('_GNUBOARD_')) {
    exit;
} // 개별 페이지 접근 불가

// 정기결제 PG를 NHN_KCP 로 사용하지 않으면
if (get_subs_option('su_pg_service') !== 'nicepay') {
    return;
}

add_event('subscription_add_form_html', 'nicepay_subscription_form_html');

function nicepay_subscription_form_html()
{
    ?>

<input type="hidden" name="cardNo" value="">
<input type="hidden" name="expMonth" value="">
<input type="hidden" name="expYear" value="">
<input type="hidden" name="idNo" value="">
<input type="hidden" name="cardPw" value="">

<div id="nicepay_modal" class="modal">
    <h2>신용카드정보 입력</h2>
    <div class="modal-content">
        <ul class="nicepay-form flex">
            <li>
                <label for="cardNo">카드번호<strong class="sound_only"> 필수</strong></label>
                <input type="text" name="cardNo_1" value="" required="" class="frm_input required" maxlength="4">
                <input type="text" name="cardNo_2" value="" required="" class="frm_input required" maxlength="4">
                <input type="password" name="cardNo_3" value="" required="" class="frm_input required" maxlength="4">
                <input type="text" name="cardNo_4" value="" required="" class="frm_input required" maxlength="4">
            </li>
            <li>
                <label for="expMonth">유효기간<strong class="sound_only"> 필수</strong></label>
                <input type="password" name="expMonth" placeholder="MM" value="" id="expMonth" required="" class="frm_input required" autocomplete="off" maxlength="2">
                <input type="password" name="expYear" placeholder="YY" value="" id="expYear" required="" class="frm_input required" autocomplete="off" maxlength="2">
            </li>
            <li>
                <label for="idNo">생년월일(6)<br>또는 사업자번호<strong class="sound_only"> 필수</strong></label>
                <input type="text" name="idNo" value="" id="idNo" required="" class="frm_input required" autocomplete="off" maxlength="20">
            </li>
            <li>
                <label for="cardPw">카드 비밀번호<br>앞자리 2글자<strong class="sound_only"> 필수</strong></label>
                <input type="password" name="cardPw" value="" id="cardPw" required="" class="frm_input required" autocomplete="off" maxlength="2">
            </li>
        </ul>
        <div class="description-top">결제조건에 동의하시면 '주문' 버튼을 클릭해 주세요.</div>
    </div>
    <div class="bottom-btns">
        <a href="#" class="modal-btn modal-close" rel="modal:close">취소</a>
        <button type="button" class="modal-btn order-progress">주문</button>
    </div>
</div>
<div class="" style="display:none"><a href="#nicepay_modal" id="nicepay-modal-btn" rel="modal:open"></a></div>

<script>
function validateCreditCard(cardNumber) {
    // 카드 번호를 숫자 배열로 변환
    const digits = cardNumber.replace(/-/g, '').split('').map(Number);
    
    // Luhn 알고리즘 적용
    for (let i = digits.length - 2; i >= 0; i -= 2) {
        let doubled = digits[i] * 2;
        if (doubled > 9) {
            doubled -= 9;
        }
        digits[i] = doubled;
    }

    // 모든 숫자 합산
    const sum = digits.reduce((acc, curr) => acc + curr, 0);

    // 합계가 10의 배수인지 확인
    return sum % 10 === 0;
}

function validateCardExpiry(expiry) {
    // MM/YY 형식에서 월과 연도를 분리
    const [month, year] = expiry.split('/').map(Number);

    if (!month || !year || month < 1 || month > 12) {
        return false; // 잘못된 형식 또는 비정상적인 월 입력
    }

    // 현재 연도와 월
    const currentYear = new Date().getFullYear() % 100; // 마지막 두 자리 연도
    const currentMonth = new Date().getMonth() + 1; // 월은 0부터 시작하므로 1을 더함

    // 카드 유효기간이 아직 유효한지 확인
    if (year > currentYear || (year === currentYear && month >= currentMonth)) {
        return true;
    } else {
        return false;
    }
}

// jQuery("#nicepay-modal-btn").trigger("click");

function nicepay_modal_open() {
    jQuery("#nicepay-modal-btn").modal({
        escapeClose: false,
        clickClose: false
    });
}

// nicepay_modal_open();

jQuery(function($){
    $('.order-progress').click(function (event) {
        event.preventDefault();
        
        let isValid = true;
        let errorMessage = "";
        let focusTarget = null;
        const $nicepay_form = ".nicepay-form";

        // 유효기간 검증 (MM, YY 형식, 숫자만)
        let expMonth = $($nicepay_form + ' input[name="expMonth"]').val();
        let expYear = $($nicepay_form +' input[name="expYear"]').val();
        let currentYear = new Date().getFullYear() % 100; // 현재 연도의 마지막 두 자리 (예: 2024 → 24)
        
        let idNo = $('input[name="idNo"]', $nicepay_form).val();
        let cardPw = $('input[name="cardPw"]', $nicepay_form).val();
            
        // 카드번호 검증 (각 4자리 숫자)
        let cardNo = "";
        $('input[name^="cardNo_"]').each(function () {
            if (!$(this).val().match(/^\d{4}$/)) {
                isValid = false;
                errorMessage = "카드번호를 정확히 입력해주세요.";
                focusTarget = focusTarget || this;
            }
            cardNo += $(this).val();
        });
        
        if (isValid) {
            
            if (!expMonth.match(/^(0[1-9]|1[0-2])$/)) {
                isValid = false;
                errorMessage = "유효기간을 정확히 입력해주세요 (MM).";
                focusTarget = focusTarget || $($nicepay_form + ' input[name="expMonth"]');
            } else if (!expYear.match(/^\d{2}$/) || parseInt(expYear, 10) < currentYear) {
                isValid = false;
                errorMessage = "유효기간을 정확히 입력해주세요 (YY, 현재 연도 이상).";
                focusTarget = focusTarget || $($nicepay_form + ' input[name="expYear"]');
            }
            
        }
            
        if (isValid) {
            // 생년월일(6) 또는 사업자번호 검증 (숫자만 입력, 최소 6자리)
            if (!idNo.match(/^\d{6,20}$/)) {
                isValid = false;
                errorMessage = "생년월일(6자리) 또는 사업자번호를 올바르게 입력해주세요.";
                focusTarget = focusTarget || $('input[name="idNo"]', $nicepay_form);
            }
        }
        
        if (isValid) {
            // 카드 비밀번호 앞 2자리 검증 (숫자만, 2자리)
            if (!cardPw.match(/^\d{2}$/)) {
                isValid = false;
                errorMessage = "카드 비밀번호 앞 2자리를 입력해주세요.";
                focusTarget = focusTarget || $('input[name="cardPw"]', $nicepay_form);
            }
        }

        // 유효성 검사 실패 시 알림 및 포커스 이동
        if (!isValid) {
            alert(errorMessage);
            if (focusTarget) {
                $(focusTarget).focus();
            }
            return false;
        }
        
        $('input[type="hidden"][name="cardNo"]').val(cardNo);
        $('input[type="hidden"][name="expMonth"]').val(expMonth);
        $('input[type="hidden"][name="expYear"]').val(expYear);
        $('input[type="hidden"][name="idNo"]').val(idNo);
        $('input[type="hidden"][name="cardPw"]').val(cardPw);
        
        //alert("결제가 진행됩니다.");
        // 실제 결제 진행 로직 추가 가능
        
        document.forderform.submit();
    });
});

</script>
<?php
}
