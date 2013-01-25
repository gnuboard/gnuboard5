<?
include_once("./_common.php");
include_once("$g4[path]/lib/latest.lib.php");

define("_INDEX_", TRUE);

$g4[title] = "";
include_once("$g4[path]/head.php");
?>

<script language="JavaScript" src="<?=$g4[path]?>/js/shop.js"></script>

<table width=100% cellpadding=0 cellspacing=0>
<tr>
    <td valign=top>
        <img src='<?=$g4[path]?>/data/common/main_img' border=0><br><br>
        <table width=100% cellpadding=0 cellspacing=0>
        <tr>
            <td colspan=2>
                <?
                // 히트상품
                $type = 1;
                if ($default["de_type{$type}_list_use"]) 
                {
                    echo "<a href='$g4[shop_path]/listtype.php?type={$type}'><img src='$g4[shop_img_path]/bar_type{$type}.gif' border=0></a><br>";
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
                    echo "<a href='$g4[shop_path]/listtype.php?type={$type}'><img src='$g4[shop_img_path]/bar_type{$type}.gif' border=0></a><br>";
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
                    echo "<a href='$g4[shop_path]/listtype.php?type={$type}'><img src='$g4[shop_img_path]/bar_type{$type}.gif' border=0></a><br>";
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
                    echo "<a href='$g4[shop_path]/listtype.php?type={$type}'><img src='$g4[shop_img_path]/bar_type{$type}.gif' border=0></a><br>";
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
            echo "<a href='$g4[shop_path]/listtype.php?type={$type}'><img src='$g4[shop_img_path]/bar_type{$type}.gif' border=0></a><br>";
            display_type($type, $default["de_type{$type}_list_skin"], $default["de_type{$type}_list_mod"], $default["de_type{$type}_list_row"], $default["de_type{$type}_img_width"], $default["de_type{$type}_img_height"]);
        }
        ?><br><br>

		<!-- 온라인 투표 -->
        <?=poll();?><br>

		<!-- 방문자 수 -->
        <?=visit();?><br>

		<!-- 메인 배너 -->
        <?=display_banner('메인');?><br>
	</td>
</tr>
</table>
<BR><BR>

<?
include "$g4[shop_path]/newwin.inc.php"; // 새창띄우기

include_once("$g4[path]/tail.php");
?>