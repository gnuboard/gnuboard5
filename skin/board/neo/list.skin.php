<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// 선택옵션으로 인해 셀합치기가 가변적으로 변함
$colspan = 5;

if ($is_checkbox) $colspan++;
if ($is_good) $colspan++;
if ($is_nogood) $colspan++;
?>

<? if ($admin_href) { ?><div id="bo_admin_btn"><a href="<?=$admin_href?>">관리자 바로가기</a></div><?}?>

<div>
    <? if ($is_category) { ?>
    <form name="fcategory" method="get" style="margin:0px;">
        <select name="sca" onchange="location='<?=$category_location?>'+<?=strtolower($g4['charset'])=='utf-8' ? "encodeURIComponent(this.value)" : "this.value"?>;">
            <option value=''>전체</option>
            <?=$category_option?>
        </select>
    </form>
    <? } ?>

    <span>Total <?=number_format($total_count)?></span>

    <form name="fsearch" method="get">
    <input type="hidden" name="bo_table" value="<?=$bo_table?>">
    <input type="hidden" name="sca" value="<?=$sca?>">
    <fieldset>
        <legend>게시물 검색</legend>
        <label for="sfl">검색대상</label>
        <select id="sfl" name="sfl">
            <option value="wr_subject">제목</option>
            <option value="wr_content">내용</option>
            <option value="wr_subject||wr_content">제목+내용</option>
            <option value="mb_id,1">회원아이디</option>
            <option value="mb_id,0">회원아이디(코)</option>
            <option value="wr_name,1">글쓴이</option>
            <option value="wr_name,0">글쓴이(코)</option>
        </select>
        <label for="stx">검색어</label>
        <input id="stx" id="stx" name="stx" maxlength="15" required value="<?=stripslashes($stx)?>">
        <input type="radio" id="sop_and" name="sop" value="and">
        <label for="sop_and">and</label>
        <input type="radio" id="sop_or" name="sop" value="or">
        <label for="sop_or">or</label>
        <input type="submit" value="검색">
    </fieldset>
    </form>
</div>

<? if ($rss_href) { ?><a href='<?=$rss_href?>'>RSS</a><?}?>

<!-- 게시판 목록 시작 -->
<form name="fboardlist" method="post">
<input type="hidden" name="bo_table" value="<?=$bo_table?>">
<input type="hidden" name="sfl" value="<?=$sfl?>">
<input type="hidden" name="stx" value="<?=$stx?>">
<input type="hidden" name="spt" value="<?=$spt?>">
<input type="hidden" name="page" value="<?=$page?>">
<input type="hidden" name="sw" value="">
<table>
<caption></caption>
<thead>
<tr>
    <th scope="col">번호</th>
    <? if ($is_checkbox) { ?><th scope="col"><input onclick="if (this.checked) all_checked(true); else all_checked(false);" type="checkbox"></th><?}?>
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
<tr>
    <td>
    <?
    if ($list[$i]['is_notice']) // 공지사항
        echo '공지';
    else if ($wr_id == $list[$i]['wr_id']) // 현재위치
        echo $list[$i]['num'];
    else
        echo $list[$i]['num'];
    ?>
    </td>
    <? if ($is_checkbox) { ?><td><input type="checkbox" name="chk_wr_id[]" value="<?=$list[$i]['wr_id']?>" title="이 게시물 선택"></td><? } ?>
    <td>
        <?
        echo $list[$i]['reply'];
        echo $list[$i]['icon_reply'];
        if ($is_category && $list[$i]['ca_name']) {
            echo '<a href="'.$list[$i]['ca_name_href'].'">'.$list[$i]['ca_name'].'</a>';
        }

        if ($list[$i]['is_notice'])
            echo '<a href="'.$list[$i]['href'].'">'.$list[$i]['subject'].'</a>';
        else
            echo '<a href="'.$list[$i]['href'].'">'.$list[$i]['subject'].'</a>';

        if ($list[$i]['comment_cnt'])
            echo '<a href="'.$list[$i]['comment_href'].'">'.$list[$i]['comment_cnt'].'</a>';

        // if ($list[$i]['link']['count']) { echo '['.$list[$i]['link']['count']}.']'; }
        // if ($list[$i]['file']['count']) { echo '<'.$list[$i]['file']['count'].'>'; }

        echo $list[$i]['icon_new'];
        echo $list[$i]['icon_file'];
        echo $list[$i]['icon_link'];
        echo $list[$i]['icon_hot'];
        echo $list[$i]['icon_secret'];
        ?>
    </td>
    <td><?=$list[$i]['name']?></td>
    <td><?=$list[$i]['datetime2']?></td>
    <td><?=$list[$i]['wr_hit']?></td>
    <? if ($is_good) { ?><td><?=$list[$i]['wr_good']?></td><? } ?>
    <? if ($is_nogood) { ?><td><?=$list[$i]['wr_nogood']?></td><? } ?>
