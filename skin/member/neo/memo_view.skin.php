<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
?>

<div id="memo_view" class="new_win">
    <h1>
    <?
    //$nick = cut_str($mb[mb_nick], $config[cf_cut_name]);
    $nick = get_sideview($mb['mb_id'], $mb['mb_nick'], $mb['mb_email'], $mb['mb_homepage']);
    if ($kind == "recv")
        echo "{$nick}님께서 {$memo['me_send_datetime']}에 보내온 쪽지의 내용입니다.";
    if ($kind == "send")
        echo "{$nick}님께 {$memo['me_send_datetime']}에 보낸 쪽지의 내용입니다.";
    ?>
    </h1>
    <ul class="new_win_ul">
        <li><a href="./memo.php?kind=recv">받은쪽지</a></li>
        <li><a href="./memo.php?kind=send">보낸쪽지</a></li>
        <li><a href="./memo_form.php">쪽지보내기</a></li>
    </ul>
    <section>
        <h2>쪽지내용</h2>
        <p>
            <?=conv_content($memo['me_memo'], 0)?>
        </p>
    </section>
    <div class="btn_win">
        <? if($prev_link) { ?>
        <a href="<?=$prev_link?>">이전쪽지</a>
        <? } ?>
        <? if($next_link) { ?>
        <a href="<?=$next_link?>">다음쪽지</a>
        <? } ?>
        <? if ($kind == 'recv') { ?><a href="./memo_form.php?me_recv_mb_id=<?=$mb['mb_id']?>&amp;me_id=<?=$memo['me_id']?>">답장</a><? } ?>
        <a href="./memo.php?kind=<?=$kind?>">목록보기</a>
    </div>
</article>

<script>
$(function() {
    $(".btn_win").append("<a id=\win_close\">창닫기</a>");

    $(".btn_win a.win_close").click(function() {
        window.close();
    });
});
</script>