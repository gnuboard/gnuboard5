<?
$sub_menu = '500210';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g4['title'] = '가격비교사이트';
include_once (G4_ADMIN_PATH.'/admin.head.php');
$pg_anchor = '<ul class="anchor">
<li><a href="#anc_pricecompare_info">가격비교사이트 연동 안내</a></li>
<li><a href="#anc_pricecompare_engine">사이트별 엔진페이지 URL</a></li>
</ul>';
?>

<section id="anc_pricecompare_info" class="cbox">
    <h2>가격비교사이트 연동 안내</h2>
    <?=$pg_anchor?>

    <ol>
        <li>가격비교사이트는 네이버 지식쇼핑, 다음 쇼핑하우, 어바웃, 다나와, 비비, 에누리, 마이마진 등이 있습니다.</li>
        <li>앞서 나열한 가격비교사이트 중 희망하시는 사이트에 입점합니다.</li>
        <li><strong>사이트별 엔진페이지 URL</strong>을 참고하여 해당 엔진페이지 URL 을 입점하신 사이트에 알려주시면 됩니다.</li>
    </ol>
</section>

<section id="anc_pricecompare_engine" class="cbox">
    <h2>사이트별 엔진페이지 URL</h2>
    <?=$pg_anchor?>
    <p>사이트 명을 클릭하시면 해당 사이트로 이동합니다.</p>

    <dl>
        <dt><a href="http://shopping.naver.com/" target="_blank">네이버 지식쇼핑</a></dt>
        <dd>
            <ul>
                <li>입점 안내 : <a href="http://shopping.naver.com/join/index.nhn" target="_blank">http://shopping.naver.com/join/index.nhn</a></li>
                <li>전체상품 URL : <a href="<?=G4_SHOP_URL?>/price/naver.php" target="_blank"><?=G4_SHOP_URL?>/price/naver.php</a></li>
                <li>요약상품 URL : <a href="<?=G4_SHOP_URL?>/price/naver_summary.php" target="_blank"><?=G4_SHOP_URL?>/price/naver_summary.php</a></li>
                <li>신규상품 URL : <a href="<?=G4_SHOP_URL?>/price/naver_new.php" target="_blank"><?=G4_SHOP_URL?>/price/naver_new.php</a></li>
                <li>신규요약 URL : <a href="<?=G4_SHOP_URL?>/price/naver_new_summary.php" target="_blank"><?=G4_SHOP_URL?>/price/naver_new_summary.php</a></li>
            </ul>
        </dd>
        <dt><a href="http://shopping.daum.net/" target="_blank">다음 쇼핑하우</a></dt>
        <dd>
            <ul>
                <li>입점 안내 : <a href="http://commerceone.biz.daum.net/join/intro.daum" target="_blank">http://commerceone.biz.daum.net/join/intro.daum</a></li>
                <li>전체상품 URL : <a href="<?=G4_SHOP_URL?>/price/daum.php" target="_blank"><?=G4_SHOP_URL?>/price/daum.php</a></li>
            </ul>
        </dd>
        <dt><a href="http://www.about.co.kr/" target="_blank">어바웃</a></dt>
        <dd>
            <ul>
                <li>입점 안내 : <a href="http://member.about.co.kr/LaunchIntroduce/Default.aspx" target="_blank">http://member.about.co.kr/LaunchIntroduce/Default.aspx</a></li>
                <li>전체EP URL : <a href="<?=G4_SHOP_URL?>/price/about.php" target="_blank"><?=G4_SHOP_URL?>/price/about.php</a></li>
                <li>요약EP URL : <a href="<?=G4_SHOP_URL?>/price/about_new.php" target="_blank"><?=G4_SHOP_URL?>/price/about_new.php</a></li>
            </ul>
        </dd>
        <dt><a href="http://www.danawa.co.kr" target="_blank">다나와</a></dt>
        <dd>
            <ul>
                <li>입점 안내 : <a href="http://pc.danawa.com/contact/contactus.html" target="_blank">http://pc.danawa.com/contact/contactus.html</a></li>
                <li>엔진페이지 URL : <a href="<?=G4_SHOP_URL?>/price/danawa.php" target="_blank"><?=G4_SHOP_URL?>/price/danawa.php</a></li>
            </ul>
        </dd>
        <dt><a href="http://www.bb.co.kr" target="_blank">비비</a></dt>
        <dd>
            <ul>
                <li>입점 안내 : <a href="http://www.bb.co.kr/mainbbr/regist/entry_kcp1.php?partner=kcp" target="_blank">http://www.bb.co.kr/mainbbr/regist/entry_kcp1.php?partner=kcp</a></li>
                <li>엔진페이지 URL : <a href="<?=G4_SHOP_URL?>/price/bb.php" target="_blank"><?=G4_SHOP_URL?>/price/bb.php</a></li>
            </ul>
        </dd>
        <dt><a href="http://www.enuri.com" target="_blank">에누리</a></dt>
        <dd>
            <ul>
                <li>입점 안내 : <a href="http://www.enuri.com/MallRegister/MallRegister.asp" target="_blank">http://www.enuri.com/MallRegister/MallRegister.asp</a></li>
                <li>엔진페이지 URL : <a href="<?=G4_SHOP_URL?>/price/enuri_list.php" target="_blank"><?=G4_SHOP_URL?>/price/enuri_list.php</a></li>
            </ul>
        </dd>
        <dt><a href="http://www.mymargin.com" target="_blank">마이마진</a></dt>
        <dd>
            <ul>
                <li>입점 안내 : <a href="http://www.mymargin.com/shop_admin/reg/process_su.asp" target="_blank">http://www.mymargin.com/shop_admin/reg/process_su.asp</a></li>
                <li>엔진페이지 URL : <a href="<?=G4_SHOP_URL?>/price/mymargin.php" target="_blank"><?=G4_SHOP_URL?>/price/mymargin.php</a></li>
            </ul>
        </dd>
    </dl>
</section>

<?
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>
