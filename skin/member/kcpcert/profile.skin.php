<?
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
?>

<div id="profile" class="new_win">
    <h1><?=$mb_nick?>님의 프로필</h1>

    <table class="frm_tbl">
    <tbody>
    <tr>
        <th scope="row">회원권한</th>
        <td><?=$mb['mb_level']?></td>
    </tr>
    <tr>
        <th scope="row">포인트</th>
        <td><?=number_format($mb['mb_point'])?></td>
    </tr>
    <? if ($mb_homepage) { ?>
    <tr>
        <th scope="row">홈페이지</th>
        <td><a href="<?=$mb_homepage?>" target="_blank"><?=$mb_homepage?></a></td>
    </tr>
    <? } ?>
    <tr>
        <th scope="row">회원가입일</th>
        <td><?=($member['mb_level'] >= $mb['mb_level']) ?  substr($mb['mb_datetime'],0,10) ." (".number_format($mb_reg_after)." 일)" : "알 수 없음"; ?></td>
    </tr>
    <tr>
        <th scope="row">최종접속일</th>
        <td><?=($member['mb_level'] >= $mb['mb_level']) ? $mb['mb_today_login'] : "알 수 없음";?></td>
    </tr>
    </tbody>
    </table>

    <section>
        <h2>인사말</h2>
        <p><?=$mb_profile?></p>
    </section>

    <div class="btn_win">
        <a href="javascript:window.close();">창닫기</a>
    </div>
</div>
