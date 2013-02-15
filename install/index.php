<?
include_once ('../config.php');
include_once ('./install.inc.php');
?>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<title>그누보드4 설치 (1/3) - 라이센스(License)</title>
</head>

<body>
<h4>라이센스(License) 내용을 반드시 확인하십시오.</h4>
<textarea name="textarea" style='width:100%;height:300px;' readonly>
<?=implode('', file('../LICENSE.txt'));?>
</textarea>
<form method="post" action="./install_config.php" onsubmit="return frm_submit(this);">
<div>
    <input type="checkbox" id="agree" name="agree" value="동의함"> 
    <label for="agree">설치를 원하시면 위 내용에 동의하셔야 합니다.</label><br>
    동의에 선택하신 후 &lt;다음&gt; 버튼을 클릭해 주세요.
</div>
<input type="submit" value="다음">
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

</body>
</html>