<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
?>

<table cellpadding=0 cellspacing=0 bgcolor=#FFFFFF>
<tr><td><a href='<?=$g4[shop_path]?>/wishlist.php'><img src='<?=$g4[shop_img_path]?>/bar_wishlist.gif' border=0></a></td></tr>
<?
$hsql = " select a.it_id, b.it_name from $g4[yc4_wish_table] a, $g4[yc4_item_table] b
          where a.mb_id = '$member[mb_id]'
            and a.it_id  = b.it_id
          order by a.wi_id desc ";
$hresult = sql_query($hsql);
for ($i=0; $row=sql_fetch_array($hresult); $i++)
{
    echo "<tr><td height=22><nobr style='display:block; overflow:hidden; width:170px;'>&nbsp;&nbsp;· ";
    $it_name = get_text($row[it_name]);
    // 이미지로 할 경우
    //$it_name = get_it_image($row[it_id]."_s", 50, 50, $row[it_id]);
    echo "<a href=\"$g4[shop_path]/wishlist.php\">$it_name</a></nobr></td></tr>\n";
}

if ($i==0)
    echo "<tr><td><img src='$g4[shop_img_path]/nowishlist.gif'></td></tr>\n";
?>
</table>
