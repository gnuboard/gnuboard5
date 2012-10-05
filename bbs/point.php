<?
include_once("./_common.php");

if (!$member[mb_id]) 
    alert_close("회원만 조회하실 수 있습니다.");

$g4[title] = $member[mb_nick] . "님의 포인트 내역";
include_once("$g4[path]/head.sub.php");

$list = array();

$sql_common = " from $g4[point_table] where mb_id = '".mysql_escape_string($member[mb_id])."' ";
$sql_order = " order by po_id desc ";

$sql = " select count(*) as cnt $sql_common ";
$row = sql_fetch($sql);
$total_count = $row[cnt];

$rows = $config[cf_page_rows];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if (!$page) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함
?>

<table width="600" height="50" border="0" cellpadding="0" cellspacing="0">
<tr>
    <td align="center" valign="middle" bgcolor="#EBEBEB"><table width="590" height="40" border="0" cellspacing="0" cellpadding="0">
        <tr> 
            <td width="25" align="center" bgcolor="#FFFFFF" ><img src="<?=$g4[bbs_img_path]?>/icon_01.gif" width="5" height="5"></td>
            <td width="" align="left" bgcolor="#FFFFFF" ><font color="#666666"><b><?=$g4[title]?></b></font></td>
        </tr>
        </table></td>
</tr>
</table>

<table width="600" border="0" cellspacing="0" cellpadding="0">
<tr> 
    <td height="200" align="center" valign="top"><table width="540" border="0" cellspacing="0" cellpadding="0">
        <tr> 
            <td height="20"></td>
        </tr>
        <tr> 
            <td height="2" bgcolor="#808080"></td>
        </tr>
        <tr> 
            <td width="540" bgcolor="#FFFFFF">
                <table width=100% cellpadding=1 cellspacing=1 border=0>
                <tr bgcolor=#E1E1E1 align=center> 
                    <td width="130" height="24"><b>일시</b></td>
                    <td width=""><b>내용</b></td>
                    <td width="70"><b>지급포인트</b></td>
                    <td width="70"><b>사용포인트</b></td>
                </tr>

                <?
                $sum_point1 = $sum_point2 = 0;

                $sql = " select * 
                          $sql_common
                          $sql_order
                          limit $from_record, $rows ";
                $result = sql_query($sql);
                for ($i=0; $row=sql_fetch_array($result); $i++) {
                    $point1 = $point2 = 0;
                    if ($row[po_point] > 0) {
                        $point1 = "+" . number_format($row[po_point]);
                        $sum_point1 += $row[po_point];
                    } else {
                        $point2 = number_format($row[po_point]);
                        $sum_point2 += $row[po_point];
                    }
                    
                    echo <<<HEREDOC
                    <tr height=25 bgcolor="#F6F6F6" align="center"> 
                        <td height="24">$row[po_datetime]</td>
                        <td align="left" title='$row[po_content]'><nobr style='display:block; overflow:hidden; width:250px;'>&nbsp;$row[po_content]</a></td>
                        <td align=right>{$point1}&nbsp;</td>
                        <td align=right>{$point2}&nbsp;</td>
                    </tr>
HEREDOC;
                }

                if ($i == 0)
                    echo "<tr><td colspan=5 align=center height=100>자료가 없습니다.</td></tr>";
                else {
                    if ($sum_point1 > 0)
                        $sum_point1 = "+" . number_format($sum_point1);
                    $sum_point2 = number_format($sum_point2);
                    echo <<<HEREDOC
                    <tr height=25 bgcolor="#E1E1E1" align="center"> 
                        <td height="24" colspan=2 align=center>소계</td>
                        <td align=right>{$sum_point1}&nbsp;</td>
                        <td align=right>{$sum_point2}&nbsp;</td>
                    </tr>
HEREDOC;
                }
                ?>
                </table></td>
        </tr>
        </table></td>
</tr>
<tr> 
    <td height="30" align="center"><?=get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr&page=");?></td>
</tr>
<tr>
    <td height="30" align="center" bgcolor="#F6F6F6">
        <img src='<?=$g4[bbs_img_path]?>/icon_02.gif'> 보유 포인트 : <B><?=number_format($member[mb_point])?> 점</B></td>
</tr>
<tr> 
    <td height="2" align="center" valign="top" bgcolor="#D5D5D5"></td>
</tr>
<tr>
    <td height="2" align="center" valign="top" bgcolor="#E6E6E6"></td>
</tr>
<tr>
    <td height="40" align="center" valign="bottom"><a href="javascript:window.close();"><img src="<?=$g4[bbs_img_path]?>/close.gif" width="66" height="20" border="0"></a></td>
</tr>
</table>
<br>


<?/*?>
<table width='100%' cellpadding=0 cellspacing=0>
	<tr><td bgcolor=#B8B7B7><img src='<?=$g4[bbs_img_path]?>/title_point.gif'></td></tr>
</table>
<br>

<table width='99%' align=center cellpadding=3 cellspacing=0 border=1 bordercolordark=#F0F0F0>
<colgroup width=130></colgroup>
<colgroup width=''></colgroup>
<colgroup width=70></colgroup>
<colgroup width=70></colgroup>
<tr height=25 bgcolor=#F9F9F9 align=center>
	<td>일시</td>
	<td>내용</td>
	<td>지급포인트</td>
	<td>사용포인트</td>
</tr>

<?
$sql = " select * 
          $sql_common
          $sql_order
          limit $from_record, $rows ";
$result = sql_query($sql);
for ($i=0; $row=sql_fetch_array($result); $i++) {
    $point1 = $point2 = "";
    if ($row[po_point] >= 0) {
        $point1 = "+" . number_format($row[po_point]);
    } else {
        $point2 = number_format($row[po_point]);
    }

    echo "
    <tr height=25>
        <td align=center>$row[po_datetime]</td>
        <td title='$row[po_content]'><nobr style='display:block; overflow:hidden; width:200px;'>&nbsp;$row[po_content]</a></td>
        <td align=right>$point1&nbsp;</td>
        <td align=right>$point2&nbsp;</td>
    </tr>
    ";
}

if ($i == 0)
    echo "<tr><td colspan=4 align=center height=100>자료가 없습니다.</td></tr>";
?>
</table>

<table width='100%' cellpadding=3 cellspacing=0>
<tr><td height=45 align=center><?=get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr&page=");?></td></tr>
</table>

<table width='99%' align=center cellpadding=1 cellspacing=0 bgcolor=#CCCCCC>
	<tr>
		<td>
			<table width='100%' cellpadding=0 cellspacing=0 bgcolor=#F9F9F9>
				<tr>
					<td height=50>&nbsp;&nbsp;&nbsp;<B>내 포인트 </B></td>
					<td><img src='<?=$g4[bbs_img_path]?>/icon_02.gif'> 보유 포인트 : <B><?=number_format($member[mb_point])?></B></td>
				</tr>
			</table>
		</td>
	</tr>
</table>

<table width='100%' cellpadding=3 cellspacing=0>
	<tr><td colspan=2 height=45 align=center><a href='javascript:window.close();'><img src='<?=$g4[bbs_img_path]?>/btn_close.gif' border=0></a></td></tr>
</table>
<?*/?>

<?
include_once("$g4[path]/tail.sub.php");
?>