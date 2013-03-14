<?
$sub_menu = "400000";
include_once("./_common.php");

$max_limit = 7; // 몇행 출력할 것인지?

$g4[title] = " 쇼핑몰관리";
include_once ("$g4[admin_path]/admin.head.php");
?>

<table width=100%>
<tr>
	<td width=50% valign=top>
        <table width=100% cellpadding=0 cellspacing=0>
        <tr>
            <td width=70%><?=subtitle("입금완료 미배송내역")?></td>
            <td width=30% align=right><a href='./deliverylist.php?sort1=od_invoice&sort2=asc&chk_misu=1'><img src='<?=$g4[admin_path]?>/img/icon_more.gif' border=0></a>&nbsp;</td>
        </tr>
        </table>

        <table width=100% cellpadding=0 cellspacing=0>
        <tr><td colspan=5 height=2 bgcolor=#0E87F9></td></tr>
        <tr align=center class=ht>
            <td width=80>주문번호</td>
            <td>주문자</td>
            <td width=90>입금액</td>
            <td width=90>결제방법</td>
            <td width=40>수정</td>
        </tr>
        <tr><td colspan=5 height=1 bgcolor=#CCCCCC></td></tr>
        <?
        // 미수금이 없고 운송장번호가 없는 자료를 구함
        $sql = " select a.od_id, 
                        a.*, "._MISU_QUERY_."
                   from $g4[yc4_order_table] a
                   left join $g4[yc4_cart_table] b on (b.on_uid=a.on_uid)
                  group by a.od_id 
                  /*having misu <= 0 and a.od_invoice = '' and ordercancel = 0*/
                  /*having orderamount - receiptamount = 0 and a.od_invoice = ''*/
                  having misu <= 0 and a.od_invoice = ''
                  order by a.od_id desc 
                  limit $max_limit ";
        $result = sql_query($sql);
        for ($i=0; $row=sql_fetch_array($result); $i++) 
        {
            $sql1 = " select * from $g4[member_table] where mb_id = '$row[mb_id]' ";
            $row1 = sql_fetch($sql1);

            $name = get_sideview($row[mb_id], get_text($row[od_name]), $row1[mb_email], $row1[mb_homepage]);

            $settle_method = "";
            if ($row[od_settle_case])
            {
                $settle_method = $row[od_settle_case];
            }
            else
            {
                if ($row[od_receipt_bank])  $settle_method .= "무통장";
                if ($row[od_receipt_card])  $settle_method .= "카드";
                if ($row[od_receipt_point]) $settle_method .= "포인트";
            }

            $list = $i%2;
            echo "
            <tr align=center class='list$list ht'>
                <td>$row[od_id]</td>
                <td>$name</td>
                <td align=right>".display_amount($row[receiptamount])."&nbsp;</td>
                <td>$settle_method</td>
                <td>".icon("수정", "./orderform.php?od_id=$row[od_id]")."</td>
            </tr>
            ";
        }

        if ($i == 0) {
            echo "<tr><td colspan=5 align=center class=ht>자료가 없습니다.</td></tr>";
        }
        ?>
            <tr><td colspan=5 height=1 bgcolor=#CCCCCC></td></tr>
        </table>
    </td>
	<td width=1%></td>
	<td width=49% valign=top>
        <table width=100% cellpadding=0 cellspacing=0>
        <tr>
            <td width=70%><?=subtitle("미입금 주문내역")?></td>
            <td width=30% align=right><a href='./orderlist.php?sort1=receiptamount&sort2=asc'><img src='<?=$g4[admin_path]?>/img/icon_more.gif' border=0></a>&nbsp;</td>
        </tr>
        </table>

        <table width=100%  cellpadding=0 cellspacing=0>
        <tr><td colspan=5 height=2 bgcolor=#0E87F9></td></tr>
        <tr align=center class=ht>
            <td width=80>주문번호</td>
            <td>주문자</td>
            <td width=90>주문액</td>
            <td width=90>결제방법</td>
            <td width=40>수정</td>
        </tr>
        <tr><td colspan=5 height=1 bgcolor=#CCCCCC></td></tr>
        <?
        // 미수금이 있고 송장번호가 없는 자료를 구함
        $sql = " select a.od_id, 
                        a.*, "._MISU_QUERY_."
                   from $g4[yc4_order_table] a 
                   left join $g4[yc4_cart_table] b on (b.on_uid=a.on_uid)
                  group by a.od_id 
                  /* having receiptamount <= 0 */
                  having misu > 0
                  order by a.od_id desc
                  limit $max_limit ";
        $result = sql_query($sql);
        for ($i=0; $row=sql_fetch_array($result); $i++) 
        {
            $sql1 = " select * from $g4[member_table] where mb_id = '$row[mb_id]' ";
            $row1 = sql_fetch($sql1);

            $name = get_sideview($row[mb_id], get_text($row[od_name]), $row1[mb_email], $row1[mb_homepage]);

            $settle_method = "";
            if ($row[od_settle_case])
            {
                $settle_method = $row[od_settle_case];
            }
            else
            {
                if ($row[od_temp_bank])   $settle_method  .= "무통장";
                if ($row[od_temp_card])   $settle_method .= "카드";
                if ($row[od_temp_milage]) $settle_method .= "포인트";
            }

            $list = $i%2;
            echo "
            <tr align=center class='list$list ht'>
                <td><a href='./orderstatuslist.php?sort1=od_id&sel_field=od_id&search=$row[od_id]'>$row[od_id]</a></td>
                <td>$name</td>
                <td align=right>".display_amount($row[orderamount])."&nbsp;</td>
                <td>$settle_method</td>
                <td>".icon("수정", "./orderform.php?od_id=$row[od_id]")."</td>
            </tr>";
        }

        if ($i == 0)
            echo "<tr><td colspan=5 align=center class=ht>자료가 없습니다.</td></tr>";
        ?>
        <tr><td colspan=5 height=1 bgcolor=#CCCCCC></td></tr>
        </table>
    
    
    </td>
