<?
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 선택옵션으로 인해 셀합치기가 가변적으로 변함
$colspan = 5;

if ($is_checkbox) $colspan++;
if ($is_good) $colspan++;
if ($is_nogood) $colspan++;
?>

<? if (!$wr_id) {?><h1 id="bo_list_title"><?=$g4['title']?></h1><?}?>

<!-- 게시판 목록 시작 -->
<div id="bo_list" style="width:<?=$width;?>">

    <? if ($is_category) { ?>
    <form id="fcategory" name="fcategory" method="get">
    <nav id="bo_cate">
        <h2><?=$board['bo_subject']?> 카테고리</h2>
        <ul id="bo_cate_ul">
            <?=$category_option?>
        </ul>
    </nav>
    </form>
    <? } ?>

    <div class="bo_fx">
        <div id="bo_list_total">
            <span>Total <?=number_format($total_count)?>건</span>
            <?=$page?> 페이지
        </div>

        <? if ($rss_href || $write_href) {?>
        <ul class="btn_bo_user">
            <? if ($rss_href) { ?><li><a href="<?=$rss_href?>" class="btn_b01">RSS</a></li><? } ?>
            <? if ($admin_href) { ?><li><a href="<?=$admin_href?>" class="btn_admin">관리자</a></li><? } ?>
            <? if ($write_href) { ?><li><a href="<?=$write_href?>" class="btn_b02">글쓰기</a></li><? } ?>
        </ul>
        <? } ?>
    </div>

    <form id="fboardlist" name="fboardlist" method="post" action="./board_list_update.php" onsubmit="return fboardlist_submit(this);">
    <input type="hidden" name="bo_table" value="<?=$bo_table?>">
    <input type="hidden" name="sfl" value="<?=$sfl?>">
    <input type="hidden" name="stx" value="<?=$stx?>">
    <input type="hidden" name="spt" value="<?=$spt?>">
    <input type="hidden" name="page" value="<?=$page?>">
    <input type="hidden" name="sw" value="">

    <table class="basic_tbl">
    <thead>
    <tr>
        <th scope="col">번호</th>
        <? if ($is_checkbox) { ?><th scope="col"><input type="checkbox" onclick="if (this.checked) all_checked(true); else all_checked(false);" title="현재 페이지 게시물 전체선택"></th><?}?>
        <th scope="col">제목</th>
        <th scope="col">글쓴이</th>
        <th scope="col"><?=subject_sort_link('wr_datetime', $qstr2, 1)?>날짜</a></th>
        <th scope="col"><?=subject_sort_link('wr_hit', $qstr2, 1)?>조회</a></th>
        <? if ($is_good) { ?><th scope="col"><?=subject_sort_link('wr_good', $qstr2, 1)?>추천</a></th><?}?>
        <? if ($is_nogood) { ?><th scope="col"><?=subject_sort_link('wr_nogood', $qstr2, 1)?>비추천</a></th><?}?>
    </tr>
    </thead>
    <tbody>
    <?
    for ($i=0; $i<count($list); $i++) {
    ?>
    <tr class="<? if ($list[$i]['is_notice']) echo "bo_notice";?><? if ($board[1]) echo "bo_sideview";?>">
        <td class="td_num">
        <?
        if ($list[$i]['is_notice']) // 공지사항
            echo '<strong>공지</strong>';
        else if ($wr_id == $list[$i]['wr_id'])
            echo "<span class=\"bo_current\">열람중</span>";
        else
            echo $list[$i]['num'];
        ?>
        </td>
        <? if ($is_checkbox) { ?><td class="td_chk"><input type="checkbox" name="chk_wr_id[]" value="<?=$list[$i]['wr_id']?>" title="이 게시물 선택"></td><? } ?>
        <td class="td_subject">
            <?
            echo $list[$i]['icon_reply'];
            if ($is_category && $list[$i]['ca_name']) {
            ?>
            <a href="<?=$list[$i]['ca_name_href']?>" class="bo_cate_link"><?=$list[$i]['ca_name']?></a>
            <? } ?>

            <a href="<?=$list[$i]['href']?>">
                <?=$list[$i]['subject']?>
                <? if ($list[$i]['comment_cnt']) { ?><span class="sound_only">댓글</span><?=$list[$i]['comment_cnt'];?><span class="sound_only">개</span><? } ?>
            </a>

            <?
            // if ($list[$i]['link']['count']) { echo '['.$list[$i]['link']['count']}.']'; }
            // if ($list[$i]['file']['count']) { echo '<'.$list[$i]['file']['count'].'>'; }

            if (isset($list[$i]['icon_new'])) echo $list[$i]['icon_new'];
            if (isset($list[$i]['icon_hot'])) echo $list[$i]['icon_hot'];
            if (isset($list[$i]['icon_file'])) echo $list[$i]['icon_file'];
            if (isset($list[$i]['icon_link'])) echo $list[$i]['icon_link'];
            if (isset($list[$i]['icon_secret'])) echo $list[$i]['icon_secret'];

            ?>
        </td>
        <td class="td_name"><?=$list[$i]['name']?></td>
        <td class="td_date"><?=$list[$i]['datetime2']?></td>
        <td class="td_num"><?=$list[$i]['wr_hit']?></td>
        <? if ($is_good) { ?><td class="td_num"><?=$list[$i]['wr_good']?></td><? } ?>
        <? if ($is_nogood) { ?><td class="td_num"><?=$list[$i]['wr_nogood']?></td><? } ?>
    </tr>
    <?}?>
    <? if (count($list) == 0) { echo '<tr><td colspan="'.$colspan.'" class="empty_table">게시물이 없습니다.</td></tr>'; } ?>
    </tbody>
    </table>

    <? if ($list_href || $is_checkbox || $write_href) {?>
    <div class="bo_fx">
        <ul class="btn_bo_adm">
            <? if ($list_href) { ?>
            <li><a href="<?=$list_href?>" class="btn_b01"> 목록</a></li>
            <? } ?>
            <? if ($is_checkbox) { ?>
            <li><input type="submit" name="btn_submit" onclick="document.pressed=this.value" value="선택삭제"></li>
            <li><input type="submit" name="btn_submit" onclick="document.pressed=this.value" value="선택복사"></li>
            <li><input type="submit" name="btn_submit" onclick="document.pressed=this.value" value="선택이동"></li>
            <? } ?>
        </ul>

        <ul class="btn_bo_user">
            <li><? if ($write_href) { ?><a href="<?=$write_href?>" class="btn_b02">글쓰기</a><? } ?></li>
        </ul>
    </div>
    <? } ?>
    </form>
