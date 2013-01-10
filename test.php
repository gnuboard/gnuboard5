<?
$str = "xx";
$len = 2;
//echo ord($str{$len});
?>

<!doctype html>
<html lang="ko">
<head>
<title>테스트</title>
<meta charset="utf-8">
<style>
input {
border-radius:7px;  /*모서리 깍이는 정도*/
border:1px solid #dedede;  /*선두께, 스타일(점선), 컬러*/
background-color:#f7f7f7;  /*배경 컬러*/
padding:5px;
box-shadow:0 0 10px silver;
}
input:focus {
border-radius:7px;  /*모서리 깍이는 정도*/
border:1px solid #ff3061;  /*선두께, 스타일(점선), 컬러*/
background-color:#f7f7f7;  /*배경 컬러*/
padding:5px;
box-shadow:0 0 10px #ff3061;
outline:0;
}
</style>
</head>
<body>


<label for="text">테스트 필수</label>
<input type="text" id="text" title="테스트">

<input type="checkbox">

</body>
</html>