<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
?>

<article>
    <header>
        <h1>
        <?
        //$nick = cut_str($mb[mb_nick], $config[cf_cut_name]);
        $nick = get_sideview($mb[mb_id], $mb[mb_nick], $mb[mb_email], $mb[mb_homepage]);
        if ($kind == "recv")
            echo "{$nick}님께서 {$memo[me_send_datetime]}에 보내온 쪽지의 내용입니다.";
        if ($kind == "send") 
            echo "{$nick}님께 {$memo[me_send_datetime]}에 보낸 쪽지의 내용입니다."; 
        ?>
        </h1>
    </header>
    <p>
        <?=conv_content($memo[me_memo], 0)?>
    </p>
    <div>
        <a href="<?=$prev_link?>">이전쪽지</a>
        <a href="<?=$next_link?>">다음쪽지</a>
        <? if ($kind == 'recv') { ?><a href="./memo_form.php?me_recv_mb_id=<?=$mb[mb_id]?>&amp;me_id=<?=$memo[me_id]?>">답장</a><? } ?>
        <a href="./memo.php?kind=<?=$kind?>">목록보기</a>
        <a href="javascript:window.close();">창닫기</a>
    </div>
</article>
