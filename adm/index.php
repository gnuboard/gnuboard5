<?
include_once("./_common.php");

$g4['title'] = "관리자메인";
include_once ("./admin.head.php");

$new_member_rows = 5;
$new_point_rows = 5;
$new_write_rows = 5;

$sql_common = " from $g4[member_table] ";

$sql_search = " where (1) ";

//if ($is_admin == 'group') $sql_search .= " and mb_level = '$member[mb_level]' ";
if ($is_admin != 'super') 
    $sql_search .= " and mb_level <= '$member[mb_level]' ";

if (!isset($sst)) {
    $sst = "mb_datetime";
    $sod = "desc";
}

$sql_order = " order by $sst $sod ";

$sql = " select count(*) as cnt
         $sql_common
         $sql_search
         $sql_order ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

// 탈퇴회원수
$sql = " select count(*) as cnt
         $sql_common
         $sql_search
            and mb_leave_date <> ''
         $sql_order ";
$row = sql_fetch($sql);
$leave_count = $row['cnt'];

// 차단회원수
$sql = " select count(*) as cnt
         $sql_common
         $sql_search
            and mb_intercept_date <> ''
         $sql_order ";
$row = sql_fetch($sql);
$intercept_count = $row['cnt'];

$sql = " select *
          $sql_common
          $sql_search
          $sql_order
          limit $new_member_rows ";
$result = sql_query($sql);

$colspan = 12;
?>

<?=subtitle("신규가입회원 {$new_member_rows}건", "./member_list.php");?>

<table width=100%>
<tr>
    <td width=50% align=left><?//=$listall?> (총회원수 : <?=number_format($total_count)?>, <font color=orange>차단 : <?=number_format($intercept_count)?></font>, <font color=crimson>탈퇴 : <?=number_format($leave_count)?></font>)</td>
    <td width=50% align=right></td>
</tr>
</table>

<table width=100% cellpadding=0 cellspacing=0>
<input type=hidden name=sst  value='<?=$sst?>'>
<input type=hidden name=sod  value='<?=$sod?>'>
<input type=hidden name=sfl  value='<?=$sfl?>'>
<input type=hidden name=stx  value='<?=$stx?>'>
<input type=hidden name=page value='<?=$page?>'>
<colgroup width=80>
<colgroup width=80>
<colgroup width=>
<colgroup width=40>
<colgroup width=50>
<colgroup width=80>
<colgroup width=40>
<colgroup width=40>
<colgroup width=40>
<colgroup width=40>
<colgroup width=40>
<tr><td colspan='<?=$colspan?>' class='line1'></td></tr>
<tr class='bgcol1 bold col1 ht center'>
    <td>회원아이디</td>
    <td>이름</td>
    <td>별명</td>
    <td>권한</td>
    <td>포인트</td>
    <td>최종접속</td>
    <td title='메일수신허용여부'>수신</td>
    <td title='정보공개여부'>공개</td>
    <td title='이메일인증'>인증</td>
    <td>차단</td>
    <td title='접근가능한 그룹수'>그룹</td>
</tr>
<tr><td colspan='<?=$colspan?>' class='line2'></td></tr>
<?
for ($i=0; $row=sql_fetch_array($result); $i++) 
{
    // 접근가능한 그룹수
    $sql2 = " select count(*) as cnt from $g4[group_member_table] where mb_id = '$row[mb_id]' ";
    $row2 = sql_fetch($sql2);
    $group = "";
    if ($row2['cnt'])
        $group = "<a href='./boardgroupmember_form.php?mb_id=$row[mb_id]'>$row2[cnt]</a>";

    if ($is_admin == 'group') 
    {
        $s_mod = "";
        $s_del = "";
    } 
    else 
    {
        $s_mod = "<a href=\"./member_form.php?$qstr&w=u&mb_id=$row[mb_id]\"><img src='img/icon_modify.gif' border=0 title='수정'></a>";
        $s_del = "<a href=\"javascript:del('./member_delete.php?$qstr&w=d&mb_id=$row[mb_id]&url=$_SERVER[PHP_SELF]');\"><img src='img/icon_delete.gif' border=0 title='삭제'></a>";
    }
    $s_grp = "<a href='./boardgroupmember_form.php?mb_id=$row[mb_id]'><img src='img/icon_group.gif' border=0 title='그룹'></a>";

    $leave_date = $row['mb_leave_date'] ? $row['mb_leave_date'] : date("Ymd", $g4['server_time']);
    $intercept_date = $row['mb_intercept_date'] ? $row['mb_intercept_date'] : date("Ymd", $g4['server_time']);

    $mb_nick = get_sideview($row['mb_id'], $row['mb_nick'], $row['mb_email'], $row['mb_homepage']);

    $mb_id = $row['mb_id'];
    if ($row['mb_leave_date'])
        $mb_id = "<font color=crimson>$mb_id</font>";
    else if ($row['mb_intercept_date'])
        $mb_id = "<font color=orange>$mb_id</font>";

    $list = $i%2;
    echo "
    <input type=hidden name=mb_id[$i] value='$row[mb_id]'>
    <tr class='list$list col1 ht center'>
        <td title='$row[mb_id]'><nobr style='display:block; overflow:hidden; width:100px;'>&nbsp;$mb_id</nobr></td>
        <td>$row[mb_name]</td>
        <td>$mb_nick</td>
        <td>$row[mb_level]</td>
        <td align=right><a href='./point_list.php?sfl=mb_id&stx=$row[mb_id]' class=tt>".number_format($row['mb_point'])."</a>&nbsp;</td>
        <td>".substr($row['mb_today_login'],2,8)."</td>
        <td>".($row['mb_mailling']?'&radic;':'&nbsp;')."</td>
        <td>".($row['mb_open']?'&radic;':'&nbsp;')."</td>
        <td title='$row[mb_email_certify]'>".(preg_match('/[1-9]/', $row['mb_email_certify'])?'&radic;':'&nbsp;')."</td>
        <td title='$row[mb_intercept_date]'>".($row['mb_intercept_date']?'&radic;':'&nbsp;')."</td>
        <td>$group</td>               
    </tr>";
}

