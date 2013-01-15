<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 

global $is_admin;

// 투표번호가 넘어오지 않았다면 가장 큰(최근에 등록한) 투표번호를 얻는다
if (!$po_id) {
    $po_id = $config['cf_max_po_id'];

    if (!$po_id) return;
}

$po = sql_fetch(" select * from {$g4['poll_table']} where po_id = '$po_id' ");
?>

<form name="fpoll" method="post" action="<?=$g4['bbs_url']?>/poll_update.php" onsubmit="return fpoll_submit(this);" target="win_poll">
<input type="hidden" name="po_id" value="<?=$po_id?>">
<input type="hidden" name="skin_dir" value="<?=$skin_dir?>">
<section id="poll">
    <header>
        <h2>설문조사</h2>
<<<<<<< HEAD
        <? if ($is_admin == "super") { ?><a href="<?=$g4['admin_url']?>/poll_form.php?w=u&amp;po_id=<?=$po_id?>">설문조사 관리</a><? } ?>
=======
        <? if ($is_admin == "super") { ?><a href="<?=$g4[admin_path]?>/poll_form.php?w=u&amp;po_id=<?=$po_id?>">설문관리</a><? } ?>
>>>>>>> 0ab17c519cdc47fc3b68ff37272f5d3ee0aff8b1
        <p><?=$po['po_subject']?></p>
    </header>
    <ul>
        <? for ($i=1; $i<=9 && $po["po_poll{$i}"]; $i++) { ?>
        <li><input type="radio" name="gb_poll" value="<?=$i?>" id='gb_poll_<?=$i?>'> <label for='gb_poll_<?=$i?>'><?=$po['po_poll'.$i]?></label></li>
        <? } ?>
    </ul>
    <footer>
        <input type="submit" value="투표하기">
        <a href="<?="{$g4['bbs_url']}/poll_result.php?po_id={$po_id}&amp;skin_dir={$skin_dir}";?>" target="win_poll">결과보기</a>
    </footer>
</section>
</form>

<script>
function fpoll_submit(f)
{
    var chk = false;
    for (i=0; i<f.gb_poll.length;i ++) {
        if (f.gb_poll[i].checked == true) {
            chk = f.gb_poll[i].value;
            break;
        }
    }

    <?
    if ($member['mb_level'] < $po['po_level'])
        echo " alert('권한 {$po['po_level']} 이상의 회원만 투표에 참여하실 수 있습니다.'); return false; ";
    ?>

    if (!chk) {
        alert("항목을 선택하세요");
        return false;
    }

    win_poll();
    return true;
}
</script>