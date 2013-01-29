<?
if (!defined('_GNUBOARD_')) exit;
?>

    <noscript>
        <p>
            귀하께서 사용하시는 브라우저는 현재 <strong>자바스크립트를 사용하지 않음</strong>으로 설정되어 있습니다.<br>
            <strong>자바스크립트를 사용하지 않음</strong>으로 설정하신 경우는 수정이나 삭제시 별도의 경고창이 나오지 않으므로 이점 주의하시기 바랍니다.
        </p>
    </noscript>
</div>

<footer>
    <p>Copyright &copy; 소유하신 도메인. All rights reserved.</p>
</footer>

<!-- <p>실행시간 : <?=get_microtime() - $begin_time;?> -->

<script src="<?=G4_ADMIN_URL?>/admin.js"></script>

<?
include_once(G4_PATH.'/tail.sub.php');
?>