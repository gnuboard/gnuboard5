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
<section class="order-common-section">
    <h2>신용카드정보 입력</h2>
    <div class="tbl_frm01 tbl_wrap">
        <table>
            <tbody>
                <tr>
                    <th scope="row"><label for="cardNo">신용카드번호<strong class="sound_only"> 필수</strong></label></th>
                    <td>
                        <!-- <input type="text" name="cardNo" value="" id="cardNo" required="" class="frm_input required" autocomplete="off" maxlength="20"> -->
                        <input type="text" name="cardNo" value="" id="cardNo" required="" class="frm_input required" maxlength="20">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="expMonth">card expiry date MM<strong class="sound_only"> 필수</strong></label></th>
                    <td><input type="text" name="expMonth" placeholder="MM" value="" id="expMonth" required="" class="frm_input required" autocomplete="off" maxlength="20"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="expYear">card expiry date YY<strong class="sound_only"> 필수</strong></label></th>
                    <td><input type="text" name="expYear" placeholder="YY" value="" id="expYear" required="" class="frm_input required" autocomplete="off" maxlength="20"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="idNo">생년월일(6)/사업자번호<strong class="sound_only"> 필수</strong></label></th>
                    <td><input type="text" name="idNo" value="" id="idNo" required="" class="frm_input required" autocomplete="off" maxlength="20"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="cardPw">card password 2 digit<strong class="sound_only"> 필수</strong></label></th>
                    <td><input type="text" name="cardPw" value="" id="cardPw" required="" class="frm_input required" autocomplete="off" maxlength="20"></td>
                </tr>
            </tbody>
        </table>
    </div>
</section>
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

</script>
<?php
}
