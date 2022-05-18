<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$outlogin_skin_url.'/style.css">', 0);
?>

<!-- 로그인 후 외부로그인 시작 -->
<aside id="ol_after">
   
    <h2>나의 회원정보</h2>
    <div id="ol_after_hd" class="ol">
        <span class="profile_img">
            <?php echo get_member_profile_img($member['mb_id'], 60, 60); ?>
            <a href="<?php echo G5_BBS_URL ?>/member_confirm.php?url=register_form.php" id="ol_after_info"><i class="fa fa-cog" aria-hidden="true"></i><span class="sound_only">정보수정</span></a>
        </span>
        <strong class="nickname"><?php echo $nick ?>님</strong>
        <a href="<?php echo G5_BBS_URL ?>/point.php" target="_blank" class="point win_point"><strong><?php echo $point ?></strong>  포인트
        </a>
        <div id="ol_after_btn">
            <?php if ($is_admin == 'super' || $is_auth) { ?><a href="<?php echo G5_ADMIN_URL ?>" class="btn_admin">관리자</a><?php } ?>
            <a href="<?php echo G5_BBS_URL ?>/logout.php" id="ol_after_logout">로그아웃</a>
        </div>
        <button type="button" class="menu_close"><i class="fa fa-times" aria-hidden="true"></i><span class="sound_only">카테고리닫기</span></button>
    </div>

    <ul id="ol_after_private">
        <li id="ol_after_memo">
            <a href="<?php echo G5_BBS_URL ?>/memo.php" target="_blank" class="win_memo">
                <i class="fa fa-envelope" aria-hidden="true"></i>쪽지
                <strong><?php echo $memo_not_read; ?></strong>
            </a>
        </li>

        <li><a href="<?php echo G5_SHOP_URL ?>/coupon.php" target="_blank" class="win_coupon"><i class="fa fa-ticket" aria-hidden="true"></i>쿠폰<strong><?php echo number_format(get_shop_member_coupon_count($member['mb_id'], true)); ?></strong></a></li>
    </ul>

</aside>

<script>
// 탈퇴의 경우 아래 코드를 연동하시면 됩니다.
function member_leave()
{
    if (confirm("정말 회원에서 탈퇴 하시겠습니까?"))
        location.href = "<?php echo G5_BBS_URL ?>/member_confirm.php?url=member_leave.php";
}
</script>
<!-- 로그인 후 외부로그인 끝 -->
