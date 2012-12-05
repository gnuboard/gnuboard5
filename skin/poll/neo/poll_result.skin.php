<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
?>

<h1>설문조사 결과 보기</h1>

<section>
<h2><?=$po_subject?></h2>
<span>전체 <?=$nf_total_po_cnt?>표</span>

<ol>
<? for ($i=1; $i<=count($list); $i++) { ?>
    <li>
        <p>
            <?=$list[$i][content]?>
            <span><?=$list[$i][cnt]?>표 <?=number_format($list[$i][rate], 1)?>%</span>
        </p>
        <div>
            <span></span>
        </div>
    </li>
<? } ?>
</ol>
</section>

<? if ($is_etc) { ?>
<section>
    <h2>의견</h2>

    <? if ($member[mb_level] >= $po[po_level]) { ?>
    <form name="fpollresult" method="post" onsubmit="return fpollresult_submit(this);" autocomplete="off">
    <input type=hidden name="po_id" value="<?=$po_id?>">
    <input type=hidden name="w" value="">
    <input type=hidden name="skin_dir" value="<?=$skin_dir?>">
        <fieldset>
            <legend>의견남기기</legend>
            <p><?=$po_etc?></p>
            <? if ($member[mb_id]) { ?>
                <input type="hidden" name="pc_name" value="<?=cut_str($member[mb_nick],255)?>">
                <b><?=$member[mb_nick]?></b>
            <? } else { ?>
                <label for="pc_name">이름</label> <input type='text' id="pc_name" name="pc_name" size="10" required>
            <? } ?>
            <label for="pc_idea">의견</label> <input type="text" id="pc_idea" name="pc_idea" size="55" required maxlength="100">
            <input type="submit" value="의견남기기">
        </fieldset>
    </form>

    <script>
    function fpollresult_submit(f)
    {
        f.action = "./poll_etc_update.php";
        return true;
    }
    </script>
    <? } ?>

    <? for ($i=0; $i<count($list2); $i++) { ?>
    <article>
        <header>
            <h1><?=$list2[$i][name]?>님의 의견</h1>
            <?=$list2[$i][datetime]?>
        </header>
        <p>
            <?=$list2[$i][idea]?>
        </p>
        <? if ($list2[$i][del]) { echo $list2[$i][del]."삭제</a>"; } ?>
    </article>
    <? } ?>

</section>
<? } ?>

<section>
<h2>다른 투표 결과 보기</h2>
<ul>
<? for ($i=0; $i<count($list3); $i++) { ?><li><a href="./poll_result.php?po_id=<?=$list3[$i][po_id]?>&amp;skin_dir=<?=$skin_dir?>">[<?=$list3[$i][date]?>] <?=$list3[$i][subject]?></a></li><? } ?>
</ul>
</section>

<a href="javascript:window.close();">창닫기</a>
