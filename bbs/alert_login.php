<?php
include_once('./_common.php');
include_once(G4_PATH.'/head.sub.php');
?>

<script>
alert("<?php echo $msg; ?>");
document.location=g4_bbs_url+"/login.php?url=<?php echo $url; ?>";
</script>

<?php
include_once(G4_PATH.'/tail.sub.php');
?>