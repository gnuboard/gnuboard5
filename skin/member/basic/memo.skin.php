<?
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
?>

<div id="memo_list" class="new_win">
    <h1><?=$g4['title']?></h1>

    <ul class="new_win_ul">
        <li><a href="./memo.php?kind=recv">받은쪽지</a></li>
        <li><a href="./memo.php?kind=send">보낸쪽지</a></li>
        <li><a href="./memo_form.php">쪽지쓰기</a></li>
    </ul>

    <table class="basic_tbl">
    <caption>
        전체 <?=$kind_title?>쪽지 <?=$total_count?>통<br>
    </caption>
    <thead>
    <tr>
        <th scope="col"><?= ($kind == "recv") ? "보낸사람" : "받는사람"; ?></th>
        <th scope="col">보낸시간</th>
        <th scope="col">읽은시간</th>
        <th scope="col">관리</th>
    </tr>
    </thead>
    <tbody>
    <? for ($i=0; $i<count($list); $i++) { ?>
    <tr>
        <td><div><?=$list[$i]['name']?></div></td>
        <td class="td_datetime"><a href="<?=$list[$i]['view_href']?>"><?=$list[$i]['send_datetime']?></font></td>
        <td class="td_datetime"><a href="<?=$list[$i]['view_href']?>"><?=$list[$i]['read_datetime']?></font></td>
        <td class="td_mng"><a href="<?=$list[$i]['del_href']?>" onclick="del(this.href); return false;">삭제</a></td>
    </tr>
    <? } ?>
    <? if ($i==0) { echo "<tr><td colspan=\"4\" class=\"empty_table\">자료가 없습니다.</td></tr>"; } ?>
    </tbody>
    </table>

    <p class="new_win_desc">
        쪽지 보관일수는 최장 <strong><?=$config['cf_memo_del']?></strong>일 입니다.
    </p>

    <div class="btn_win"><a href="javascript:;" onclick="window.close();">창닫기</a></div>
</div>