<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
?>

<!-- 링크 버튼 -->
<div>
<? if ($update_href) { ?>
    <a href="<?=$update_href?>">수정</a>
<? } ?>
<? if ($delete_href) { ?>
    <a href="<?=$delete_href?>">삭제</a>
<? } ?>
</div>

<div>
<?
ob_start();
?>
<? if ($copy_href) { ?>
    <a href="<?=$copy_href?>">복사</a>
<? } ?>
<? if ($move_href) { ?>
    <a href="<?=$move_href?>">이동</a>
<? } ?>
<? if ($search_href) { ?>
    <a href="<?=$search_href?>">검색</a>
<? } ?>
    <a href="<?=$list_href?>">목록</a>
<? if ($reply_href) { ?>
    <a href="<?=$reply_href?>">답변</a>
<? } ?>
<? if ($write_href) { ?>
    <a href="<?=$write_href?>">글쓰기</a>
<? } ?>
<?
$link_buttons = ob_get_contents();
ob_end_flush();
?>
</div>

<article>

    <header>
        <h1><? if ($is_category) { echo ($category_name ? "[{$view['ca_name']}] " : ""); } ?><?=cut_hangul_last(get_text($view['wr_subject']))?></h1>
        <dl>
            <dt>작성자</dt>
            <dd><?=$view[name]?><? if ($is_ip_view) { echo "&nbsp;($ip)"; } ?></dd>
            <dt>작성일</dt>
            <dd><?=date("y-m-d H:i", strtotime($view['wr_datetime']))?></dd>
            <dt>조회</dt>
            <dd><?=number_format($view['wr_hit'])?>회</dd>
            <dt>댓글</dt>
            <dd></dd>
            <? if ($is_good) { ?>
            <dt>추천</dt>
            <dd><?=number_format($view['wr_good'])?>회</dd>
            <? } ?>
            <? if ($is_nogood) { ?>
            <dt>비추천</dt>
            <dd><?=number_format($view['wr_nogood'])?></dd>
            <? } ?>
        </dl>
    </header>

    <section>
        <h2>첨부파일</h2>
        <?
        // 가변 파일
        $cnt = 0;
        for ($i=0; $i<count($view['file']); $i++) {
            if ($view['file'][$i]['source'] && !$view['file'][$i]['view']) {
                $cnt++;
        ?>
        <a href="javascript:file_download('<?=$view['file'][$i]['href']?>', '<?=urlencode($view['file'][$i]['source'])?>');">
            <span><?=$view['file'][$i]['source']?> (<?=$view['file'][$i]['size']?>)</span>
            <span><?=$view['file'][$i]['download']?></span>
            <span>DATE : <?=$view['file'][$i]['datetime']?></span>
        </a>
        <?
            }
        }
        ?>
    </section>

    <section>
        <h2>관련링크</h2>
        <?
        // 링크
        $cnt = 0;
        for ($i=1; $i<=$g4['link_count']; $i++) {
            if ($view['link'][$i]) {
                $cnt++;
                $link = cut_str($view['link'][$i], 70);
        ?>
        <a href="<?=$view['link_href'][$i]?>" target="_blank">
            <span><?=$link?></span>
            <span><?=$view['link_hit'][$i]?></span>
        </a>
        <?
            }
        }
        ?>
    </section>

    <div>
        <?
        // 파일 출력
        for ($i=0; $i<=count($view['file']); $i++) {
            if ($view['file'][$i]['view'])
                echo $view['file'][$i]['view'];
        }
        ?>
    </div>

    <p><?=$view['content'];?></p>

    <?//echo $view[rich_content]; // {이미지:0} 과 같은 코드를 사용할 경우?>
    <!-- 테러 태그 방지용 --></xml></xmp><a href=""></a><a href=''></a>

    <? if ($is_signature) { echo "<tr><td align='center' style='border-bottom:1px solid #E7E7E7; padding:5px 0;'>$signature</td></tr>"; } // 서명 출력 ?>

    <? if ($scrap_href) { echo "<a href=\"javascript:;\" onclick=\"win_scrap('$scrap_href');\"><img src='$board_skin_path/img/btn_scrap.gif' border='0' align='absmiddle'></a> "; } ?>
    <? if ($trackback_url) { ?><a href="javascript:trackback_send_server('<?=$trackback_url?>');" style="letter-spacing:0;" title='주소 복사'><img src="<?=$board_skin_path?>/img/btn_trackback.gif" border='0' align="absmiddle"></a><?}?>

    <? if ($good_href) {?>
    <div style="width:72px; height:55px; background:url(<?=$board_skin_path?>/img/good_bg.gif) no-repeat; text-align:center; float:right;">
        <div>추천 : <?=number_format($view['wr_good'])?></div>
        <div><a href="<?=$good_href?>" target="hiddenframe"><img src="<?=$board_skin_path?>/img/icon_good.gif" border='0' align="absmiddle"></a></div>
    </div>
    <? } ?>

    <? if ($nogood_href) {?>
    <div>
        <div>비추천 : <?=number_format($view['wr_nogood'])?></div>
        <div><a href="<?=$nogood_href?>" target="hiddenframe"><img src="<?=$board_skin_path?>/img/icon_nogood.gif" border='0' align="absmiddle"></a></div>
    </div>
    <? } ?>

    <?
    // 코멘트 입출력
    include_once('./view_comment.php');
    ?>

</article>

<div>
    <? if ($prev_href) { echo "<a href=\"$prev_href\" title=\"$prev_wr_subject\"><img src='$board_skin_path/img/btn_prev.gif' border='0' align='absmiddle'></a>&nbsp;"; } ?>
    <? if ($next_href) { echo "<a href=\"$next_href\" title=\"$next_wr_subject\"><img src='$board_skin_path/img/btn_next.gif' border='0' align='absmiddle'></a>&nbsp;"; } ?>
</div>

<!-- 링크 버튼 -->
<div>
    <?=$link_buttons?>
</div>

<script>
function file_download(link, file) {
    <? if ($board['bo_download_point'] < 0) { ?>if (confirm("'"+decodeURIComponent(file)+"' 파일을 다운로드 하시면 포인트가 차감(<?=number_format($board['bo_download_point'])?>점)됩니다.\n\n포인트는 게시물당 한번만 차감되며 다음에 다시 다운로드 하셔도 중복하여 차감하지 않습니다.\n\n그래도 다운로드 하시겠습니까?"))<?}?>
    document.location.href=link;
}
</script>

<script src="<?=$g4['path']?>/js/board.js"></script>
<script>
window.onload=function() {
    resizeBoardImage(<?=(int)$board['bo_image_width']?>);
}
</script>
<!-- 게시글 보기 끝 -->
