<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
include_once(G4_LIB_PATH.'/thumb.lib.php');
?>

<!-- 사용후기 -->
<a name="use"></a>
<div id='item_use' style='display:block;'>
<table width=100% cellpadding=0 cellspacing=0>
<tr><td rowspan=2 width=31 valign=top bgcolor=#BDD3E5><img src='<?=G4_SHOP_URL?>/img/item_t02.gif'></td><td height=2 bgcolor=#BDD3E5></td></tr>
<tr><td style='padding:15px;'>
        <table width=100% cellpadding=0 cellspacing=0 border=0>
        <tr>
            <td width=11><img src='<?=G4_SHOP_URL?>/img/corner01.gif'></td>
            <td valign=top>
                <table width=100% height=31 cellpadding=0 cellspacing=0 border=0>
                <tr align=center>
                    <td width=40 background='<?=G4_SHOP_URL?>/img/box_bg01.gif'>번호</td>
                    <td background='<?=G4_SHOP_URL?>/img/box_bg01.gif'>제목</td>
                    <td width=80 background='<?=G4_SHOP_URL?>/img/box_bg01.gif'>작성자</td>
                    <td width=100 background='<?=G4_SHOP_URL?>/img/box_bg01.gif'>작성일</td>
                    <td width=80 background='<?=G4_SHOP_URL?>/img/box_bg01.gif'>평가점수</td>
                </tr>
                </table></td>
            <td width=11><img src='<?=G4_SHOP_URL?>/img/corner02.gif'></td>
        </tr>
        <?
        $sql_common = " from {$g4['yc4_item_ps_table']} where it_id = '{$it['it_id']}' and is_confirm = '1' ";

        // 테이블의 전체 레코드수만 얻음
        $sql = " select COUNT(*) as cnt " . $sql_common;
        $row = sql_fetch($sql);
        $use_total_count = $row['cnt'];

        $use_total_page  = ceil($use_total_count / $use_page_rows); // 전체 페이지 계산
        if ($use_page == "") $use_page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
        $use_from_record = ($use_page - 1) * $use_page_rows; // 시작 레코드 구함

        $sql = "select * $sql_common order by is_id desc limit $use_from_record, $use_page_rows ";
        $result = sql_query($sql);

        for ($i=0; $row=sql_fetch_array($result); $i++)
        {
            if ($i > 0)
                echo "<tr><td colspan=3 background='".G4_SHOP_URL."/img/dot_line.gif' height='1'></td></tr>";

            $num = $use_total_count - ($use_page - 1) * $use_page_rows - $i;

            $star = get_star($row['is_score']);

            $is_name = get_text($row['is_name']);
            $is_subject = conv_subject($row['is_subject'],50,"…");
            //$is_content = conv_content($row[is_content],0);
            $is_content = $row['is_content'];
            //$is_content = preg_replace_callback("#<img[^>]+>#iS", "g4_thumb", $is_content);

            $thumb = new g4_thumb(G4_DATA_PATH.'/itemuse', 500);
            $is_content = $thumb->run($is_content);

            $is_time = substr($row['is_time'], 2, 14);

            echo "
            <tr>
                <td width=11 background='".G4_SHOP_URL."/img/box_bg02.gif'></td>
                <td valign=top>
                    <table width=100% cellpadding=0 cellspacing=0 border=0>
                    <tr align=center>
                        <td width=40 height=25>$num</td>
                        <td align=left>
                            <b><a href='javascript:;' onclick=\"use_menu('is$i')\"><b>$is_subject</b></a></b>
                        <td width=80>$is_name</td>
                        <td width=100>$is_time</td>
                        <td width=80><img src='".G4_SHOP_URL."/img/star{$star}.gif' border=0></td>
                    </tr>
                    </table>

                    <div id='is$i' style='display:none;'>
                    <table width=100% cellpadding=0 cellspacing=0 border=0>
                    <tr>
                        <td style='padding:10px;' class=lh>{$is_content}</td>
                    </tr>
                    <tr>
                        <td align=right height=30>
                            <textarea id='tmp_is_id{$i}' style='display:none;'>{$row['is_id']}</textarea>
                            <textarea id='tmp_is_name{$i}' style='display:none;'>{$row['is_name']}</textarea>
                            <textarea id='tmp_is_subject{$i}' style='display:none;'>{$row['is_subject']}</textarea>
                            <textarea id='tmp_is_content{$i}' style='display:none;'>{$row['is_content']}</textarea>";

            if ($row[mb_id] == $member[mb_id])
            {
                //echo "<a href='javascript:itemuse_update({$i});'><span class=small><b>수정</b></span></a>&nbsp;";
                echo "<a href=\"javascript:itemusewin('is_id={$row['is_id']}&w=u');\"><span class=small><b>수정</b></span></a>&nbsp;";
                echo "<a href=\"javascript:itemuse_delete(fitemuse_password{$i}, {$i});\"><span class=small><b>삭제</b></span></a>&nbsp;";
            }

            echo "
                        </td>
                    </tr>
                    <!-- 사용후기 삭제 패스워드 입력 폼 -->
                    <tr id='itemuse_password{$i}' style='display:none;'>
                        <td align=right height=30>
                            <form name='fitemuse_password{$i}' method='post' action='./itemuseupdate.php' autocomplete=off style='padding:0px;'>
                            <input type=hidden name=w value=''>
                            <input type=hidden name=is_id value=''>
                            <input type=hidden name=it_id value='{$it['it_id']}'>
                            패스워드 : <input type=password class=ed name=is_password required itemname='패스워드'>
                            <input type=image src='".G4_SHOP_URL."/img/btn_confirm.gif' border=0 align=absmiddle></a>
                            </form>
                        </td>
                    </tr>
                    </table>
                    </div></td>
                <td width=11 background='".G4_SHOP_URL."/img/box_bg03.gif'>&nbsp;</td></tr>
            </tr>
            ";
        }

        if (!$i)
        {
            echo "
            <tr>
                <td width=11 background='".G4_SHOP_URL."/img/box_bg02.gif'></td>
                <td height=100 align=center class=lh>
                    이 상품에 대한 사용후기가 아직 없습니다.<br>
                    사용후기를 작성해 주시면 다른 분들께 많은 도움이 됩니다.</td>
                <td width=11 background='".G4_SHOP_URL."/img/box_bg03.gif'>&nbsp;</td></tr>
            </tr>";
        }

        $use_pages = get_paging(10, $use_page, $use_total_page, "./item.php?it_id=$it_id&$qstr&use_page=", "#use");
        if ($use_pages)
        {
            echo "<tr><td colspan=3 background='".G4_SHOP_URL."/img/dot_line.gif'></td></tr>";
            echo "<tr>";
            echo "<td width=11 background='".G4_SHOP_URL."/img/box_bg02.gif'></td>";
            echo "<td height=22 align=center>$use_pages</td>";
            echo "<td width=11 background='".G4_SHOP_URL."/img/box_bg03.gif'>&nbsp;</td></tr>";
            echo "</tr>";
        }
        ?>
        <tr>
            <td width=11><img src='<?=G4_SHOP_URL?>/img/corner03.gif'></td>
            <td width=100% background='<?=G4_SHOP_URL?>/img/box_bg04.gif'></td>
            <td width=11><img src='<?=G4_SHOP_URL?>/img/corner04.gif'></td>
        </tr>
        </table>



        <table width=100% cellpadding=0 cellspacing=0>
        <tr><td colspan=2 height=35>* 이 상품을 사용해 보셨다면 사용후기를 써 주십시오.
            <!-- <input type=image src='<?="$g4[shop_img_path]/btn_story.gif"?>' onclick="itemuse_insert();" align=absmiddle></td></tr> -->
            <input type=image src='<?=G4_SHOP_URL?>/img/btn_story.gif' onclick="itemusewin('it_id=<?=$it_id?>');" align=absmiddle></td></tr>
        </table>

