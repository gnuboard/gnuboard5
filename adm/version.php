<?
//
// 조병완(korone)님 , 남규아빠(eagletalon)님께서 만들어 주셨습니다.
//

$sub_menu = "100400";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

$g5[title] = "버전확인";

include_once("./admin.head.php");
include_once("$g5[path]/lib/mailer.lib.php");

echo "현재버전 : <b>";
$args = "head -1 ".$g5[path]."/HISTORY";
system($args);
echo "</b>";
?>

<table width=100% border="0" align="left" cellpadding="0" cellspacing="0">
<tr>
    <td>
        <textarea name="textarea" style='width:100%; line-height:150%; padding:10px;' rows="25" class=tx readonly><?php echo implode("", file(G5_PATH.'/HISTORY'));?></textarea>
    </td>
</tr>
</table>

<?
include_once("./admin.tail.php");
?>
