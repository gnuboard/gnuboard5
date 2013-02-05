<!doctype html>
<html lang="ko">
<head>
<title>title</title>
<meta charset="utf-8">
<script src="js/jquery-1.8.3.min.js"></script>
</head>
<body>

<p>이 문자열에서 부분을 치환합니다.</p>

<a href="#" title="치환">치환하기</a>

<script>
$(function(){
    $('a').click(function(){
        $('p').text("치환");
    });
});
</script>


</body>
</html>