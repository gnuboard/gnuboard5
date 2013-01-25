<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 

// marquee 태그를 사용하지 않고 자바스크립트로 이미지가 여러개씩 위로 롤링되도록 함
// 출력되는 총이미지수는 환경설정의 1라인이미지수 x 총라인수 입니다.

// 출력되는 테이블의 높이
$marquee[height] = 170 * $list_mod;

// include 될때 마다 다른 아이디를 부여 (여러곳에서 이 페이지를 include 할 경우를 대비함)
$uniqinc = uniqid((int)get_microtime());
?>

<script language="javascript">
    var roll_height_<?=$uniqinc?> = <?=$marquee[height]?>;
    var total_area_<?=$uniqinc?> = 0;
    var wait_flag_<?=$uniqinc?> = true;

    var bMouseOver_<?=$uniqinc?> = 1;
    var roll_speed_<?=$uniqinc?> = 1;
    var waitingtime_<?=$uniqinc?> = 3000;
    var s_tmp_<?=$uniqinc?> = 0;
    var s_amount_<?=$uniqinc?> = <?=$marquee[height]?>;
    var roll_text_<?=$uniqinc?> = new Array();
    var startPanel_<?=$uniqinc?> = 0;
    var n_panel_<?=$uniqinc?> = 0;
    var i_<?=$uniqinc?> = 0;

    function start_roll_<?=$uniqinc?>()
    { 
        i_<?=$uniqinc?> = 0;
        for (i_<?=$uniqinc?> in roll_text_<?=$uniqinc?>)
            n_panel_<?=$uniqinc?>++;

        n_panel_<?=$uniqinc?> = n_panel_<?=$uniqinc?> -1 ;
        startPanel_<?=$uniqinc?> = Math.round(Math.random()*n_panel_<?=$uniqinc?>);
        if(startPanel_<?=$uniqinc?> == 0)
        {
            i_<?=$uniqinc?> = 0;
            for (i_<?=$uniqinc?> in roll_text_<?=$uniqinc?>) 
                insert_area_<?=$uniqinc?>(total_area_<?=$uniqinc?>, total_area_<?=$uniqinc?>++); // area 삽입
        }
        else if(startPanel_<?=$uniqinc?> == n_panel_<?=$uniqinc?>)
        {
            insert_area_<?=$uniqinc?>(startPanel_<?=$uniqinc?>, total_area_<?=$uniqinc?>);
            total_area_<?=$uniqinc?>++;
            for (i_<?=$uniqinc?>=0; i_<?=$uniqinc?><startPanel_<?=$uniqinc?>; i_<?=$uniqinc?>++) 
            {
                insert_area_<?=$uniqinc?>(i_<?=$uniqinc?>, total_area_<?=$uniqinc?>); // area 삽입
                total_area_<?=$uniqinc?>++;
            }
        }
        else if((startPanel_<?=$uniqinc?> > 0) || (startPanel_<?=$uniqinc?> < n_panel_<?=$uniqinc?>))
        {
            insert_area_<?=$uniqinc?>(startPanel_<?=$uniqinc?>, total_area_<?=$uniqinc?>);
            total_area_<?=$uniqinc?>++;
            for (i_<?=$uniqinc?>=startPanel_<?=$uniqinc?>+1; i_<?=$uniqinc?><=n_panel_<?=$uniqinc?>; i_<?=$uniqinc?>++) 
            {
                insert_area_<?=$uniqinc?>(i_<?=$uniqinc?>, total_area_<?=$uniqinc?>); // area 삽입
                total_area_<?=$uniqinc?>++;
            }
            for (i_<?=$uniqinc?>=0; i_<?=$uniqinc?><startPanel_<?=$uniqinc?>; i_<?=$uniqinc?>++) 
            {
                insert_area_<?=$uniqinc?>(i_<?=$uniqinc?>, total_area_<?=$uniqinc?>); // area 삽입
                total_area_<?=$uniqinc?>++;
            }
        }
        if ( navigator.appName == "Microsoft Internet Explorer" )
        {
            if ( navigator.appVersion.indexOf ( "MSIE 4" ) > -1 )
            return ;
        }
        window.setTimeout("rolling_<?=$uniqinc?>()",waitingtime_<?=$uniqinc?>);
    }

    function rolling_<?=$uniqinc?>()
    { 
        if (bMouseOver_<?=$uniqinc?> && wait_flag_<?=$uniqinc?>)
        {
            for (i_<?=$uniqinc?>=0;i_<?=$uniqinc?><total_area_<?=$uniqinc?>;i_<?=$uniqinc?>++){
                tmp_<?=$uniqinc?> = document.getElementById('scroll_area_<?=$uniqinc?>'+i_<?=$uniqinc?>).style;
                tmp_<?=$uniqinc?>.top = parseInt(tmp_<?=$uniqinc?>.top)-roll_speed_<?=$uniqinc?>;
                if (parseInt(tmp_<?=$uniqinc?>.top) <= -roll_height_<?=$uniqinc?>){
                    tmp_<?=$uniqinc?>.top = roll_height_<?=$uniqinc?>*(total_area_<?=$uniqinc?>-1);
                }
                if (s_tmp_<?=$uniqinc?>++ > (s_amount_<?=$uniqinc?>-1)*roll_text_<?=$uniqinc?>.length){
                    wait_flag_<?=$uniqinc?>=false;
                    window.setTimeout("wait_flag_<?=$uniqinc?>=true;s_tmp_<?=$uniqinc?>=0;",waitingtime_<?=$uniqinc?>);
                }
            }
        }
        window.setTimeout("rolling_<?=$uniqinc?>()", 1);
    }

    function insert_area_<?=$uniqinc?>(idx_<?=$uniqinc?>, n_<?=$uniqinc?>)
    { 
        document.write('<div style="left: 0px; width: 100%; position: absolute; top: '+(roll_height_<?=$uniqinc?>*n_<?=$uniqinc?>)+'px" id="scroll_area_<?=$uniqinc?>'+n_<?=$uniqinc?>+'">\n'+roll_text_<?=$uniqinc?>[idx_<?=$uniqinc?>]+'\n</div>\n');
    }

    <?
    $i = 0;
    while (1) {
        $str = "";
        
        for ($k=0; $k<$list_mod; $k++) {
            $row = sql_fetch_array($result);
            if (!$row[it_id]) break;

            $href = "<a href='$g4[shop_path]/item.php?it_id=$row[it_id]' class=item>";
            $str .= "<table width='100%' cellpadding=1 cellspacing=0 border=0>";
            $str .= "<tr><td align=center>$href".get_it_image($row[it_id]."_s", $img_width, $img_height)."</a></td></tr>";
            $str .= "<tr><td align=center>$href".addslashes($row[it_name])."</a></td></tr>";
            $str .= "<tr><td align=center><span class=amount>".display_amount(get_amount($row), $row[it_tel_inq])."</span></td></tr>";
            $str .= "</table>";
        }

        if ($str) {
            $str = "<table width='100%' height='$marquee[height]px' cellpadding=1 cellspacing=0 border=0><tr><td>$str</td></tr></table>";
            echo "roll_text_{$uniqinc}[$i] = \"{$str}\";\n";
        }

        if (!$row[it_id]) break;

        $i++;

    }
    ?>
</script>

<div style="left: 0px; width: 100%; position: relative; top: 0px; height: <?=$marquee[height]?>px; overflow:hidden;" onMouseover="bMouseOver_<?=$uniqinc?>=0" onMouseout="bMouseOver_<?=$uniqinc?>=1" id="scroll_image_<?=$uniqinc?>">
    <script>
        var no_script_flag_<?=$uniqinc?> = false ;
        if ( navigator.appName == "Microsoft Internet Explorer" )
        {
            if ( navigator.appVersion.indexOf ( "MSIE 4" ) > -1 )
            {
                document.write ( roll_text_<?=$uniqinc?>[0] ) ;
                no_script_flag_<?=$uniqinc?> = true ;
            }
        }
        if ( no_script_flag_<?=$uniqinc?> == false )
            start_roll_<?=$uniqinc?>();
    </script>
</div>							   
