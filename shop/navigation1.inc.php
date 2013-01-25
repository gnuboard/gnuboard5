<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

if ($ca_id)
{    
    $str = $bar = "";
    $len = strlen($ca_id) / 2;
    for ($i=1; $i<=$len; $i++) 
    {
        $code = substr($ca_id,0,$i*2);

        $sql = " select ca_name from $g4[yc4_category_table] where ca_id = '$code' ";
        $row = sql_fetch($sql);

        $style = "";
        if ($ca_id == $code)
            $style = "style='font-weight:bold;'";

        $str .= $bar . "<a href='./list.php?ca_id=$code' $style>$row[ca_name]</a>";
        $bar = " > ";
    }
}
else
    $str = $g4[title];

//if ($it_id) $str .= " > $it[it_name]";

include("./navigation2.inc.php");
?>

