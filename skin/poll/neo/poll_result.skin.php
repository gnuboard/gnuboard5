<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
?>

<div id="poll_result" class="new_win">
    <h1><?=$g4['title']?></h1>

    <section id="poll_result_list">
        <h2><?=$po_subject?> 결과</h2>
        
        <dl>
            <dt><span>전체 <?=$nf_total_po_cnt?>표</span></dt>
            <dd>
                <ol>
                <? for ($i=1; $i<=count($list); $i++) { ?>
                    <li>
                        <p>
                            <?=$list[$i][content]?>
                            <span><?=$list[$i][cnt]?>표 <?=number_format($list[$i][rate], 1)?>%</span>
                        </p>
                        <div class="poll_result_graph">
                            <span style="width:<?=number_format($list[$i][rate], 1)?>%"></span>
                        </div>
                    </li>
                <? } ?>
                </ol>
            </dd>
        </dl>
    </section>

    <? if ($is_etc) { ?>
    <section id="poll_result_cmt">
        <h2>이 설문에 대한 기타의견</h2>

        <? for ($i=0; $i<count($list2); $i++) { ?>
        <article>
            <header>
                <h1><?=$list2[$i][name]?>님의 의견</h1>
                <span class="poll_datetime"><?=$list2[$i][datetime]?></span>
                <span class="poll_del"><? if ($list2[$i][del]) { echo $list2[$i][del]."삭제</a>"; } ?></span>
            </header>
            <p>
                <?=$list2[$i][idea]?>
            </p>
        </article>
        <? } ?>

        <? if ($member[mb_level] >= $po[po_level]) { ?>
        <form name="fpollresult" method="post" onsubmit="return fpollresult_submit(this);" autocomplete="off">
        <input type=hidden name="po_id" value="<?=$po_id?>">
        <input type=hidden name="w" value="">
        <input type=hidden name="skin_dir" value="<?=$skin_dir?>">
        <fieldset>
            <legend>의견남기기</legend>
            <p><?=$po_etc?></p>
            <?
            $comment_size = "";
            if ($member[mb_id]) { $comment_size = 52; ?>
                <input type="hidden" name="pc_name" value="<?=cut_str($member[mb_nick],255)?>">
            <? } else { $comment_size = 32; ?>
                <label for="pc_name">이름</label>
                <input type='text' id="pc_name" name="pc_name" class="fieldset_input required" size="10" required>
            <? } ?>
            <input type="text" id="pc_idea" name="pc_idea" class="fieldset_input required" size="<?=$comment_size?>" required maxlength="100" title="의견">
            <input type="submit" class="fieldset_submit" value="의견남기기">
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

    </section>
    <? } ?>

    <section id="poll_result_another">
        <h2>다른 투표 결과 보기</h2>
        <ul>
        <? for ($i=0; $i<count($list3); $i++) { ?><li><a href="./poll_result.php?po_id=<?=$list3[$i][po_id]?>&amp;skin_dir=<?=$skin_dir?>">[<?=$list3[$i][date]?>] <?=$list3[$i][subject]?></a></li><? } ?>
        </ul>
    </section>

    <div class="btn_window">
        <a href="javascript:window.close();">창닫기</a>
    </div>
</div>