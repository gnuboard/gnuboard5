<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8">
<script src="./js/jquery-1.8.3.min.js"></script>
<style>
.nos {position:relative;padding:100px}
.test {position:relative}
.test li {float:left}
</style>
<script>
$("head").append("<link>");
css = $("head").children(":last");
css.attr({
    rel:  "stylesheet",
    type: "text/css",
    href: "./css/test.css"
});
</script>
</head>
<body>

<div class="nos">
    <ul class="test">
        <li>테스트</li>
        <li>테스트2</li>
    </ul>
</div>

</body>
</html>
