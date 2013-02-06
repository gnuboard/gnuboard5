<?
$sub_menu = "400730";
include_once("./_common.php");

auth_check($auth[$sub_menu], "w");

$html_title = "배너";
if ($w=="u")
{
    $html_title .= " 수정";
    $sql = " select * from $g4['yc4_banner_table'] where bn_id = '$bn_id' ";
    $bn = sql_fetch($sql);
}
else
{
    $html_title .= " 입력";
    $bn[bn_url]        = "http://";
    $bn[bn_begin_time] = date("Y-m-d 00:00:00", time());
    $bn[bn_end_time]   = date("Y-m-d 00:00:00", time()+(60*60*24*31));
}

$g4[title] = $html_title;
include_once(G4_ADMIN_PATH."/admin.head.php");
?>

<form id="fbanner" name="fbanner" method="post" action="./bannerformupdate.php" enctype="multipart/form-data">
<input type="hidden" name="w" value="<?=$w ?>">
<input type="hidden" name="bn_id" value="<?=$bn_id ?>">
<table>
<caption><?=$html_title?></caption>
<tbody>
<tr>
    <th scope="row"><label for="bn_bimg">이미지</label></th>
    <td>
        <input type="file" id="bn_bimg" name="bn_bimg" size="40">
        <?
        $bimg_str = "";
        $bimg = "$g4['path']/data/banner/{$bn['bn_id']}";
        if (file_exists($bimg) && $bn['bn_id']) {
            echo "<input type=\"checkbox\" id=\"bn_bimg_del\" name=\"bn_bimg_del\" value=\"1\">삭제";
            $bimg_str = "<img src=\"$bimg\" alt=\"\">";
            //$size = getimagesize($bimg);
            //echo "<img src=\"$g4['admin_path']/img/icon_viewer.gif\" onclick=\"imageview('bimg', $size[0], $size[1]);\" alt=\"\"><input type=\"checkbox\" id=\"bn_bimg_del\" name=\"bn_bimg_del\" value=\"1\">삭제";
            //echo "<div id=\"bimg\" style=\"left:0; top:0; z-index:+1; display:none; position:absolute;\"><img src=\"$bimg\" alt=\"\"></div>";
        }
        ?>
    </td>
</tr>
<? if ($bimg_str) { echo "<tr><td></td><td>$bimg_str</td></tr>"; } ?>

<tr>
    <th scope="row"><label for="bn_alt">이미지 설명</label></th>
    <td>
        <?=help("img 태그의 alt, title 에 해당되는 내용입니다.\n배너에 마우스를 오버하면 이미지의 설명이 나옵니다.");?>
        <input type="text" id="bn_alt" name="bn_alt" size="80" value="<?=$bn['bn_alt'] ?>">
    </td>
</tr>
<tr>
    <th scope="row"><label for="bn_url">링크</label></th>
    <td>
        <?=help("배너클릭시 이동하는 주소입니다.");?>
        <input type="text" id="bn_url" name="bn_url" size="80" value="<?=$bn['bn_url'] ?>">
    </td>
</tr>
<tr>
    <th scope="row"><label for="bn_position">출력위치</label></th>
    <td>
        <?=help("왼쪽 : 쇼핑몰화면 왼쪽에 출력합니다.\n메인 : 쇼핑몰 메인화면(index.php)에만 출력합니다.", 50);?>
        <select id="bn_position" name="bn_position">
        <option value="왼쪽">왼쪽</option>
        <option value="메인">메인</option>
        </select>
    </td>
</tr>
<tr>
    <th scope="row"><label for="bn_border">테두리</label></th>
    <td>
        <?=help("배너이미지에 테두리를 넣을지를 설정합니다.", 50);?>
        <select id="bn_border" name="bn_border">
        <option value="0">아니오</option>
        <option value="1">예</option>
        </select>
    </td>
</tr>
<tr>
    <th scope="row"><label for="bn_new_win">새창</label></th>
    <td>
        <?=help("배너클릭시 새창을 띄울지를 설정합니다.", 50);?>
        <select id="bn_new_win" name="bn_new_win">
        <option value="0">아니오</option>
        <option value="1">예</option>
        </select>
    </td>
</tr>
<tr>
    <th scope="row"><label for="bn_begin_time">시작일시</label></th>
    <td>
        <?=help("현재시간이 시작일시와 종료일시 기간안에 있어야 배너가 출력됩니다.");?>
        <input type="text" id="bn_begin_time" name="bn_begin_time" size="21" maxlength="19" value="<?=$bn['bn_begin_time']?>">
        <input type="checkbox" id="bn_begin_chk" name="bn_begin_chk" value="<? echo date("Y-m-d 00:00:00", time()); ?>" onclick="if (this.checked == true) this.form.bn_begin_time.value=this.form.bn_begin_chk.value; else this.form.bn_begin_time.value = this.form.bn_begin_time.defaultValue;">
        <label for="bn_begin_chk">오늘</label>
    </td>
</tr>
<tr>
    <th scope="row"><label for="bn_end_time">종료일시</label></th>
    <td>
        <input type="text" id="bn_end_time" name="bn_end_time" size="21" maxlength="19" value="<?=$bn['bn_end_time'] ?>">
        <input type="checkbox" id="bn_end_chk" name="bn_end_chk" value="<? echo date("Y-m-d 23:59:59", time()+60*60*24*31); ?>" onclick="if (this.checked == true) this.form.bn_end_time.value=this.form.bn_end_chk.value; else this.form.bn_end_time.value = this.form.bn_end_time.defaultValue;">
        <label for="bn_end_chk">오늘+31일</label>
    </td>
</tr>
<tr>
    <th scope="row"><label for="">출력 순서</label></th>
    <td>
        <?=help("배너를 출력할 때 순서를 정합니다.\n\n숫자가 작을수록 상단에 출력합니다.");?>
        <?=order_select("bn_order", $bn['bn_order'])?>
    </td>
</tr>
</tbody>
</table>

<div class="btn_confirm">
    <input type="submit" class="btn_submit" accesskey="s" value="확인">
    <a href="./bannerlist.php">목록으로</a>
</div>
</form>


<script>
if (document.fbanner.w.value == 'u')
{
    document.fbanner.bn_position.value = '<?=$bn[bn_position]?>';
    document.fbanner.bn_border.value   = '<?=$bn[bn_border]?>';
    document.fbanner.bn_new_win.value  = '<?=$bn[bn_new_win]?>';
}
</script>

<?
include_once(G4_ADMIN_PATH."/admin.tail.php");
?>
