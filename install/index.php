<?php
include_once ('../config.php');
$title = G5_VERSION." 설치 3단계 중 1단계 라이센스 확인";
require_once('./library.check.php');
include_once ('./install.inc.php');
?>

<?php
if ($exists_data_dir && $write_data_dir) {
?>
    <p>
        <strong class="st_strong">라이센스(License) 내용을 반드시 확인하십시오.</strong><br>
        라이센스에 동의하시는 경우에만 설치가 진행됩니다.
    </p>

    <textarea name="textarea" id="idx_license" readonly><?php echo implode('', file('../LICENSE.txt')); ?></textarea>

<form action="./install_config.php" method="post" onsubmit="return frm_submit(this);">
<div id="idx_agree">
    <label for="agree">동의합니다.</label>
    <input type="checkbox" id="agree" name="agree" value="동의함">
</div>

<div id="btn_confirm">
    <input type="submit" value="다음">
</div>

</form>

<script>
function frm_submit(f)
{
    if (!f.agree.checked) {
        alert("라이센스 내용에 동의하셔야 설치가 가능합니다.");
        return false;
    }
    return true;
}
</script>
<?php
} // if
?>

</div>

</body>
</html>