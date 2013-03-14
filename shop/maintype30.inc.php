<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 

// marquee 태그를 사용하지 않고 자바스크립트로 이미지가 여러개씩 롤링되도록 함

// 출력되는 테이블의 높이
$marquee[height] = 170;

// include 될때 마다 다른 아이디를 부여 (여러곳에서 이 페이지를 include 할 경우를 대비함)
$uni = uniqid("");
?>

<script language="javascript">
    var roll_height_<?=$uni?> = <?=$marquee[height]?>;
    var total_area_<?=$uni?> = 0;
    var wait_flag_<?=$uni?> = true;

    var bMouseOver_<?=$uni?> = 1;
    var roll_speed_<?=$uni?> = 1;
    var waitingtime_<?=$uni?> = 3000;
    var s_tmp_<?=$uni?> = 0;
    var s_amount_<?=$uni?> = <?=$marquee[height]?>;
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
                tmp_<?=$uni?>.top = parseInt(tmp_<?=$uni?>.top)-roll_speed_<?=$uni?>;
                if (parseInt(tmp_<?=$uni?>.top) <= -roll_height_<?=$uni?>){
                    tmp_<?=$uni?>.top = roll_height_<?=$uni?>*(total_area_<?=$uni?>-1);
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
        document.write('<div style="left: 0px; width: 100%; position: absolute; top: '+(roll_height_<?=$uni?>*n_<?=$uni?>)+'px" id="scroll_area_<?=$uni?>'+n_<?=$uni?>+'">\n'+roll_text_<?=$uni?>[idx_<?=$uni?>]+'\n</div>\n');
    }

    <?
    $width = (int)(100 / $list_mod);
    for ($i=0; $i<10000; $i++) 
    {

        $roll_text[$i]  = "";
        $roll_text[$i] .= "<table width='100%' height='$marquee[height]px' cellpadding=1 cellspacing=0 border=0>";
        $roll_text[$i] .= "<tr>";

        $k=0;
        while ($row=sql_fetch_array($result)) 
        {
            if (!$row) break;

            $href = "<a href='$g4[shop_path]/item.php?it_id=$row[it_id]' class=item>";

            $roll_text[$i] .= "<td width='$width%' valign=top>";
            $roll_text[$i] .= "<table width='100%' cellpadding=1 cellspacing=0 border=0>";
            $roll_text[$i] .= "<tr><td align=center>$href".get_it_image($row[it_id]."_s", $img_width, $img_height)."</a></td></tr>";
            $roll_text[$i] .= "<tr><td align=center>$href".addslashes($row[it_name])."</a></td></tr>";
            $roll_text[$i] .= "<tr><td align=center><span class=amount>".display_amount(get_amount($row), $row[it_tel_inq])."</span></td></tr>";
            $roll_text[$i] .= "</table>";
            $roll_text[$i] .= "</td>";
            $k++;
            if ($k==$list_mod) break;
        }

        if (($cnt = $k%$list_mod) != 0)
            for ($m=$cnt; $m<$list_mod; $m++)
                $roll_text[$i] .= "<td>&nbsp;</td>";

        $roll_text[$i] .= "</tr>";
        $roll_text[$i] .= "</table>";

        if ($k > 0)
            echo "roll_text_{$uni}[$i] = \"{$roll_text[$i]}\";\n";

        if (!$row) break;
    }
    ?>
</script>

<div style="left: 0px; width: 100%; position: relative; top: 0px; height: <?=$marquee[height]?>px; overflow:hidden;" onMouseover="bMouseOver_<?=$uni?>=0" onMouseout="bMouseOver_<?=$uni?>=1" id="scroll_image_<?=$uni?>">
    <script>
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
