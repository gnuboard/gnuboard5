<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

function get_datetime($datetime) 
{
    global $g4;

    $time = strtotime($datetime);
    if (date("Y-m-d", $time) == $g4[time_ymd]) {
        $date = date("H:i", $time);
    }
    else {
        $date = date("m.d", $time);
    }
    return $date;
}
?>

<div data-role="navbar">
<ul>
    <li><? 
        if ($prev_href) 
            echo "<a href='$prev_href'>";
        else {
            echo "<a href='javascript:;' onclick=\"alert('이전 글 없음');\">";
        }
        ?><&nbsp; 이전</a></li>
    <li><? 
        if ($next_href) 
            echo "<a href='$next_href'>";
        else {
            echo "<a href='javascript:;' onclick=\"alert('다음 글 없음');\">";
        }
        ?>다음 &nbsp;></a></li>
    <li><a href="<?=$list_href?>">목록보기</a></li>
    <li><a href="javascript:;" onclick="document.getElementById('comment_top').scrollIntoView();">댓글 (<?=$view[wr_comment]?>)</a></li>
</ul>
</div><!-- /navbar -->

<h3><?=cut_hangul_last(get_text($view[wr_subject]))?> </h3>
<div>
    <p style="clear:both;" class="view_name"><?=$view[wr_name]?></p>
    <p class="view_time"><?=get_datetime($view[wr_datetime])?></p>
</div>

<!-- <div class="view_cmnt"><button>댓글보기 : <?=(int)$view[wr_comment]?></button></div> -->

<div style="clear:both; line-height:150%;">


<? if ($view[wr_singo]) { ?>
    <p>신고된 게시물 입니다.</p>
<? } else { ?>
    
    <p>
    <?
    // 파일 출력
    for ($i=0; $i<=count($view[file]); $i++) {
        if ($view_file = $view[file][$i][view]) {
            $str = $view_file;
            echo preg_replace_callback("#<img[^>]+>#iS", "mobile_thumb", $str);
        }
    }
    ?>
    </p>

    <p>
    <?
    $str = $view[content];
    $str = preg_replace_callback("#(<a\s+[^>]+>\s*)?<img[^>]+>(?(1)\s*</a>)#iS", "mobile_thumb", $str);
    // 개인정보노출방지
    if (!($is_admin || ($write[mb_id] && $write[mb_id] == $member[mb_id]))) {
        if ($board[gr_id] == 'request')
            $str = get_privacy_hidden($str);
    }

    $s = $str;
    //if ($is_admin) 
    {
        //$s = $view[content];

        preg_match("/(?<=\<embed).*width=[\'\"]?(\d+)[\'\"]?/i",  $s, $match_w);
        preg_match("/(?<=\<embed).*height=[\'\"]?(\d+)[\'\"]?/i", $s, $match_h);
        
        $width  = $match_w[1];
        $height = $match_h[1];

        if ($width > 300) {
            $rate = (int)($width / 300);
            $height = (int)($height / $rate);
            $width = 300;
        }

        $s = preg_replace("/(?<=\<object)(.*width=)[\'\"]?(\d+)[\'\"]?/i",  "$1'$width'", $s);
        $s = preg_replace("/(?<=\<object)(.*height=)[\'\"]?(\d+)[\'\"]?/i", "$1'$height'", $s);
        $s = preg_replace("/(?<=\<embed)(.*width=)[\'\"]?(\d+)[\'\"]?/i",  "$1'$width'", $s);
        $s = preg_replace("/(?<=\<embed)(.*height=)[\'\"]?(\d+)[\'\"]?/i", "$1'$height'", $s);

        //echo htmlspecialchars($s);
        echo $s;
    }
    ?>
    </p>

    <? 
    // 설문
    if ($view['wr_6'] == 1) {
        $minus_wr_id = $wr_id * (-1);
        $sql = " select wr_content from `$write_table` where wr_parent = '$minus_wr_id' and wr_subject = 'pollOption' and wr_is_comment = 2 order by wr_id asc ";
        $result = sql_query($sql);
        echo "<ul>";
        for ($i=0; $row=sql_fetch_array($result); $i++) {
            $row[wr_content] = strip_tags($row[wr_content]);
            echo "<li>$row[wr_content]</li>\n";
        }
        echo "</ul>";
        echo "<div>설문결과는 PC버전에서만 보실수 있습니다.</div>";
    } 
    ?>
<? } ?>


</div>

<p id="comment_top">&nbsp;</p>

<?
// 코멘트 입출력
include_once("./view_comment.php");
?>

<? if ($delete_href) {  ?><a href="<?=$delete_href?>" data-role="button" data-icon="delete" data-inline="true">삭제</a><? } ?>

<br>
<div data-role="navbar">
<ul>
    <li><? 
        if ($prev_href) 
            echo "<a href='$prev_href'>";
        else {
            echo "<a href='javascript:;' onclick=\"alert('이전 글 없음');\">";
        }
        ?><&nbsp; 이전</a></li>
    <li><? 
        if ($next_href) 
            echo "<a href='$next_href'>";
        else {
            define("_NEXT_POPUP_", true);
            echo "<a href='#next_popup' data-rel='dialog' data-transition='pop'>";
        }
        ?>다음 &nbsp;></a></li>
    <li><a href="<?=$list_href?>">목록보기</a></li>
    <li><a href="javascript:;" onclick="window.scrollTo(0,0);">위로</a></li>
    <!-- <li><a href="<?=$list_href?>">목록</a></li> -->
</ul>
</div><!-- /navbar -->
