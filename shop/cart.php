<?
include_once("./_common.php");

$g4[title] = "장바구니";
include_once("./_head.php");
?>

<img src="<?=$g4[shop_img_path]?>/top_cart.gif" border="0"><p>

<?
$s_page = 'cart.php';
$s_on_uid = get_session('ss_on_uid');
include "$g4[shop_path]/cartsub.inc.php";
?>
<br><br>

<?
include_once("./_tail.php");
?>