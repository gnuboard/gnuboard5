<?php
include_once('./_common.php');
include_once('./ipin.config.php');

$option = "C";// Option

// 명령어
$cmd = "$exe $keypath $memid \"{$reserved1}\" \"{$reserved2}\" $EndPointURL $logpath $option";

// 실행
exec($cmd, $out, $ret);

$pubkey = "";
$sig = "";
$curtime = "";

$pubkey=$out[0];
$sig=$out[1];
$curtime=$out[2];

$g4['title'] = 'KCB 아이핀 본인확인';
include_once(G4_PATH.'/head.sub.php');
?>

<form name="kcbInForm" method="post" action="<?php echo $kcbForm_action; ?>">
  <input type="hidden" name="IDPCODE" value="<?php echo $idpCode; ?>" />
  <input type="hidden" name="IDPURL" value="<?php echo $idpUrl; ?>" />
  <input type="hidden" name="CPCODE" value="<?php echo $cpCode; ?>" />
  <input type="hidden" name="CPREQUESTNUM" value="<?php echo $curtime; ?>" />
  <input type="hidden" name="RETURNURL" value="<?php echo $returnUrl; ?>" />
  <input type="hidden" name="WEBPUBKEY" value="<?php echo $pubkey; ?>" />
  <input type="hidden" name="WEBSIGNATURE" value="<?php echo $sig; ?>" />
</form>

<script>
document.kcbInForm.submit();
</script>

<?php
include_once(G4_PATH.'/tail.sub.php');
?>