<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
?>

<!-- 상품문의 -->
<a name="qa"></a>
<div id="item_qa" style="display:block;">
<table width=100% cellpadding=0 cellspacing=0>
<tr><td rowspan=2 width=31 valign=top bgcolor=#A7DFE1><img src='<?=$g4[shop_img_path]?>/item_t03.gif'></td><td height=2 bgcolor=#A7DFE1></td></tr> 
<tr><td style='padding:15px'>
        <table width=100% cellpadding=0 cellspacing=0 border=0>
        <tr>
            <td width=11><img src='<?=$g4[shop_img_path]?>/corner01.gif'></td>
            <td valign=top>
                <table width=100% height=31 cellpadding=0 cellspacing=0 border=0>
                <tr align=center>
                    <td width=40 background='<?=$g4[shop_img_path]?>/box_bg01.gif'>번호</td>
                    <td background='<?=$g4[shop_img_path]?>/box_bg01.gif'>제목</td>
                    <td width=80 background='<?=$g4[shop_img_path]?>/box_bg01.gif'>작성자</td>
                    <td width=100 background='<?=$g4[shop_img_path]?>/box_bg01.gif'>작성일</td>
                    <td width=80 background='<?=$g4[shop_img_path]?>/box_bg01.gif'>답변</td>
                </tr>
                </table></td>
            <td width=11><img src='<?=$g4[shop_img_path]?>/corner02.gif'></td>
        </tr>
        <?
        $sql_common = " from $g4[yc4_item_qa_table] where it_id = '$it[it_id]' ";

        // 테이블의 전체 레코드수만 얻음
        $sql = " select COUNT(*) as cnt " . $sql_common;
        $row = sql_fetch($sql);
        $qa_total_count = $row[cnt];

        $qa_total_page  = ceil($qa_total_count / $qa_page_rows); // 전체 페이지 계산
        if ($qa_page == "") $qa_page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
        $qa_from_record = ($qa_page - 1) * $qa_page_rows; // 시작 레코드 구함

        $sql = "select *
                 $sql_common
                 order by iq_id desc
                 limit $qa_from_record, $qa_page_rows ";
        $result = sql_query($sql);
        for ($i=0; $row=sql_fetch_array($result); $i++) 
        {
            if ($i > 0)
                echo "<tr><td colspan=3 background='$g4[shop_img_path]/dot_line.gif' height='1'></td></tr>";

            $num = $qa_total_count - ($qa_page - 1) * $qa_page_rows - $i;

            $iq_name  = get_text($row[iq_name]);
            $iq_subject  = conv_subject($row[iq_subject],50,"…");
            $iq_question = conv_content($row[iq_question],0);
            $iq_answer   = conv_content($row[iq_answer],0);

            $iq_time = substr($row[iq_time], 2, 14);

            //$qa = "<img src='$g4[shop_img_path]/icon_poll_q.gif' border=0>";
            //if ($row[iq_answer]) $qa .= "<img src='$g4[shop_img_path]/icon_answer.gif' border=0>";
            //$qa = "$qa";

            $icon_answer = "";
            $iq_answer = "";
            if ($row[iq_answer]) 
            {
                $iq_answer = "<br><hr width=100% size=0><img src='$g4[shop_img_path]/icon_answer.gif' border=0 align=left><font color=#466C8A> : ".conv_content($row[iq_answer],0) . "</font>";
                $icon_answer = "<a href='javascript:;' onclick=\"qa_menu('iq$i')\"><img src='$g4[shop_img_path]/icon_answer.gif' border=0></a>";
            }
            
            echo "
            <tr>
                <td width=11 background='$g4[shop_img_path]/box_bg02.gif'></td>
                <td valign=top>
                    <table width=100% cellpadding=0 cellspacing=0 border=0>
                    <tr align=center>
                        <td width=40 height=25>$num</td>
                        <td align=left>
                            <b><a href='javascript:;' onclick=\"qa_menu('iq$i')\"><b>$iq_subject</b></a></b>
                        <td width=80>$iq_name</td>
                        <td width=100>$iq_time</td>
                        <td width=80>$icon_answer</td>
                    </tr>
                    </table>

                    <div id='iq$i' style='display:none;'> 
                    <table width=100% cellpadding=0 cellspacing=0 border=0>
                    <tr>
                        <td style='padding:10px;' class=lh>{$iq_question}</td>
                    </tr>";
            
            if ($iq_answer) 
                echo "
                    <tr>
                        <td style='padding:10px;' class=lh>$iq_answer</td>
                    </tr>";

            echo "
                <tr>
                    <td align=right height=30>
                        <textarea id='tmp_iq_id{$i}' style='display:none;'>{$row[iq_id]}</textarea>
                        <textarea id='tmp_iq_name{$i}' style='display:none;'>{$row[iq_name]}</textarea>
                        <textarea id='tmp_iq_subject{$i}' style='display:none;'>{$row[iq_subject]}</textarea>
                        <textarea id='tmp_iq_question{$i}' style='display:none;'>{$row[iq_question]}</textarea>";

            if ($row[mb_id] == $member[mb_id] && !$iq_answer)
            {
                echo "<a href='javascript:itemqa_update({$i});'><span class=small><b>수정</b></span></a>&nbsp;";
                echo "<a href='javascript:itemqa_delete(fitemqa_password{$i}, {$i});'><span class=small><b>삭제</b></span></a>&nbsp;";
            }

            echo "
                    </td>
                </tr>
                <!-- 상품문의 삭제 패스워드 입력 폼 -->
                <tr id='itemqa_password{$i}' style='display:none;'>
                    <td align=right height=30>
                        <form name='fitemqa_password{$i}' method='post' action='./itemqaupdate.php' autocomplete=off style='padding:0px;'>
                        <input type=hidden name=w value=''>
                        <input type=hidden name=iq_id value=''>
                        <input type=hidden name=it_id value='{$it[it_id]}'>
                        패스워드 : <input type=password class=ed name=iq_password required itemname='패스워드'> 
                        <input type=image src='{$g4[shop_img_path]}/btn_confirm.gif' border=0 align=absmiddle></a>
                        </form>
                    </td>
                </tr>";

            echo "                            
                    </table>
                    </div></td>
                </td>
                <td width=11 background='$g4[shop_img_path]/box_bg03.gif'>&nbsp;</td></tr>
            </tr>
            ";
        }


        if (!$i)
        {
            echo "
            <tr>
                <td width=11 background='$g4[shop_img_path]/box_bg02.gif'></td>
                <td height=100 align=center class=lh>
                    이 상품에 대한 질문이 아직 없습니다.<br>
                    궁금하신 사항은 이곳에 질문하여 주십시오.</td>
                <td width=11 background='$g4[shop_img_path]/box_bg03.gif'>&nbsp;</td></tr>
            </tr>";
        }


        $qa_pages = get_paging(10, $qa_page, $qa_total_page, "./item.php?it_id=$it_id&$qstr&qa_page=", "#qa");
        if ($qa_pages)
        {
            echo "<tr><td colspan=3 background='$g4[shop_img_path]/dot_line.gif'></td></tr>";
            echo "<tr>";
            echo "<td width=11 background='$g4[shop_img_path]/box_bg02.gif'></td>";
            echo "<td height=22 align=center>$qa_pages</td>";
            echo "<td width=11 background='$g4[shop_img_path]/box_bg03.gif'>&nbsp;</td></tr>";
            echo "</tr>";
        }
        ?>
        <tr>
            <td width=11><img src='<?=$g4[shop_img_path]?>/corner03.gif'></td>
            <td width=100% background='<?=$g4[shop_img_path]?>/box_bg04.gif'></td>
            <td width=11><img src='<?=$g4[shop_img_path]?>/corner04.gif'></td>
        </tr>
        </table>


        <table width=100% cellpadding=0 cellspacing=0>
        <tr><td colspan=2 height=35>* 이 상품에 대한 궁금한 사항이 있으신 분은 질문해 주십시오.
            <input type=image src='<? echo "$g4[shop_img_path]/btn_qa.gif"?>' onclick="itemqa_insert(itemqa);" align=absmiddle></td></tr>
        </table>

        <!-- 상품문의 폼-->
        <div id=itemqa style='display:none;'>
        <form name="fitemqa" method="post" onsubmit="return fitemqa_submit(this);" autocomplete=off style="padding:0px;">
        <input type=hidden name=w value=''>
        <input type=hidden name=token value='<?=$token?>'>
        <input type=hidden name=iq_id value=''>
        <input type=hidden name=it_id value='<?=$it[it_id]?>'>
        <table width=100% cellpadding=0 cellspacing=0>
        <tr><td height=2 bgcolor=#63BCC0 colspan=2></td></tr>

        <? if (!$is_member) { ?>
        <tr bgcolor=#fafafa>
            <td height=30 align=right>이름&nbsp;</td>
            <td>&nbsp;<input type="text" name="iq_name" class=ed maxlength=20 minlength=2 required itemname="이름"></td></tr>
        <tr bgcolor=#fafafa>
            <td height=30 align=right>패스워드&nbsp;</td>
            <td>&nbsp;<input type="password" name="iq_password" class=ed maxlength=20 minlength=3 required itemname="패스워드"> 
                <span class=small>패스워드는 최소 3글자 이상 입력하십시오.</span></td></tr>
        <? } ?>

        <tr bgcolor=#fafafa>
            <td height=30 align=right>제목&nbsp;</td>
            <td>&nbsp;<input type="text" name="iq_subject" style='width:90%;' class=ed required itemname="제목" maxlength=100></td></tr>
        <tr bgcolor=#fafafa>
            <td align=right>내용&nbsp;</td>
            <td>&nbsp;<textarea name="iq_question" rows="7" style='width:90%;' class=ed required itemname="내용"></textarea></td></tr>
        <tr bgcolor=#fafafa>
            <td width=100 align=right><img id='kcaptcha_image_qa' /></td>
            <td>
                &nbsp;<input type='text' name='iq_key' class='ed' required itemname='자동등록방지용 코드'>
                &nbsp;* 왼쪽의 자동등록방지 코드를 입력하세요.</td></tr>
        <tr><td height=5 colspan=2></td></tr>
        <tr><td height=2 bgcolor=#63bcc0 colspan=2></td></tr>
        <tr><td colspan=2 align=right height=30><input type=image src='<?=$g4[shop_img_path]?>/btn_confirm.gif' border=0></td></tr>
        </table>
        </form>
        <br><br>
        </div>
    </td>
