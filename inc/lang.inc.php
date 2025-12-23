<?php
// 다국어 사용시 사용하는 파일
// 활성화된 언어 목록 가져오기
$lang_types = !empty($config['cf_lang_type']) ? explode(',', $config['cf_lang_type']) : array('ko');
$lang_types = array_map('trim', $lang_types);
$lang_names = array('ko' => '한국어', 'en' => '영어', 'zh' => '중국어', 'ja' => '일본어');
$current_lang = get_current_lang();

// 활성화된 언어가 2개 이상일 때만 표시
if (count($lang_types) > 1) {
?>
<div id="hd_lang_select">
    <select id="lang_select" name="lang_select">
        <?php foreach ($lang_types as $lang) {
            $lang_name = isset($lang_names[$lang]) ? $lang_names[$lang] : $lang;
            $selected = ($current_lang == $lang) ? 'selected' : '';
            echo '<option value="' . $lang . '" ' . $selected . '>' . $lang_name . '</option>';
        } ?>
    </select>
</div>
<?php } ?>