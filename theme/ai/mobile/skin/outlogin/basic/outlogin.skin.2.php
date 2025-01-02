<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$outlogin_skin_url.'/style.css">', 0);
?>

<!-- 로그인 후 외부로그인 시작 -->
<aside id="ol_after" class="ol">
   
    <h2>나의 회원정보</h2>
    <div id="ol_after_hd">
        <span class="profile_img">
            <?php echo get_member_profile_img($member['mb_id']); ?>
            <a href="<?php echo G5_BBS_URL ?>/member_confirm.php?url=register_form.php" id="ol_after_info"><i class="fa fa-cog fa-3x fa-fw"></i><span class="sound_only">정보수정</span></a>
        </span>
        <strong><?php echo $nick ?>님</strong>
        <div id="ol_after_btn">
	        <?php if ($is_admin == 'super' || $is_auth) { ?><a href="<?php echo G5_ADMIN_URL ?>" class="btn_admin"><i class="fa fa-cog fa-spin fa-fw"></i><span class="sound_only">관리자</span></a><?php } ?>
	        <a href="<?php echo G5_BBS_URL ?>/logout.php" id="ol_after_logout">로그아웃</a>
	    </div>
    </div>

    <ul id="ol_after_private">
        <li id="ol_after_memo">
            <a href="<?php echo G5_BBS_URL ?>/memo.php" target="_blank" class="win_memo">
            	<i class="fa fa-envelope-o" aria-hidden="true"></i>
                <span class="sound_only">안 읽은</span>쪽지
                <strong><?php echo $memo_not_read ?></strong>
            </a>
        </li>
        <li id="ol_after_pt">
            <a href="<?php echo G5_BBS_URL ?>/point.php" target="_blank" class="win_point">
                <i class="fa fa-database" aria-hidden="true"></i>
                포인트
                <strong><?php echo $point ?></strong>
            </a>
        </li>
        <li id="ol_after_scrap">
            <a href="<?php echo G5_BBS_URL ?>/scrap.php" target="_blank" class="win_scrap">
				<i class="fa fa-thumb-tack" aria-hidden="true"></i>스크랩
            </a>
        </li>
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
