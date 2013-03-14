<?
include_once("./_common.php");

if (!$fm_id) $fm_id = 1;

// FAQ MASTER
$sql = " select * from $g4[yc4_faq_master_table] where fm_id = '$fm_id' ";
$fm = sql_fetch($sql);
if (!$fm[fm_id]) 
    alert("등록된 내용이 없습니다.");

$g4[title] = $fm[fm_subject];
include_once("./_head.php");
?>

<img src="<?=$g4[shop_img_path]?>/top_faq.gif" border=0><p>

<?
$himg = "$g4[path]/data/faq/{$fm_id}_h";
if (file_exists($himg)) 
    echo "<img src='$himg' border=0><br>";

if ($is_admin) 
    echo "<p align=center><a href='$g4[shop_admin_path]/faqmasterform.php?w=u&fm_id=$fm_id'><img src='$g4[shop_img_path]/btn_admin_modify.gif' border=0></a></p>";

// 상단 HTML
echo stripslashes($fm[fm_head_html]);
echo "<br>";

echo "<table width=95% align=center cellpadding=1 cellspacing=0>\n";
echo "<tr><td class=bg_faq><table width=100% cellpadding=2 cellspacing=1 border=0 bgcolor=#FFFFFF>\n";

$sql = " select * from $g4[yc4_faq_table]
          where fm_id = '$fm_id'
          order by fa_order , fa_id ";
$result = sql_query($sql);
$str = "";
for ($i=1; $row=sql_fetch_array($result); $i++)
{
    echo "<tr>";
    echo "<td width=20 align=right valign=top>$i.</td>";
    echo "<td valign=top><a href='#faq_{$fm_id}_{$i}' class=faq>" . stripslashes($row[fa_subject]) . "</a></td>";
    echo "</tr>\n";

    $str .= "<a name='faq_{$fm_id}_{$i}'><br></a><table cellpadding=2 cellspacing=1 width=100%>";
    $str .= "<tr>";
    $str .= "<td width=38 valign=top align=right><img src='$g4[shop_img_path]/icon_poll_q.gif'></td>";
    $str .= "<td class=point valign=top>" . stripslashes($row[fa_subject]) . "</td>";
    $str .= "</tr>";
    $str .= "<tr>";
    $str .= "<td valign=top align=right><img src='$g4[shop_img_path]/icon_answer.gif'></td>";
    $str .= "<td class=leading valign=top>" . stripslashes($row[fa_content]) . "</td>";
    $str .= "</tr>";
    $str .= "<tr>";
    $str .= "<td colspan=2 align=right><a href='#g4_head'><img src='$g4[shop_img_path]/icon_top.gif' border=0></a></td>";
    $str .= "</tr>";
    $str .= "</table>";
}
echo "</table></td></tr></table>\n";

echo $str;

echo "<br>";
echo stripslashes($fm[fm_tail_html]);

$timg = "$g4[path]/data/faq/{$fm_id}_t";
if (file_exists($timg)) 
    echo "<br><img src='$timg' border=0><br>";

include_once("./_tail.php");
?>
