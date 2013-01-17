<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
?>

<p id="bo_v_title"><strong><?=$board['bo_subject']?></strong></p>

<div id="bo_v">
    <h1 id="bo_v_h1"><?=cut_hangul_last(get_text($view['wr_subject']))?></h1>

    <section id="bo_v_info">
        <h2>게시물 정보</h2>
        <dl>
            <? if ($is_category) { ?>
            <dt>분류</dt>
            <dd><?=($category_name ? "{$view['ca_name']} " : "");?></dd>
            <? } ?>
            <dt>작성자</dt>
            <dd><div><?=$view['name']?><? if ($is_ip_view) { echo "&nbsp;($ip)"; } ?></div></dd>
            <dt>작성일</dt>
            <dd><?=date("y-m-d H:i", strtotime($view['wr_datetime']))?></dd>
            <dt>조회</dt>
            <dd><?=number_format($view['wr_hit'])?>회</dd>
            <dt>댓글</dt>
            <dd><?=number_format($view['wr_comment'])?>건</dd>
        </dl>
    </section>

    <? if (!empty($view['file'])) {?>
    <section id="bo_v_file">
        <h2>첨부파일</h2>
        <ul>
        <?
        // 가변 파일
        $cnt = 0;
        for ($i=0; $i<count($view['file']); $i++) {
            if (isset($view['file'][$i]['source']) && $view['file'][$i]['source'] && !$view['file'][$i]['view']) {
                $cnt++;
        ?>
            <li>
                <a href="<? echo $view['file'][$i]['href']; ?>" onclick="javascript:file_download('<? echo $view['file'][$i]['href'].'&amp;confirm=yes'; ?>', '<?=$view['file'][$i]['source']?>'); return false;">
                    <span><?=$view['file'][$i]['source']?> (<?=$view['file'][$i]['size']?>)</span>
                    <span class="bo_v_file_cnt"><?=$view['file'][$i]['download']?></span>
                    <span>DATE : <?=$view['file'][$i]['datetime']?></span>
                </a>
            </li>
        <?
            }
        }
        ?>
        </ul>
    </section>
    <? } ?>

    <? if (!empty($view['link'])) {?>
    <section id="bo_v_link">
        <h2>관련링크</h2>
        <ul>
        <?
        // 링크
        $cnt = 0;
        for ($i=1; $i<=$g4['link_count']; $i++) {
            if ($view['link'][$i]) {
                $cnt++;
                $link = cut_str($view['link'][$i], 70);
        ?>
            <li>
                <a href="<?=$view['link_href'][$i]?>" target="_blank">
                    <span><?=$link?></span>
                    <span class="bo_v_link_cnt"><?=$view['link_hit'][$i]?>회 연결</span>
                </a>
            </li>
        <?
            }
        }
        ?>
        </ul>
    </section>
    <? } ?>

    <aside id="bo_v_top">
        <h2>게시물 상단 링크</h2>
        <?
        ob_start();
        ?>
        <ul class="bo_v_com">
            <? if ($update_href) { ?><li><a href="<?=$update_href?>" class="btn02">수정</a></li><? } ?>
            <? if ($delete_href) { ?><li><a href="<?=$delete_href?>" onclick="del(this.href); return false;" class="btn02">삭제</a></li><? } ?>
            <? if ($copy_href) { ?><li><a href="<?=$copy_href?>" onclick="board_move(this.href); return false;" class="btn03">복사</a></li><? } ?>
            <? if ($move_href) { ?><li><a href="<?=$move_href?>" onclick="board_move(this.href); return false;" class="btn03">이동</a></li><? } ?>
            <? if ($search_href) { ?><li><a href="<?=$search_href?>" class="btn02">검색</a></li><? } ?>
            <li><a href="<?=$list_href?>" class="btn02">목록</a></li>
            <? if ($reply_href) { ?><li><a href="<?=$reply_href?>" class="btn02">답변</a></li><? } ?>
            <? if ($write_href) { ?><li><a href="<?=$write_href?>" class="btn01">글쓰기</a></li><? } ?>
        </ul>
        <?
        $link_buttons = ob_get_contents();
        ob_end_flush();
        ?>
    </aside>

    <article id="bo_v_atc">
        <header>
            <h1>본문</h1>
        </header>
        <div>
            <?
            // 파일 출력
            for ($i=0; $i<=count($view['file']); $i++) {
                if (isset($view['file'][$i]['view']) && $view['file'][$i]['view'])
                    echo $view['file'][$i]['view'];
            }
            ?>
        </div>

        <p><?=$view['content'];?></p>
        <?//echo $view[rich_content]; // {이미지:0} 과 같은 코드를 사용할 경우?>
        <!-- 테러 태그 방지용 --></xml></xmp><a href=""></a><a href=''></a>

        <? if ($is_signature) { ?><p><?=$signature?></p><? } ?>

        <? if ($scrap_href || $good_href || $nogood_href) { ?>
        <div id="bo_v_act">
            <? if ($scrap_href) { ?><a href="<? echo $scrap_href; ?>" onclick="win_scrap(this.href); return false;" target="_blank" class="btn02">스크랩</a><? } ?>
            <? if ($good_href) {?><a href="<?=$good_href?>" class="btn02" target="hiddenframe">추천 <strong><?=number_format($view['wr_good'])?></strong></a><? } ?>
            <? if ($nogood_href) {?><a href="<?=$nogood_href?>" class="btn02" target="hiddenframe">비추천 <strong><?=number_format($view['wr_nogood'])?></strong></a><? } ?>
        </div>
        <? } ?>
    </article>

    <?
    // 코멘트 입출력
    include_once('./view_comment.php');
    ?>

    <aside id="bo_v_bot">
        <h2>게시물 하단 링크</h2>
        <? if ($prev_href || $next_href) { ?>
        <ul id="bo_v_nb">
            <? if ($prev_href) { ?><li><a href="<?=$prev_href?>" class="btn02">이전</a></li><? } ?>
            <? if ($next_href) { ?><li><a href="<?=$next_href?>" class="btn02">다음</a></li><? } ?>
        </ul>
        <? } ?>

        <!-- 링크 버튼 -->
        <?=$link_buttons?>
    </aside>

</div>

<script>
function file_download(link, file) {
    <? if ($board['bo_download_point'] < 0) { ?>if (confirm("'"+file+"' 파일을 다운로드 하시면 포인트가 차감(<?=number_format($board['bo_download_point'])?>점)됩니다.\n\n포인트는 게시물당 한번만 차감되며 다음에 다시 다운로드 하셔도 중복하여 차감하지 않습니다.\n\n그래도 다운로드 하시겠습니까?"))<?}?>
    document.location.href=link;
}

function board_move(href)
{
    window.open(href, "boardmove", "left=50, top=50, width=500, height=550, scrollbars=1");
}
</script>

<script src="<?=$g4['path']?>/js/board.js"></script>
<!-- 게시글 보기 끝 -->

<script>
// 이미지 등비율 리사이징
$(document).ready(function(){
    var img = $('#bo_v_atc img');
    var img_org_width = img.width();
    $(window).resize(function(){
        var wrapper_width = $('#bo_v_atc').width();
        img.each(function() {
            var img_width = $(this).width();
            if (img_width > wrapper_width) {
                $(this).addClass('img_fix');
            } else if (img_width <= wrapper_width && img_width >= img_org_width) {
                $(this).removeClass('img_fix');
            }
        });
    }).resize();
});
</script>