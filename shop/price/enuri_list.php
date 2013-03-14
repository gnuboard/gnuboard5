<?PHP
/*
**  가격비교사이트 에누리 분류페이지
*/
include_once("./_common.php");
?>
<html>
<title>에누리 분류페이지</title>
<head>
<meta http-equiv="Cache-Control" content="no-cache"/> 
<meta http-equiv="Expires" content="0"/> 
<meta http-equiv="Pragma" content="no-cache"/> 
<style type="text/css">
<!--
A:link		{text-decoration: underline; color:steelblue}
A:visited	{text-decoration: none; color:steelblue}
A:hover		{text-decoration: underline; color:RoyalBlue}   
font		{font-family:굴림; font-size:10pt}
th,td		{font-family:굴림; font-size:10pt ; height:15pt}

//-->
</style>
</head>
<body>

<table border="0" cellspacing="1" cellpadding="5" bgcolor="black" width="91%" align='center'>
	<tr bgcolor="#ededed">
		<th width=60 align='center'>대분류</th>
		<th>중분류</th>
	</tr>
    <tr bgcolor='white'>

<?PHP
$url = "http://" . $_SERVER["HTTP_HOST"] . dirname($_SERVER["PHP_SELF"]);

$sql =" SELECT LENGTH(ca_id)=2 AS cnt, ca_id
        FROM $g4[yc4_category_table]
        HAVING cnt";
$result = @mysql_query($sql);

$tr = "";

for ($i=0; $row=mysql_fetch_array($result); $i++) 
{
    $row2 = sql_fetch(" select ca_name from $g4[yc4_category_table] where ca_id = '".$row[ca_id]."'");
    echo $tr;
    echo "    <td align=center><a href='./enuri.php?ca_id=$row[ca_id]'>$row2[ca_name]</a></td>\n";
    $str = "  <td>";
    $sql3 = "select ca_name,ca_id from $g4[yc4_category_table] where ca_id LIKE '".$row[ca_id]."%' AND LENGTH(ca_id) !=2 AND LENGTH(ca_id) < 5";
    $result3 = @mysql_query($sql3); 
    
    $bar = "";

    for ($j=0;$row3=mysql_fetch_array($result3);$j++) 
    {   
        $str .= $bar;
        $str .= "<a href='./enuri.php?ca_id=$row3[ca_id]'>$row3[ca_name]</a>";        
        $bar = " | \n";
    }

    $str .= "    </td>\n";

    echo $str;
   
    $tr = "  </tr>\n  <tr bgcolor='white'>\n";
}
?>
  </tr>  
</table>

</body>
</html>