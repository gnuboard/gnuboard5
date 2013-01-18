<?
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
?>

<script>
// 글자수 제한
var char_min = parseInt(<?=$comment_min?>); // 최소
var char_max = parseInt(<?=$comment_max?>); // 최대
</script>

<!-- 댓글 리스트 -->
<section id="bo_vc">
    <h2>댓글</h2>
    <?
    for ($i=0; $i<count($list); $i++) {
        $comment_id = $list[$i]['wr_id'];
        $cmt_depth = ""; // 댓글단계
        $cmt_depth = strlen($list[$i]['wr_comment_reply']) * 20;
        if (strstr($list[$i]['wr_option'], "secret")) echo '비밀글';
            $str = $list[$i]['content'];
            if (strstr($list[$i]['wr_option'], "secret"))
                $str = $str;
            $str = preg_replace("/\[\<a\s.*href\=\"(http|https|ftp|mms)\:\/\/([^[:space:]]+)\.(mp3|wma|wmv|asf|asx|mpg|mpeg)\".*\<\/a\>\]/i", "<script>doc_write(obj_movie('$1://$2.$3'));</script>", $str);
            // FLASH XSS 공격에 의해 주석 처리 - 110406
            //$str = preg_replace("/\[\<a\s.*href\=\"(http|https|ftp)\:\/\/([^[:space:]]+)\.(swf)\".*\<\/a\>\]/i", "<script>doc_write(flash_movie('$1://$2.$3'));</script>", $str);
            $str = preg_replace("/\[\<a\s*href\=\"(http|https|ftp)\:\/\/([^[:space:]]+)\.(gif|png|jpg|jpeg|bmp)\"\s*[^\>]*\>[^\s]*\<\/a\>\]/i", "<img src='$1://$2.$3' id='target_resize_image[]' onclick='image_window(this);'>", $str);
    ?>
    <article id="c_<?=$comment_id?>" <?if ($cmt_depth) {?>style="margin-left:<?=$cmt_depth?>px"<?}?>>
        <header>
            <h1><?=$list[$i]['name']?><span class="sound_only">님의 댓글</span></h1>
            <? if ($cmt_depth) {?><img src="<?=$board_skin_path?>/img/icon_reply.gif" class="icon_reply" alt="댓글의 댓글"><? } ?>
            <dl class="bo_vc_info">
                <? if ($is_ip_view) { ?>
                <dt>아이피</dt>
                <dd><?=$list[$i]['ip'];?></dd>
                <? } ?>
                <dt>작성일</dt>
                <dd><time datetime="<?=date('Y-m-d\TH:i:s+09:00', strtotime($list[$i]['datetime']))?>"><?=$list[$i]['datetime']?></time></dd>
            </dl>
        </header>

        <!-- 댓글 출력 -->
        <p><?=$str?></p>

        <span id="edit_<?=$comment_id?>"></span><!-- 수정 -->
        <span id="reply_<?=$comment_id?>"></span><!-- 답변 -->

        <input type="hidden" id="secret_comment_<?=$comment_id?>" value="<?=strstr($list[$i]['wr_option'],"secret")?>">
        <textarea id="save_comment_<?=$comment_id?>" style="display:none"><?=get_text($list[$i]['content1'], 0)?></textarea>

        <? if($list[$i]['is_reply'] || $list[$i]['is_edit'] || $list[$i]['is_del']) {
            $query_string = str_replace("&", "&amp;", $_SERVER['QUERY_STRING']);

            if($w == 'cu') {
                $sql = " select wr_id, wr_content from $write_table where wr_id = '$c_id' and wr_is_comment = '1' ";
                $cmt = sql_fetch($sql);
                $c_wr_content = $cmt['wr_content'];
            }

            $c_reply_href = './board.php?'.$query_string.'&amp;c_id='.$comment_id.'&amp;w=c#bo_vc_w';
            $c_edit_href = './board.php?'.$query_string.'&amp;c_id='.$comment_id.'&amp;w=cu#bo_vc_w';
        ?>
        <footer>
            <ul class="bo_vc_act">
                <? if ($list[$i]['is_reply']) { ?><li><a href="<? echo $c_reply_href; ?>" onclick="comment_box('<?=$comment_id?>', 'c'); return false;">답변</a></li><? } ?>
                <? if ($list[$i]['is_edit']) { ?><li><a href="<? echo $c_edit_href; ?>" onclick="comment_box('<?=$comment_id?>', 'cu'); return false;">수정</a></li><? } ?>
                <? if ($list[$i]['is_del'])  { ?><li><a href="<? echo $list[$i]['del_link']; ?>" onclick="comment_delete('<?=$list[$i]['del_link']?>'); return false;">삭제</a></li><? } ?>
            </ul>
        </footer>
        <? } ?>
    </article>
    <?}?>

    <? if ($is_comment_write) { ?>
    <aside id="bo_vc_w">
        <h2>댓글쓰기</h2>
        <form name="fviewcomment" method="post" action="./write_comment_update.php" onsubmit="return fviewcomment_submit(this);" autocomplete="off">
        <input type="hidden" id="w" name="w" value="<?=$w?>">
        <input type="hidden" name="bo_table" value="<?=$bo_table?>">
        <input type="hidden" name="wr_id" value="<?=$wr_id?>">
        <input type="hidden" id="comment_id" name="comment_id" value="<?=$c_id?>">
        <input type="hidden" name="sca" value="<?=$sca?>">
        <input type="hidden" name="sfl" value="<?=$sfl?>">
        <input type="hidden" name="stx" value="<?=$stx?>">
        <input type="hidden" name="spt" value="<?=$spt?>">
        <input type="hidden" name="page" value="<?=$page?>">
        <input type="hidden" name="is_good" value="">

        <fieldset id="bo_vc_winfo">
            <legend class="sound_only">작성자</legend>
            <? if ($is_guest) { ?>
            <label for="wr_name">이름<strong class="sound_only">필수</strong></label> <input type="text" id="wr_name" name="wr_name" class="fieldset_input required" maxLength="20" size="5" required>
            <label for="wr_password">패스워드<strong class="sound_only">필수</strong></label> <input type="password" id="wr_password" name="wr_password" class="fieldset_input required" maxLength="20" size="10" required>
            <? } ?>
            <input type="checkbox" id="wr_secret" name="wr_secret" value="secret">
            <label for="wr_secret">비밀글</label>
       </fieldset>
       <? echo $captcha_html; ?>
        <? if ($comment_min || $comment_max) { ?><strong id="char_cnt"><span id="char_count"></span>글자</strong><?}?>
        <div id="bo_vc_warea">
            <textarea id="wr_content" name="wr_content" required
            <? if ($comment_min || $comment_max) { ?>onkeyup="check_byte('wr_content', 'char_count');"<?}?> title="댓글내용입력(필수)"><? echo $c_wr_content; ?></textarea>
            <? if ($comment_min || $comment_max) { ?><script> check_byte('wr_content', 'char_count'); </script><?}?>
            <input type="submit" class="bo_vc_submit" value="댓글입력">
        </div>
        </form>
    </aside>

    <script>
    var save_before = '';
    var save_html = document.getElementById('bo_vc_w').innerHTML;

    function good_and_write()
    {
        var f = document.fviewcomment;
        if (fviewcomment_submit(f)) {
            f.is_good.value = 1;
            f.submit();
        } else {
            f.is_good.value = 0;
        }
    }

    function fviewcomment_submit(f)
    {
        var pattern = /(^\s*)|(\s*$)/g; // \s 공백 문자

        f.is_good.value = 0;

        /*
        var s;
        if (s = word_filter_check(document.getElementById('wr_content').value))
        {
            alert("내용에 금지단어('"+s+"')가 포함되어있습니다");
            document.getElementById('wr_content').focus();
            return false;
        }
        */

        var subject = "";
        var content = "";
        $.ajax({
            url: g4_bbs_path+"/filter.ajax.php",
            type: "POST",
            data: {
                "subject": "",
                "content": f.wr_content.value
            },
            dataType: "json",
            async: false,
            cache: false,
            success: function(data, textStatus) {
                subject = data.subject;
                content = data.content;
            }
        });

        if (content) {
            alert("내용에 금지단어('"+content+"')가 포함되어있습니다");
            f.wr_content.focus();
            return false;
        }

        // 양쪽 공백 없애기
        var pattern = /(^\s*)|(\s*$)/g; // \s 공백 문자
        document.getElementById('wr_content').value = document.getElementById('wr_content').value.replace(pattern, "");
        if (char_min > 0 || char_max > 0)
        {
            check_byte('wr_content', 'char_count');
            var cnt = parseInt(document.getElementById('char_count').innerHTML);
            if (char_min > 0 && char_min > cnt)
            {
                alert("댓글는 "+char_min+"글자 이상 쓰셔야 합니다.");
                return false;
            } else if (char_max > 0 && char_max < cnt)
            {
                alert("댓글는 "+char_max+"글자 이하로 쓰셔야 합니다.");
                return false;
            }
        }
        else if (!document.getElementById('wr_content').value)
        {
            alert("댓글를 입력하여 주십시오.");
            return false;
        }

        if (typeof(f.wr_name) != 'undefined')
        {
            f.wr_name.value = f.wr_name.value.replace(pattern, "");
            if (f.wr_name.value == '')
            {
                alert('이름이 입력되지 않았습니다.');
                f.wr_name.focus();
                return false;
            }
        }

        if (typeof(f.wr_password) != 'undefined')
        {
            f.wr_password.value = f.wr_password.value.replace(pattern, "");
            if (f.wr_password.value == '')
            {
                alert('패스워드가 입력되지 않았습니다.');
                f.wr_password.focus();
                return false;
            }
        }

        <? echo chk_captcha_js(); ?>

        return true;
    }

    function comment_box(comment_id, work)
    {
        var el_id;
        // 댓글 아이디가 넘어오면 답변, 수정
        if (comment_id)
        {
            if (work == 'c')
                el_id = 'reply_' + comment_id;
            else
                el_id = 'edit_' + comment_id;
        }
        else
            el_id = 'bo_vc_w';

        if (save_before != el_id)
        {
            if (save_before)
            {
                document.getElementById(save_before).style.display = 'none';
                document.getElementById(save_before).innerHTML = '';
            }

            document.getElementById(el_id).style.display = '';
            document.getElementById(el_id).innerHTML = save_html;
            // 댓글 수정
            if (work == 'cu')
            {
                document.getElementById('wr_content').value = document.getElementById('save_comment_' + comment_id).value;
                if (typeof char_count != 'undefined')
                    check_byte('wr_content', 'char_count');
                if (document.getElementById('secret_comment_'+comment_id).value)
                    document.getElementById('wr_secret').checked = true;
                else
                    document.getElementById('wr_secret').checked = false;
            }

            document.getElementById('comment_id').value = comment_id;
            document.getElementById('w').value = work;

            save_before = el_id;
        }

        if (typeof(wrestInitialized) != 'undefined')
            wrestInitialized();
    }

    function comment_delete(url)
    {
        if (confirm("이 댓글를 삭제하시겠습니까?")) location.href = url;
    }

    comment_box('', 'c'); // 댓글 입력폼이 보이도록 처리하기위해서 추가 (root님)
    </script>
    <? } ?>
</section>
