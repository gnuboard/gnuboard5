<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 

// marquee 태그를 사용하지 않고 자바스크립트로 이미지가 여러개씩 왼쪽으로 롤링되도록 함
// 출력되는 총이미지수는 환경설정의 1라인이미지수 x 총라인수 입니다.

// 출력되는 테이블의 높이, 폭
$scroll[h] = 170;
$scroll[w] = 175 * $list_mod;
$scroll[td] = (int)($scroll['w'] / $list_mod);

// include 될때 마다 다른 아이디를 부여 (여러곳에서 이 페이지를 include 할 경우를 대비함)
$uni = uniqid("");
?>

<script language="javascript">
var roll_width_<?=$uni?> = <?=$scroll[w]?>;
var total_area_<?=$uni?> = 0;
var wait_flag_<?=$uni?> = true;

var bMouseOver_<?=$uni?> = 1;
var roll_speed_<?=$uni?> = 1;
var waitingtime_<?=$uni?> = 3000;
var s_tmp_<?=$uni?> = 0;
var s_amount_<?=$uni?> = <?=$scroll[w]?>;
var roll_text_<?=$uni?> = new Array();
var startPanel_<?=$uni?> = 0;
var n_panel_<?=$uni?> = 0;
var i_<?=$uni?> = 0;

function start_roll_<?=$uni?>()
{ 
    i_<?=$uni?> = 0;
    for (i_<?=$uni?> in roll_text_<?=$uni?>)
        n_panel_<?=$uni?>++;

    n_panel_<?=$uni?> = n_panel_<?=$uni?> -1 ;
    startPanel_<?=$uni?> = Math.round(Math.random()*n_panel_<?=$uni?>);
    if(startPanel_<?=$uni?> == 0)
    {
        i_<?=$uni?> = 0;
        for (i_<?=$uni?> in roll_text_<?=$uni?>) 
            insert_area_<?=$uni?>(total_area_<?=$uni?>, total_area_<?=$uni?>++); // area 삽입
    }
    else if(startPanel_<?=$uni?> == n_panel_<?=$uni?>)
    {
        insert_area_<?=$uni?>(startPanel_<?=$uni?>, total_area_<?=$uni?>);
        total_area_<?=$uni?>++;
        for (i_<?=$uni?>=0; i_<?=$uni?><startPanel_<?=$uni?>; i_<?=$uni?>++) 
        {
            insert_area_<?=$uni?>(i_<?=$uni?>, total_area_<?=$uni?>); // area 삽입
            total_area_<?=$uni?>++;
        }
    }
    else if((startPanel_<?=$uni?> > 0) || (startPanel_<?=$uni?> < n_panel_<?=$uni?>))
    {
        insert_area_<?=$uni?>(startPanel_<?=$uni?>, total_area_<?=$uni?>);
        total_area_<?=$uni?>++;
        for (i_<?=$uni?>=startPanel_<?=$uni?>+1; i_<?=$uni?><=n_panel_<?=$uni?>; i_<?=$uni?>++) 
        {
            insert_area_<?=$uni?>(i_<?=$uni?>, total_area_<?=$uni?>); // area 삽입
            total_area_<?=$uni?>++;
        }
        for (i_<?=$uni?>=0; i_<?=$uni?><startPanel_<?=$uni?>; i_<?=$uni?>++) 
        {
            insert_area_<?=$uni?>(i_<?=$uni?>, total_area_<?=$uni?>); // area 삽입
            total_area_<?=$uni?>++;
        }
    }
    if ( navigator.appName == "Microsoft Internet Explorer" )
    {
        if ( navigator.appVersion.indexOf ( "MSIE 4" ) > -1 )
        return ;
    }
    window.setTimeout("rolling_<?=$uni?>()",waitingtime_<?=$uni?>);
}

function rolling_<?=$uni?>()
{ 
    if (bMouseOver_<?=$uni?> && wait_flag_<?=$uni?>)
    {
        for (i_<?=$uni?>=0;i_<?=$uni?><total_area_<?=$uni?>;i_<?=$uni?>++){
            tmp_<?=$uni?> = document.getElementById('scroll_area_<?=$uni?>'+i_<?=$uni?>).style;
            tmp_<?=$uni?>.left = parseInt(tmp_<?=$uni?>.left)-roll_speed_<?=$uni?>;
            if (parseInt(tmp_<?=$uni?>.left) <= -roll_width_<?=$uni?>){
                tmp_<?=$uni?>.left = roll_width_<?=$uni?>*(total_area_<?=$uni?>-1);
            }
            if (s_tmp_<?=$uni?>++ > (s_amount_<?=$uni?>-1)*roll_text_<?=$uni?>.length){
                wait_flag_<?=$uni?>=false;
                window.setTimeout("wait_flag_<?=$uni?>=true;s_tmp_<?=$uni?>=0;",waitingtime_<?=$uni?>);
            }
        }
    }
    window.setTimeout("rolling_<?=$uni?>()", 1);
}

function insert_area_<?=$uni?>(idx_<?=$uni?>, n_<?=$uni?>)
{ 
    document.write('<div style="left: 0px; width: 100%; position: absolute; left: '+(roll_width_<?=$uni?>*n_<?=$uni?>)+'px" id="scroll_area_<?=$uni?>'+n_<?=$uni?>+'">\n'+roll_text_<?=$uni?>[idx_<?=$uni?>]+'\n</div>\n');
}

<?
$i = 0;
while (1) 
{
    $str = $str2 = "";
    for ($k=0; $k<$list_mod; $k++) 
    {
        $row = sql_fetch_array($result);
        if (!$row[it_id]) break;

        $href = "<a href='$g4[shop_path]/item.php?it_id=$row[it_id]' class=item>";

        $str .= "<td width='$scroll[td]' valign=top align=center><table width='98%' cellpadding=0 cellspacing=0 border=0>";
        $str .= "<tr><td align=center>$href".get_it_image($row[it_id]."_s", $img_width, $img_height)."</a></td></tr>";
        $str .= "<tr><td align=center>$href".addslashes($row[it_name])."</a></td></tr>";
        $str .= "<tr><td align=center><span class=amount>".display_amount(get_amount($row), $row[it_tel_inq])."</span></td></tr>";
        $str .= "</table></td>";
    }

    if ($str) 
    {
        $str2 = "<table width='100%' cellpadding=0 cellspacing=0 border=0><tr>$str</tr></table>";
        echo "roll_text_{$uni}[$i] = \"{$str2}\";\n";
    }

    if (!$row[it_id]) break;

    $i++;
}
?>
</script>

<div style="left: 0px; width: <?=$scroll[w]?>px; position: relative; top: 15px; height:<?=$scroll[h]?>px; overflow:hidden;" onMouseover="bMouseOver_<?=$uni?>=0" onMouseout="bMouseOver_<?=$uni?>=1" id="scroll_image_<?=$uni?>">
    <script language="javascript">
    var no_script_flag_<?=$uni?> = false ;
    if ( navigator.appName == "Microsoft Internet Explorer" )
    {
        if ( navigator.appVersion.indexOf ( "MSIE 4" ) > -1 )
        {
            document.write ( roll_text_<?=$uni?>[0] ) ;
            no_script_flag_<?=$uni?> = true ;
        }
    }
    if ( no_script_flag_<?=$uni?> == false )
        start_roll_<?=$uni?>();
    </script>
</div>							   
