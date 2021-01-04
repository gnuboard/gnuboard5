<?php
if (!defined('_GNUBOARD_')) exit;
?>
<!DOCTYPE html>
	<head>
		<meta name="robots" content="NOINDEX, NOFOLLOW">
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <meta name="viewport" content="width=device-width,initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=yes">
		<title>소셜 로그인 - <?php echo isset($provider) ? $provider : ''; ?></title>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
        <style>
        .error-container{padding:1em}
        .bs-callout {
            padding: 20px;
            margin: 20px 0;
            border: 1px solid #eee;
            border-left-width: 5px;
            border-radius: 3px;
        }
        .bs-callout-danger {
            border-left-color: #ce4844;
        }
        .bs-callout-danger h4 {
            color: #ce4844;
        }
        </style>
	</head>
	<body>
        <div class="error-container">
            <h4>Error : <?php echo isset($code) ? $code : ''; ?></h4>
            <div class="alert alert-danger" role="alert">
              <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
              <span class="sr-only">Error:</span>
              <?php echo $get_error; ?>
            </div>
            <div class="bs-callout bs-callout-danger" id="callout-images-ie-rounded-corners">
                <?php if(isset($code) && ($code <= 0 && $code > 10) ){ ?>
                <p>잠시후에 다시 시도해 주세요.</p>
                <?php } ?>
                <a href="<?php echo G5_URL; ?>" class="btn btn-primary go_home">홈으로</a>
                <a href="<?php echo G5_URL; ?>" class="btn btn-default close" style="display:none">이 페이지 닫기</a>
            </div>
        </div>
	</body>
    <script>
    jQuery(function($){
        $(".go_home.btn").click(function(e){
            if( window.opener ){
                e.preventDefault();
                window.opener.location.href = $(this).attr("href");
                window.close();
            }
        });
        
        if( window.opener ){
            $(".close.btn").show();
        }

        $(".close.btn").click(function(e){
            window.close();
        });
    });
    </script>
</html>
<?php
die();