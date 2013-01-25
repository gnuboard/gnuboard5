<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 

// marquee 태그를 사용하지 않고 자바스크립트로 이미지가 여러개씩 롤링되도록 함

// include 될때 마다 다른 아이디를 부여 (여러곳에서 이 페이지를 include 할 경우를 대비함)
$uniqinc = uniqid((int)get_microtime());

// 출력되는 테이블의 높이
$marquee["height_$uniqinc"] = 170;
?>

<script language="javascript">
var htmlstr_<?=$uniqinc?> = "";
var mouse_<?=$uniqinc?> = 1;
var wait_<?=$uniqinc?> = 4000;
var iheight_<?=$uniqinc?> = 88;

var ctnt_i_<?=$uniqinc?> = new Array();
var m_OldImg_<?=$uniqinc?> = -1;
var k_<?=$uniqinc?> = 0;
var newi_<?=$uniqinc?> = 0;
var newj_<?=$uniqinc?> = 0;
var befTmp_<?=$uniqinc?>;

<?
$width = (int)(100 / $list_mod);
for ($i=0; $i<10000; $i++) 
{

    $roll_text[$i]  = "";
    $roll_text[$i] .= "<table width='100%' height='{$marquee["height_$uniqinc"]}px' cellpadding=1 cellspacing=0 border=0>";
    $roll_text[$i] .= "<tr>";

    $k=0;
    while ($row=sql_fetch_array($result)) 
    {
        if (!$row) break;

        $href = "<a href='$g4[shop_path]/item.php?it_id=$row[it_id]' class=item>";

        $roll_text[$i] .= "<td width='$width%' valign=top align=center>";
        $roll_text[$i] .= "<table width='100%' cellpadding=1 cellspacing=0 border=0>";
        $roll_text[$i] .= "<tr><td align=center>$href".get_it_image($row[it_id]."_s", $img_width, $img_height)."</a></td></tr>";
        $roll_text[$i] .= "<tr><td align=center>$href".addslashes($row[it_name])."</a></td></tr>";
        $roll_text[$i] .= "<tr><td align=center><span class=amount>".display_amount(get_amount($row), $row[it_tel_inq])."</span></td></tr>";
        $roll_text[$i] .= "</table>";
        $roll_text[$i] .= "</td>";
        $k++;
        if ($k==$list_mod) break;
    }

    $roll_text[$i] .= "</tr>";
    $roll_text[$i] .= "</table>";

    if ($k > 0)
        echo "ctnt_i_{$uniqinc}[$i] = \"{$roll_text[$i]}\";\n";

    if (!$row) break;
}
?>

function ImgBannerStart_<?=$uniqinc?>() 
{
    for (k_<?=$uniqinc?> = 0; k_<?=$uniqinc?> < ctnt_i_<?=$uniqinc?>.length; k_<?=$uniqinc?>++) {
        insertImg_<?=$uniqinc?>(k_<?=$uniqinc?>);
    }

    newj_<?=$uniqinc?> = Math.round(Math.random()*(ctnt_i_<?=$uniqinc?>.length - 1));
    tmp_<?=$uniqinc?> = document.getElementById('img_area_<?=$uniqinc?>'+ newj_<?=$uniqinc?>).style;
    tmp_<?=$uniqinc?>.display = '';
    befTmp_<?=$uniqinc?> = tmp_<?=$uniqinc?>;
    window.setTimeout("scrollimg_<?=$uniqinc?>()", wait_<?=$uniqinc?>);
}

function scrollimg_<?=$uniqinc?>() 
{
    if (mouse_<?=$uniqinc?>) {
        befTmp_<?=$uniqinc?>.display = 'none';
        newj_<?=$uniqinc?>++;
        newi_<?=$uniqinc?> = newj_<?=$uniqinc?> % ctnt_i_<?=$uniqinc?>.length;
        newj_<?=$uniqinc?> = newi_<?=$uniqinc?>;

        tmp_<?=$uniqinc?> = document.getElementById('img_area_<?=$uniqinc?>'+ newj_<?=$uniqinc?>).style;
        tmp_<?=$uniqinc?>.display = '';
        m_OldImg_<?=$uniqinc?> = newj_<?=$uniqinc?>;
        befTmp_<?=$uniqinc?> = tmp_<?=$uniqinc?>;
    }
    window.setTimeout("scrollimg_<?=$uniqinc?>()",wait_<?=$uniqinc?>);
}

function insertImg_<?=$uniqinc?>(n) 
{
    htmlstr_<?=$uniqinc?> = '<div style="left: 0px; width: 100%; display=none;" onMouseover="mouse_<?=$uniqinc?> = 0" onMouseout="mouse_<?=$uniqinc?> = 1" id="img_area_<?=$uniqinc?>'+n+'">\n';
    htmlstr_<?=$uniqinc?> += ctnt_i_<?=$uniqinc?>[n] + '\n' + '</div>\n';
    document.write(htmlstr_<?=$uniqinc?>);
}
</script>

<div style="left: 0px; width: 100%; position: relative; top: 0px; height: <?=$marquee["height_$uniqinc"]?>px; overflow:hidden;" onMouseover="mouse_<?=$uniqinc?> = 0" onMouseout="mouse_<?=$uniqinc?> = 1" id="img_div_<?=$uniqinc?>">
	<script language="JavaScript">ImgBannerStart_<?=$uniqinc?>();</script>
</div>
