<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

include_once("$g4[path]/head.sub.php");
include_once("$g4[path]/lib/outlogin.lib.php");
include_once("$g4[path]/lib/poll.lib.php");
include_once("$g4[path]/lib/visit.lib.php");
include_once("$g4[path]/lib/connect.lib.php");
include_once("$g4[path]/lib/popular.lib.php");

//print_r2(get_defined_constants());

// 사용자 화면 상단과 좌측을 담당하는 페이지입니다.
// 상단, 좌측 화면을 꾸미려면 이 파일을 수정합니다.

$table_width = 900;

//print_r2($g4);
//$dir = dirname($HTTP_SERVER_VARS["PHP_SELF"]);
?>

<style>
.amount { color:#2266BB; font-weight:bold; font-family:Verdana; }
.c1 { background-color:#94D7E7; }
.c2 { background-color:#E7F3F7; }
</style>

<!-- 전체 -->
<table align=center cellpadding=0 cellspacing=0 border=0>
<tr>
    <td width='900'>

<!-- 상단 -->
<table align=center width='<?=$table_width?>' cellpadding=0 cellspacing=0 border=0>
<tr><td colspan=2 height=4 bgcolor=#DCDCDB></td></tr>
<tr>
    <td rowspan=2 align=left width=200 height=60><a href='<?=$g4[path]?>/'><img src='<?=$g4[path]?>/data/common/logo_img' border=0></a></td>
    <td align=right class=small>

       <div style="position:relative">
            <div style='position:absolute; top:70px; right:-100px;'>
                <?include("$g4[shop_path]/boxtodayview.inc.php");?>
            </div>
        </div>

        <? if ($is_member) { ?>
        <a href='<?=$g4[bbs_path]?>/logout.php'>로그아웃</a> |
        <a href='<?=$g4[bbs_path]?>/member_confirm.php?url=register_form.php'>정보수정</a> |
        <? } else { ?>
        <a href='<?=$g4[bbs_path]?>/login.php?url=<?=$urlencode?>'>로그인</a> |
        <a href='<?=$g4[bbs_path]?>/register.php'>회원가입</a> |
        <? } ?>

        <a href='<?=$g4[shop_path]?>/cart.php'>장바구니<span class=small>(<?=get_cart_count(get_session('ss_uniqid'), $sw_direct, $member['mb_id']);?>)</span></a> |
        <a href='<?=$g4[shop_path]?>/orderinquiry.php'>주문조회</a> |
        <a href='<?=$g4[shop_path]?>/faq.php'>FAQ</a> |
        <a href='<?=$g4[shop_path]?>/itemuselist.php'>사용후기</a> |
        <a href='<?=$g4[shop_path]?>/mypage.php'>마이페이지</a>&nbsp;
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
        <input type=image src='<?=$g4[path]?>/img/btn_search.gif' border=0 align=absmiddle>&nbsp;
        </form>
        <script>
        function search_submit(f) {
            if (f.search_flag.value == '상품') {
                f.action = '<?=$g4[shop_path]?>/search.php';
            } else {
                f.stx.value = f.search_str.value;
                f.action = '<?=$g4[bbs_path]?>/search.php';
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
        <tr><td><?include_once("$g4[shop_path]/boxcategory.inc.php");?></td></tr>
        </table><br>


        <!-- 이벤트 -->
        <table bgcolor=#CCCCCC width=185 cellpadding=1 cellspacing=0>
        <tr><td><?include_once("$g4[shop_path]/boxevent.inc.php");?></td></tr>
        </table><br>


        <!-- 커뮤니티 -->
        <table bgcolor=#CCCCCC width=185 cellpadding=1 cellspacing=0>
        <tr><td><?include_once("$g4[shop_path]/boxcommunity.inc.php");?></td></tr>
        </table><br>


        <!-- 장바구니 -->
        <table cellpadding=1 cellspacing=0 bgcolor=#D2D2D2>
        <tr><td><?include_once("$g4[shop_path]/boxcart.inc.php");?></td></tr>
        </table><br>


        <!-- 보관함 -->
        <table cellpadding=1 cellspacing=0 bgcolor=#D2D2D2>
        <tr><td><?include_once("$g4[shop_path]/boxwish.inc.php");?></td></tr>
        </table><br>

        <!-- 왼쪽 배너 -->
        <?=display_banner('왼쪽');?><br>

    </td>
    <td width=5></td>
    <td valign=top width='<?=((int)$table_width-190)?>'>

