<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
?>

<div id="bo_v">
    <h1 id="bo_v_h1"><? if ($is_category) { echo ($category_name ? "{$view['ca_name']} " : ""); } ?><?=cut_hangul_last(get_text($view['wr_subject']))?></h1>

    <aside>
        <h2>게시물 상단 링크</h2>
        <!-- 링크 버튼 -->
        <? if ($update_href || $delete_href) {?>
        <ul>
        <? if ($update_href) { ?>
            <li><a href="<?=$update_href?>">수정</a></li>
        <? } ?>
        <? if ($delete_href) { ?>
            <li><a href="<?=$delete_href?>">삭제</a></li>
        <? } ?>
        </ul>
        <? } ?>

        <ul>
        <?
        ob_start();
        ?>
        <? if ($copy_href) { ?>
            <li><a href="<?=$copy_href?>">복사</a></li>
        <? } ?>
        <? if ($move_href) { ?>
            <li><a href="<?=$move_href?>">이동</a></li>
        <? } ?>
        <? if ($search_href) { ?>
            <li><a href="<?=$search_href?>">검색</a></li>
        <? } ?>
            <li><a href="<?=$list_href?>">목록</a></li>
        <? if ($reply_href) { ?>
            <li><a href="<?=$reply_href?>">답변</a></li>
        <? } ?>
        <? if ($write_href) { ?>
            <li><a href="<?=$write_href?>">글쓰기</a></li>
        <? } ?>
        <?
        $link_buttons = ob_get_contents();
        ob_end_flush();
        ?>
        </ul>
    </aside>

    <section id="bo_v_info">
        <h2>게시물 정보</h2>
        <dl>
            <dt>작성자</dt>
            <dd><?=$view['name']?><? if ($is_ip_view) { echo "&nbsp;($ip)"; } ?></dd>
            <dt>작성일</dt>
            <dd><?=date("y-m-d H:i", strtotime($view['wr_datetime']))?></dd>
            <dt>조회</dt>
            <dd><?=number_format($view['wr_hit'])?>회</dd>
            <dt>댓글</dt>
            <dd><?=number_format($view['wr_comment'])?>건</dd>
            <? if ($is_good) { ?>
            <dt>추천</dt>
            <dd><?=number_format($view['wr_good'])?>회</dd>
            <? } ?>
            <? if ($is_nogood) { ?>
            <dt>비추천</dt>
            <dd><?=number_format($view['wr_nogood'])?></dd>
            <? } ?>
        </dl>
    </section>

    <? if ($view['file'][$i]) {?>
    <section>
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
                <a href="javascript:file_download('<?=$view['file'][$i]['href']?>', '<?=urlencode($view['file'][$i]['source'])?>');">
                    <span><?=$view['file'][$i]['source']?> (<?=$view['file'][$i]['size']?>)</span>
                    <span><?=$view['file'][$i]['download']?></span>
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

    <? if ($view['link'][$i]) {?>
    <section>
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
                    <span><?=$view['link_hit'][$i]?></span>
                </a>
            </li>
        <?
            }
        }
        ?>
        </ul>
    </section>
    <? } ?>

    <article>
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
        <ul>
            <? if ($scrap_href) { ?><li><a href="javascript:;" onclick="win_scrap('<?=$scrap_href?>');">스크랩</a></li><? } ?>
            <? if ($good_href) {?><li>추천 <?=number_format($view['wr_good'])?> <a href="<?=$good_href?>" target="hiddenframe">추천</a></li><? } ?>
            <? if ($nogood_href) {?><li>비추천 <?=number_format($view['wr_nogood'])?> <a href="<?=$nogood_href?>" target="hiddenframe">비추천</a></li><? } ?>
        </ul>
        <? } ?>
    </article>

    <?
    // 코멘트 입출력
    include_once('./view_comment.php');
    ?>

    <aside>
        <h2>게시물 하단 링크</h2>
        <ul>
            <? if ($prev_href) { ?><li><a href="<?=$prev_href?>">이전</a></li><? } ?>
            <? if ($next_href) { ?><li><a href="<?=$next_href?>">다음</a></li><? } ?>
        </ul>

        <!-- 링크 버튼 -->
        <ul>
            <?=$link_buttons?>
        </ul>
    </aside>

</div>

<script>
function file_download(link, file) {
    <? if ($board['bo_download_point'] < 0) { ?>if (confirm("'"+decodeURIComponent(file)+"' 파일을 다운로드 하시면 포인트가 차감(<?=number_format($board['bo_download_point'])?>점)됩니다.\n\n포인트는 게시물당 한번만 차감되며 다음에 다시 다운로드 하셔도 중복하여 차감하지 않습니다.\n\n그래도 다운로드 하시겠습니까?"))<?}?>
    document.location.href=link;
}
</script>

<script src="<?=$g4['path']?>/js/board.js"></script>
<!-- 게시글 보기 끝 -->

<script>
// 이미지 등비율 리사이징
$(document).ready(function(){
    var img = $('article img');
    var img_org_width = img.width();
    $(window).resize(function(){
        var wrapper_width = $('#wrapper').width();
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