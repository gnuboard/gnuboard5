<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$member_skin_url.'/style.css">', 0);
?>

<!-- 자기소개 시작 { -->
<div id="profile" class="new_win">
    <h1 id="win_title"><?php echo $mb_nick ?>님의 프로필</h1>
    <div class="profile_name">
        <span class="my_profile_img">
            <?php echo get_member_profile_img($mb['mb_id']); ?>
        </span>
        <?php echo $mb_nick ?>
    </div>
    <div class="tbl_head02 tbl_wrap new_win_con">
        <table>
        <tbody>
        <tr>
            <th scope="row"><i class="fa fa-star-o" aria-hidden="true"></i>  회원권한</th>
            <td><?php echo $mb['mb_level'] ?></td>
            <th scope="row"><i class="fa fa-database" aria-hidden="true"></i> 포인트</th>
            <td><?php echo number_format($mb['mb_point']) ?></td>
        </tr>
        <tr>
            <th scope="row"><i class="fa fa-clock-o" aria-hidden="true"></i> 회원가입일</th>
            <td><?php echo ($member['mb_level'] >= $mb['mb_level']) ?  substr($mb['mb_datetime'],0,10) ." (".number_format($mb_reg_after)." 일)" : "알 수 없음";  ?></td>
            <th scope="row"><i class="fa fa-clock-o" aria-hidden="true"></i> 최종접속일</th>
            <td><?php echo ($member['mb_level'] >= $mb['mb_level']) ? $mb['mb_today_login'] : "알 수 없음"; ?></td>
        </tr>
        <?php if ($mb_homepage) {  ?>
        <tr>
            <th scope="row"><i class="fa fa-home" aria-hidden="true"></i> 홈페이지</th>
            <td colspan="3"><a href="<?php echo $mb_homepage ?>" target="_blank"><?php echo $mb_homepage ?></a></td>
        </tr>
        <?php }  ?>

        </tbody>
        </table>
    

        <section>
            <h2>인사말</h2>
            <p><?php echo $mb_profile ?></p>
        </section>
    </div>
    <div class="win_btn">
        <button type="button" onclick="window.close();" class="btn_close">창닫기</button>
    </div>
</div>
<!-- } 자기소개 끝 -->