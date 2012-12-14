<?
if (!defined("_GNUBOARD_")) exit;
?>

</div>

<footer>
    <p>Copyright &copy; 소유하신 도메인. All rights reserved.</p>
</footer>

<!-- <p>실행시간 : <?=get_microtime() - $begin_time;?> -->

<script src="<?=$g4['admin_path']?>/admin.js"></script>
<script src="<?=$g4['admin_path']?>/gnb.js"></script>

<?
include_once($g4['path'].'/tail.sub.php');
?>