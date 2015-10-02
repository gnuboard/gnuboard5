<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$poll_skin_url.'/style.css">', 0);
?>

<div id="poll_result" class="new_win">
    <h1 id="win_title"><?php echo $g5['title'] ?></h1>

    <section id="poll_result_list">
        <h2><?php echo $po_subject ?> 결과</h2>

        <dl>
            <dt><span>전체 <?php echo $nf_total_po_cnt ?>표</span></dt>
            <dd>
                <ol>
                <?php for ($i=1; $i<=count($list); $i++) { ?>
                    <li>
                        <p>
                            <?php echo $list[$i]['content'] ?>
                            <strong><?php echo $list[$i]['cnt'] ?> 표</strong>
                            <span><?php echo number_format($list[$i]['rate'], 1) ?>%</span>
                        </p>
                        <div class="poll_result_graph">
                            <span style="width:<?php echo number_format($list[$i]['rate'], 1) ?>%"></span>
                        </div>
                    </li>
                <?php } ?>
                </ol>
            </dd>
        </dl>
    </section>

    <?php if ($is_etc) { ?>
    <section id="poll_result_cmt">
        <h2>이 설문에 대한 기타의견</h2>

        <?php for ($i=0; $i<count($list2); $i++) { ?>
        <article>
            <header>
                <h1><?php echo $list2[$i]['pc_name'] ?><span class="sound_only">님의 의견</span></h1>
                <?php echo $list2[$i]['name'] ?>
                <span class="poll_datetime"><?php echo $list2[$i]['datetime'] ?></span>
            </header>
            <p>
                <?php echo $list2[$i]['idea'] ?>
            </p>
            <footer>
                <span class="poll_cmt_del"><?php if ($list2[$i]['del']) { echo $list2[$i]['del']."삭제</a>"; } ?></span>
            </footer>
        </article>
        <?php } ?>

        <?php if ($member['mb_level'] >= $po['po_level']) { ?>
        <form name="fpollresult" method="post" action="./poll_etc_update.php" onsubmit="return fpollresult_submit(this);" autocomplete="off">
        <input type="hidden" name="po_id" value="<?php echo $po_id ?>">
        <input type="hidden" name="w" value="">
        <input type="hidden" name="skin_dir" value="<?php echo urlencode($skin_dir); ?>">
        <?php if ($is_member) { ?><input type="hidden" name="pc_name" value="<?php echo get_text(cut_str($member['mb_nick'],255)); ?>"><?php } ?>
        <h3><?php echo $po_etc ?></h3>

        <div class="tbl_frm01 tbl_wrap">
            <table id="poll_result_wcmt">
            <tbody>
            <?php if ($is_guest) { ?>
            <tr>
                <td><input type="text" name="pc_name" id="pc_name" class="frm_input required" required placeholder="이름(필수)"></td>
            </tr>
            <?php } ?>
            <tr>
                <td><input type="text" name="pc_idea" id="pc_idea" class="frm_input required" required maxlength="100" placeholder="의견(필수)"></td>
            </tr>
            <?php if ($is_guest) { ?>
            <tr>
                <td><?php echo captcha_html(); ?></td>
            </tr>
            <?php } ?>
            </tbody>
            </table>
        </div>

        <div class="btn_confirm">
            <input type="submit" class="btn_submit" value="의견남기기">
        </div>
        </form>
        <?php } ?>

    </section>
    <?php } ?>

    <aside id="poll_result_oth">
        <h2>다른 투표 결과 보기</h2>
        <ul>
            <?php for ($i=0; $i<count($list3); $i++) { ?>
            <li><a href="./poll_result.php?po_id=<?php echo $list3[$i]['po_id'] ?>&amp;skin_dir=<?php echo urlencode($skin_dir); ?>">[<?php echo $list3[$i]['date'] ?>] <?php echo $list3[$i]['subject'] ?></a></li>
            <?php } ?>
        </ul>
    </aside>

    <div class="win_btn">
        <button type="button" onclick="window.close();">창닫기</button>
    </div>
</div>

<script>
$(function() {
    $(".poll_delete").click(function() {
        if(!confirm("해당 기타의견을 삭제하시겠습니까?"))
            return false;
    });
});

function fpollresult_submit(f)
{
    <?php if ($is_guest) { echo chk_captcha_js(); } ?>

    return true;
}
</script>