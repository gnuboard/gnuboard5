<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>AJAX 동적 컨텐츠 테스트</title>
<script src="js/jquery-1.8.3.min.js"></script>
</head>
<body>

<a href="">클릭</a>
<div></div>

<script>
$(function(){
    $('a').click(function(){
        $('div').text('클릭되었습니다.');
    });
});
</script>

</body>
</html>