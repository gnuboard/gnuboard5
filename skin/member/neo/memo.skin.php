<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
?>

<h1>쪽지함</h1>

<ul>
    <li><a href="./memo.php?kind=recv">받은쪽지</a></li>
    <li><a href="./memo.php?kind=send">보낸쪽지</a></li>
    <li><a href="./memo_form.php">쪽지보내기</a></li>
</ul>

<table>
<caption>
전체 <?=$kind_title?>쪽지 <?=$total_count?>통<br>
쪽지 보관일수는 최장 <?=$config[cf_memo_del]?>일 입니다.
</caption>
<thead>
<tr>
    <th scope="col"><?= ($kind == "recv") ? "보낸사람" : "받는사람"; ?></th>
    <th scope="col">보낸시간</th>
    <th scope="col">읽은시간</th>
    <th scope="col">쪽지삭제</th>
</tr>
</thead>
<tbody>
<? for ($i=0; $i<count($list); $i++) { ?>
<tr>
    <td><?=$list[$i][name]?></td>
    <td><a href="<?=$list[$i][view_href]?>"><?=$list[$i][send_datetime]?></font></td>
    <td><a href="<?=$list[$i][view_href]?>"><?=$list[$i][read_datetime]?></font></td>
    <td><a href="javascript:del('<?=$list[$i][del_href]?>');"><img src="<?=$member_skin_path?>/img/btn_comment_delete.gif" width="45" height="14" border="0"></a></td>
</tr>
<? } ?>
<? if ($i==0) { echo "<tr><td colspan=\"4\" class=\"empty_table\">자료가 없습니다.</td></tr>"; } ?>
</tbody>
</table>
