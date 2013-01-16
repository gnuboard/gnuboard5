<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
?>
인기검색어 : 
<? 
for ($i=0; $i<count($list); $i++) {
    echo "<a href='$g4[bbs_path]/search.php?sfl=wr_subject&sop=and&stx=".urlencode($list[$i][pp_word])."'>{$list[$i][pp_word]}</a>&nbsp;";
} 
?>