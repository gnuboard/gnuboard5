<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 

// 사용자 화면 우측과 하단을 담당하는 페이지입니다.
// 우측, 하단 화면을 꾸미려면 이 파일을 수정합니다.
?>

</td>
<td width=40></td>
</tr></table>

<!-- 카피라이트 시작 -->
<table width="<?=$table_width?>" border="0" cellspacing="10" cellpadding="10">
<tr>
    <td valign="top" background="<?=$g4['path']?>/img/copyright.gif"><a href="#g4_head"><img src="<?=$g4['path']?>/img/icon.gif" width="15" height="12" border="0"></a><font color="#848484">Copyright ⓒ your-domain. All rights reserved.</font></td>
</tr>
</table>
<!-- 카피라이트 끝 -->

<?
include_once("$g4[path]/tail.sub.php");
?>