<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
?>

<!-- 로그인 후 외부로그인 시작 -->
<section>
    <h2>회원정보</h2>
    <?=$nick?>님
    <? if ($is_admin == 'super' || $is_auth) { ?><a href="<?=$g4['admin_path']?>/">관리자</a><? } ?>
    <a href="javascript:win_point();">포인트 : <?=$point?>점</a>
    <a href="<?=$g4['bbs_path']?>/member_confirm.php?url=register_form.php">정보수정</a>
    <a href="javascript:win_memo();">쪽지 <?=$memo_not_read?>통</a>
    <a href="javascript:win_scrap();">스크랩</a>
    <a href="<?=$g4['bbs_path']?>/logout.php">로그아웃</a>
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
