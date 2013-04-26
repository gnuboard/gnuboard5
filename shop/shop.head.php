<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

include_once(G4_PATH.'/head.sub.php');
include_once(G4_LIB_PATH.'/outlogin.lib.php');
include_once(G4_LIB_PATH.'/poll.lib.php');
include_once(G4_LIB_PATH.'/visit.lib.php');
include_once(G4_LIB_PATH.'/connect.lib.php');
include_once(G4_LIB_PATH.'/popular.lib.php');

//print_r2(get_defined_constants());

// 사용자 화면 상단과 좌측을 담당하는 페이지입니다.
// 상단, 좌측 화면을 꾸미려면 이 파일을 수정합니다.

$table_width = 900;

//print_r2($g4);
//$dir = dirname($HTTP_SERVER_VARS["PHP_SELF"]);
?>

<header id="shd">
    <h1><?php echo $config['cf_title'] ?></h1>

    <div id="to_content"><a href="#container">내용 바로가기</a></div>

</header>

<!-- 전체 -->
<table align=center cellpadding=0 cellspacing=0 border=0>
<tr>
    <td width='900'>
        <!-- 새창 -->
        <? if(defined('_INDEX_')) { // index에서만 실행 ?>
        <div style="position:relative;z-index:100"><? include G4_SHOP_PATH.'/newwin.inc.php'; // 새창띄우기 ?></div>
        <? } ?>

        <!-- 오늘본 상품 {-->
        <div style="position:relative">
            <div style='position:absolute; top:70px; left:<?=($table_width+10)?>px'>
                <?include(G4_SHOP_PATH.'/boxtodayview.inc.php');?>
            </div>
        </div>
        <!-- 오늘본 상품 }-->

<!-- 상단 -->
<table align=center width='<?=$table_width?>' cellpadding=0 cellspacing=0 border=0>
<tr><td colspan=2 height=4 bgcolor=#DCDCDB></td></tr>
<tr>
    <td rowspan=2 align=left width=200 height=60><a href='<?=G4_SHOP_URL?>/'><img src='<?=G4_DATA_URL?>/common/logo_img' border=0></a></td>
    <td align=right class=small>

        <? if ($is_member) { ?>
        <a href='<?=G4_BBS_URL?>/logout.php'>로그아웃</a> |
        <a href='<?=G4_BBS_URL?>/member_confirm.php?url=register_form.php'>정보수정</a> |
        <? } else { ?>
        <a href='<?=G4_BBS_URL?>/login.php?url=<?=$urlencode?>'>로그인</a> |
        <a href='<?=G4_BBS_URL?>/register.php'>회원가입</a> |
        <? } ?>

        <a href='<?=G4_SHOP_URL?>/cart.php'>장바구니<span class=small>(<?=get_cart_count(get_session('ss_uq_id'));?>)</span></a> |
        <a href='<?=G4_SHOP_URL?>/orderinquiry.php'>주문조회</a> |
        <a href='<?=G4_SHOP_URL?>/faq.php'>FAQ</a> |
        <a href='<?=G4_SHOP_URL?>/itemuselist.php'>사용후기</a> |
        <a href='<?=G4_SHOP_URL?>/mypage.php'>마이페이지</a>&nbsp;
    </td>
</tr>
<tr>
    <td colspan=2 align=right height=30>
        <!-- 검색 시작 -->
        <form name='frmsearch1' style='margin:0px;' onsubmit='return search_submit(this);'>
        <input type='hidden' name='sfl' value='wr_subject||wr_content'>
        <input type='hidden' name='sop' value='and'>
        <input type='hidden' name='stx' value=''>
        <select name='search_flag' id='search_flag' class='small'>
        <option value='상품'>상품
        <option value='게시판'>게시판
        </select>
        <input type=text name=search_str class='ed' value='<?=stripslashes(get_text($search_str))?>'>
        <input type=image src='<?=G4_SHOP_URL?>/img/btn_search.gif' border=0 align=absmiddle>&nbsp;
        </form>
        <script>
        function search_submit(f) {
            if (f.search_flag.value == '상품') {
                f.action = '<?=G4_SHOP_URL?>/search.php';
            } else {
                f.stx.value = f.search_str.value;
                f.action = '<?=G4_BBS_URL?>/search.php';
            }
        }

        <?
        if ($search_flag) {
            echo "document.getElementById('search_flag').value = '$search_flag';";
        }
        ?>
        </script>
        <!-- 검색 끝 -->
    </td>
</tr>
<tr><td colspan=2 height=1 bgcolor=#DCDCDC></td></tr>
<tr><td colspan=2 height=5></td></tr>
</table>
<!-- 상단끝 -->

<!-- 중간 -->
<table align=center width='<?=$table_width?>' cellpadding=0 cellspacing=0 border=0>
<tr>
    <td align=center valign=top width=185>

        <?=outlogin("shop_outlogin"); // 외부 로그인 ?>
        <br>

        <!-- 상품분류 -->
        <table bgcolor=#CCCCCC width=185 cellpadding=1 cellspacing=0>
        <tr><td><?include_once(G4_SHOP_PATH.'/boxcategory.inc.php');?></td></tr>
        </table><br>


        <!-- 이벤트 -->
        <table bgcolor=#CCCCCC width=185 cellpadding=1 cellspacing=0>
        <tr><td><?include_once(G4_SHOP_PATH.'/boxevent.inc.php');?></td></tr>
        </table><br>


        <!-- 커뮤니티 -->
        <table bgcolor=#CCCCCC width=185 cellpadding=1 cellspacing=0>
        <tr><td><?include_once(G4_SHOP_PATH.'/boxcommunity.inc.php');?></td></tr>
        </table><br>


        <!-- 장바구니 -->
        <table cellpadding=1 cellspacing=0 bgcolor=#D2D2D2>
        <tr><td><?include_once(G4_SHOP_PATH.'/boxcart.inc.php');?></td></tr>
        </table><br>


        <!-- 보관함 -->
        <table cellpadding=1 cellspacing=0 bgcolor=#D2D2D2>
        <tr><td><?include_once(G4_SHOP_PATH.'/boxwish.inc.php');?></td></tr>
        </table><br>

        <!-- 왼쪽 배너 -->
        <?=display_banner('왼쪽');?><br>

    </td>
    <td width=5></td>
    <td valign=top width='<?=((int)$table_width-190)?>'>

