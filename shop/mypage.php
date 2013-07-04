<?php
include_once('./_common.php');

if (G4_IS_MOBILE) {
    include_once(G4_MSHOP_PATH.'/mypage.php');
    return;
}

if (!$is_member)
    goto_url(G4_BBS_URL."/login.php?url=".urlencode(G4_SHOP_URL."/mypage.php"));

$g4['title'] = $member['mb_name'].'님 마이페이지';
include_once('./_head.php');

//$str = $g4[title];
//include("./navigation2.inc.php");

// 쿠폰
$sql = " select count(*) as cnt
            from {$g4['shop_coupon_table']}
            where mb_id = '{$member['mb_id']}'
              and cp_used = '0'
              and cp_start <= '".G4_TIME_YMD."'
              and cp_end >= '".G4_TIME_YMD."' ";
$cp = sql_fetch($sql);
?>

<!-- 마이페이지 시작 { -->
<div id="smb_my">

    <!-- 회원정보 개요 시작 { -->
    <section id="smb_my_ov">
        <h2>회원정보 개요</h2>

        <div id="smb_my_act">
            <ul>
                <?php if ($is_admin == 'super') { ?><li><a href="<?php echo G4_ADMIN_URL; ?>/" class="btn_admin">관리자</a></li><?php } ?>
                <li><a href="<?php echo G4_BBS_URL; ?>/memo.php" target="_blank" class="win_memo btn01">쪽지함</a></li>
                <li><a href="<?php echo G4_BBS_URL; ?>/member_confirm.php?url=register_form.php" class="btn01">회원정보수정</a></li>
                <li><a href="<?php echo G4_BBS_URL; ?>/member_confirm.php?url=member_leave.php" onclick="return member_leave();" class="btn02">회원탈퇴</a></li>
            </ul>
        </div>

        <dl>
            <?php if($default['de_mileage_use']) { ?>
            <dt>마일리지</dt>
            <dd><a href="<?php echo G4_SHOP_URL; ?>/mileage.php" target="_blank" class="win_point"><?php echo number_format($member['mb_mileage']); ?>점</a></dd>
            <?php } else { ?>
            <dt>보유포인트</dt>
            <dd><a href="<?php echo G4_BBS_URL; ?>/point.php" target="_blank" class="win_point"><?php echo number_format($member['mb_point']); ?>점</a></dd>
            <?php } ?>
            <dt>보유쿠폰</dt>
            <dd><a href="<?php echo G4_SHOP_URL; ?>/coupon.php" target="_blank" class="win_coupon"><?php echo number_format($cp['cnt']); ?></a></dd>
            <dt>연락처</dt>
            <dd><?php echo ($member['mb_tel'] ? $member['mb_tel'] : '미등록'); ?></dd>
            <dt>E-Mail</dt>
            <dd><?php echo ($member['mb_email'] ? $member['mb_email'] : '미등록'); ?></dd>
            <dt>최종접속일시</dt>
            <dd><?php echo $member['mb_today_login']; ?></dd>
            <dt>회원가입일시</dt>
            <dd><?php echo $member['mb_datetime']; ?></dd>
            <dt id="smb_my_ovaddt">주소</dt>
            <dd id="smb_my_ovaddd"><?php echo sprintf("(%s-%s) %s %s", $member['mb_zip1'], $member['mb_zip2'], $member['mb_addr1'], $member['mb_addr2']); ?></dd>
        </dl>
    </section>
    <!-- } 회원정보 개요 끝 -->

    <!-- 최근 주문내역 시작 { -->
    <section id="smb_my_od">
        <h2>최근 주문내역</h2>
        <?php
        // 최근 주문내역
        define("_ORDERINQUIRY_", true);

        $limit = " limit 0, 5 ";
        include G4_SHOP_PATH.'/orderinquiry.sub.php';
        ?>

        <div id="smb_my_more">
            <a href="./orderinquiry.php" class="btn01">주문내역 더보기</a>
        </div>
    </section>
    <!-- } 최근 주문내역 끝 -->

    <!-- 최근 위시리스트 시작 { -->
    <section id="smb_my_wish">
        <h2>최근 위시리스트</h2>

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

        <div id="smb_my_more">
            <a href="./wishlist.php" class="btn01">위시리스트 더보기</a>
        </div>
    </section>
    <!-- } 최근 위시리스트 끝 -->

</div>

<script>
$(function() {
    $(".win_coupon").click(function() {
        var new_win = window.open($(this).attr("href"), "win_coupon", "left=100,top=100,width=700, height=600, scrollbars=1");
        new_win.focus();
        return false;
    });
});

function member_leave()
{
    return confirm('정말 회원에서 탈퇴 하시겠습니까?')
}
</script>
<!-- } 마이페이지 끝 -->

<?php
include_once("./_tail.php");
?>