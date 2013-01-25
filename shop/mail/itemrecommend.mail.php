<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=<?=$g4['charset']?>">
<title>추천 상품</title>
</head>

<style>
body, th, td, form, input, select, text, textarea, caption { font-size: 12px; font-family:굴림;}
.line {border: 1px solid #868F98;}
</style>

<body leftmargin="0" topmargin="20" marginwidth="0" marginheight="20">
<table width=600 cellpadding=0 cellspacing=0>
    <tr>
        <td rowspan=2 align=right valign=top><img src="<?=$g4['shop_url']?>/mail/img/mail_left.gif" width="25" height="281"></td>
        <td height=1 colspan=3 bgcolor=#E3E3E3></td>
        <td rowspan=2 valign=top><img src="<?=$g4['shop_url']?>/mail/img/mail_right.gif" width="25" height="281"></td>
    </tr>
    <tr>
        <td valign=top align=center style='padding-top:15px'>
            <table width=95% cellpadding=1 cellspacing=0 bgcolor=#C5C5C5>
                <tr>
                    <td>
                        <table width=100% cellpadding=10 cellspacing=0> 
                            <tr><td bgcolor=#2396C5 height=50 align=right><font color="#FFFFFF"><B>추천상품메일</B></font></td></tr>
                            <tr><td bgcolor=#FFFFFF style="padding-left:15px; padding-bottom:15px; text-align:left;" height=30><font color=#5AB0D4><B><?=$from_name?> </B></font>님께서 추천하신 상품입니다.
                                    <table width=98% cellpadding=1 cellspacing=0 bgcolor=#DFDFDF>
                                        <tr><td>
                                                <table width=100% cellpadding=20 cellspacing=0 bgcolor=#F8F8F8>
                                                    <tr><td style="text-align:left;" height=30><b><a href='<?="$g4[shop_url]/item.php?it_id=$it_id"?>' target=_top><?=$it_name?></a></b></td></tr>
                                                    <tr>
                                                        <td valign=top style="padding:10px;line-height:150%; text-align:left;"><a href='<?="$g4[shop_url]/item.php?it_id=$it_id"?>' target=_top><img src='<?="$g4[url]/data/item/$it_mimg"?>' border=0 align=left hspace="10"></a><?=$content?></td>
                                                    </tr>
                                                    <tr><td style="text-align:left;">※ 이 메일은 광고 메일이 아닙니다.</td></tr>
                                                </table>
                                            </td></tr>
                                    </table>
                                </td></tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
