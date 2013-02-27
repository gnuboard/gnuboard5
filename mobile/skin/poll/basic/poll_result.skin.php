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
                            <?=$list[$i]['content']?>
                            <strong><?=$list[$i]['cnt']?> 표</strong>
                            <span><?=number_format($list[$i]['rate'], 1)?> 퍼센트</span>
                        </p>
                        <div class="poll_result_graph">
                            <span style="width:<?=number_format($list[$i]['rate'], 1)?>%"></span>
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
                <h1><?=$list2[$i]['pc_name']?><span class="sound_only">님의 의견</span></h1>
                <?=$list2[$i]['name']?>
                <span class="poll_datetime"><?=$list2[$i]['datetime']?></span>
            </header>
            <p>
                <?=$list2[$i]['idea']?>
            </p>
            <footer>
                <span class="poll_cmt_del"><? if ($list2[$i]['del']) { echo $list2[$i]['del']."삭제</a>"; } ?></span>
            </footer>
        </article>
        <? } ?>

        <? if ($member['mb_level'] >= $po['po_level']) { ?>
        <form name="fpollresult" method="post" action="./poll_etc_update.php" onsubmit="return fpollresult_submit(this);" autocomplete="off">
        <input type=hidden name="po_id" value="<?=$po_id?>">
        <input type=hidden name="w" value="">
        <input type=hidden name="skin_dir" value="<?=$skin_dir?>">
        <? if ($is_member) { ?><input type="hidden" name="pc_name" value="<?=cut_str($member['mb_nick'],255)?>"><? } ?>
        <h3><?=$po_etc?></h3>
        <table id="poll_result_wcmt" class="frm_tbl">
        <tbody>
        <? if ($is_guest) { ?>
        <tr>
            <th scope="row"><label for="pc_name">이름<strong class="sound_only">필수</strong></label></th>
            <td><input type="text" id="pc_name" name="pc_name" class="frm_input required" size="10" required></td>
        </tr>
        <? } ?>
        <tr>
            <th scope="row"><label for="pc_idea">의견<strong class="sound_only">필수</strong></label></th>
            <td><input type="text" id="pc_idea" name="pc_idea" class="frm_input required" size="47" required maxlength="100"></td>
        </tr>
        <? if ($is_guest) { ?>
        <tr>
            <th scope="row">자동등록방지</th>
            <td><?=captcha_html();?></td>
        </tr>
        <? } ?>
        </tbody>
        </table>

        <div class="btn_confirm">
            <input type="submit" class="btn_submit" value="의견남기기">
        </div>
        </form>
        <? } ?>

    </section>
    <? } ?>

    <section id="poll_result_oth">
        <h2>다른 투표 결과 보기</h2>
        <ul>
            <? for ($i=0; $i<count($list3); $i++) { ?>
            <li><a href="./poll_result.php?po_id=<?=$list3[$i]['po_id']?>&amp;skin_dir=<?=$skin_dir?>">[<?=$list3[$i]['date']?>] <?=$list3[$i]['subject']?></a></li>
            <? } ?>
        </ul>
    </section>

    <div class="btn_win">
        <a href="javascript:;" onclick="window.close();">창닫기</a>
    </div>
</div>

<script>
function fpollresult_submit(f)
{
    <? if ($is_guest) { echo chk_captcha_js(); } ?>

    return true;
}
</script>