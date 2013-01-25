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
include_once ("$g4[admin_path]/admin.head.php");

$qstr = "page=$page&sort1=$sort1&sort2=$sort2";
?>

<?=subtitle($g4[title])?>

<table cellpadding=0 cellspacing=0 width=100%>
<form name=frmitemqaform method=post action="./itemqaformupdate.php">
<input type=hidden name=w     value='<? echo $w ?>'>
<input type=hidden name=iq_id value='<? echo $iq_id ?>'>
<input type=hidden name=page  value='<? echo $page ?>'>
<input type=hidden name=sort1 value='<? echo $sort1 ?>'>
<input type=hidden name=sort2 value='<? echo $sort2 ?>'>
<colgroup width=120></colgroup>
<colgroup width='' bgcolor=#ffffff></colgroup>
<tr><td colspan=2 height=2 bgcolor=#0E87F9></td></tr>
<tr class=ht>
    <td>&nbsp;이 름</td>
    <td><?=$name?></td>
</tr>
<tr class=ht>
    <td>&nbsp;제 목</td>
    <td><input type=text class=ed name=iq_subject required itenmae='제목' style='width:99%;' value='<?=conv_subject($iq[iq_subject],120)?>'></td>
</tr>
<tr>
    <td>&nbsp;질 문</td>
    <td style='padding-top:5px; padding-bottom:5px;'>
        <?=textarea_size('iq_question')?>
        <textarea id='iq_question' name='iq_question' rows="7" style='width:99%;' class=ed required itemname='질문'><? echo get_text($iq[iq_question]) ?></textarea>
    </td>
</tr>
<tr>
    <td>&nbsp;답 변</td>
    <td style='padding-top:5px; padding-bottom:5px;'>
        <?=textarea_size('iq_answer')?>
        <textarea id='iq_answer' name='iq_answer' rows="7" style='width:99%;' class=ed itemname='답변'><? echo get_text($iq[iq_answer]) ?></textarea>
    </td>
</tr>
<tr><td colspan=2 height=1 bgcolor=#CCCCCC></td></tr>
</table>

<p align=center>
    <input type=submit class=btn1 accesskey='s' value='  확  인  '>&nbsp;
    <input type=button class=btn1 accesskey='l' value='  목  록  ' onclick="document.location.href='./itemqalist.php?<?=$qstr?>';">
</form>

<?
include_once ("$g4[admin_path]/admin.tail.php");
?>
