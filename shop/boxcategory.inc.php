<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

//
// 2단계 분류 레이어 표시
//
$menu = ""; // 메뉴 레이어 임시저장 변수 (처음엔 아무값도 없어야 합니다.)
$sub_menu_left = 100; // 2단계 메뉴 왼쪽 좌표 (1단계 좌표에서 부터)
?>

<table bgcolor=#FFFFFF width=100% cellpadding=0 cellspacing=0>
<tr><td align=center valign=top height=50><img src='<?=G4_SHOP_URL?>/img/bar_category.gif'></td></tr>
<?
// 1단계 분류 판매가능한것만
$hsql = " select ca_id, ca_name from {$g4['yc4_category_table']}
          where length(ca_id) = '2'
            and ca_use = '1'
          order by ca_id ";
$hresult = sql_query($hsql);
$hnum = @mysql_num_rows($hresult);
for ($i=0; $row=sql_fetch_array($hresult); $i++)
{
    // 2단계 분류
    $menubody = "";
    $onmouseover = "";
    $onmouseout  = "";
    $sql2 = " select ca_id, ca_name from {$g4['yc4_category_table']}
               where LENGTH(ca_id) = '4'
                 and SUBSTRING(ca_id,1,2) = '{$row['ca_id']}'
                 and ca_use = '1'
               order by ca_id ";
    $result2 = sql_query($sql2);
    $hnum2 = @mysql_num_rows($result2);
    for ($j=0; $row2=sql_fetch_array($result2); $j++)
    {
        $menubody .= "<tr height='22'><td>&nbsp;&nbsp;· <a href='".G4_SHOP_URL."/list.php?ca_id={$row2['ca_id']}'>{$row2['ca_name']}</a></td></tr>";
        // 맨밑줄은 출력하지 않음
        if ($j < $hnum2)
            $menubody .= "<tr><td align=center><img src='".G4_SHOP_URL."/img/dot_line.gif'></td></tr>";
    }

    if ($menubody)
    {
        $onmouseover = " layer_view('lmenu{$i}', 'lmenu_layer{$i}', 'view', $sub_menu_left, -22); ";
        $onmouseout  = " layer_view('lmenu{$i}', 'lmenu_layer{$i}', 'hide'); ";
    }

    $category_link = "<a href='".G4_SHOP_URL."/list.php?ca_id={$row['ca_id']}'>";
    echo "<tr id='lmenu{$i}' onmouseover=\"$onmouseover\" onmouseout=\"$onmouseout\">";
    echo "<td height='22'>&nbsp;&nbsp;· $category_link{$row['ca_name']}</a>\n";

    if ($menubody)
    {
        //echo "<div id='lmenu_layer{$i}' style='width:180px; display:none; position:absolute; FILTER: alpha(opacity=95); z-index:999;'>";
        echo "<div id='lmenu_layer{$i}' style='width:180px; display:none; position:absolute; z-index:999;'>";
        echo "<table cellpadding=1 cellspacing=0 bgcolor=#CCCCCC width=100%><tr><td>";
        echo "<table border=0 width=100% bgcolor=#FFFFFF cellpadding=0 cellspacing=0>$menubody</table>";
        echo "</td></tr></table>";
        echo "</div>";
    }

    echo "</td></tr>\n";

    if ($i<$hnum-1) // 맨밑줄은 출력하지 않음
        echo "<tr><td align=center><img src='".G4_SHOP_URL."/img/dot_line.gif'></td></tr>\n";
}

if ($i==0)
    echo "<tr><td height=50 align=center>등록된 자료가 없습니다.</td></tr>\n";
?>
</table>

<?=$menu?>

<script language="JavaScript">
var save_layer = null;
function layer_view(link_id, menu_id, opt, x, y)
{
    var link = document.getElementById(link_id);
    var menu = document.getElementById(menu_id);

    //for (i in link) { document.write(i + '<br/>'); } return;

    if (save_layer != null)
        save_layer.style.display = "none";

    if (opt == 'hide')
    {
        menu.style.display = 'none';
    }
    else
    {
        x = parseInt(x);
        y = parseInt(y);
        menu.style.left = get_left_pos(link) + x + 'px';
        menu.style.top  = get_top_pos(link) + link.offsetHeight + y + 'px';
        menu.style.display = 'block';
    }

    save_layer = menu;
}
</script>