</div>

<? if($is_checkbox) { ?>
<noscript>
<p>자바스크립트를 사용하지 않는 경우<br>별도의 확인 절차 없이 바로 선택삭제 처리하므로 주의하시기 바랍니다.</p>
</noscript>
<? } ?>

<!-- 페이지 -->
<? echo $write_pages; ?>

<fieldset id="bo_sch">
    <legend>게시물 검색</legend>

    <form name="fsearch" method="get">
    <input type="hidden" name="bo_table" value="<?=$bo_table?>">
    <input type="hidden" name="sca" value="<?=$sca?>">
    <input type="hidden" name="sop" value="and">
    <select name="sfl" title="검색대상">
        <option value="wr_subject"<?=get_selected($sfl, 'wr_subject', true);?>>제목</option>
        <option value="wr_content"<?=get_selected($sfl, 'wr_content');?>>내용</option>
        <option value="wr_subject||wr_content"<?=get_selected($sfl, 'wr_subject||wr_content');?>>제목+내용</option>
        <option value="mb_id,1"<?=get_selected($sfl, 'mb_id,1');?>>회원아이디</option>
        <option value="mb_id,0"<?=get_selected($sfl, 'mb_id,0');?>>회원아이디(코)</option>
        <option value="wr_name,1"<?=get_selected($sfl, 'wr_name,1');?>>글쓴이</option>
        <option value="wr_name,0"<?=get_selected($sfl, 'wr_name,0');?>>글쓴이(코)</option>
    </select>
    <input name="stx" class="fs_input required" maxlength="15" size="15" required value="<?=stripslashes($stx)?>" title="검색어(필수)">
    <input type="submit" class="fs_submit" value="검색">
    </form>
</fieldset>

<? if ($is_checkbox) { ?>
<script>
function all_checked(sw) {
    var f = document.fboardlist;

    for (var i=0; i<f.length; i++) {
        if (f.elements[i].name == "chk_wr_id[]")
            f.elements[i].checked = sw;
    }
}

function fboardlist_submit(f) {
    var chk_count = 0;

    for (var i=0; i<f.length; i++) {
        if (f.elements[i].name == "chk_wr_id[]" && f.elements[i].checked)
            chk_count++;
    }

    if (!chk_count) {
        alert(document.pressed + "할 게시물을 하나 이상 선택하세요.");
        return false;
    }

    if(document.pressed == "선택복사") {
        select_copy("copy");
        return;
    }

    if(document.pressed == "선택이동") {
        select_copy("move");
        return;
    }

    if(document.pressed == "선택삭제") {
        if (!confirm("선택한 게시물을 정말 삭제하시겠습니까?\n\n한번 삭제한 자료는 복구할 수 없습니다"))
            return false;
    }

    return true;
}

// 선택한 게시물 복사 및 이동
function select_copy(sw) {
    var f = document.fboardlist;

    if (sw == 'copy')
        str = "복사";
    else
        str = "이동";

    var sub_win = window.open("", "move", "left=50, top=50, width=500, height=550, scrollbars=1");

    f.sw.value = sw;
    f.target = "move";
    f.action = "./move.php";
    f.submit();
}
</script>
<? } ?>
<!-- 게시판 목록 끝 -->
