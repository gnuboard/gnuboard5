<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
?>

<!-- 로그인 후 아웃로그인 시작 { -->
<section id="ol_after" class="ol relative border border-solid border-gray-200 rounded mb-3.5 dark:border-mainborder dark:text-white">
    <header id="ol_after_hd" class="relative h-20 p-2.5 pl-20">
        <h2 class="blind">나의 회원정보</h2>
        <span class="profile_img absolute top-4 left-4 inline-block">
            <?php echo get_member_profile_img($member['mb_id']); ?>
        </span>
        <strong class="block mt-1 mb-2.5"><?php echo $nick ?>님</strong>
        <a href="<?php echo G5_BBS_URL ?>/member_confirm.php?url=register_form.php" id="ol_after_info" title="정보수정" class="inline-block h-7 leading-6 border border-solid border-gray-300 text-blue-500 rounded py-0.5 px-1 dark:border-mainborder">정보수정</a>
        <?php if ($is_admin == 'super' || $is_auth) {  ?><a href="<?php echo correct_goto_url(G5_ADMIN_URL); ?>" class="btn_admin btn inline-block h-6 leading-6 align-middle no-underline rounded px-2.5" title="관리자"><i class="fa fa-cog fa-spin fa-fw"></i><span class="sound_only">관리자</span></a><?php }  ?>
    </header>
    <ul id="ol_after_private">
    	<li class="group relative text-left hover:bg-slate-100 hover:border-l-2 border-l-2 border-transparent hover:border-blue-500 dark:hover:bg-zinc-800">
            <a href="<?php echo G5_BBS_URL ?>/point.php" target="_blank" id="ol_after_pt" class="win_point block text-slate-800 leading-4 p-2.5 pl-5 group-hover:text-blue-500 dark:text-white">
				<i class="fa fa-database w-6 text-gray-400 mr-1 group-hover:text-blue-500" aria-hidden="true"></i>포인트
				<strong class="inline-block float-right max-w-20 overflow-hidden whitespace-nowrap text-clip text-blue-500 rounded-2xl text-xs px-1.5 group-hover:bg-emerald-500 group-hover:text-white"><?php echo $point; ?></strong>
            </a>
        </li>
        <li class="group relative text-left hover:bg-slate-100 hover:border-l-2 border-l-2 border-transparent hover:border-blue-500 dark:hover:bg-zinc-800">
            <a href="<?php echo G5_BBS_URL ?>/memo.php" target="_blank" id="ol_after_memo" class="win_memo block text-slate-800 leading-4 p-2.5 pl-5 group-hover:text-blue-500 dark:text-white">
            	<i class="fa fa-envelope-o w-6 text-gray-400 mr-1 group-hover:text-blue-500" aria-hidden="true"></i><span class="sound_only">안 읽은 </span>쪽지
                <strong class="inline-block float-right max-w-20 overflow-hidden whitespace-nowrap text-clip text-blue-500 rounded-2xl text-xs px-1.5 group-hover:bg-green-400 group-hover:text-white"><?php echo $memo_not_read; ?></strong>
            </a>
        </li>
        <li class="group relative text-left hover:bg-slate-100 hover:border-l-2 border-l-2 border-transparent hover:border-blue-500 dark:hover:bg-zinc-800">
            <a href="<?php echo G5_BBS_URL ?>/scrap.php" target="_blank" id="ol_after_scrap" class="win_scrap block text-slate-800 leading-4 p-2.5 pl-5 group-hover:text-blue-500 dark:text-white">
            	<i class="fa fa-thumb-tack w-6 text-gray-400 mr-1 group-hover:text-blue-500" aria-hidden="true"></i>스크랩
            	<strong class="scrap float-right max-w-20 overflow-hidden whitespace-nowrap text-clip text-blue-500 rounded-2xl text-xs px-1.5 group-hover:bg-orange-400 group-hover:text-white"><?php echo $mb_scrap_cnt; ?></strong>
            </a>
        </li>
    </ul>
    <footer>
    	<a href="<?php echo G5_BBS_URL ?>/logout.php" id="ol_after_logout" class="block text-center font-bold py-3.5 text-gray-400 border-t border-gray-200 hover:text-blue-500 dark:border-mainborder"><i class="fa fa-sign-out" aria-hidden="true"></i> 로그아웃</a>
    </footer>
</section>

<script>
// 탈퇴의 경우 아래 코드를 연동하시면 됩니다.
function member_leave()
{
    if (confirm("정말 회원에서 탈퇴 하시겠습니까?"))
        location.href = "<?php echo G5_BBS_URL ?>/member_confirm.php?url=member_leave.php";
}
</script>
<!-- } 로그인 후 아웃로그인 끝 -->
