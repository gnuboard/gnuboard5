<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 

/******************************************************
** 1.03.01 추가
** 사용방법 : 
** 분류관리 > 출력스킨에서 list.skin.4.php 를 선택하십시오.
** 출력이미지 폭과 높이는 적당하게 입력하십시오.
** 1라인 이미지수는 1 로 입력하십시오.
** 총라인수는 분류에 속한 상품수만큼 입력하십시오. 
** 상품수를 모르신다면 100000 을 입력하십시오.
** 주의) 이 스킨은 분류하단에 페이지수를 출력하지 않습니다.
******************************************************/

// 배열에 저장
unset($arr);
for ($i=0; $row=mysql_fetch_array($result); $i++) 
{
    $order = substr("0000000000" . (int)(1000000000 + $row[it_order]),-10);
    //$arr[$row[ca_id]."_".$order.$i] = $row;
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
        // 가로줄 
        if ($i>0)
            echo "<tr><td colspan=4 background='$g4[shop_img_path]/line_h.gif' height=1></td></tr>\n";

        if ($save_ca_id != $row[ca_id]) 
        {
            $sql = " select ca_name from $g4[yc4_category_table] where ca_id = '$row[ca_id]' ";
            $tmp_ca = sql_fetch($sql);

            echo "
            <tr height=30>
                <td bgcolor='#EEEEEE' colspan=4>&nbsp;<img src='$g4[shop_img_path]/icon3.gif'> <b><a href='./list.php?ca_id=$row[ca_id]'>$tmp_ca[ca_name]</a></b></td>
            </tr>";
            $save_ca_id = $row[ca_id];
        }

        echo "
        <tr>
            <td>
                <table width=100% cellpadding=0 cellspacing=0>
                <tr>
                    <td width=".($img_width+10).">".get_it_image($row[it_id]."_s", $img_width , $img_height, $row[it_id])."</td>
                    <td><a href='./item.php?it_id=$row[it_id]' class=item>".it_name_icon($row)."</a></td>
                </tr>
                </table>
            </td>
            <td align=center>$row[it_maker]</td>
            <td align=right>";

            if (!$row[it_gallery]) 
            {
                echo "<span class=amount>".display_amount(get_amount($row), $row[it_tel_inq])."</span>";
                echo "<br>".display_point($row[it_point]);
            }

            echo "</td></tr>";

        $i++;
    }
} else {
    echo "<tr><td align=center><span class=point>자료가 한건도 없습니다.</span></td></tr>\n";
}
?>
<tr><td colspan=4 background='<?="$g4[shop_img_path]/line_h.gif"?>' height=1></td></tr>
</table>
