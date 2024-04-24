<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
?>

<!-- 로그인 후 외부로그인 시작 -->
<aside id="ol_after" class="ol">
   
    <h2 class="blind">나의 회원정보</h2>
    <div id="ol_after_hd" class="relative bg-zinc-800 px-3 py-2.5 after:block after:invisible after:clear-both after:content-['']">
        <span class="profile_img relative float-left inline-block">
            <?php echo get_member_profile_img($member['mb_id']); ?>
            <a href="<?php echo G5_BBS_URL ?>/member_confirm.php?url=register_form.php" id="ol_after_info" class="absolute -bottom-1 -right-1 w-5 h-5 text-center rounded-full bg-zinc-800"><i class="fa fa-cog fa-fw text-white text-xs"></i><span class="sound_only">정보수정</span></a>
        </span>
        <strong class="block float-left text-white leading-10 pl-4"><?php echo $nick ?>님</strong>
        <div id="ol_after_btn" class="absolute top-4 right-14">
	        <?php if ($is_admin == 'super' || $is_auth) { ?><a href="<?php echo G5_ADMIN_URL ?>" class="btn_admin inline-block leading-8 float-left text-center rounded !text-white w-8 bg-red-500 mr-3 !p-0"><i class="fa fa-cog fa-spin fa-fw"></i><span class="sound_only">관리자</span></a><?php } ?>
	        <a href="<?php echo G5_BBS_URL ?>/logout.php" id="ol_after_logout" class="inline-block leading-8 bg-blue-500 text-white rounded font-bold px-3">로그아웃</a>
	    </div>
    </div>

    <ul id="ol_after_private" class="flex bg-white border-b border-gray-200 list-none p-0 mt-4 dark:bg-zinc-800 dark:border-mainborder">
        <li id="ol_after_memo" class="relative w-1/3 text-left border-r border-gray-200 dark:border-mainborder">
            <a href="<?php echo G5_BBS_URL ?>/memo.php" target="_blank" class="win_memo inline-block text-black py-4 px-3 dark:text-white">
            	<i class="fa fa-envelope-o text-gray-300 text-sm mr-1" aria-hidden="true"></i>
                <span class="sound_only">안 읽은</span>쪽지
                <strong class="absolute top-4 right-3"><?php echo $memo_not_read ?></strong>
            </a>
        </li>
        <li id="ol_after_pt" class="relative w-1/3 text-left border-r border-gray-200 dark:border-mainborder">
            <a href="<?php echo G5_BBS_URL ?>/point.php" target="_blank" class="win_point inline-block text-black py-4 px-3 dark:text-white">
                <i class="fa fa-database text-gray-300 text-sm mr-1" aria-hidden="true"></i>
                포인트
                <strong class="absolute top-4 right-3"><?php echo $point ?></strong>
            </a>
        </li>
        <li id="ol_after_scrap" class="relative w-1/3 text-left text-center dark:border-mainborder">
            <a href="<?php echo G5_BBS_URL ?>/scrap.php" target="_blank" class="win_scrap inline-block text-black py-4 px-3 dark:text-white">
				<i class="fa fa-thumb-tack text-gray-300 text-sm mr-1" aria-hidden="true"></i>스크랩
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
