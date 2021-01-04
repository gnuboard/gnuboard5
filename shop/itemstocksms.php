<?php
include_once('./_common.php');

$it_id = isset($_REQUEST['it_id']) ? safe_replace_regex($_REQUEST['it_id'], 'it_id') : '';

$g5['title'] = '상품 재입고 알림 (SMS)';
include_once(G5_PATH.'/head.sub.php');

// 상품정보
$it = get_shop_item($it_id, true);

if(! (isset($it['it_id']) && $it['it_id']))
    alert_close('상품정보가 존재하지 않습니다.');

if(!$it['it_soldout'] || !$it['it_stock_sms'])
    alert_close('재입고SMS 알림을 신청할 수 없는 상품입니다.');

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.G5_SHOP_SKIN_URL.'/style.css">', 0);

if (G5_IS_MOBILE) {
    add_stylesheet('<link rel="stylesheet" href="'.G5_MSHOP_SKIN_URL.'/style.css">', 0);

}
?>

<div id="sit_sms_new" class="new_win">
    <h1 id="win_title"><?php echo $g5['title']; ?></h1>

    <form name="fstocksms" method="post" action="<?php echo G5_HTTPS_SHOP_URL; ?>/itemstocksmsupdate.php" onsubmit="return fstocksms_submit(this);"  autocomplete="off">
    <input type="hidden" name="it_id" value="<?php echo $it_id; ?>">

    <div class="form_01">
        <ul>
            <li class="prd_name">
                <?php echo $it['it_name']; ?>
            </li>
            <li>
                <label for="ss_hp" class="sound_only">휴대폰번호<strong> 필수</strong></label>
                <input type="text" name="ss_hp" value="<?php echo $member['mb_hp']; ?>" id="ss_hp" required class="required frm_input full_input" placeholder="휴대폰번호">
            </li>
            <li>
                <strong>개인정보처리방침안내</strong>
                <textarea readonly><?php echo get_text($config['cf_privacy']) ?></textarea>
            </li>
        </ul>
        
        <div id="sms_agree" class="chk_box">
            <input type="checkbox" name="agree" value="1" id="agree" class="selec_chk">
            <label for="agree"><span></span>개인정보처리방침안내의 내용에 동의합니다.</label>
        </div>
        
        <div class="win_btn">
            <button type="submit" class="btn_submit">확인</button>
            <button type="button" onclick="window.close();" class="btn_close">닫기</button>
        </div>
    </div>
    </form>
</div>

<script>
function fstocksms_submit(f)
{
    if(!f.agree.checked) {
        alert("개인정보처리방침안내에 동의해 주십시오.");
        return false;
    }

    if(confirm("재입고SMS 알림 요청을 등록하시겠습니까?")) {
        return true;
    } else {
        window.close();
        return false;
    }
}
</script>

<?php
include_once(G5_PATH.'/tail.sub.php');