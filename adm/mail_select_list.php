<?
$sub_menu = "200300";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

$token = get_token();

$html_title = "선택된 회원메일리스트";

$ma_last_option = "";

$sql_common = " from $g4[member_table] ";
$sql_where = " where (1) ";

// 회원ID ..에서 ..까지
if ($mb_id1 != 1)
    $sql_where .= " and mb_id between '$mb_id1_from' and '$mb_id1_to' ";

// E-mail에 특정 단어 포함
if ($mb_email != "")
    $sql_where .= " and mb_email like '%$mb_email%' ";

// 성별
if ($mb_sex != "")
    $sql_where .= " and mb_sex = '$mb_sex' ";

// 생일
if ($mb_birth_from && $mb_birth_to)
    $sql_where .= " and substring(mb_birth,5,4) between '$mb_birth_from' and '$mb_birth_to' ";

// 지역
if ($mb_area != "")
    $sql_where .= " and mb_addr1 like '$mb_area%' ";

// 메일링
if ($mb_mailling != "")
    $sql_where .= " and mb_mailling = '$mb_mailling' ";

// 권한
$sql_where .= " and mb_level between '$mb_level_from' and '$mb_level_to' ";

// 게시판그룹회원
if ($gr_id)
{
    $group_member = "";
    $comma = "";
    $sql2 = " select mb_id from $g4[group_member_table] where gr_id = '$gr_id' order by mb_id ";
    $result2 = sql_query($sql2);
    for ($k=0; $row2=sql_fetch_array($result2); $k++)
    {
        $group_member .= "{$comma}'$row2[mb_id]'"; 
        $comma = ",";
    }

    if (!$group_member)
        alert("선택하신 게시판 그룹회원이 한명도 없습니다.");

    $sql_where .= " and mb_id in ($group_member) ";
}

// 탈퇴, 차단된 회원은 제외
$sql_where .= " and mb_leave_date = '' and mb_intercept_date = '' ";

$sql = " select COUNT(*) as cnt $sql_common $sql_where ";
$row = sql_fetch($sql);
$cnt = $row[cnt];
if ($cnt == 0)
    alert("선택하신 내용으로는 해당되는 회원자료가 없습니다.");

// 마지막 옵션을 저장합니다.
$ma_last_option .= "mb_id1=$mb_id1";
$ma_last_option .= "||mb_id1_from=$mb_id1_from";
$ma_last_option .= "||mb_id1_to=$mb_id1_to";
$ma_last_option .= "||mb_email=$mb_email";
$ma_last_option .= "||mb_sex=$mb_sex";
$ma_last_option .= "||mb_birth_from=$mb_birth_from";
$ma_last_option .= "||mb_birth_to=$mb_birth_to";
$ma_last_option .= "||mb_area=$mb_area";
$ma_last_option .= "||mb_mailling=$mb_mailling";
$ma_last_option .= "||mb_level_from=$mb_level_from";
$ma_last_option .= "||mb_level_to=$mb_level_to";
$ma_last_option .= "||gr_id=$gr_id";

sql_query(" update $g4[mail_table] set ma_last_option = '$ma_last_option' where ma_id = '$ma_id' ");


include_once("./admin.head.php");
?>

<table width=500 align=center><tr><td>

<?//=subtitle_bar($html_title)?><p>

<div align=right>선택된 회원수 : <?=number_format($cnt)?> 명</div>
<form name=fmailselectlist method=post onsubmit="return fmailselectlist_submit(this);">
<input type=hidden name=token value='<?=$token?>'>
<table cellpadding=4 cellspacing=1 width=100% class=tablebg>
<input type="hidden" name="ma_id" value="<? echo $ma_id ?>">
<tr>
    <td align=center>
        <select size=25 name='list' style='width:500px;'>
        <option>번호 . 회원아이디 / 이름 / 별명 / 생일 / E-mail
        <?
            $sql = " select mb_id, mb_name, mb_nick, mb_email, mb_birth, mb_datetime $sql_common $sql_where order by mb_id ";
            $result = sql_query($sql);
            $i=0;
            $ma_list = "";
            $cr = "";
            while ($row=sql_fetch_array($result)) 
            {
                $i++;
                echo "<option>$i . $row[mb_id] / $row[mb_name] / $row[mb_nick] / $row[mb_birth] / $row[mb_email]";
                $ma_list .= $cr . $row[mb_email] . "||" . $row[mb_id] . "||" . $row[mb_name] . "||" . $row[mb_nick] . "||" . $row[mb_birth] . "||" . $row[mb_datetime];
                $cr = "\n";
            }
        ?>
        </select>
        <textarea name="ma_list" style="display:none"><?=$ma_list?></textarea>
    </td>
</tr>
</table>

<p align=center>
    <input type=submit class=btn1 value='  메일 보내기  '>&nbsp;
    <input type=button class=btn1 value='  뒤  로  ' onclick="history.go(-1);">
</form>

</td></tr></table>

<script type='text/javascript'> 
function fmailselectlist_submit(f)
{
    f.action = "./mail_select_update.php";
    return true;
}
</script>

<?
include_once("./admin.tail.php");
?>
