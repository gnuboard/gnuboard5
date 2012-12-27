<?php
include_once('./_common.php');
include_once($g4['path'].'/lib/latest.lib.php');

$g4['title'] = '';
include_once('./_head.php');
?>

<?=latest("neo",1,5)?>
<?=latest("neo",1,5)?>

<?
include_once('./_tail.php');
?>
