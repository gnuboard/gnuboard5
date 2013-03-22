<?
include_once('./_common.php');

$field = '';

foreach($_POST as $key=>$value)
{
    $field .= '<input type="hidden" name="'.$key.'" value="'.$value.'"/>'."\n";
}

$g4['title'] = '투표하기';
include_once(G4_PATH.'/head.sub.php');
?>

<form id="fpoll" name="fpoll" action="<?=G4_BBS_URL?>/poll_update.php" method="post">
<? echo $field; ?>
</form>

<script>
$(function() {
    $("#fpoll").submit();
});
</script>

<?
include_once(G4_PATH.'/tail.sub.php');
?>