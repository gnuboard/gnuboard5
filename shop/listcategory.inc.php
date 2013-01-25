<?
$str = "";
$exists = false;

$ca_id_len = strlen($ca_id);
$len2 = $ca_id_len + 2;
$len4 = $ca_id_len + 4;

// 차차기 분류의 건수를 얻음
$sql = " select count(*) as cnt from $g4[yc4_category_table]
          where ca_id like '$ca_id%'
            and length(ca_id) = $len4 
            and ca_use = '1' ";
$row = sql_fetch($sql);
$cnt = $row['cnt'];
if (!$cnt) 
    $str .= "<tr><td width=11 background='$g4[shop_img_path]/ca_bg02.gif'></td><td>";

$sql = " select ca_id, ca_name from $g4[yc4_category_table]
          where ca_id like '$ca_id%'
            and length(ca_id) = $len2 
            and ca_use = '1'
          order by ca_id ";
$result = sql_query($sql);
$str .= "<tr><td width=11 background='$g4[shop_img_path]/ca_bg02.gif'></td>";
$str .= "<td><table width=100% border=0><tr><td>";
while ($row=sql_fetch_array($result)) {

    //$row2 = sql_fetch(" select count(*) as cnt from $g4[yc4_category_table] where ca_id like '$row[ca_id]%' ");
    $row2 = sql_fetch(" select count(*) as cnt from $g4[yc4_item_table] where (ca_id like '$row[ca_id]%' or ca_id2 like '$row[ca_id]%' or ca_id3 like '$row[ca_id]%') and it_use = '1'  ");

    $str .= "<a href='./list.php?ca_id=$row[ca_id]'>$row[ca_name] ($row2[cnt])</a> &nbsp; ";
    $exists = true;
}
$str .= "</td></tr></table></td><td width=11 background='$g4[shop_img_path]/ca_bg03.gif'></td>";

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