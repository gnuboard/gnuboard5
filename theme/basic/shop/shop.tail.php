<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

if (G5_IS_MOBILE) {
    include_once(G5_THEME_MSHOP_PATH.'/shop.tail.php');
    return;
}

$admin = get_admin("super");

// 사용자 화면 우측과 하단을 담당하는 페이지입니다.
// 우측, 하단 화면을 꾸미려면 이 파일을 수정합니다.
?>
        </div>  <!-- } .shop-content 끝 -->
	</div>      <!-- } #container 끝 -->
</div>
<!-- } 전체 콘텐츠 끝 -->

<!-- 하단 시작 { -->
<div id="ft">
    <div id="ft_wr">
        <ul id="ft_link" class="ft_cnt">
            <li><a href="<?php echo get_pretty_url('content', 'company'); ?>">회사소개</a></li>
            <li><a href="<?php echo get_pretty_url('content', 'provision'); ?>">서비스이용약관</a></li>
            <li><a href="<?php echo get_pretty_url('content', 'privacy'); ?>">개인정보처리방침</a></li>
            <li><a href="<?php echo get_device_change_url(); ?>">모바일버전</a></li>
        </ul>
        <div id="ft_company" class="ft_cnt">
        	<h2>사이트 정보</h2>
	        <p class="ft_info">
		        <span><b>회사명</b> <?php echo $default['de_admin_company_name']; ?></span>
	            <span><b>주소</b> <?php echo $default['de_admin_company_addr']; ?></span><br>
	            <span><b>사업자 등록번호</b> <?php echo $default['de_admin_company_saupja_no']; ?></span>
	            <span><b>대표</b> <?php echo $default['de_admin_company_owner']; ?></span>
	            <span><b>전화</b> <?php echo $default['de_admin_company_tel']; ?></span>
	            <span><b>팩스</b> <?php echo $default['de_admin_company_fax']; ?></span><br>
	            <!-- <span><b>운영자</b> <?php echo $admin['mb_name']; ?></span><br> -->
	            <span><b>통신판매업신고번호</b> <?php echo $default['de_admin_tongsin_no']; ?></span>
	            <span><b>개인정보 보호책임자</b> <?php echo $default['de_admin_info_name']; ?></span><br>
				<?php if ($default['de_admin_buga_no']) echo '<span><b>부가통신사업신고번호</b> '.$default['de_admin_buga_no'].'</span>'; ?>
			</p>
	    </div>
	    
	    <!-- 커뮤니티 최신글 시작 { -->
        <section id="sidx_lat">
            <?php echo latest('theme/notice', 'notice', 5, 30); ?>
        </section>
        <!-- } 커뮤니티 최신글 끝 -->

		<?php echo visit('theme/shop_basic'); // 접속자 ?>
    </div>

    <div id="ft_copy">Copyright &copy; 2001-2013 <?php echo $default['de_admin_company_name']; ?>. All Rights Reserved.</div>
</div>

<?php
$sec = get_microtime() - $begin_time;
$file = $_SERVER['SCRIPT_NAME'];

if ($config['cf_analytics']) {
    echo $config['cf_analytics'];
}
?>

<script src="<?php echo G5_JS_URL; ?>/sns.js"></script>
<!-- } 하단 끝 -->

<?php
include_once(G5_THEME_PATH.'/tail.sub.php');