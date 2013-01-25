<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 

// 배열에 저장
unset($arr);
for ($i=0; $row=mysql_fetch_array($result); $i++) 
{
    $order = substr("0000000000" . (int)(1000000000 + $row[it_order]),-10);
    if ($sort) 
    {
        if (preg_match("/^it_amount/", $sort)) 
        {
            $order = substr("0000000000" . get_amount($row), -10) . "." . $order;
        } 
        else if (preg_match("/^it_type/", $sort)) 
        {
            $type = substr($sort, 0, 8);
            $order = (1 - $row[$type]) . "." . $order;
        }
    }
    $key = $row[ca_id] . "." . $order . "." . $row[it_id];
    $arr[$key] = $row;
}
?>

<table width=100% cellpadding=4 cellspacing=1>
<?
if (count($arr) > 0) 
{ 
    ksort($arr);
    $i=0;
    $save_ca_id = "";
    while (list($key, $row) = each ($arr)) 
    {
        // 1라인에 설정한 수만큼의 상품이 출력되면 행을 바꿈
        if ( ($i>0) && (($i%$list_mod)==0) ) 
        {
            echo "</tr>\n\n";
            echo "<tr><td colspan='$list_mod' background='$g4[shop_img_path]/line_h.gif' height=1></td></tr>\n\n";
            echo "<tr>\n";
        }
        
        // 임시저장한 분류코드와 다르다면 분류명을 출력
        if ($save_ca_id != $row[ca_id]) 
        {
            $sql = " select ca_name from $g4[yc4_category_table] where ca_id = '$row[ca_id]' ";
            $tmp_ca = sql_fetch($sql);

            echo "<tr height=30>";
            echo "<td bgcolor=#EEEEEE colspan='$list_mod'>&nbsp;<img src='$g4[shop_img_path]/icon3.gif'> <b><a href='./list.php?ca_id=$row[ca_id]'>$tmp_ca[ca_name]</a></b></td>";
            echo "</tr>";
            $save_ca_id = $row[ca_id];

            $i=0;
        }

        echo "<td width='{$td_width}%' align=center valign=top>";
        echo "<br>";
        echo "<table width=98% cellpadding=2 cellspacing=0>";
        echo "<tr><td align=center>".get_it_image($row[it_id]."_s", $img_width , $img_height, $row[it_id])."</td></tr>";
        echo "<tr><td align=center>".it_name_icon($row)."</td></tr>";
        
        if ($row[it_cust_amount] && !$row[it_gallery])
            echo "<tr><td align=center><strike>".display_amount($row[it_cust_amount])."</strike></td></tr>";

        echo "<tr><td align=center>";

        if (!$row[it_gallery]) 
            echo "<span class=amount>".display_amount(get_amount($row), $row[it_tel_inq])."</span>";

        echo "</td></tr>";
        echo "</table></td>\n";

        $i++;
    }
} 
else 
{
    echo "<tr><td align=center height='100'><span class=point>자료가 한건도 없습니다.</span></td></tr>\n";
}

// 나머지 td 를 채운다.
if (($cnt = $i%$list_mod) != 0)
    for ($k=$cnt; $k<$list_mod; $k++)
        echo "<td>&nbsp;</td>\n";
?>
<tr><td colspan='<?=$list_mod?>' background='<?="$g4[shop_img_path]/line_h.gif"?>' height=1></td></tr>
</table>
