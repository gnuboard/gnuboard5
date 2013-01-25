<?
$str = "";
$exists = false;

$ca_id_len = strlen($ca_id);
$len2 = $ca_id_len + 2;
$len4 = $ca_id_len + 4;

// 최하위 분류의 경우 상단에 동일한 레벨의 분류를 출력해주는 코드
if (!$exists) {
    $str = "";

    $tmp_ca_id = substr($ca_id, 0, strlen($ca_id)-2);
    $tmp_ca_id_len = strlen($tmp_ca_id);
    $len2 = $tmp_ca_id_len + 2;
    $len4 = $tmp_ca_id_len + 4;

    // 차차기 분류의 건수를 얻음
    $sql = " select count(*) as cnt from $g4[yc4_category_table]
              where ca_id like '$tmp_ca_id%'
                and ca_use = '1'
                and length(ca_id) = $len4 ";
    $row = sql_fetch($sql);
    $cnt = $row['cnt'];
    if (!$cnt) 
        $str .= "<tr><td width=11 background='$g4[shop_img_path]/ca_bg02.gif'></td><td>";

    $sql = " select ca_id, ca_name from $g4[yc4_category_table]
              where ca_id like '$tmp_ca_id%'
                and ca_use = '1'
                and length(ca_id) = $len2 order by ca_id ";
    $result = sql_query($sql);
    while ($row=sql_fetch_array($result)) {
        $style = "";
        if ($ca_id == $row[ca_id])
            $style = " class='accent' ";

        if ($cnt) {
            $str .= "<tr><td width=11 background='$g4[shop_img_path]/ca_bg02.gif'></td>";
            $str .= "<td><table width=100% border=0><tr><td width=120><b>· <a href='./list.php?ca_id=$row[ca_id]'><span $style>$row[ca_name]</span></a></b></td>";
            $sql2 = " select ca_id, ca_name from $g4[yc4_category_table]
                       where ca_id like '$row[ca_id]%'
                         and ca_use = '1'
                         and length(ca_id) = $len4 order by ca_id ";
            $result2 = sql_query($sql2);
            $k=0;
            while ($row2=sql_fetch_array($result2)) {
                if (!$k)
                    $str .= "<td width=20 align=center>|</td><td class=lh>";
                $str .= "<a href='./list.php?ca_id=$row2[ca_id]'>$row2[ca_name]</a> &nbsp; ";
                $k++;
            }
            //if (!$k) $str .= "<td></td><td>";
            $str .= "</td></tr></table></td><td width=11 background='$g4[shop_img_path]/ca_bg03.gif'></td>";
        } else {
            $str .= "<a href='./list.php?ca_id=$row[ca_id]'><span $style>$row[ca_name]</span></a> &nbsp; ";
        }
        $exists = true;
    }

    if (!$cnt) 
        $str .= "</td><td width=11 background='$g4[shop_img_path]/ca_bg03.gif'></td></tr>";
}


if ($exists) {
    echo "
    <br>
    <table width=98% cellpadding=0 cellspacing=0 align=center border=0>
    <colgroup width=11>
    <colgroup width=''>
    <colgroup width=11>
    <tr>
        <td width=11><img src='$g4[shop_img_path]/ca_box01.gif'></td>
        <td background='$g4[shop_img_path]/ca_bg01.gif'></td>
        <td width=11><img src='$g4[shop_img_path]/ca_box02.gif'></td>
    </tr>
    $str
    <tr>
        <td width=11><img src='$g4[shop_img_path]/ca_box03.gif'></td>
        <td background='$g4[shop_img_path]/ca_bg04.gif'></td>
        <td width=11><img src='$g4[shop_img_path]/ca_box04.gif'></td>
    </tr>
    </table><br>";
}
?>