</tr>
<?}?>
<? if (count($list) == 0) { echo '<tr><td colspan="'.$colspan.'" class="empty_table">게시물이 없습니다.</td></tr>'; } ?>
</tbody>
</table>
</form>

<div>
    <div>
    <? if ($list_href) { ?>
    <a href="<?=$list_href?>">목록</a>
    <? } ?>
    <? if ($is_checkbox) { ?>
    <a href="javascript:select_delete();">선택삭제</a>
    <a href="javascript:select_copy('copy');">선택복사</a>
    <a href="javascript:select_copy('move');">선택이동</a>
    <? } ?>
    </div>

    <div>
    <? if ($write_href) { ?><a href="<?=$write_href?>">글쓰기</a><? } ?>
    </div>
</div>

<!-- 페이지 -->
<div>
    <? if ($prev_part_href) { echo "<a href='$prev_part_href'><img src='$board_skin_path/img/page_search_prev.gif' border='0' align=absmiddle title='이전검색'></a>"; } ?>
    <?
    // 기본으로 넘어오는 페이지를 아래와 같이 변환하여 이미지로도 출력할 수 있습니다.
    //echo $write_pages;
    $write_pages = str_replace('처음', '<img src="'.$board_skin_path.'/img/page_begin.gif" alt="처음">', $write_pages);
    $write_pages = str_replace('이전', '<img src="'.$board_skin_path.'/img/page_prev.gif" alt="이전">', $write_pages);
    $write_pages = str_replace('다음', '<img src="'.$board_skin_path.'/img/page_next.gif" alt="다음">', $write_pages);
    $write_pages = str_replace('맨끝', '<img src="'.$board_skin_path.'/img/page_end.gif" alt="맨끝">', $write_pages);
    //$write_pages = preg_replace("/<span>([0-9]*)<\/span>/", "$1", $write_pages);
    $write_pages = preg_replace('/<b>([0-9]*)<\/b>/', '<b><span>$1</span></b>', $write_pages);
    ?>
    <?=$write_pages?>
    <? if ($next_part_href) { echo '<a href="'.$next_part_href.'">다음검색</a>'; } ?>
</div>

<script>
if ('<?=$sca?>') document.fcategory.sca.value = '<?=$sca?>';
if ('<?=$stx?>') {
    document.fsearch.sfl.value = '<?=$sfl?>';

    if ('<?=$sop?>' == 'and')
        document.fsearch.sop[0].checked = true;

    if ('<?=$sop?>' == 'or')
        document.fsearch.sop[1].checked = true;
} else {
    document.fsearch.sop[0].checked = true;
}
</script>

<? if ($is_checkbox) { ?>
<script>
function all_checked(sw) {
    var f = document.fboardlist;

    for (var i=0; i<f.length; i++) {
        if (f.elements[i].name == "chk_wr_id[]")
            f.elements[i].checked = sw;
    }
}

function check_confirm(str) {
    var f = document.fboardlist;
    var chk_count = 0;

    for (var i=0; i<f.length; i++) {
        if (f.elements[i].name == "chk_wr_id[]" && f.elements[i].checked)
            chk_count++;
    }

    if (!chk_count) {
        alert(str + "할 게시물을 하나 이상 선택하세요.");
        return false;
    }
    return true;
}

// 선택한 게시물 삭제
function select_delete() {
    var f = document.fboardlist;

    str = "삭제";
    if (!check_confirm(str))
        return;

    if (!confirm("선택한 게시물을 정말 "+str+" 하시겠습니까?\n\n한번 "+str+"한 자료는 복구할 수 없습니다"))
        return;

    f.action = "./delete_all.php";
    f.submit();
}

// 선택한 게시물 복사 및 이동
function select_copy(sw) {
    var f = document.fboardlist;

    if (sw == "copy")
        str = "복사";
    else
        str = "이동";

    if (!check_confirm(str))
        return;

    var sub_win = window.open("", "move", "left=50, top=50, width=500, height=550, scrollbars=1");

    f.sw.value = sw;
    f.target = "move";
    f.action = "./move.php";
    f.submit();
}
</script>
<? } ?>
<!-- 게시판 목록 끝 -->
