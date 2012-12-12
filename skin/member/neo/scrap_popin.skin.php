<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
?>

<h1>스크랩하기</h1>

<form name="f_scrap_popin" method="post" action="./scrap_popin_update.php">
<input type="hidden" name="bo_table" value="<?=$bo_table?>">
<input type="hidden" name="wr_id" value="<?=$wr_id?>">

<table>
<caption>스크랩 확인 및 댓글 달기</caption>
<tbody>
<tr>
    <th scope="row">제목</th>
    <td><?=get_text(cut_str($write[wr_subject], 255))?></td>
</tr>
<tr>
    <th scope="row"><label for="wr_content">댓글</label></th>
    <td><textarea id="wr_content" name="wr_content"></textarea></td>
</tr>
</tbody>
</table>

</form>

<div class="btn_window">
    <input type="submit" value="스크랩">
</div>
