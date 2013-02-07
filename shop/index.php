<?
include_once("./_common.php");
include_once(G4_LIB_PATH.'/latest.lib.php');
include_once(G4_LIB_PATH.'/thumbnail.lib.php');

define("G4_SHOP_INDEX", TRUE);

$sum = 0;
for ($i=1; $i<=30; $i++) {
    $sum += constant('G4_TYPE'.$i);
}
echo $sum;

$g4['title'] = $default['de_admin_company_name'];
include_once('./_head.php');
?>

<script src="<?=G4_JS_URL?>/shop.js"></script>

<table width=100% cellpadding=0 cellspacing=0>
<tr>
    <td valign=top>
        <img src='<?=G4_DATA_URL?>/common/main_img' border=0><br><br>
        <table width=100% cellpadding=0 cellspacing=0>
        <tr>
            <td colspan=2>
                <?
                // 히트상품
                $type = 1;
                if ($default["de_type{$type}_list_use"])
                {
                    echo "<a href='".G4_SHOP_URL."/listtype.php?type={$type}'><img src='".G4_SHOP_IMG_URL."/bar_type{$type}.gif' border=0></a><br>";
                    display_type($type, $default["de_type{$type}_list_skin"], $default["de_type{$type}_list_mod"], $default["de_type{$type}_list_row"], $default["de_type{$type}_img_width"], $default["de_type{$type}_img_height"]);
                }
                ?>
            </td>
        </tr>
        <tr><td colspan=2 height=20></td></tr>
        <tr>
            <td colspan=2>
                <?
                // 추천상품
                $type = 2;
                if ($default["de_type{$type}_list_use"])
                {
                    echo "<a href='".G4_SHOP_URL."/listtype.php?type={$type}'><img src='".G4_SHOP_IMG_URL."/bar_type{$type}.gif' border=0></a><br>";
                    display_type($type, $default["de_type{$type}_list_skin"], $default["de_type{$type}_list_mod"], $default["de_type{$type}_list_row"], $default["de_type{$type}_img_width"], $default["de_type{$type}_img_height"]);
                }
                ?>
            </td>
        </tr>
        <tr><td colspan=2 height=20></td></tr>
        <tr>
            <td colspan=2>
                <?
                // 인기상품
                $type = 4;
                if ($default["de_type{$type}_list_use"])
                {
                    echo "<a href='".G4_SHOP_URL."/listtype.php?type={$type}'><img src='".G4_SHOP_IMG_URL."/bar_type{$type}.gif' border=0></a><br>";
                    display_type($type, $default["de_type{$type}_list_skin"], $default["de_type{$type}_list_mod"], $default["de_type{$type}_list_row"], $default["de_type{$type}_img_width"], $default["de_type{$type}_img_height"]);
                }
                ?>
            </td>
        </tr>
        <tr><td colspan=2 height=20></td></tr>
        <tr>
            <td colspan=2>
                <?
                // 할인상품
                $type = 5;
                if ($default["de_type{$type}_list_use"])
                {
                    echo "<a href='".G4_SHOP_URL."/listtype.php?type={$type}'><img src='".G4_SHOP_IMG_URL."/bar_type{$type}.gif' border=0></a><br>";
                    display_type($type, $default["de_type{$type}_list_skin"], $default["de_type{$type}_list_mod"], $default["de_type{$type}_list_row"], $default["de_type{$type}_img_width"], $default["de_type{$type}_img_height"]);
                }
                ?>
            </td>
        </tr>
        </table>
    </td>
    <td valign=top>

        <?
        // 최신상품
        $type = 3;
        if ($default["de_type{$type}_list_use"])
        {
            echo "<a href='".G4_SHOP_URL."/listtype.php?type={$type}'><img src='".G4_SHOP_IMG_URL."/bar_type{$type}.gif' border=0></a><br>";
            display_type($type, $default["de_type{$type}_list_skin"], $default["de_type{$type}_list_mod"], $default["de_type{$type}_list_row"], $default["de_type{$type}_img_width"], $default["de_type{$type}_img_height"]);
        }
        ?><br><br>

		<!-- 온라인 투표 -->
        <?=poll('neo');?><br>

		<!-- 방문자 수 -->
        <?=visit('neo');?><br>

		<!-- 메인 배너 -->
        <?=display_banner('메인');?><br>
	</td>
</tr>
</table>
<BR><BR>

<?
include_once(G4_SHOP_PATH.'/newwin.inc.php'); // 새창띄우기

include_once('./_tail.php');
?>