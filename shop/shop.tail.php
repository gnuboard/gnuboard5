<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

$admin = get_admin("super");

// 사용자 화면 우측과 하단을 담당하는 페이지입니다.
// 우측, 하단 화면을 꾸미려면 이 파일을 수정합니다.
?>

    </div><!-- Container End -->
</div><!-- Wrapper End -->

<footer id="ft">
    <div>
        <a href="<?php echo G4_SHOP_URL; ?>/" id="ft_logo"><img src="<?php echo G4_DATA_URL; ?>/common/logo_img" alt="처음으로"></a>
        <ul>
            <li><a href="<?=G4_SHOP_URL?>/content.php?co_id=company">회사소개</a></li>
            <li><a href="<?=G4_SHOP_URL?>/content.php?co_id=provision">서비스이용약관</a></li>
            <li><a href="<?=G4_SHOP_URL?>/content.php?co_id=privacy">개인정보 취급방침</a></li>
        </ul>
        <p>
            <span><?=$default['de_admin_company_addr']?></span>
            <span><b>전화</b> <?=$default['de_admin_company_tel']?></span>
            <span><b>팩스</b> <?=$default['de_admin_company_fax']?></span>
            <span><b>운영자</b> <?=$admin['mb_name']?></span><br>
            <span><b>사업자 등록번호</b> <?=$default['de_admin_company_saupja_no']?></span>
            <span><b>대표</b> <?=$default['de_admin_company_owner']?></span>
            <span><b>개인정보관리책임자</b> <?=$default['de_admin_info_name']?></span><br>
            <span><b>통신판매업신고번호</b> <?=$default['de_admin_tongsin_no']?></span>
            <?php if ($default['de_admin_buga_no']) echo '<span>부가통신사업신고번호 '.$default['de_admin_buga_no'].'</span>'; ?><br>
            Copyright &copy; 2001-2013 <?=$default['de_admin_company_name']?>. All Rights Reserved.
        </p>
        <a href="#" id="ft_totop">상단으로</a>
    </div>
</footer>

<?php if(!G4_IS_MOBILE){ ?>
<a href="<?php echo $_SERVER['PHP_SELF'].($_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING'].'&amp;':'?').'device=mobile'; ?>" id="device_change">모바일 버전으로 보기</a>
<?php } ?>

<?
$sec = get_microtime() - $begin_time;
$file = $_SERVER['PHP_SELF'];
?>

<?
include_once(G4_PATH.'/tail.sub.php');
?>