<script>
function itemusewin(query_string)
{
    window.open("./itemusewin.php?"+query_string, "itemusewin", "width=800,height=700");
}
</script>

        <? /*
        <!-- 사용후기 폼 -->
        <div id=itemuse style='display:none;'>
        <form name="fitemuse" method="post" onsubmit="return fitemuse_submit(this);" autocomplete=off style="padding:0px;">
        <input type=hidden name=w value=''>
        <input type=hidden name=token value='<?=$token?>'>
        <input type=hidden name=is_id value=''>
        <input type=hidden name=it_id value='<?=$it['it_id']?>'>
        <table width=100% cellpadding=0 cellspacing=0 border=0>
        <tr><td height=2 bgcolor=#6EA7D3 colspan=2></td></tr>

        <? if (!$is_member) { ?>
        <tr bgcolor=#fafafa>
            <td height=30 align=right>이름&nbsp;</td>
            <td>&nbsp;<input type="text" name="is_name" class=ed maxlength=20 minlength=2 required itemname="이름"></td></tr>
        <tr bgcolor=#fafafa>
            <td height=30 align=right>패스워드&nbsp;</td>
            <td>&nbsp;<input type="password" name="is_password" class=ed maxlength=20 minlength=3 required itemname="패스워드">
                <span class=small>패스워드는 최소 3글자 이상 입력하십시오.</span></td></tr>
        <? } ?>

        <tr bgcolor=#fafafa>
            <td height=30 align=right>제목&nbsp;</td>
            <td>&nbsp;<input type="text" name="is_subject" style="width:90%;" class=ed required itemname="제목"></td></tr>
        <tr bgcolor=#fafafa>
            <td align=right>내용&nbsp;</td>
            <td>&nbsp;<textarea name="is_content" rows="7" style="width:90%;" class=ed required itemname="내용"></textarea></td></tr>
        <tr bgcolor=#fafafa>
            <td height=30 align=right>평가&nbsp;</td>
            <td>
                <input type=radio name=is_score value='10' checked><img src='<?=G4_SHOP_URL?>/img/star5.gif' align=absmiddle>
                <input type=radio name=is_score value='8'><img src='<?=G4_SHOP_URL?>/img/star4.gif' align=absmiddle>
                <input type=radio name=is_score value='6'><img src='<?=G4_SHOP_URL?>/img/star3.gif' align=absmiddle>
                <input type=radio name=is_score value='4'><img src='<?=G4_SHOP_URL?>/img/star2.gif' align=absmiddle>
                <input type=radio name=is_score value='2'><img src='<?=G4_SHOP_URL?>/img/star1.gif' align=absmiddle></td></tr>
        <tr bgcolor=#fafafa>
            <td colspan="2"><?=$captcha_html?>abc</td>
        </tr>
        <tr><td height=2 bgcolor=#6ea7d3 colspan=2></td></tr>
        <tr><td colspan=2 align=right height=30><input type=image src='<?=G4_SHOP_URL?>/img/btn_confirm.gif' border=0></a></td></tr>
        </table>
        </form>
        <br><br>
        </div>
        */ ?>
    </td>
