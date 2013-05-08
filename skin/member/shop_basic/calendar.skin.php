<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
?>

<table border=0 cellpadding=4 align=center width=100%>
<form name=fcalendar autocomplete=off>
<input type=hidden name=fld value='<?=$fld?>'>
<input type=hidden name=cur_date value='<?=$cur_date?>'>
<input type=hidden id=delimiter name=delimiter value='<?=$delimiter?>'>
<tr><td align=center height=30>
        <a href='<?=$yyyy_before_href?>'><<</a>&nbsp;
        <a href='<?=$mm_before_href?>'><</a>
        <?=$yyyy_select?>
        <?=$mm_select?>
        <a href='<?=$mm_after_href?>'>></a>&nbsp;
        <a href='<?=$yyyy_after_href?>'>>></a>
    </td>
</tr>
<tr>
    <td align=center>
        <table border=0 cellpadding=4 cellspacing=0 width=100%>
        <tr align=center>
            <td width=14% style="color:<?=$sunday_color?>"><?=$yoil[0];?></td>
            <td width=14% style="color:<?=$weekday_color?>"><?=$yoil[1];?></td>
            <td width=14% style="color:<?=$weekday_color?>"><?=$yoil[2];?></td>
            <td width=14% style="color:<?=$weekday_color?>"><?=$yoil[3];?></td>
            <td width=14% style="color:<?=$weekday_color?>"><?=$yoil[4];?></td>
            <td width=14% style="color:<?=$weekday_color?>"><?=$yoil[5];?></td>
            <td width=14% style="color:<?=$saturday_color?>"><?=$yoil[6];?></td>
        </tr>
        <?
        $cnt = $day = 0;
        for ($i=0; $i<6; $i++)
        {
            echo "<tr>";
            for ($k=0; $k<7; $k++)
            {
                $cnt++;

                echo "<td align=center>";

                if ($cnt > $dt[wday])
                {
                    $day++;
                    if ($day <= $last_day)
                    {
                        $mm2 = substr("0".$mm,-2);
                        $day2 =  substr("0".$day,-2);

                        echo "<table width=100% height=100% cellpadding=0 cellspacing=0><tr><td id='id$i$k' onclick=\"date_send('$yyyy', '$mm2', '$day2', '$k', '$yoil[$k]');\" align=center style='cursor:pointer;'>$day</td></tr></table>";

                        if ($k==0)
                            echo "<script language='JavaScript'>document.getElementById('id$i$k').style.color='$sunday_color';</script>";
                        else if ($k==6)
                            echo "<script language='JavaScript'>document.getElementById('id$i$k').style.color='$saturday_color';</script>";
                        else
                            echo "<script language='JavaScript'>document.getElementById('id$i$k').style.color='$weekday_color';</script>";

                        $tmp_date = $yyyy.substr("0".$mm,-2).substr("0".$day,-2);

                        $tmp = $mm2."-".$day2;
                        if ($nal[$tmp])
                        {
                            $title = trim($nal[$tmp][1]);
                            //echo $title;
                            echo "<script language='JavaScript'>document.getElementById('id$i$k').title='{$title}';</script>";
                            if (trim($nal[$tmp][2]) == "*") 
                                echo "<script language='JavaScript'>document.getElementById('id$i$k').style.color='$sunday_color';</script>";
                        }
                        
                        // 오늘이라면
                        if ($today[year] == $yyyy && $today[mon] == $mm && $today[mday] == $day)
                        {
                            echo "<script language='JavaScript'>document.getElementById('id$i$k').style.backgroundColor='$today_bgcolor';</script>";
                            echo "<script language='JavaScript'>document.getElementById('id$i$k').title+='[오늘]';</script>";
                        }
                        // 선택일(넘어온 값) 이라면
                        else if ($tmp_date == $cur_date)
                        {
                            echo "<script language='JavaScript'>document.getElementById('id$i$k').style.backgroundColor='$select_bgcolor';</script>";
                            echo "<script language='JavaScript'>document.getElementById('id$i$k').title+='[선택일]';</script>";
                        }
                    } else
                        echo "&nbsp;";
                } else
                    echo "&nbsp;";
                echo "</td>";
            }
            echo "</tr>\n";
            if ($day >= $last_day)
                break;
        }
        ?>
        </table>
    </td>
<tr>
    <td align=center height=30>
        <span style='background-color:<?=$today_bgcolor?>;'>
        <?="<a href=\"javascript:date_send('{$today[year]}', '{$mon}', '{$mday}', '{$today[wday]}', '{$yoil[$today[wday]]}');\">";?>
        오늘 : <?="{$today[year]}년 {$today[mon]}월 {$today[mday]}일 ({$yoil[$today[wday]]})";?></a>
        </span></td>
</tr>
</form>
</table>