if ($i == 0)
    echo "<tr><td colspan='$colspan' align=center height=100 class=contentbg>자료가 없습니다.</td></tr>";

echo "<tr><td colspan='$colspan' class='line2'></td></tr>";
echo "</table>";
?>



<?
//$sql_common = " from $g4[board_new_table] a, $g4[board_table] b, $g4[group_table] c where a.bo_table = b.bo_table and b.gr_id = c.gr_id and b.bo_use_search = '1' ";
$sql_common = " from $g4[board_new_table] a, $g4[board_table] b, $g4[group_table] c where a.bo_table = b.bo_table and b.gr_id = c.gr_id ";

if (isset($gr_id))
    $sql_common .= " and b.gr_id = '$gr_id' ";
if (isset($view)) {
    if ($view == "w")
        $sql_common .= " and a.wr_id = a.wr_parent ";
    else if ($view == "c")
        $sql_common .= " and a.wr_id <> a.wr_parent ";
}
$sql_order = " order by a.bn_id desc ";

$sql = " select count(*) as cnt $sql_common ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$colspan = 5;
?>

<br><br>
<?=subtitle("최근게시물 {$new_write_rows}건", "$g4[bbs_path]/new.php");?>

<table width=100% cellpadding=0 cellspacing=1>
<input type=hidden name=sst  value='<?=$sst?>'>
<input type=hidden name=sod  value='<?=$sod?>'>
<input type=hidden name=sfl  value='<?=$sfl?>'>
<input type=hidden name=stx  value='<?=$stx?>'>
<input type=hidden name=page value='<?=$page?>'>
<colgroup width=100>
<colgroup width=100>
<colgroup width=''>
<colgroup width=80>
<colgroup width=80>
<tr><td colspan='<?=$colspan?>' class='line1'></td></tr>
<tr class='bgcol1 bold col1 ht center'>
    <td>그룹</td>
    <td>게시판</td>
    <td>제목</td>
    <td>이름</td>
    <td>일시</td>
</tr>
<tr><td colspan='<?=$colspan?>' class='line2'></td></tr>
<?
$sql = " select a.*, b.bo_subject, c.gr_subject, c.gr_id
          $sql_common
          $sql_order
          limit $new_write_rows ";
$result = sql_query($sql);
for ($i=0; $row=sql_fetch_array($result); $i++) 
{
    $tmp_write_table = $g4['write_prefix'] . $row['bo_table'];

    if ($row['wr_id'] == $row['wr_parent']) // 원글
    {
        $comment = "";
        $comment_link = "";
        $row2 = sql_fetch(" select * from $tmp_write_table where wr_id = '$row[wr_id]' ");

        $name = get_sideview($row2['mb_id'], cut_str($row2['wr_name'], $config['cf_cut_name']), $row2['wr_email'], $row2['wr_homepage']);
        // 당일인 경우 시간으로 표시함
        $datetime = substr($row2['wr_datetime'],0,10);
        $datetime2 = $row2['wr_datetime'];
        if ($datetime == $g4['time_ymd'])
            $datetime2 = substr($datetime2,11,5);
        else
            $datetime2 = substr($datetime2,5,5);

    }
    else // 코멘트
    {
        $comment = "[코] ";
        $comment_link = "#c_{$row[wr_id]}";
        $row2 = sql_fetch(" select * from $tmp_write_table where wr_id = '$row[wr_parent]' ");
        $row3 = sql_fetch(" select mb_id, wr_name, wr_email, wr_homepage, wr_datetime from $tmp_write_table where wr_id = '$row[wr_id]' ");

        $name = get_sideview($row3['mb_id'], cut_str($row3['wr_name'], $config['cf_cut_name']), $row3['wr_email'], $row3['wr_homepage']);
        // 당일인 경우 시간으로 표시함
        $datetime = substr($row3['wr_datetime'],0,10);
        $datetime2 = $row3['wr_datetime'];
        if ($datetime == $g4['time_ymd'])
            $datetime2 = substr($datetime2,11,5);
        else
            $datetime2 = substr($datetime2,5,5);
    }

    $list = $i%2;
    echo "
    <tr class='list$list col1 ht center'>
        <td class=small><a href='$g4[bbs_path]/new.php?gr_id=$row[gr_id]'>".cut_str($row['gr_subject'],10)."</a></td>
        <td class=small><a href='$g4[bbs_path]/board.php?bo_table=$row[bo_table]'>".cut_str($row['bo_subject'],20)."</a></td>
        <td align=left style='word-break:break-all;'>&nbsp;<a href='$g4[bbs_path]/board.php?bo_table=$row[bo_table]&wr_id=$row2[wr_id]{$comment_link}'>{$comment}".conv_subject($row2['wr_subject'], 100)."</a></td>
        <td>$name</td>
        <td>$datetime</td>
    </tr> ";  
}

