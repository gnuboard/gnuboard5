<?php
include_once('./_common.php');

if (!$is_member)
    goto_url(G4_BBS_URL."/login.php?url=".urlencode(G4_SHOP_URL."/mypage.php"));

$g4['title'] = '마이페이지';
include_once(G4_MSHOP_PATH.'/_head.php');
?>

<div id="smb_my">

    <section id="smb_my_ov">
        <h2>회원정보 개요</h2>

        <dl>
            <dt>회원권한</dt>
            <dd><?php echo $member['mb_level']; ?></dd>
            <?php if($default['de_mileage_use']) { ?>
            <dt>마일리지</dt>
            <dd><a href="<?php echo G4_SHOP_URL; ?>/mileage.php" target="_blank" class="win_point"><?php echo number_format($member['mb_mileage']); ?>점</a></dd>
            <?php } else { ?>
            <dt>보유포인트</dt>
            <dd><a href="<?php echo G4_BBS_URL; ?>/point.php" target="_blank" class="win_point"><?php echo number_format($member['mb_point']); ?>점</a></dd>
            <?php } ?>
            <dt>연락처</dt>
            <dd><?php echo ($member['mb_tel'] ? $member['mb_tel'] : '미등록'); ?></dd>
            <dt>E-Mail</dt>
            <dd><?php echo ($member['mb_email'] ? $member['mb_email'] : '미등록'); ?></dd>
            <dt>최종접속일시</dt>
            <dd><?php echo $member['mb_today_login']; ?></dd>
            <dt>회원가입일시</dt>
            <dd><?php echo $member['mb_datetime']; ?></dd>
            <dt>주소</dt>
            <dd><?php echo sprintf("(%s-%s) %s %s", $member['mb_zip1'], $member['mb_zip2'], $member['mb_addr1'], $member['mb_addr2']); ?></dd>
        </dl>
    </section>

    <section id="smb_my_od">
        <h2><a href="<?php echo G4_SHOP_URL; ?>/orderinquiry.php">최근 주문내역</a></h2>
        <?php
        // 최근 주문내역
        define("_ORDERINQUIRY_", true);

        $limit = " limit 0, 5 ";
        include G4_MSHOP_PATH.'/orderinquiry.sub.php';
        ?>
    </section>

    <section id="smb_my_wish">
        <h2><a href="<?php echo G4_SHOP_URL; ?>/wishlist.php">최근 위시리스트</a></h2>

        <table class="basic_tbl">
        <thead>
        <tr>
            <th scope="col">이미지</th>
            <th scope="col">상품명</th>
            <th scope="col">보관일시</th>
        </tr>
        </thead>
        <tbody>
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
            $image = get_it_image($row['it_id'], 70, 70, true);
        ?>

        <tr>
            <td class="smb_my_img"><?php echo $image; ?></td>
            <td><a href="./item.php?it_id=<?php echo $row['it_id']; ?>"><?php echo stripslashes($row['it_name']); ?></a></td>
            <td class="td_datetime"><?php echo $row['wi_time']; ?></td>
        </tr>

        <?php
        }

        if ($i == 0)
            echo '<tr><td colspan="3" class="empty_table">보관 내역이 없습니다.</td></tr>';
        ?>
        </tbody>
        </table>
    </section>

</div>

<script>
function member_leave()
{
    return confirm('정말 회원에서 탈퇴 하시겠습니까?')
}
</script>

<?php
include_once(G4_MSHOP_PATH.'/_tail.php');
?>