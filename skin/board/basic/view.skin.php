<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
include_once(G4_LIB_PATH.'/thumbnail.lib.php');
?>

<div id="bo_v" style="width:<?=$width;?>">

    <p id="bo_v_cate">
        <?=$board['bo_subject']?>
        <? if ($category_name) { // 분류가 지정되었다면 ?><?=($category_name ? "{$view['ca_name']} " : "");?><? } // 분류 출력 끝 ?>
    </p>

    <h1 id="bo_v_h1"><?=cut_str(get_text($view['wr_subject']), 70) // 글제목 출력?></h1>

    <section id="bo_v_info">
        <h2>게시물 정보</h2>
        작성자 <strong><?=$view['name']?><? if ($is_ip_view) { echo "&nbsp;($ip)"; } ?></strong>
        <span class="sound_only">작성일</span><strong><?=date("y-m-d H:i", strtotime($view['wr_datetime']))?></strong>
        조회<strong><?=number_format($view['wr_hit'])?>회</strong>
        댓글<strong><?=number_format($view['wr_comment'])?>건</strong>
    </section>

    <?
    if ($view['file']['count']) {
        $cnt = 0;
        for ($i=0; $i<count($view['file']); $i++) {
            if (isset($view['file'][$i]['source']) && $view['file'][$i]['source'] && !$view['file'][$i]['view'])
                $cnt++;
        }
    }
    ?>

    <? if($cnt) { ?>
    <section id="bo_v_file">
        <h2>첨부파일</h2>
        <ul>
        <?
        // 가변 파일
        for ($i=0; $i<count($view['file']); $i++) {
            if (isset($view['file'][$i]['source']) && $view['file'][$i]['source'] && !$view['file'][$i]['view']) {
        ?>
            <li>
                <a href="<? echo $view['file'][$i]['href']; ?>" onclick="javascript:file_download('<? echo $view['file'][$i]['href'].'&amp;confirm=yes'; ?>', '<?=$view['file'][$i]['source']?>'); return false;">
                    <img src="<?=$board_skin_url?>/img/icon_file.gif" alt="첨부파일">
                    <strong><?=$view['file'][$i]['source']?></strong>
                    <span> (<?=$view['file'][$i]['size']?>)</span>
                </a>
                <span class="bo_v_file_cnt"><?=$view['file'][$i]['download']?>회 다운로드</span>
                <span>DATE : <?=$view['file'][$i]['datetime']?></span>
            </li>
        <?
            }
        }
        ?>
        </ul>
    </section>
    <? } ?>

    <?
    if (implode('', $view['link'])) {
    ?>
    <section id="bo_v_link">
        <h2>관련링크</h2>
        <ul>
        <?
        // 링크
        $cnt = 0;
        for ($i=1; $i<=count($view['link']); $i++) {
            if ($view['link'][$i]) {
                $cnt++;
                $link = cut_str($view['link'][$i], 70);
        ?>
            <li>
                <a href="<?=$view['link_href'][$i]?>" target="_blank">
                    <img src="<?=$board_skin_url?>/img/icon_link.gif" alt="관련링크">
                    <strong><?=$link?></strong>
                </a>
                <span class="bo_v_link_cnt"><?=$view['link_hit'][$i]?>회 연결</span>
            </li>
        <?
            }
        }
        ?>
        </ul>
    </section>
    <? } ?>

    <nav id="bo_v_top">
        <h2>게시물 상단 버튼</h2>
        <?
        ob_start();
        ?>
        <? if ($prev_href || $next_href) { ?>
        <ul class="bo_v_nb">
            <? if ($prev_href) { ?><li><a href="<?=$prev_href?>" class="btn_b01">이전글</a></li><? } ?>
            <? if ($next_href) { ?><li><a href="<?=$next_href?>" class="btn_b01">다음글</a></li><? } ?>
        </ul>
        <? } ?>

        <ul class="bo_v_com">
            <? if ($update_href) { ?><li><a href="<?=$update_href?>" class="btn_b01">수정</a></li><? } ?>
            <? if ($delete_href) { ?><li><a href="<?=$delete_href?>" class="btn_b01" onclick="del(this.href); return false;">삭제</a></li><? } ?>
            <? if ($copy_href) { ?><li><a href="<?=$copy_href?>" class="btn_admin" onclick="board_move(this.href); return false;">복사</a></li><? } ?>
            <? if ($move_href) { ?><li><a href="<?=$move_href?>" class="btn_admin" onclick="board_move(this.href); return false;">이동</a></li><? } ?>
            <? if ($search_href) { ?><li><a href="<?=$search_href?>" class="btn_b01">검색</a></li><? } ?>
            <li><a href="<?=$list_href?>" class="btn_b01">목록</a></li>
            <? if ($reply_href) { ?><li><a href="<?=$reply_href?>" class="btn_b01">답변</a></li><? } ?>
            <? if ($write_href) { ?><li><a href="<?=$write_href?>" class="btn_b02">글쓰기</a></li><? } ?>
        </ul>
        <?
        $link_buttons = ob_get_contents();
        ob_end_flush();
        ?>
    </nav>

    <article id="bo_v_atc">
        <header>
            <h1>본문</h1>
        </header>

        <?
        // 파일 출력
        $v_img_count = count($view['file']);
        if($v_img_count) {
            echo "<div id=\"bo_v_img\">\n";

            for ($i=0; $i<=count($view['file']); $i++) {
                if ($view['file'][$i]['view']) {
                    //echo $view['file'][$i]['view'];
                    echo get_view_thumbnail($view['file'][$i]['view']);
                }
            }

            echo "</div>\n";
        }
        ?>

        <div id="bo_v_con"><?=get_view_thumbnail($view['content']);?></div>
        <?//echo $view[rich_content]; // {이미지:0} 과 같은 코드를 사용할 경우?>
        <!-- 테러 태그 방지용 --></xml></xmp><a href=""></a><a href=''></a>

        <? if ($is_signature) { ?><p><?=$signature?></p><? } ?>

        <? if ($scrap_href || $good_href || $nogood_href) { ?>
        <div id="bo_v_act">
            <? if ($scrap_href) { ?><a href="<?=$scrap_href; ?>" target="_blank" onclick="win_scrap(this.href); return false;" class="btn_b01">스크랩</a><? } ?>
            <? if ($good_href) {?><a href="<?=$good_href?>" class="btn_b01" target="hiddenframe">추천 <strong><?=number_format($view['wr_good'])?></strong></a><? } ?>
            <? if ($nogood_href) {?><a href="<?=$nogood_href?>" class="btn_b01" target="hiddenframe">비추천 <strong><?=number_format($view['wr_nogood'])?></strong></a><? } ?>
        </div>
        <? } else {
            if($board['bo_use_good'] || $board['bo_use_nogood']) {
        ?>
        <div id="bo_v_act">
            <? if($board['bo_use_good']) { ?><span>추천 <strong><?=number_format($view['wr_good'])?></strong></span><? } ?>
            <? if($board['bo_use_nogood']) { ?><span>비추천 <strong><?=number_format($view['wr_nogood'])?></strong></span><? } ?>
        </div>
        <?
            }
        }
        ?>
    </article>

    <?
    // 코멘트 입출력
    include_once('./view_comment.php');
    ?>

    <nav id="bo_v_bot">
        <h2>게시물 하단 버튼</h2>

        <!-- 링크 버튼 -->
        <?=$link_buttons?>
    </nav>

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

<!-- 게시글 보기 끝 -->

<script>
// 이미지 등비율 리사이징
$(window).load(function() {
    view_image_resize();
});

$(function() {
    $("a.view_image").click(function() {
        window.open(this.href, "large_image", "top=10,left=10,width=10,height=10,resizable=yes,scrollbars=no,status=no");
        return false;
    });
});

function view_image_resize()
{
    var $img = $("#bo_v_atc img");
    var img_wrap = $("#bo_v_atc").width();

    $img.each(function() {
        var img_width = $(this).width();
        $(this).data("width", img_width); // 원래 이미지 사이즈
        if (img_width > img_wrap) {
            $(this).addClass("img_fix");
        } else if (img_width <= img_wrap && img_width >= $(this).data("width")) {
            $(this).removeClass("img_fix");
        }
    });
}
</script>