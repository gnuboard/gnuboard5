<?php
include_once('./_common.php');

// 테마에 mypage.php 있으면 include
if(defined('G5_THEME_SHOP_PATH')) {
    $theme_mypage_file = G5_THEME_MSHOP_PATH.'/mypage.php';
    if(is_file($theme_mypage_file)) {
        include_once($theme_mypage_file);
        return;
        unset($theme_mypage_file);
    }
}

$g5['title'] = '마이페이지';
include_once(G5_MSHOP_PATH.'/_head.php');

// 쿠폰
$cp_count = get_shop_member_coupon_count($member['mb_id'], true);
?>

<div id="smb_my">
    <section id="smb_my_ov">
        <h2>회원정보 개요</h2>
        <div class="my_name">
            <span class="profile_img">
            	<img src="<?php echo G5_IMG_URL ;?>/no_profile.gif" alt="프로필이미지" width="20">
            	<a href="<?php echo G5_BBS_URL; ?>/member_confirm.php?url=register_form.php" class="my_info_modi"><span class="sound_only">정보수정</span><i class="fa fa-cog" aria-hidden="true"></i></a>	
            </span>
            <strong><?php echo $member['mb_id'] ? $member['mb_name'] : '비회원'; ?>님</strong>
            <a href="<?php echo G5_BBS_URL; ?>/point.php" target="_blank" class="win_point"><strong><?php echo number_format($member['mb_point']); ?></strong> 포인트</a>
            <ul class="smb_my_act">
                <?php if ($is_admin == 'super') { ?><li><a href="<?php echo G5_ADMIN_URL; ?>/" class="btn_admin">관리자</a></li><?php } ?>
				<li><a href="<?php echo G5_BBS_URL ?>/logout.php" class="btn_logout">로그아웃</a></li>
            </ul>
        </div>
        <ul class="my_pocou">
        	<li class="my_cou"><a href="<?php echo G5_SHOP_URL; ?>/coupon.php" target="_blank" class="win_coupon"><i class="fa fa-ticket" aria-hidden="true"></i> 쿠폰 <span><?php echo number_format($cp_count); ?></span></a></li>
        	<li class="my_memo"><a href="<?php echo G5_BBS_URL ?>/memo.php" target="_blank" class="win_memo"><i class="fa fa-envelope-o" aria-hidden="true"></i>  쪽지<span><?php echo $memo_not_read ?></span></a></li>
        </ul>
        
        <div class="my_ov_btn">
        	<button type="button" class="btn_op_area">내정보<span class="sound_only">상세보기</span><i class="fa fa-chevron-down" aria-hidden="true"></i></button>
        </div>
        
        <div class="my_info">
            <div class="my_info_wr">
                <strong>연락처</strong>
                <span><?php echo ($member['mb_tel'] ? $member['mb_tel'] : '미등록'); ?></span>
            </div>
            <div class="my_info_wr">
                <strong>E-Mail</strong>
                <span><?php echo ($member['mb_email'] ? $member['mb_email'] : '미등록'); ?></span>
            </div>
            <div class="my_info_wr">
                <strong>최종접속일시</strong>
                <span><?php echo $member['mb_today_login']; ?></span>
             </div>
            <div class="my_info_wr">
            <strong>회원가입일시</strong>
                <span><?php echo $member['mb_datetime']; ?></span>
            </div>
            <div class="my_info_wr">
                <strong>주소</strong>
                <span><?php echo sprintf("(%s%s)", $member['mb_zip1'], $member['mb_zip2']).' '.print_address($member['mb_addr1'], $member['mb_addr2'], $member['mb_addr3'], $member['mb_addr_jibeon']); ?></span>
            </div>
            <div class="my_info_wr ov_addr">
            	<a href="<?php echo G5_BBS_URL; ?>/member_confirm.php?url=member_leave.php" onclick="return member_leave();">회원탈퇴</a>
            </div>
        </div>

    </section>

    <script> 
    $(".btn_op_area").on("click", function() {
        $(".my_info").toggle();
        $(".fa-chevron-down").toggleClass("fa-caret-up")
    });
    </script>

    <section id="smb_my_od">
        <h2><a href="<?php echo G5_SHOP_URL; ?>/orderinquiry.php">최근 주문내역</a></h2>
        <?php
        // 최근 주문내역
        define("_ORDERINQUIRY_", true);

        $limit = " limit 0, 5 ";
        include G5_MSHOP_PATH.'/orderinquiry.sub.php';
        ?>
        <a href="<?php echo G5_SHOP_URL; ?>/orderinquiry.php" class="btn_more">더보기</a>
    </section>

    <section id="smb_my_wish" class="wishlist">
        <h2><a href="<?php echo G5_SHOP_URL; ?>/wishlist.php">위시리스트</a></h2>
        <ul>
            <?php
            $sql = " select *
                       from {$g5['g5_shop_wish_table']} a,
                            {$g5['g5_shop_item_table']} b
                      where a.mb_id = '{$member['mb_id']}'
                        and a.it_id  = b.it_id
                      order by a.wi_id desc
                      limit 0, 6 ";
            $result = sql_query($sql);
            for ($i=0; $row = sql_fetch_array($result); $i++)
            {
                $image_w = 250;
                $image_h = 250;
                $image = get_it_image($row['it_id'], $image_w, $image_h, true);
                $list_left_pad = $image_w + 10;
            ?>

            <li>
                <div class="wish_img"><?php echo $image; ?></div>
                <!-- 상품명, 날짜를 노출하려면 주석을 지우세요. -->
                <!--
                <div class="wish_info">
                    <a href="<?php echo get_shop_item($row['it_id'], true); ?>" class="info_link"><?php echo stripslashes($row['it_name']); ?></a>
                     <span class="info_date"><?php echo substr($row['wi_time'], 2, 8); ?></span>
                </div>
                -->
            </li>

            <?php
            }

            if ($i == 0)
                echo '<li class="empty_list">보관 내역이 없습니다.</li>';
            ?>
        </ul>
         <a href="<?php echo G5_SHOP_URL; ?>/wishlist.php" class="btn_more">더보기</a>
    </section>

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

<?php
include_once(G5_MSHOP_PATH.'/_tail.php');
?>