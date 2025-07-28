<?php
include_once('./_common.php');

if (!$is_member)
    goto_url(G5_BBS_URL . "/login.php?url=" . urlencode(G5_SUBSCRIPTION_URL . "/mycard.php"));

// 읽지 않은 쪽지수
$memo_not_read = isset($member['mb_memo_cnt']) ? (int) $member['mb_memo_cnt'] : 0;

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="' . G5_SUBSCRIPTION_SKIN_URL . '/style.css">', 0);

$g5['title'] = '마이페이지';
include_once('./_head.php');

// 쿠폰
$cp_count = 0;
$sql = " select cp_id
            from {$g5['g5_shop_coupon_table']}
            where mb_id IN ( '{$member['mb_id']}', '전체회원' )
              and cp_start <= '" . G5_TIME_YMD . "'
              and cp_end >= '" . G5_TIME_YMD . "' ";
$res = sql_query($sql);

for ($k = 0; $cp = sql_fetch_array($res); $k++) {
    if (!is_used_coupon($member['mb_id'], $cp['cp_id']))
        $cp_count++;
}
?>

<!-- 마이페이지 시작 { -->
<div id="smb_my">

    <!-- 회원정보 개요 시작 { -->
    <section id="smb_my_ov">
        <h2>회원정보 개요</h2>

        <div class="smb_me">
            <strong class="my_ov_name"><?php echo get_member_profile_img($member['mb_id']); ?><br><?php echo $member['mb_name']; ?></strong><br>
            <a href="<?php echo G5_BBS_URL ?>/member_confirm.php?url=register_form.php" class="smb_info">정보수정</a>
            <a href="<?php echo G5_BBS_URL ?>/logout.php">로그아웃</a>
        </div>

        <ul id="smb_private">
            <li>
                <a href="<?php echo G5_BBS_URL ?>/point.php" target="_blank" class="win_point">
                    <i class="fa fa-database" aria-hidden="true"></i>포인트
                    <strong><?php echo number_format($member['mb_point']); ?></strong>
                </a>
            </li>
            <li>
                <a href="<?php echo G5_SHOP_URL ?>/coupon.php" target="_blank" class="win_coupon">
                    <i class="fa fa-ticket" aria-hidden="true"></i>쿠폰
                    <strong><?php echo number_format($cp_count); ?></strong>
                </a>
            </li>
            <li>
                <a href="<?php echo G5_BBS_URL ?>/memo.php" target="_blank" class="win_memo">
                    <i class="fa fa-envelope-o" aria-hidden="true"></i><span class="sound_only">안 읽은 </span>쪽지
                    <strong><?php echo $memo_not_read ?></strong>
                </a>
            </li>
            <li>
                <a href="<?php echo G5_BBS_URL ?>/scrap.php" target="_blank" class="win_scrap">
                    <i class="fa fa-thumb-tack" aria-hidden="true"></i>스크랩
                    <strong class="scrap"><?php echo number_format($member['mb_scrap_cnt']); ?></strong>
                </a>
            </li>

            <?php
            $sql = "SELECT COUNT(cnt) AS num 
                    FROM (
                        SELECT COUNT(*) AS cnt 
                        FROM {$g5['g5_subscription_mb_cardinfo_table']} 
                        WHERE card_billkey != '' 
                        AND mb_id = 'admin' 
                        GROUP BY od_card_name, card_mask_number
                    ) AS subquery";
            $total = sql_fetch($sql);

            $total_card_num = isset($total['num']) ? (int) $total['num'] : 0;
            ?>
            <li>
                <a href="<?php echo G5_SUBSCRIPTION_URL ?>/mycard.php" class="subscription-card-manage">
                    <i class="fa fa-thumb-tack" aria-hidden="true"></i>정기구독 카드 관리
                    <strong class="card-manage"><?php echo number_format($total_card_num); ?></strong>
                </a>
            </li>
        </ul>

        <h3>내정보</h3>
        <dl class="op_area">
            <dt>연락처</dt>
            <dd><?php echo ($member['mb_tel'] ? $member['mb_tel'] : '미등록'); ?></dd>
            <dt>E-Mail</dt>
            <dd><?php echo ($member['mb_email'] ? $member['mb_email'] : '미등록'); ?></dd>
            <dt>최종접속일시</dt>
            <dd><?php echo $member['mb_today_login']; ?></dd>
            <dt>회원가입일시</dt>
            <dd><?php echo $member['mb_datetime']; ?></dd>
            <dt id="smb_my_ovaddt">주소</dt>
            <dd id="smb_my_ovaddd"><?php echo sprintf("(%s%s)", $member['mb_zip1'], $member['mb_zip2']) . ' ' . print_address($member['mb_addr1'], $member['mb_addr2'], $member['mb_addr3'], $member['mb_addr_jibeon']); ?></dd>
        </dl>

        <a href="<?php echo G5_BBS_URL; ?>/member_confirm.php?url=member_leave.php" onclick="return member_leave();" class="withdrawal">회원탈퇴</a>
    </section>
    <!-- } 회원정보 개요 끝 -->

    <div id="smb_my_list">

        <!-- 등록된 카드 시작 { -->
        <section id="smb_my_wish">
            <h2>정기구독 등록된 카드 <span class="total-card-num"><?php echo $total_card_num; ?></span></h2>
            <form name="fwishlist" method="post" action="./manage_card_update.php">
                <input type="hidden" name="act" value="multi">
                <input type="hidden" name="sw_direct" value="">
                <input type="hidden" name="prog" value="subscription_card">
                <ul>
                    <?php
                    $sql = "SELECT ci_id, MAX(ci_id) AS max_id, pg_service, od_id, od_card_name, card_mask_number, od_test, ci_time 
                            FROM {$g5['g5_subscription_mb_cardinfo_table']} 
                            WHERE card_billkey != '' 
                            AND mb_id = '" . sql_real_escape_string($member['mb_id']) . "' 
                            GROUP BY od_card_name, card_mask_number 
                            ORDER BY max_id DESC 
                            LIMIT 30";
                    $result = sql_query($sql);

                    for ($i = 0; $row = sql_fetch_array($result); $i++) {
                        $card_num_str = $row['card_mask_number'] ? ' (' . substr($row['card_mask_number'], 0, 4) . ')' : '';
                    ?>

                        <li>
                            <div class="smb_my_chk">
                                <div class="chk_box">
                                    <input type="checkbox" name="chk_ci_id[<?php echo $i; ?>]" value="1" id="chk_ci_id_<?php echo $i; ?>" onclick="out_cd_check(this, '<?php echo $out_cd; ?>');" class="selec_chk">
                                    <label for="chk_ci_id_<?php echo $i; ?>"><span></span><b class="sound_only"><?php echo $row['it_name']; ?></b></label>
                                </div>
                                <input type="hidden" name="ci_id[<?php echo $i; ?>]" value="<?php echo $row['ci_id']; ?>">
                            </div>
                            <div class="smb_my_img jumbotron-icon">
                                <i class="fa fa-credit-card fa-4" aria-hidden="true"></i>
                            </div>
                            <div class="smb_my_tit"><?php echo $row['od_card_name'] . $card_num_str; ?></div>
                            <div class="smb_my_date">등록일:<br><?php echo $row['ci_time']; ?></div>
                            <div><?php echo $row['pg_service']; ?></div>
                            <a href="./manage_card_update.php?w=d&amp;ci_id=<?php echo $row['ci_id']; ?>" class="wish_del"><i class="fa fa-trash" aria-hidden="true"></i><span class="sound_only">삭제</span></a>
                        </li>

                    <?php
                    }

                    if ($i == 0)
                        echo '<li class="empty_li">등록된 카드가 없습니다.</li>';
                    ?>
                </ul>

                <div id="smb_ws_act">
                    <button type="submit" class="btn02" onclick="return fwishlist_check(document.fwishlist,'direct_buy');">카드 삭제하기</button>
                </div>
            </form>
        </section>
        <!-- } 최근 위시리스트 끝 -->

        <?php run_event('g5_shop_mypage_sub_bottom'); ?>

    </div>
</div>

<script>
    function member_leave() {
        return confirm('정말 회원에서 탈퇴 하시겠습니까?')
    }

    function out_cd_check(fld, out_cd) {
        if (out_cd == 'no') {
            alert("옵션이 있는 상품입니다.\n\n상품을 클릭하여 상품페이지에서 옵션을 선택한 후 주문하십시오.");
            fld.checked = false;
            return;
        }

        if (out_cd == 'tel_inq') {
            alert("이 상품은 전화로 문의해 주십시오.\n\n장바구니에 담아 구입하실 수 없습니다.");
            fld.checked = false;
            return;
        }
    }

    function fwishlist_check(f, act) {
        var k = 0;
        var length = f.elements.length;

        for (i = 0; i < length; i++) {
            if (f.elements[i].checked) {
                k++;
            }
        }

        if (k == 0) {
            alert("상품을 하나 이상 체크 하십시오");
            return false;
        }

        if (act == "direct_buy") {
            f.sw_direct.value = 1;
        } else {
            f.sw_direct.value = 0;
        }

        return true;
    }
</script>
<!-- } 마이페이지 끝 -->

<?php
include_once("./_tail.php");
