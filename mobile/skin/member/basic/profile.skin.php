<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$member_skin_url.'/style.css">', 0);
?>

<div id="profile" class="new_win mbskin">
    <h1 id="win_title"><?php echo $mb_nick ?>님의 프로필</h1>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <tbody>
        <tr>
            <th scope="row">회원권한</th>
            <td><?php echo $mb['mb_level'] ?></td>
        </tr>
        <tr>
            <th scope="row">포인트</th>
            <td><?php echo number_format($mb['mb_point']) ?></td>
        </tr>
        <?php if ($mb_homepage) { ?>
        <tr>
            <th scope="row">홈페이지</th>
            <td><a href="<?php echo $mb_homepage ?>" target="_blank"><?php echo $mb_homepage ?></a></td>
        </tr>
        <?php } ?>
        <tr>
            <th scope="row">회원가입일</th>
            <td><?php echo ($member['mb_level'] >= $mb['mb_level']) ?  substr($mb['mb_datetime'],0,10) ." (".number_format($mb_reg_after)." 일)" : "알 수 없음"; ?></td>
        </tr>
        <tr>
            <th scope="row">최종접속일</th>
            <td><?php echo ($member['mb_level'] >= $mb['mb_level']) ? $mb['mb_today_login'] : "알 수 없음"; ?></td>
        </tr>
        </tbody>
        </table>
    </div>

    <section>
        <h2>인사말</h2>
        <p><?php echo $mb_profile ?></p>
    </section>

    <div class="win_btn">
        <button type="button" onclick="window.close();">창닫기</button>
    </div>
</div>
