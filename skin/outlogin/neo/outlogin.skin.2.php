<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
?>

<!-- 로그인 후 외부로그인 시작 -->
<section id="ol_after" class="ol">
    <header id="ol_after_hd">
        <h2>나의 이용정보</h2>
        <strong><?=$nick?></strong>님
        <? if ($is_admin == 'super' || $is_auth) { ?><a href="<?=$g4['admin_path']?>/" id="ol_admin">관리자</a><? } ?>
    </header>
    <ul id="ol_after_private">
        <li>
            <a href="<?=$g4['path']?>/bbs/memo.php" id="ol_after_memo" target="_blank" onclick="win_memo(); return false;">
                <span class="sound_only">안 읽은 </span>쪽지
                <strong><?=$memo_not_read?></strong>
            </a>
        </li>
        <li>
            <a href="<?=$g4['path']?>/bbs/point.php" id="ol_after_pt" target="_blank" onclick="win_point(); return false;">
                포인트
                <strong><?=$point?></strong>
            </a>
        </li>
        <li>
            <a href="<?=$g4['path']?>/bbs/scrap.php" id="ol_after_scrap" target="_blank" onclick="win_scrap(); return false;">스크랩</a>
        </li>
    </ul>
    <footer id="ol_after_ft">
        <a href="<?=$g4['bbs_path']?>/member_confirm.php?url=register_form.php" id="ol_after_info">정보수정</a>
        <a href="<?=$g4['bbs_path']?>/logout.php" id="ol_after_logout">로그아웃</a>
    </footer>
</section>

<script>
// 탈퇴의 경우 아래 코드를 연동하시면 됩니다.
function member_leave()
{
    if (confirm("정말 회원에서 탈퇴 하시겠습니까?"))
        location.href = "<?=$g4['bbs_path']?>/member_confirm.php?url=member_leave.php";
}
</script>
<!-- 로그인 후 외부로그인 끝 -->
