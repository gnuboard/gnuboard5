<?
$sub_menu = "400660";
include_once("./_common.php");

auth_check($auth[$sub_menu], "w");

$sql = " select * 
           from $g4[yc4_item_qa_table] a
           left join $g4[member_table] b on (a.mb_id = b.mb_id) 
          where iq_id = '$iq_id' ";
$iq = sql_fetch($sql);
if (!$iq[iq_id]) alert("등록된 자료가 없습니다.");

$name = get_sideview($is[mb_id], $iq[iq_name], $is[mb_email], $is[mb_homepage]);

$g4[title] = "상품문의 수정";
include_once(G4_ADMIN_PATH."/admin.head.php");

$qstr = "page=$page&sort1=$sort1&sort2=$sort2";
?>

<?=subtitle($g4[title])?>

<table>
<form id="frmitemqaform" name="frmitemqaform" method=post action="./itemqaformupdate.php">
<input type="hidden" id="w" name="w"     value='<? echo $w ?>'>
<input type="hidden" id="iq_id" name="iq_id" value='<? echo $iq_id ?>'>
<input type="hidden" id="page" name="page"  value='<? echo $page ?>'>
<input type="hidden" id="sort1" name="sort1" value='<? echo $sort1 ?>'>
<input type="hidden" id="sort2" name="sort2" value='<? echo $sort2 ?>'>
<colgroup></colgroup>
<colgroup bgcolor=#ffffff></colgroup>

<tr>
    <td>이 름</td>
    <td><?=$name?></td>
</tr>
<tr>
    <td>제 목</td>
    <td><input type="text" id="iq_subject" name="iq_subject" required itenmae='제목' value='<?=conv_subject($iq[iq_subject],120)?>'></td>
</tr>
<tr>
    <td>질 문</td>
    <td style='padding-top:5px; padding-bottom:5px;'>
        <?=textarea_size('iq_question')?>
        <textarea id="iq_question" id="iq_question" name="iq_question" rows="7" required itemname='질문'><? echo get_text($iq[iq_question]) ?></textarea>
    </td>
</tr>
<tr>
    <td>답 변</td>
    <td style='padding-top:5px; padding-bottom:5px;'>
        <?=textarea_size('iq_answer')?>
        <textarea id="iq_answer" id="iq_answer" name="iq_answer" rows="7" itemname='답변'><? echo get_text($iq[iq_answer]) ?></textarea>
    </td>
</tr>

</table>

<p>
    <input type="submit" accesskey='s' value='  확  인  '>&nbsp;
    <input type="button" accesskey='l' value='  목  록  ' onclick="document.location.href='./itemqalist.php?<?=$qstr?>';">
</form>

<?
include_once(G4_ADMIN_PATH."/admin.tail.php");
?>
