<?php
include_once('./_common.php');

if (!$is_member)
    goto_url(G4_BBS_URL."/login.php?url=".urlencode(G4_SHOP_URL."/mypage.php"));

$g4['title'] = '마이페이지';
include_once('./_head.php');
?>

<img src="<?php echo G4_SHOP_URL; ?>/img/top_mypage.gif" border=0><p>

<table align=center width=100%>
<tr>
    <td><B><?php echo $member['mb_name']; ?></B> 님의 마이페이지입니다.</td>
    <td align=right>
        <?php if ($is_admin == 'super') { echo "<a href='".G4_ADMIN_URL."/'><img src='".G4_SHOP_URL."/img/btn_admin.gif' border=0 align='absmiddle'></a>"; } ?>
        <a href='<?php echo G4_BBS_URL; ?>/member_confirm.php?url=register_form.php'><img src='<?php echo G4_SHOP_URL; ?>/img/my_modify.gif' border=0 align='absmiddle'></a>
        <a href="<?php echo G4_BBS_URL; ?>/member_confirm.php?url=member_leave.php" onclick="return member_leave();"><img src='<?php echo G4_SHOP_URL; ?>/img/my_leave.gif' border=0 align='absmiddle'></a></td>
</tr>
</table>

<script>
function member_leave()
{
    return confirm('정말 회원에서 탈퇴 하시겠습니까?')
}
</script>

<table cellpadding=0 cellspacing=0 align=center background='<?php echo G4_SHOP_URL; ?>/img/my_bg.gif'>
<tr><td colspan=4><img src='<?php echo G4_SHOP_URL; ?>/img/my_box01.gif'></td></tr>
<tr>
    <td height=25>&nbsp;&nbsp;&nbsp;보유포인트 </td>
    <td>: <a href="<?php echo G4_BBS_URL; ?>/point.php" target="_blank" class="win_point"><?php echo number_format($member['mb_point']); ?>점</a></td>
    <td>쪽지함</td>
    <td>: <a href="<?php echo G4_BBS_URL; ?>/memo.php" target="_blank" class="win_memo">쪽지보기</a></td>
</tr>
<tr>
    <td height=25>&nbsp;&nbsp;&nbsp;주소</td>
    <td>: <?php echo sprintf("(%s-%s) %s %s", $member['mb_zip1'], $member['mb_zip2'], $member['mb_addr1'], $member['mb_addr2']); ?></td>
    <td>회원권한</td>
    <td>: <?php echo $member['mb_level']; ?></td>
</tr>
<tr>
    <td height=25>&nbsp;&nbsp;&nbsp;연락처</td>
    <td>: <?php echo $member['mb_tel']; ?></td>
    <td>최종접속일시</td>
    <td>: <?php echo $member['mb_today_login']; ?></td>
</tr>
<tr>
    <td height=25>&nbsp;&nbsp;&nbsp;E-mail</td>
    <td>: <?php echo $member['mb_email']; ?></td>
    <td>회원가입일시</td>
    <td>: <?php echo $member['mb_datetime']; ?></td>
</tr>
<tr><td colspan=4><img src='<?php echo G4_SHOP_URL; ?>/img/my_box02.gif'></td></tr>
</table><BR><BR>


<table width=98% cellpadding=0 cellspacing=0 align=center>
<tr>
    <td height=35><img src='<?php echo G4_SHOP_URL; ?>/img/my_title01.gif'></td>
    <td align=right><a href='./orderinquiry.php'><img src='<?php echo G4_SHOP_URL; ?>/img/icon_more.gif' border=0></a></td>
</tr>
</table>

<?php
// 최근 주문내역
define("_ORDERINQUIRY_", true);

$limit = " limit 0, 5 ";
include G4_MSHOP_PATH.'/orderinquiry.sub.php';
?>
<br>

<table width=98% cellpadding=0 cellspacing=0 align=center>
<tr>
    <td height=35 colspan=2><img src='<?php echo G4_SHOP_URL; ?>/img/my_title02.gif'></td>
    <td align=right><a href='./wishlist.php'><img src='<?php echo G4_SHOP_URL; ?>/img/icon_more.gif' border=0></a></td>
</tr>
<tr><td height=2 colspan=3 class=c1></td></tr>
<tr align=center height=25 class=c2>
    <td colspan=2>상품명</td>
    <td>보관일시</td>
</tr>
<tr><td height=1 colspan=3 class=c1></td></tr>
<?php
$sql = " select *
           from {$g4['shop_wish_table']} a,
                {$g4['shop_item_table']} b
          where a.mb_id = '{$member['mb_id']}'
            and a.it_id  = b.it_id
          order by a.wi_id desc
          limit 0, 3 ";
$result = sql_query($sql);
for ($i=0; $row = sql_fetch_array($result); $i++)
{
    if ($i>0)
        echo "<tr><td colspan=3 height=1 background='".G4_SHOP_URL."/img/dot_line.gif'></td></tr>";

    $image = get_it_image($row['it_id']."_s", 50, 50, $row['it_id']);

    echo "<tr align=center height=60>";
    echo "<td width=100>$image</td>";
    echo "<td align=left><a href='./item.php?it_id={$row['it_id']}'>".stripslashes($row['it_name'])."</a></td>";
    echo "<td>$row[wi_time]</td>";
    echo "</tr>";
}

if ($i == 0)
    echo "<tr><td colspan=3 height=100 align=center><span class=point>보관 내역이 없습니다.</span></td></tr>";
?>
<tr><td height=1 colspan=3 bgcolor=#94D7E7></td></tr>
</table>


<?php
include_once("./_tail.php");
?>