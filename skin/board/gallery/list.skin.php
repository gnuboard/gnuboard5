<?
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
include_once(G4_LIB_PATH.'/thumbnail.lib.php');
?>

<link rel="stylesheet" href="<?=$board_skin_url?>/style.css">

<? if (!$wr_id) {?><h1 id="bo_list_title"><?=$board['bo_subject']?></h1><?}?>

<!-- 게시판 목록 시작 -->
<div id="bo_img" style="width:<?=$width;?>">

    <? if ($is_category) { ?>
    <form name="fcategory" id="fcategory" method="get">
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

    <form name="fboardlist"  id="fboardlist" action="./board_list_update.php" onsubmit="return fboardlist_submit(this);" method="post">
    <input type="hidden" name="bo_table" value="<?=$bo_table?>">
    <input type="hidden" name="sfl" value="<?=$sfl?>">
    <input type="hidden" name="stx" value="<?=$stx?>">
    <input type="hidden" name="spt" value="<?=$spt?>">
    <input type="hidden" name="page" value="<?=$page?>">
    <input type="hidden" name="sw" value="">

    <h2>이미지 목록</h2>

    <ul id="bo_img_list">
        <? for ($i=0; $i<count($list); $i++) {
            if($i>0 && ($i % $bo_gallery_cols == 0))
                $style = 'clear:both;';
            else
                $style = '';
            if ($i == 0) $k = 0;
            $k += 1;
            if ($k % $bo_gallery_cols == 0) $style .= "margin:0 !important;";
        ?>
        <li class="bo_img_list_li <? if ($wr_id == $list[$i]['wr_id']) { ?>bo_img_now<? } ?>" style="<?=$style?>width:<?=$board['bo_gallery_width']?>px">
            <? if ($is_checkbox) { ?>
            <label for="chk_wr_id_<?=$i?>" class="sound_only"><?=$list[$i]['wr_subject']?></label>
            <input type="checkbox" name="chk_wr_id[]" value="<?=$list[$i]['wr_id']?>" id="chk_wr_id_<?=$i?>">
            <? } ?>
            <span class="sound_only">
                <?
                if ($wr_id == $list[$i]['wr_id'])
                    echo "<span class=\"bo_current\">열람중</span>";
                else
                    echo $list[$i]['num'];
                ?>
            </span>
            <ul class="bo_img_con">
                <li class="bo_img_href">
                    <a href="<?=$list[$i]['href']?>">
                    <?
                    if ($list[$i]['is_notice']) { // 공지사항 ?>
                        <strong style="width:<?=$board['bo_gallery_width']?>px;height:<?=$board['bo_gallery_height']?>px">공지</strong>
                    <? } else {
                        $thumb = get_list_thumbnail($board['bo_table'], $list[$i]['wr_id'], $board['bo_gallery_width'], $board['bo_gallery_height']);

                        if($thumb['src']) {
                            $img_content = '<img src="'.$thumb['src'].'" alt="'.$thumb['alt'].'" width="'.$board['bo_gallery_width'].'" height="'.$board['bo_gallery_height'].'">';
                        } else {
                            $img_content = '<span style="width:'.$board['bo_gallery_width'].'px;height:'.$board['bo_gallery_height'].'px">no image</span>';
                        }

                        echo $img_content;
                    }
                    ?>
                    </a>
                </li>
                <li class="bo_img_text_href" style="width:<?=$board['bo_gallery_width']?>px">
                    <?
                    // echo $list[$i]['icon_reply']; 갤러리는 reply 를 사용 안 할 것 같습니다. - 지운아빠 2013-03-04
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
                    //if (isset($list[$i]['icon_file'])) echo $list[$i]['icon_file'];
                    //if (isset($list[$i]['icon_link'])) echo $list[$i]['icon_link'];
                    //if (isset($list[$i]['icon_secret'])) echo $list[$i]['icon_secret'];
                    ?>
                </li>
                <li><span class="bo_img_subject">작성자 </span><?=$list[$i]['name']?></li>
                <li><span class="bo_img_subject">작성일 </span><?=$list[$i]['datetime2']?></li>
                <li><span class="bo_img_subject">조회 </span><?=$list[$i]['wr_hit']?></li>
                <? if ($is_good) {?><li><span class="bo_img_subject">추천</span><strong><?=$list[$i]['wr_good']?></strong></li><? } ?>
                <? if ($is_nogood) {?><li><span class="bo_img_subject">비추천</span><strong><?=$list[$i]['wr_nogood']?></strong></li><? } ?>
            </ul>
        </li>
        <? } ?>
        <? if (count($list) == 0) { echo "<li class=\"empty_list\">게시물이 없습니다.</li>"; } ?>
    </ul>

    <? if ($list_href || $is_checkbox || $write_href) {?>
    <div class="bo_fx">
        <ul class="btn_bo_adm">
            <? if ($list_href) { ?>
            <li><a href="<?=$list_href?>" class="btn_b01"> 목록</a></li>
            <? } ?>
            <? if ($is_checkbox) { ?>
            <li><input type="submit" name="btn_submit" value="선택삭제" onclick="document.pressed=this.value"></li>
            <li><input type="submit" name="btn_submit" value="선택복사" onclick="document.pressed=this.value"></li>
            <li><input type="submit" name="btn_submit" value="선택이동" onclick="document.pressed=this.value"></li>
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
    <label for="sfl" class="sound_only">검색대상</label>
    <select name="sfl" id="sfl">
        <option value="wr_subject"<?=get_selected($sfl, "wr_subject", true);?>>제목</option>
        <option value="wr_content"<?=get_selected($sfl, "wr_content");?>>내용</option>
        <option value="wr_subject||wr_content"<?=get_selected($sfl, "wr_subject||wr_content");?>>제목+내용</option>
        <option value="mb_id,1"<?=get_selected($sfl, "mb_id,1");?>>회원아이디</option>
        <option value="mb_id,0"<?=get_selected($sfl, "mb_id,0");?>>회원아이디(코)</option>
        <option value="wr_name,1"<?=get_selected($sfl, "wr_name,1");?>>글쓴이</option>
        <option value="wr_name,0"<?=get_selected($sfl, "wr_name,0");?>>글쓴이(코)</option>
    </select>
    <label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
    <input type="text" name="stx" value="<?=stripslashes($stx)?>" required class="frm_input required" size="15" maxlength="15">
    <input type="submit" value="검색" class="btn_submit">
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
