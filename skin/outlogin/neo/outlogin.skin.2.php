<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
?>

<!-- 로그인 후 외부로그인 시작 -->
<section id="ol_after" class="outlogin">
    <header id="ol_after_hd">
        <h2>나의 이용정보</h2>
        <strong><?=$nick?></strong>님
        <? if ($is_admin == 'super' || $is_auth) { ?><a href="<?=$g4['admin_path']?>/" id="ol_admin">관리자</a><? } ?>
    </header>
    <ul id="ol_after_rec">
        <li><a href="javascript:win_memo();" id="ol_after_memo"><span id="ol_after_memo_img"></span>안 읽은 쪽지 <strong><?=$memo_not_read?></strong></a></li>
        <li><a href="javascript:win_point();" id="ol_after_pt"><span id="ol_after_pt_img"></span>포인트 <strong><?=$point?></strong></a></li>
        <li><a href="javascript:win_scrap();" id="ol_after_scrap"><span id="ol_after_scrap_img"></span><strong>스크랩</strong></a></li>
    </ul>
    <footer id="ol_after_ft">
        <ul>
            <li><a href="<?=$g4['bbs_path']?>/member_confirm.php?url=register_form.php" id="ol_after_info">정보수정</a></li>
            <li><a href="<?=$g4['bbs_path']?>/logout.php" id="ol_after_logout">로그아웃</a></li>
        </ul>
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
