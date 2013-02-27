<?
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
$nick = get_sideview($mb['mb_id'], $mb['mb_nick'], $mb['mb_email'], $mb['mb_homepage']);
if($kind == "recv") {
    $kind_str = "보낸";
    $kind_date = "받은";
}
else {
    $kind_str = "받는";
    $kind_date = "보낸";
}
?>

<div id="memo_view" class="new_win">
    <h1><?=$g4['title']?></h1>
    <ul class="new_win_ul">
        <li><a href="./memo.php?kind=recv">받은쪽지</a></li>
        <li><a href="./memo.php?kind=send">보낸쪽지</a></li>
        <li><a href="./memo_form.php">쪽지쓰기</a></li>
    </ul>
    <section>
        <h2>쪽지 내용</h2>
        <ul id="memo_view_ul">
            <li class="memo_view_li">
                <span class="memo_view_subj"><?=$kind_str?>사람</span>
                <strong><?=$nick?></strong>
            </li>
            <li class="memo_view_li">
                <span class="memo_view_subj"><?=$kind_date?>시간</span>
                <strong><?=$memo['me_send_datetime']?></strong>
            </li>
        </ul>
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
        <? if ($kind == 'recv') { ?><a href="./memo_form.php?me_recv_mb_id=<?=$mb['mb_id']?>&amp;me_id=<?=$memo['me_id']?>" class="btn01">답장</a><? } ?>
        <a href="./memo.php?kind=<?=$kind?>">목록보기</a>
        <a href="javascript:;" onclick="window.close();">창닫기</a>
    </div>
</div>