</tr>
<tr><td colspan=2 height=1></td></tr>
</table>
</div>


<script type="text/javascript">
function fitemqa_submit(f) 
{
    if (!check_kcaptcha(f.iq_key)) { 
        return false; 
    } 

    f.action = "itemqaupdate.php";
    return true;
}

function itemqa_insert()
{
    /*
    if (!g4_is_member) {
        alert("로그인 하시기 바랍니다.");
        return;
    }
    */

    var f = document.fitemqa;
    var id = document.getElementById('itemqa');

    id.style.display = 'block';

    f.w.value = '';
    f.iq_id.value = '';
    if (!g4_is_member)
    {
        f.iq_name.value = '';
        f.iq_name.readOnly = false;
        f.iq_password.value = '';
    }
    f.iq_subject.value = '';
    f.iq_question.value = '';
}

function itemqa_update(idx)
{
    var f = document.fitemqa;
    var id = document.getElementById('itemqa');

    id.style.display = 'block';

    f.w.value = 'u';
    f.iq_id.value = document.getElementById('tmp_iq_id'+idx).value;
    if (!g4_is_member)
    {
        f.iq_name.value = document.getElementById('tmp_iq_name'+idx).value;
        f.iq_name.readOnly = true;
    }
    f.iq_subject.value = document.getElementById('tmp_iq_subject'+idx).value;
    f.iq_question.value = document.getElementById('tmp_iq_question'+idx).value;
}

function itemqa_delete(f, idx)
{
    var id = document.getElementById('itemqa');

    f.w.value = 'd';
    f.iq_id.value = document.getElementById('tmp_iq_id'+idx).value;

    if (g4_is_member)
    {
        if (confirm("삭제하시겠습니까?"))
            f.submit();
    }
    else 
    {
        id.style.display = 'none';
        document.getElementById('itemqa_password'+idx).style.display = 'block';
    }
}
</script>
<!-- 상품문의 end -->