</tr>
</table><br>


<table width=100%>
<tr>
	<td width=50% valign=top>
        <table width=100% cellpadding=0 cellspacing=0>
        <tr>
            <td width=70%><?=subtitle("사용후기")?></td>
            <td width=30% align=right><a href='./itempslist.php?sort1=is_confirm&sort2=asc'><img src='<?=$g4[admin_path]?>/img/icon_more.gif' border=0></a>&nbsp;</td>
        </tr>
        </table>

        <table width=100% cellpadding=0 cellspacing=0>
        <tr><td colspan=3 height=2 bgcolor=#0E87F9></td></tr>
        <tr align=center class=ht>
        	<td width=100>회원명</td>
        	<td>제목</td>
        	<td width=40>수정</td>
        </tr>
        <tr><td colspan=3 height=1 bgcolor=#CCCCCC></td></tr>
        <?
        $sql = " select * from $g4[yc4_item_ps_table] 
                  where is_confirm = 0
                  order by is_id desc 
                  limit $max_limit ";
        $result = sql_query($sql);
        for ($i=0; $row=sql_fetch_array($result); $i++) 
        {
            $sql1 = " select * from $g4[member_table] where mb_id = '$row[mb_id]' ";
            $row1 = sql_fetch($sql1);

            $name = get_sideview($row[mb_id], get_text($row[is_name]), $row1[mb_email], $row1[mb_homepage]);

            $list = $i%2;
            echo "
            <tr align=center class='list$list ht'>
                <td align=center>$name</td>
                <td>".cut_str($row[is_subject],40)."</td>
                <td align=center>".icon("수정", "./itempsform.php?w=u&is_id=$row[is_id]")."</td>
            </tr>";
        }

        if ($i == 0)
            echo "<tr><td colspan=3 align=center class=ht>자료가 없습니다.</td></tr>";
        ?>
        <tr><td colspan=3 height=1 bgcolor=#CCCCCC></td></tr>
        </table>
    <td>
    <td width=1%></td>
	<td width=50% valign=top>
        <table width=100% cellpadding=0 cellspacing=0>
        <tr>
            <td width=70%><?=subtitle("상품문의")?></td>
            <td width=30% align=right><a href='./itemqalist.php?sort1=iq_answer&sort2=asc'><img src='<?=$g4[admin_path]?>/img/icon_more.gif' border=0></a>&nbsp;</td>
        </tr>
        </table>

        <table width=100% cellpadding=0 cellspacing=0>
        <tr><td colspan=3 height=2 bgcolor=#0E87F9></td></tr>
        <tr align=center class=ht>
        	<td width=100>회원명</td>
        	<td>제목</td>
        	<td width=40>수정</td>
        </tr>
        <tr><td colspan=3 height=1 bgcolor=#CCCCCC></td></tr>
        <?
        $sql = " select * from $g4[yc4_item_qa_table] 
                  where iq_answer = ''
                  order by iq_id desc 
                  limit $max_limit ";
        $result = sql_query($sql);
        for ($i=0; $row=sql_fetch_array($result); $i++) 
        {
            $sql1 = " select * from $g4[member_table] where mb_id = '$row[mb_id]' ";
            $row1 = sql_fetch($sql1);

            $name = get_sideview($row[mb_id], get_text($row[iq_name]), $row1[mb_email], $row1[mb_homepage]);

            $list = $i%2;
            echo "
            <tr align=center class='list$list ht'>
                <td align=center>$name</td>
                <td>".cut_str($row[iq_subject],40)."</td>
                <td align=center>".icon("수정", "./itemqaform.php?w=u&iq_id=$row[iq_id]")."</td>
            </tr>";
        }

        if ($i == 0)
            echo "<tr><td colspan=3 align=center class=ht>자료가 없습니다.</td></tr>";
        ?>
        <tr><td colspan=3 height=1 bgcolor=#CCCCCC></td></tr>
        </table>
    </td>
</tr>
</table><br>

<?
include_once ("$g4[admin_path]/admin.tail.php");
?>
