<?
$sub_menu = "500210";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

$g4[title] = "가격비교사이트 연동";
include_once ("$g4[admin_path]/admin.head.php");
?>

<?=subtitle($g4[title])?>
<p align=lh>
사용하실 가격비교사이트에 입점하신 후 해당하는 <B>엔진페이지 URL</B> 을 해당 사이트에 알려주시면 됩니다.

<ul class=lh>
    <li><b>네이버 지식쇼핑 (<a href="http://shopping.naver.com/" target=_blank>http://shopping.naver.com/</a>)</b>
        <ul>
            <li>입점 안내 : <a href="http://shopping.naver.com/join/index.nhn" target=_blank>http://shopping.naver.com/join/index.nhn</a>
            <li>전체상품 URL : <a href="<?=$g4[shop_url]?>/price/naver.php" target=_blank><?=$g4[shop_url]?>/price/naver.php</a>
            <li>요약상품 URL : <a href="<?=$g4[shop_url]?>/price/naver_summary.php" target=_blank><?=$g4[shop_url]?>/price/naver_summary.php</a>
            <li>신규상품 URL : <a href="<?=$g4[shop_url]?>/price/naver_new.php" target=_blank><?=$g4[shop_url]?>/price/naver_new.php</a>
            <li>신규요약 URL : <a href="<?=$g4[shop_url]?>/price/naver_new_summary.php" target=_blank><?=$g4[shop_url]?>/price/naver_new_summary.php</a>
        </ul>
    </li><br>

    <li><b>다음 쇼핑하우 (<a href="http://shopping.daum.net/" target=_blank>http://shopping.daum.net/</a>)</b>
        <ul>
            <li>입점 안내 : <a href="http://commerceone.biz.daum.net/join/intro.daum" target=_blank>http://commerceone.biz.daum.net/join/intro.daum</a>
            <li>전체상품 URL : <a href="<?=$g4[shop_url]?>/price/daum.php" target=_blank><?=$g4[shop_url]?>/price/daum.php</a>
        </ul>
    </li><br>

    <li><b>어바웃 (<a href="http://www.about.co.kr/" target=_blank>http://www.about.co.kr/</a>)</b>
        <ul>
            <li>입점 안내 : <a href="http://member.about.co.kr/LaunchIntroduce/Default.aspx" target=_blank>http://member.about.co.kr/LaunchIntroduce/Default.aspx</a>
            <li>전체EP URL : <a href="<?=$g4[shop_url]?>/price/about.php" target=_blank><?=$g4[shop_url]?>/price/about.php</a>
            <li>요약EP URL : <a href="<?=$g4[shop_url]?>/price/about_new.php" target=_blank><?=$g4[shop_url]?>/price/about_new.php</a>
        </ul>
    </li><br>

<? /* ?>
    <li><b>옥션 오픈쇼핑 (<a href="http://openshopping.auction.co.kr/" target=_blank>http://openshopping.auction.co.kr/</a>)</b>
        <ul>
            <li>입점 안내 : <a href="http://openshopping.auction.co.kr/customer/shopinfo/shop_info.asp" target=_blank>http://openshopping.auction.co.kr/customer/shopinfo/shop_info.asp</a>
            <!-- <li>상품DB파일 URL : <a href="<?=$g4[shop_url]?>/price/auction.php" target=_blank><?=$g4[shop_url]?>/price/auction.php</a> -->
            <li>전체상품 URL : <a href="<?=$g4[shop_url]?>/price/auction.php" target=_blank><?=$g4[shop_url]?>/price/auction.php</a>
            <li>요약상품 URL : <a href="<?=$g4[shop_url]?>/price/auction_summary.php" target=_blank><?=$g4[shop_url]?>/price/auction_summary.php</a>
            <li>신규상품 URL : <a href="<?=$g4[shop_url]?>/price/auction_new.php" target=_blank><?=$g4[shop_url]?>/price/auction_new.php</a>
        </ul>
    </li><br>
<? */ ?>

    <li><b>다나와 (<a href="http://www.danawa.co.kr" target=_blank>http://www.danawa.co.kr</a>)</b>
        <ul>
            <li>입점 안내 : <a href="http://pc.danawa.com/contact/contactus.html" target=_blank>http://pc.danawa.com/contact/contactus.html</a>
            <li>엔진페이지 URL : <a href="<?=$g4[shop_url]?>/price/danawa.php" target=_blank><?=$g4[shop_url]?>/price/danawa.php</a>
        </ul>
    </li><br>

    <li><b>비비 (<a href="http://www.bb.co.kr" target=_blank>http://www.bb.co.kr</a>)</b>
        <ul>
            <li>입점 안내 : <a href="http://www.bb.co.kr/mainbbr/regist/entry_kcp1.php?partner=kcp" target=_blank>http://www.bb.co.kr/mainbbr/regist/entry_kcp1.php?partner=kcp</a>
            <li>엔진페이지 URL : <a href="<?=$g4[shop_url]?>/price/bb.php" target=_blank><?=$g4[shop_url]?>/price/bb.php</a>
        </ul>
    </li><br>

    <li><b>에누리 (<a href="http://www.enuri.com" target=_blank>http://www.enuri.com</a>)</b>
        <ul>
            <li>입점 안내 : <a href="http://www.enuri.com/MallRegister/MallRegister.asp" target=_blank>http://www.enuri.com/MallRegister/MallRegister.asp</a>
            <li>엔진페이지 URL : <a href="<?=$g4[shop_url]?>/price/enuri_list.php" target=_blank><?=$g4[shop_url]?>/price/enuri_list.php</a>
        </ul>
    </li><br>
    
    <li><b>마이마진 (<a href="http://www.mymargin.com" target=_blank>http://www.mymargin.com</a>)</b>
        <ul>
            <li>입점 안내 : <a href="http://www.mymargin.com/shop_admin/reg/process_su.asp" target=_blank>http://www.mymargin.com/shop_admin/reg/process_su.asp</a>
            <li>엔진페이지 URL : <a href="<?=$g4[shop_url]?>/price/mymargin.php" target=_blank><?=$g4[shop_url]?>/price/mymargin.php</a>
        </ul>
    </li><br>
    
    <li><b>오미 (<a href="http://www.omi.co.kr" target=_blank>http://www.omi.co.kr</a>)</b>
        <ul>
            <li>입점 안내 : <a href="http://www.omi.co.kr/regist/shoppingmall.asp" target=_blank>http://www.omi.co.kr/regist/shoppingmall.asp</a>
            <li>엔진페이지 URL : <a href="<?=$g4[shop_url]?>/price/omi_ufo.php" target=_blank><?=$g4[shop_url]?>/price/omi_ufo.php</a>
        </ul>
    </li><br>
    
    <li><b>샵바인더 (<a href="http://www.shopbinder.com" target=_blank>http://www.shopbinder.com</a>)</b>
        <ul>
            <li>입점 안내 : <a href="http://www.shopbinder.com/guide/regist.asp" target=_blank>http://www.shopbinder.com/guide/regist.asp</a>
            <li>엔진페이지 URL : <a href="<?=$g4[shop_url]?>/price/shopbinder.php" target=_blank><?=$g4[shop_url]?>/price/shopbinder.php</a>
        </ul>
    </li><br>
    
    <li><b>mym 야비스 (<a href="http://yavis.nate.com/" target=_blank>http://yavis.nate.com/</a>)</b>
        <ul>
            <li>입점 안내 : <a href="http://search.nate.com/yavis/search.asp?ta=front/help_guide" target=_blank>http://search.nate.com/yavis/search.asp?ta=front/help_guide</a>
            <li>엔진페이지 URL : <a href="<?=$g4[shop_url]?>/price/yavis.php" target=_blank><?=$g4[shop_url]?>/price/yavis.php</a>
        </ul>
    </li><br>
</ul>

<?
include_once ("$g4[admin_path]/admin.tail.php");
?>
