<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.G5_SHOP_SKIN_URL.'/style.css">', 0);
?>

<!-- 사용후기 쓰기 시작 { -->
<div id="sit_use_write" class="new_win">
    <h1 id="win_title">사용후기 쓰기</h1>

    <form name="fitemuse" method="post" action="./itemuseformupdate.php" onsubmit="return fitemuse_submit(this);" autocomplete="off">
    <input type="hidden" name="w" value="<?php echo $w; ?>">
    <input type="hidden" name="it_id" value="<?php echo $it_id; ?>">
    <input type="hidden" name="is_id" value="<?php echo $is_id; ?>">

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <colgroup>
            <col class="grid_2">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row"><label for="is_subject">제목<strong class="sound_only"> 필수</strong></label></th>
            <td><input type="text" name="is_subject" value="<?php echo get_text($use['is_subject']); ?>" id="is_subject" required class="required frm_input" minlength="2" maxlength="250"></td>
        </tr>
        <tr>
            <th scope="row"><label for="" style="width:200px;">내용</label></th>
            <td><?php echo $editor_html; ?></td>
        </tr>
        <tr>
            <th scope="row">평점</th>
            <td>
                <ul id="sit_use_write_star">
                    <li>
                        <input type="radio" name="is_score" value="5" id="is_score5" <?php echo ($is_score==5)?'checked="checked"':''; ?>>
                        <label for="is_score5">매우만족</label>
                        <img src="<?php echo G5_URL; ?>/shop/img/s_star5.png">
                    </li>
                    <li>
                        <input type="radio" name="is_score" value="4" id="is_score4" <?php echo ($is_score==4)?'checked="checked"':''; ?>>
                        <label for="is_score4">만족</label>
                        <img src="<?php echo G5_URL; ?>/shop/img/s_star4.png">
                    </li>
                    <li>
                        <input type="radio" name="is_score" value="3" id="is_score3" <?php echo ($is_score==3)?'checked="checked"':''; ?>>
                        <label for="is_score3">보통</label>
                        <img src="<?php echo G5_URL; ?>/shop/img/s_star3.png">
                    </li>
                    <li>
                        <input type="radio" name="is_score" value="2" id="is_score2" <?php echo ($is_score==2)?'checked="checked"':''; ?>>
                        <label for="is_score2">불만</label>
                        <img src="<?php echo G5_URL; ?>/shop/img/s_star2.png">
                    </li>
                    <li>
                        <input type="radio" name="is_score" value="1" id="is_score1" <?php echo ($is_score==1)?'checked="checked"':''; ?>>
                        <label for="is_score1">매우불만</label>
                        <img src="<?php echo G5_URL; ?>/shop/img/s_star1.png">
                    </li>
                </ul>
            </td>
        </tr>
        </tbody>
        </table>
    </div>

    <div class="win_btn">
        <input type="submit" value="작성완료" class="btn_submit">
        <button type="button" onclick="self.close();">닫기</button>
    </div>

    </form>
</div>

<script type="text/javascript">
function fitemuse_submit(f)
{
    <?php echo $editor_js; ?>

    return true;
}
</script>
<!-- } 사용후기 쓰기 끝 -->