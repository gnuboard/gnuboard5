<?
$str = "";
$exists = false;

$depth2_ca_id = substr($ca_id, 0, 2);

$sql = " select ca_id, ca_name from {$g4['shop_category_table']}
          where ca_id like '${depth2_ca_id}%'
            and length(ca_id) = 4
            and ca_use = '1'
          order by ca_id ";
$result = sql_query($sql);
$str .= "<tr><td width=11 background='".G4_SHOP_PATH."/img/ca_bg02.gif'></td>";
$str .= "<td><table width=100% border=0><tr><td>";
while ($row=sql_fetch_array($result)) {
    if (preg_match("/^{$row['ca_id']}/", $ca_id))
        $span = "<span style='font-weight:bold;'>";
    else
        $span = "<span>";
    $str .= "<a href='./list.php?ca_id={$row['ca_id']}'>{$span}{$row['ca_name']}</span></a> &nbsp; ";
    $exists = true;
}
$str .= "</td></tr></table></td><td width=11 background='".G4_SHOP_URL."/img/ca_bg03.gif'></td>";

if ($exists) {
    echo "
    <br>
    <table width=98% cellpadding=0 cellspacing=0 align=center border=0>
    <colgroup width=11>
    <colgroup width=''>
    <colgroup width=11>
    <tr>
        <td width=11><img src='".G4_SHOP_URL."/img/ca_box01.gif'></td>
        <td background='".G4_SHOP_URL."/img/ca_bg01.gif'></td>
        <td width=11><img src='".G4_SHOP_URL."/img/ca_box02.gif'></td>
    </tr>
    $str
    <tr>
        <td width=11><img src='".G4_SHOP_URL."/img/ca_box03.gif'></td>
        <td background='".G4_SHOP_URL."/img/ca_bg04.gif'></td>
        <td width=11><img src='".G4_SHOP_URL."/img/ca_box04.gif'></td>
    </tr>
    </table><br>";
}
?>