if ($i == 0)
    echo "<tr><td colspan='$colspan' align=center height=100 bgcolor=#ffffff>자료가 없습니다.</td></tr>";

echo "<tr><td colspan='$colspan' class='line2'></td></tr>";
echo "</table>";
?>



<?
$sql_common = " from $g4[point_table] ";
$sql_search = " where (1) ";
$sql_order = " order by po_id desc ";

$sql = " select count(*) as cnt
         $sql_common 
         $sql_search 
         $sql_order ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$sql = " select *
          $sql_common
          $sql_search
          $sql_order
          limit $new_point_rows ";
$result = sql_query($sql);

$colspan = 7;
?>

<br><br>
<?=subtitle("최근포인트 {$new_point_rows}건", "./point_list.php");?>

<table width=100%>
<tr>
    <td width=50% align=left>
        <?//=$listall?> (건수 : <?=number_format($total_count)?>)
        <? 
        //$row2 = sql_fetch(" select sum(po_point) as sum_point from $g4[point_table] ");
        //echo "&nbsp;(전체 포인트 합계 : " . number_format($row2[sum_point]) . "점)";
        ?>
        
    </td>
    <td width=50% align=right></td>
</tr>
</table>

<table width=100% cellpadding=0 cellspacing=1>
<input type=hidden name=sst  value='<?=$sst?>'>
<input type=hidden name=sod  value='<?=$sod?>'>
<input type=hidden name=sfl  value='<?=$sfl?>'>
<input type=hidden name=stx  value='<?=$stx?>'>
<input type=hidden name=page value='<?=$page?>'>
<colgroup width=100>
<colgroup width=80>
<colgroup width=80>
<colgroup width=140>
<colgroup width=''>
<colgroup width=50>
<colgroup width=80>
<tr><td colspan='<?=$colspan?>' class='line1'></td></tr>
<tr class='bgcol1 bold col1 ht center'>
    <td>회원아이디</td>
    <td>이름</td>
    <td>별명</td>
    <td>일시</td>
    <td>포인트 내용</td>
    <td>포인트</td>
    <td>포인트합</td>
</tr>
<tr><td colspan='<?=$colspan?>' class='line2'></td></tr>
<?
$row2['mb_id'] = '';
for ($i=0; $row=sql_fetch_array($result); $i++) 
{
    if ($row2['mb_id'] != $row['mb_id'])
    {
        $sql2 = " select mb_id, mb_name, mb_nick, mb_email, mb_homepage, mb_point from $g4[member_table] where mb_id = '$row[mb_id]' ";
        $row2 = sql_fetch($sql2);
    }

    $mb_nick = get_sideview($row['mb_id'], $row2['mb_nick'], $row2['mb_email'], $row2['mb_homepage']);

    $link1 = $link2 = "";
    if (!preg_match("/^\@/", $row['po_rel_table']) && $row['po_rel_table'])
    {
        $link1 = "<a href='$g4[bbs_path]/board.php?bo_table=$row[po_rel_table]&wr_id=$row[po_rel_id]' target=_blank>";
        $link2 = "</a>";
    }

    $list = $i%2;
    echo "
    <input type=hidden name=po_id[$i] value='$row[po_id]'>
    <input type=hidden name=mb_id[$i] value='$row[mb_id]'>
    <tr class='list$list col1 ht center'>
        <td><a href='./point_list.php?sfl=mb_id&stx=$row[mb_id]'>$row[mb_id]</a></td>
        <td>$row2[mb_name]</td>
        <td>$mb_nick</td>
        <td>$row[po_datetime]</td>
        <td align=left>&nbsp;{$link1}$row[po_content]{$link2}</td>
        <td align=right>".number_format($row['po_point'])."&nbsp;</td>
        <td align=right>".number_format($row2['mb_point'])."&nbsp;</td>
    </tr> ";
} 

if ($i == 0)
    echo "<tr><td colspan='$colspan' align=center height=100 bgcolor=#ffffff>자료가 없습니다.</td></tr>";

echo "<tr><td colspan='$colspan' class='line2'></td></tr>";
echo "</table>";
?>



<?
include_once ("./admin.tail.php");
?>
