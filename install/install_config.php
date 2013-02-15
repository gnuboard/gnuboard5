<?
include_once ('../config.php');
include_once ('./install.inc.php');

$gmnow = gmdate('D, d M Y H:i:s').' GMT';
header('Expires: 0'); // rfc2616 - Section 14.21
header('Last-Modified: ' . $gmnow);
header('Cache-Control: no-store, no-cache, must-revalidate'); // HTTP/1.1
header('Cache-Control: pre-check=0, post-check=0, max-age=0'); // HTTP/1.1
header('Pragma: no-cache'); // HTTP/1.0

if ($_POST['agree'] != '동의함') {
    echo '<meta http-equiv="content-type" content="text/html; charset=utf-8">'.PHP_EOL;
    echo '<div>라이센스(License) 내용에 동의하셔야 설치를 계속하실 수 있습니다.</div>'.PHP_EOL;
    echo '<div><a href="./">뒤로가기</a></div>'.PHP_EOL;
    exit;
}
?>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<title>그누보드4 설치 (2/3) - 설정</title>
</head>
<body>

<form id="frm_install" method="post" action="./install_db.php" onsubmit="return frm_install_submit(this)">
<table border>
<caption>MySQL 정보입력</caption>
<tr>
    <td width=100>Host</td>
    <td width=200>
        <input name="mysql_host" type="text" value="localhost">
    </td>
</tr>
<tr>
    <td>User</td>
    <td>
        <input name="mysql_user" type="text">
    </td>
</tr>
<tr>
    <td>Password</td>
    <td>
        <input name="mysql_pass" type="text">
    </td>
</tr>
<tr>
    <td>DB</td>
    <td>
        <input name="mysql_db" type="text">
    </td>
</tr>
<tr>
    <td>Port</td>
    <td>
        <input name="mysql_port" type="text" value="3306">
        <br>가능한 변경하지 마십시오.
    </td>
</tr>
<tr>
    <td>TABLE명 접두사</td>
    <td>
        <input name="table_prefix" type="text" value="g4s_">
        <br>가능한 변경하지 마십시오.
    </td>
</tr>
</table>
<br>

<table border>
<caption>최고관리자 정보입력</caption>
<tr>
    <td width=100>회원 ID</td>
    <td width=200>
        <input name="admin_id" type="text" value="admin">
    </td>
</tr>
<tr>
    <td>패스워드</td>
    <td>
        <input name="admin_pass" type="text">
    </td>
</tr>
<tr>
    <td>이름</td>
    <td>
        <input name="admin_name" type="text" value="최고관리자">
    </td>
</tr>
<tr>
    <td>E-mail</td>
    <td>
        <input name="admin_email" type="text" value="admin@domain.com">
    </td>
</tr>
</table>

<h4>이미 그누보드4가 존재한다면 DB 자료가 망실되므로 주의하십시오.</h4>

<input type="submit" value="다음">

<script>
function frm_install_submit(f)
{
    if (f.mysql_host.value == '')
    {
        alert('MySQL Host 를 입력하십시오.'); f.mysql_host.focus(); return false;
    }
    else if (f.mysql_user.value == '')
    {
        alert('MySQL User 를 입력하십시오.'); f.mysql_user.focus(); return false;
    }
    else if (f.mysql_db.value == '')
    {
        alert('MySQL DB 를 입력하십시오.'); f.mysql_db.focus(); return false;
    }
    else if (f.admin_id.value == '')
    {
        alert('최고관리자 ID 를 입력하십시오.'); f.admin_id.focus(); return false;
    }
    else if (f.admin_pass.value == '')
    {
        alert('최고관리자 패스워드를 입력하십시오.'); f.admin_pass.focus(); return false;
    }
    else if (f.admin_name.value == '')
    {
        alert('최고관리자 이름을 입력하십시오.'); f.admin_name.focus(); return false;
    }
    else if (f.admin_email.value == '')
    {
        alert('최고관리자 E-mail 을 입력하십시오.'); f.admin_email.focus(); return false;
    }


    if(/^[a-z][a-z0-9]/i.test(f.admin_id.value) == false) {
        alert('최고관리자 회원 ID는 첫자는 반드시 영문자 그리고 영문자와 숫자로만 만드셔야 합니다.');
        f.admin_id.focus();
        return false;
    }

    return true;
}
</script>

</body>
</html>
