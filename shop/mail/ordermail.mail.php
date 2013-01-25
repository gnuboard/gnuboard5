<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=<?=$g4['charset']?>">
<title>주문내역 처리 현황</title>
</head>

<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<table width="600" border="0" cellspacing="0" cellpadding="0">
<tr>
    <td width="25" height="25">&nbsp;</td>
    <td height="25">&nbsp;</td>
    <td width="25" height="25">&nbsp;</td>
</tr>
<tr>
    <td width="25" valign="top"><img src="<?=$g4['shop_url']?>/mail/img/mail_left.gif" width="25" height="281"></td>
    <td class="line" > 
        <table width="548" border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td height="59" background="<?=$g4['shop_url']?>/mail/img/mail_bg2.gif">
                <table width="500" border="0" cellspacing="0" cellpadding="0" align="center">
                <tr>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td height="16"><div align="right"><strong><font color="#02253A">주문내역 처리 현황</font></strong></div></td>
                </tr>
                </table>
            </td>
        </tr>
        </table>
        <p>

        <table width="500" border="0" align="center" cellpadding="0" cellspacing="1">
        <tr>
            <td>

                <? if (count($cart_list)) { ?>
                <table width="500" border="0" cellspacing="0" cellpadding="3">
                <tr>
                    <td width="250" height="25"  background="<?=$g4['shop_url']?>/mail/img/mail_bg1.gif"> <div align="center">품 명</div></td>
                    <td width="100" height="25"  background="<?=$g4['shop_url']?>/mail/img/mail_bg1.gif"> <div align="center">선택옵션</div></td>
                    <td width="100" height="25"  background="<?=$g4['shop_url']?>/mail/img/mail_bg1.gif"> <div align="center">처리상태</div></td>
                    <td width="50"  height="25"  background="<?=$g4['shop_url']?>/mail/img/mail_bg1.gif"> <div align="center">수 량</div></td>
                </tr>
                
                <? for ($i=0; $i<count($cart_list); $i++) { ?>
                <tr>
                    <td width="250" height="25"><a href="<?=$g4['shop_url']?>/item.php?it_id=<?=$cart_list[$i][it_id]?>" target=_blank><?=$cart_list[$i][it_name]?></a></td>
                    <td width="100" height="25"><div align="center"><?=$cart_list[$i][it_opt]?></div></td>
                    <td width="100" height="25"><div align="center"><?=$cart_list[$i][ct_status]?></div></td>
                    <td width="50"  height="25"><div align="center"><?=$cart_list[$i][ct_qty]?></div></td>
                </tr>
                <tr>
                    <td colspan=4 height=1 bgcolor=#EEEEEE></td>
                </tr>
                <? } // end for ?>

                </table>
                <? } // end if ?>

                <? if (count($card_list)) { ?>
                <p><img src="<?=$g4['shop_url']?>/mail/img/mail_icon1.gif" width="13" height="11"> 신용카드 입금을 확인하였습니다</p>
                <table width="500" border="0" cellpadding="3" cellspacing="1" bgcolor="#868F98">
                <tr bgcolor="#FFFFFF">
                <td width="130" height=25>&nbsp;&nbsp;&nbsp;승인일시</td>
                <td width="370">&nbsp;<?=$card_list[od_card_time]?></td>
                </tr>
                <tr bgcolor="#FFFFFF">
                <td width="130" height=25>&nbsp;&nbsp;&nbsp;승인금액</td>
                <td width="370">&nbsp;<?=$card_list[od_receipt_card]?></td>
                </tr>
                </table>
                <? } ?>


                <? if (count($bank_list)) { ?>
                <br />
                <p><img src="<?=$g4['shop_url']?>/mail/img/mail_icon1.gif" width="13" height="11"> 무통장 입금을 확인하였습니다</p>
                <table width="500" border="0" cellpadding="3" cellspacing="1" bgcolor="#868F98">
                <tr bgcolor="#FFFFFF">
                <td width="130" height=25>&nbsp;&nbsp;&nbsp;확인일시</td>
                <td width="370">&nbsp;<?=$bank_list[od_bank_time]?></td>
                </tr>
                <tr bgcolor="#FFFFFF">
                <td width="130" height=25>&nbsp;&nbsp;&nbsp;입금액</td>
                <td width="370">&nbsp;<?=$bank_list[od_receipt_bank]?></td>
                </tr>
                <tr bgcolor="#FFFFFF">
                <td height=25>&nbsp;&nbsp;&nbsp;입금자명</td>
                <td>&nbsp;<?=$bank_list[od_deposit_name]?></td>
                </tr>
                </table>
                <? } ?>

                <? if (count($point_list)) { ?>
                <br />
                <p><img src="<?=$g4['shop_url']?>/mail/img/mail_icon1.gif" width="13" height="11"> 포인트 입금을 확인하였습니다</p>
                <table width="500" border="0" cellpadding="3" cellspacing="1" bgcolor="#868F98">
                <tr bgcolor="#FFFFFF">
                <td width="130" height=25>&nbsp;&nbsp;&nbsp;확인일시</td>
                <td width="370">&nbsp;<?=$point_list[od_time]?></td>
                </tr>
                <tr bgcolor="#FFFFFF">
                <td width="130" height=25>&nbsp;&nbsp;&nbsp;포인트</td>
                <td width="370">&nbsp;<?=$point_list[od_receipt_point]?></td>
                </tr>
                </table>
                <? } ?>

                <? if (count($delivery_list)) { ?>
                <br />
                <p><img src="<?=$g4['shop_url']?>/mail/img/mail_icon1.gif" width="13" height="11"> 다음과 같이 배송 하였습니다</p>
                <table width="500" border="0" cellpadding="3" cellspacing="1" bgcolor="#868F98">
                <tr bgcolor="#FFFFFF">
                <td width="130" height=25>&nbsp;&nbsp;&nbsp;배송회사</td>
                <td width="370">&nbsp;<a href='<?=$delivery_list[dl_url]?>' target=_blank><?=$delivery_list[dl_company]?></a></td>
                </tr>
                <tr bgcolor="#FFFFFF">
                <td width="130" height=25>&nbsp;&nbsp;&nbsp;운송장번호</td>
                <td width="370">&nbsp;<?=$delivery_list[od_invoice]?></td>
                </tr>
                <tr bgcolor="#FFFFFF">
                <td height=25>&nbsp;&nbsp;&nbsp;배송일시</td>
                <td>&nbsp;<?=$delivery_list[od_invoice_time]?></td>
                </tr>
                <tr bgcolor="#FFFFFF">
                <td height=25>&nbsp;&nbsp;&nbsp;대표전화</td>
                <td>&nbsp;<?=$delivery_list[dl_tel]?></td>
                </tr>
                </table>
                <? } ?>

                <p><?=$addmemo?>
                <br>

                <table width="500" border="0" cellpadding="0" cellspacing="0">
                <tr>
                <td height="25" bgcolor="#ECF1F6">
                <div align="center">[<a href="<?=$g4[url]?>" target=_blank><?=$config[cf_title]?></a>] 에서 드리는 메일입니다.</div></td>
                </tr>
                </table>

            </td>
        </tr>
        </table>
        <br>
    
    </td>
    <td width="25" valign="top"><img src="<?=$g4['shop_url']?>/mail/img/mail_right.gif" width="25" height="281"></td>
</tr>
</table>

</body>
</html>
