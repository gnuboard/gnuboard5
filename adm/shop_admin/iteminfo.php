<?php
include_once('./_common.php');
include_once(G5_LIB_PATH.'/iteminfo.lib.php');

if(isset($it['it_id']) && $it['it_id']) {
    //$it_id = $it['it_id'];
    $gubun = $it['it_info_gubun'];
} else {
    $it_id = isset($_POST['it_id']) ? safe_replace_regex($_POST['it_id'], 'it_id') : '';
    $gubun = isset($_POST['gubun']) ? clean_xss_tags($_POST['gubun'], 1, 1) : 'wear';
    
    if ( $it_id ){
        $sql = " select it_id, it_info_gubun, it_info_value from {$g5['g5_shop_item_table']} where it_id = '$it_id' ";
        if( $items = sql_fetch($sql) ) {
            $it = $items;
        }
    }
}
?>

<div class="tbl_frm01 tbl_wrap">
    <table>
    <caption>상품요약정보 입력</caption>
    <colgroup>
        <col class="grid_4">
        <col>
        <col class="grid_3">
    </colgroup>
    <tbody>
    <?php
    if(isset($it['it_info_value']) && $it['it_info_value'])
        $info_value = unserialize($it['it_info_value']);

    $article = isset($item_info[$gubun]['article']) ? $item_info[$gubun]['article'] : array();
    if ($article) {
        // $el_no : 분류적용, 전체적용을 한번만 넣기 위해, $el_length : 수직병합할 셀 값 - 지운아빠 2013-05-20
        $el_no = 0;
        $el_length = count($article);
        $it_info_gubun = isset($it['it_info_gubun']) ? $it['it_info_gubun'] : '';

        foreach($article as $key=>$value) {
            $el_name    = $key;
            $el_title   = $value[0];
            $el_example = $value[1];
            $el_value = '상품페이지 참고';

            if($gubun == $it_info_gubun && isset($info_value[$key]))
                $el_value = $info_value[$key];
    ?>

    <tr>
        <th scope="row"><label for="ii_article_<?php echo $el_name; ?>"><?php echo $el_title; ?></label></th>
        <td>
            <input type="hidden" name="ii_article[]" value="<?php echo $el_name; ?>">
            <?php if ($el_example != "") echo help($el_example); ?>
            <input type="text" name="ii_value[]" value="<?php echo get_text($el_value); ?>" id="ii_article_<?php echo $el_name; ?>" required class="frm_input required" />
        </td>
        <?php if ($el_no == 0) { ?>
        <td rowspan="<?php echo $el_length; ?>" class="td_grpset">
            <input type="checkbox" name="chk_ca_it_info" value="1" id="chk_ca_it_info">
            <label for="chk_ca_it_info">분류적용</label>
            <input type="checkbox" name="chk_all_it_info" value="1" id="chk_all_it_info">
            <label for="chk_all_it_info">전체적용</label>
        </td>
        <?php } $el_no++; ?>
    </tr>
    <?php
        }
    }
    ?>
    </tbody>
    </table>
</div>