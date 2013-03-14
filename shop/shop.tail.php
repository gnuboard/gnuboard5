<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

$admin = get_admin("super");

// 사용자 화면 우측과 하단을 담당하는 페이지입니다.
// 우측, 하단 화면을 꾸미려면 이 파일을 수정합니다.
?>

</td></tr></table>
<!-- 중간끝 -->


<!-- 하단 -->
<table align=center width='<?=$table_width?>' cellpadding=0 cellspacing=0>
<tr>
    <td width=180 bgcolor=#EBEBEB><a href='<?=G4_SHOP_URL?>/'><img src='<?=G4_DATA_URL?>/common/logo_img' border=0 style="filter:gray();"></a></td>
    <td><img src='<?=G4_SHOP_URL?>/img/tail_img01.gif'></td>
    <td width=10></td>
    <td><img src='<?=G4_SHOP_URL?>/img/tail_img02.gif'></td>
    <td width=770 bgcolor=#EBEBEB style='padding-left:10px;'>
        <table width=98% cellpadding=0 cellspacing=0 border=0>
        <tr><td height=30>
            <a href="<?=G4_SHOP_URL?>/content.php?co_id=company">회사소개</a> |
            <a href="<?=G4_SHOP_URL?>/content.php?co_id=provision">서비스이용약관</a> |
            <a href="<?=G4_SHOP_URL?>/content.php?co_id=privacy">개인정보 취급방침</a>
            </td></tr>
        <tr><td height=1 bgcolor=#CBCBCB></td></tr>
        <tr><td height=60 style='line-height:150%'>
            <FONT COLOR="#46808F">
                <?=$default['de_admin_company_addr']?> /
                전화 : <?=$default['de_admin_company_tel']?> /
                팩스 : <?=$default['de_admin_company_fax']?> /
                운영자 : <?=$admin['mb_name']?> <BR>
                사업자 등록번호 : <?=$default['de_admin_company_saupja_no']?> /
                대표 : <?=$default['de_admin_company_owner']?> /
                개인정보관리책임자 : <?=$default['de_admin_info_name']?> <br>
                통신판매업신고번호 : <?=$default['de_admin_tongsin_no']?>
                <? if ($default['de_admin_buga_no']) echo " / 부가통신사업신고번호 : {$default['de_admin_buga_no']}"; ?>
                <br>Copyright &copy; 2001-2013 <?=$default['de_admin_company_name']?>. All Rights Reserved. </FONT></td></tr></table>
    </td>
</tr>
</table>
<!-- 하단끝 -->


<?
$sec = get_microtime() - $begin_time;
$file = $_SERVER['PHP_SELF'];
?>

</td>
</tr>
</table><br/>
<!-- 전체끝 -->


<?
include_once(G4_PATH.'/tail.sub.php');
?>