</tr>
<tr><td colspan=2 height=1></td></tr>
</table>
</div>


<script type="text/javascript">
function fitemuse_submit(f)
{
    <? echo chk_captcha_js(); ?>

    f.action = "itemuseupdate.php"
    return true;
}

function itemuse_insert()
{
    /*
    if (!g4_is_member) {
        alert("로그인 하시기 바랍니다.");
        return;
    }
    */

    var f = document.fitemuse;
    var id = document.getElementById('itemuse');

    id.style.display = 'block';

    f.w.value = '';
    f.is_id.value = '';
    if (!g4_is_member)
    {
        f.is_name.value = '';
        f.is_name.readOnly = false;
        f.is_password.value = '';
    }
    f.is_subject.value = '';
    f.is_content.value = '';
}

function itemuse_update(idx)
{
    var f = document.fitemuse;
    var id = document.getElementById('itemuse');

    id.style.display = 'block';

    f.w.value = 'u';
    f.is_id.value = document.getElementById('tmp_is_id'+idx).value;
    if (!g4_is_member)
    {
        f.is_name.value = document.getElementById('tmp_is_name'+idx).value;
        f.is_name.readOnly = true;
    }
    f.is_subject.value = document.getElementById('tmp_is_subject'+idx).value;
    f.is_content.value = document.getElementById('tmp_is_content'+idx).value;
}

function itemuse_delete(f, idx)
{
    var id = document.getElementById('itemuse');

    f.w.value = 'd';
    f.is_id.value = document.getElementById('tmp_is_id'+idx).value;

    if (g4_is_member)
    {
        if (confirm("삭제하시겠습니까?"))
            f.submit();
    }
    else
    {
        id.style.display = 'none';
        document.getElementById('itemuse_password'+idx).style.display = 'block';
    }
}
</script>
<!-- 사용후기 end -->
