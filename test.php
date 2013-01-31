
<!DOCTYPE html>
<html lang="ko">
	<head>
		<title>Create a Custom Select Box with jQuery - Onextrapixel</title>
		<link rel="stylesheet" href="style.css" type="text/css" media="screen" />
		<style type='text/css'>
        /*
Author : Onextrapixel
URL: http://www.onextrapixel.com
*/

* {
	border:none; 
	margin:0; 
	padding:0;
}

body {
	color:#000; 
	font:12.35px "Lucida Grande", Arial, Georgia, Verdana, sans-serif;
}

a:link, a:visited {
	color:#000;
	text-decoration:none; 
}

h1 {
	font-size:20px;
	margin-bottom:20px; 
}

h2 {
	font-size:15px;
	margin-bottom:20px;
}

p {
	margin:10px 0;
}

#wrap {
	margin:0 auto;
	width:900px; 
}

#header {
	margin-bottom:50px;
}

#header a {
	color:#0054A6;
}

#header a:hover {
	text-decoration:underline;
}

			div.selectBox
			{
				position:relative;
				display:inline-block;
				cursor:default;
				text-align:left;
				line-height:30px;
				clear:both;
				color:#888;
			}
			span.selected
			{
				width:167px;
				text-indent:20px;
				border:1px solid #ccc;
				border-right:none;
				border-top-left-radius:5px;
				border-bottom-left-radius:5px;
				background:#f6f6f6;
				overflow:hidden;
			}
			span.selectArrow
			{
				width:30px;
				border:1px solid #60abf8;
				border-top-right-radius:5px;
				border-bottom-right-radius:5px;
				text-align:center;
				font-size:20px;
				-webkit-user-select: none;
				-khtml-user-select: none;
				-moz-user-select: none;
				-o-user-select: none;
				user-select: none;
				background:#4096ee;
			}
			
			span.selectArrow,span.selected
			{
				position:relative;
				float:left;
				height:30px;
				z-index:1;
			}
			
			div.selectOptions
			{
				position:absolute;
				top:28px;
				left:0;
				width:198px;
				border:1px solid #ccc;
				border-bottom-right-radius:5px;
				border-bottom-left-radius:5px;
				overflow:hidden;
				background:#f6f6f6;
				padding-top:2px;
				display:none;
			}
				
			span.selectOption
			{
				display:block;
				width:80%;
				line-height:20px;
				padding:5px 10%;
			}
			
			span.selectOption:hover
			{
				color:#f6f6f6;
				background:#4096ee;	
			}			
		</style>
		<script type='text/javascript' src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js"></script>
		
	</head>
	<body>
	<script type='text/javascript'><!--
			$(document).ready(function() {
				enableSelectBoxes();
			});
			
			function enableSelectBoxes(){
				$('div.selectBox').each(function(){
					$(this).children('span.selected').html($(this).children('div.selectOptions').children('span.selectOption:first').html());
					$(this).attr('value',$(this).children('div.selectOptions').children('span.selectOption:first').attr('value'));
					
					$(this).children('span.selected,span.selectArrow').click(function(){
						if($(this).parent().children('div.selectOptions').css('display') == 'none'){
							$(this).parent().children('div.selectOptions').css('display','block');
						}
						else
						{
							$(this).parent().children('div.selectOptions').css('display','none');
						}
					});
					
					$(this).find('span.selectOption').click(function(){
						$(this).parent().css('display','none');
						$(this).closest('div.selectBox').attr('value',$(this).attr('value'));
						$(this).parent().siblings('span.selected').html($(this).html());
					});
				});				
			}//-->
		</script>
	<div id="wrap">
		<div id="head">
	<h1><a href="/"><img src="http://www.onextrapixel.com/examples/files/logo.png" alt="Onextrapixel Homepage" /></a></h1>
	<div class="demoads">
		<script type="text/javascript"><!--
		google_ad_client = "ca-pub-5606861741839360";
		/* OXPDemoTop728x90 */
		google_ad_slot = "2475031167";
		google_ad_width = 728;
		google_ad_height = 90;
		//-->
		</script>
		<script type="text/javascript" src="http://pagead2.googlesyndication.com/pagead/show_ads.js"></script>
	</div>
</div>
		<div id="header">
			<h1>Create a Custom Select Box with jQuery</h1>
			<a href="http://www.onextrapixel.com/2012/06/20/create-a-custom-select-box-with-jquery">Back to tutorial</a>
		</div>
		<div class='selectBox'>
			<span class='selected'></span>
			<span class='selectArrow'>&#9660</span>
			<div class="selectOptions" >
				<span class="selectOption" value="Option 1">Option 1</span>
				<span class="selectOption" value="Option 2">Option 2</span>
				<span class="selectOption" value="Option 3">Option 3</span>
			</div>
		</div>
	</div>
		<script type="text/javascript">
		var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
		document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
		</script>
		<script type="text/javascript">
		try {
		var pageTracker = _gat._getTracker("UA-8453623-1");
		pageTracker._trackPageview();
		} catch(err) {}
	</script>	</body>
</html>