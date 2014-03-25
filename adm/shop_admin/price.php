<?php
$sub_menu = '500210';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g5['title'] = '가격비교사이트';
include_once (G5_ADMIN_PATH.'/admin.head.php');
$pg_anchor = '<ul class="anchor">
<li><a href="#anc_pricecompare_info">가격비교사이트 연동 안내</a></li>
<li><a href="#anc_pricecompare_engine">사이트별 엔진페이지 URL</a></li>
<li><a href="#anc_pricecompare_engine2">사이트별 엔진페이지 URL [장바구니 담기]</a></li>
</ul>';
?>

<section id="anc_pricecompare_info">
    <h2>가격비교사이트 연동 안내</h2>
    <?php echo $pg_anchor; ?>

    <div class="local_desc01 local_desc">
        <ol>
            <li>가격비교사이트는 네이버 지식쇼핑, 다음 쇼핑하우 등이 있습니다.</li>
            <li>앞서 나열한 가격비교사이트 중 희망하시는 사이트에 입점합니다.</li>
            <li><strong>사이트별 엔진페이지 URL</strong>을 참고하여 해당 엔진페이지 URL 을 입점하신 사이트에 알려주시면 됩니다.</li>
        </ol>
    </div>
</section>

<section id="anc_pricecompare_engine">
    <h2>사이트별 엔진페이지 URL</h2>
    <?php echo $pg_anchor; ?>

    <div class="local_desc01 local_desc">
        <p>사이트 명을 클릭하시면 해당 사이트로 이동합니다.</p>

        <dl class="price_engine">
            <dt><a href="http://shopping.naver.com/" target="_blank">네이버 지식쇼핑</a></dt>
            <dd>
                <ul>
                    <li>입점 안내 : <a href="http://join.shopping.naver.com/join/intro.nhn" target="_blank">http://join.shopping.naver.com/join/intro.nhn</a></li>
                    <li>전체상품 URL : <a href="<?php echo G5_SHOP_URL; ?>/price/naver.php" target="_blank"><?php echo G5_SHOP_URL; ?>/price/naver.php</a></li>
                    <li>요약상품 URL : <a href="<?php echo G5_SHOP_URL; ?>/price/naver_summary.php" target="_blank"><?php echo G5_SHOP_URL; ?>/price/naver_summary.php</a></li>
                    <li>신규상품 URL : <a href="<?php echo G5_SHOP_URL; ?>/price/naver_new.php" target="_blank"><?php echo G5_SHOP_URL; ?>/price/naver_new.php</a></li>
                    <li>신규요약 URL : <a href="<?php echo G5_SHOP_URL; ?>/price/naver_new_summary.php" target="_blank"><?php echo G5_SHOP_URL; ?>/price/naver_new_summary.php</a></li>
                </ul>
            </dd>
            <dt><a href="http://shopping.daum.net/" target="_blank">다음 쇼핑하우</a></dt>
            <dd>
                <ul>
                    <li>입점 안내 : <a href="http://commerceone.biz.daum.net/join/intro.daum" target="_blank">http://commerceone.biz.daum.net/join/intro.daum</a></li>
                    <li>전체상품 URL : <a href="<?php echo G5_SHOP_URL; ?>/price/daum.php" target="_blank"><?php echo G5_SHOP_URL; ?>/price/daum.php</a></li>
                </ul>
            </dd>
        </dl>
    </div>
</section>

<section id="anc_pricecompare_engine2">
    <h2>사이트별 엔진페이지 URL [장바구니 담기]</h2>
    <?php echo $pg_anchor; ?>

    <div class="local_desc01 local_desc">
        <p>사이트 명을 클릭하시면 해당 사이트로 이동합니다.</p>
        <p>기존 엔진페이지 URL은 가격비교 사이트에서 상품 클릭시 상품상세 페이지로 이동하지만 아래의 URL은 상품 클릭시 해당 상품이 장바구니에 담기게 됩니다.</p>

        <dl class="price_engine">
            <dt><a href="http://shopping.naver.com/" target="_blank">네이버 지식쇼핑</a></dt>
            <dd>
                <ul>
                    <li>입점 안내 : <a href="http://join.shopping.naver.com/join/intro.nhn" target="_blank">http://join.shopping.naver.com/join/intro.nhn</a></li>
                    <li>전체상품 URL : <a href="<?php echo G5_SHOP_URL; ?>/price2/naver.php" target="_blank"><?php echo G5_SHOP_URL; ?>/price2/naver.php</a></li>
                    <li>요약상품 URL : <a href="<?php echo G5_SHOP_URL; ?>/price2/naver_summary.php" target="_blank"><?php echo G5_SHOP_URL; ?>/price2/naver_summary.php</a></li>
                    <li>신규상품 URL : <a href="<?php echo G5_SHOP_URL; ?>/price2/naver_new.php" target="_blank"><?php echo G5_SHOP_URL; ?>/price2/naver_new.php</a></li>
                    <li>신규요약 URL : <a href="<?php echo G5_SHOP_URL; ?>/price2/naver_new_summary.php" target="_blank"><?php echo G5_SHOP_URL; ?>/price2/naver_new_summary.php</a></li>
                </ul>
            </dd>
            <dt><a href="http://shopping.daum.net/" target="_blank">다음 쇼핑하우</a></dt>
            <dd>
                <ul>
                    <li>입점 안내 : <a href="http://commerceone.biz.daum.net/join/intro.daum" target="_blank">http://commerceone.biz.daum.net/join/intro.daum</a></li>
                    <li>전체상품 URL : <a href="<?php echo G5_SHOP_URL; ?>/price2/daum.php" target="_blank"><?php echo G5_SHOP_URL; ?>/price2/daum.php</a></li>
                </ul>
            </dd>
        </dl>
    </div>
</section>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
