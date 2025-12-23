<?php
include_once './_common.php';
include_once(G5_THEME_PATH.'/head.php');

// 현재 언어 출력
// echo $current_lang;
?>

<section id="sample_area">
    <div class="sa-box">
        <h1>방법 1</h1>

        <?php if($current_lang === 'ko') { ?>
            <h2>한국어</h2>
        <?php } else if($current_lang === 'en') { ?>
            <h2>영어</h2>
        <?php } else if($current_lang === 'zh') { ?>
            <h2>중국어</h2>
        <?php } else if($current_lang === 'ja') { ?>
            <h2>일본어</h2>
        <?php } ?>
    </div>

    <div class="sa-box">
        <h1>방법 2</h1>

        <pre>
            <code>
            <?php if($current_lang === 'ko') { ?>
                include_once('./lang_sample.php');
            <?php } else if($current_lang === 'en') { ?>
                include_once('./lang_sample_en.php');
            <?php } else if($current_lang === 'zh') { ?>
                include_once('./lang_sample_zh.php');
            <?php } else if($current_lang === 'ja') { ?>
                include_once('./lang_sample_ja.php');
            <?php } ?>
            </code>
        </pre>
        
        
    </div>

</section>
<?php
include_once(G5_THEME_PATH.'/tail.php');
?>