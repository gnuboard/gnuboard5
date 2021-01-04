<?php
include_once('./_common.php');

$g5['title'] = '이미지 크게보기';
include_once(G5_PATH.'/head.sub.php');

$filename = isset($_GET['fn']) ? preg_replace('/[^A-Za-z0-9 _ .\-\/]/', '', $_GET['fn']) : '';

if(function_exists('clean_relative_paths')){
    $filename = clean_relative_paths($filename);
}

$extension = pathinfo($filename, PATHINFO_EXTENSION);

if ( ! preg_match('/(jpg|jpeg|png|gif|bmp)$/i', $extension) ){
    alert_close('이미지 확장자가 아닙니다.');
}

if(strpos($filename, G5_DATA_DIR.'/editor')) {
    $editor_file = strstr($filename, 'editor');
    $filepath = G5_DATA_PATH.'/'.$editor_file;
} else if(strpos($filename, G5_DATA_DIR.'/qa')) {
    $editor_file = strstr($filename, 'qa');
    $filepath = G5_DATA_PATH.'/'.$editor_file;
} else {
    $editor_file = '';
    $filepath = G5_DATA_PATH.'/file/'.$bo_table.'/'.$filename;
}

$file_exists = (is_file($filepath) && file_exists($filepath)) ? 1 : 0;

if($file_exists = run_replace('exists_view_image', $file_exists, $filepath, $editor_file)) {
    $size = $file_exists ? run_replace('get_view_imagesize', @getimagesize($filepath), $filepath, $editor_file) : array();
    if(empty($size))
        alert_close('이미지 파일이 아닙니다.');

    $width = (isset($size[0]) && $size[0]) ? (int) $size[0] : 0;
    $height = (isset($size[1]) && $size[1]) ? (int) $size[1] : 0;

    if($editor_file)
        $fileurl = run_replace('get_editor_content_url', G5_DATA_URL.'/'.$editor_file);
    else
        $fileurl = run_replace('get_file_board_url', G5_DATA_URL.'/file/'.$bo_table.'/'.$filename, $bo_table);

    $img_attr = ($width && $height) ? 'width="'.$width.'" height="'.$height.'"' : '';

    $img = '<img src="'.$fileurl.'" alt="" '.$img_attr.' class="draggable" style="position:relative;top:0;left:0;cursor:move;">';
} else {
    alert_close('파일이 존재하지 않습니다.');
}
?>

<div class="bbs-view-image"><?php echo $img ?></div>

<script>

jQuery(function($){

$.fn.imgLoad = function(callback) {
    return this.each(function() {
        if (callback) {
            if (this.complete || /*for IE 10-*/ $(this).height() > 0) {
                callback.apply(this);
            }
            else {
                $(this).on('load', function(){
                    callback.apply(this);
                });
            }
        }
    });
};

    $(".bbs-view-image img").imgLoad(function(){

        var win_w = <?php echo $width ?>;
        var win_h = <?php echo $height ?> + 70;

        if( !win_w || !win_h ){
            win_w = $(this).width();
            win_h = $(this).height();
        }

        var win_l = (screen.width - win_w) / 2;
        var win_t = (screen.height - win_h) / 2;

        if(win_w > screen.width) {
            win_l = 0;
            win_w = screen.width - 20;

            if(win_h > screen.height) {
                win_t = 0;
                win_h = screen.height - 40;
            }
        }

        if(win_h > screen.height) {
            win_t = 0;
            win_h = screen.height - 40;

            if(win_w > screen.width) {
                win_w = screen.width - 20;
                win_l = 0;
            }
        }

        window.moveTo(win_l, win_t);
        window.resizeTo(win_w, win_h);
    });

    var is_draggable = false;
    var x = y = 0;
    var pos_x = pos_y = 0;

    $(".draggable").mousemove(function(e) {
        if(is_draggable) {
            x = parseInt($(this).css("left")) - (pos_x - e.pageX);
            y = parseInt($(this).css("top")) - (pos_y - e.pageY);

            pos_x = e.pageX;
            pos_y = e.pageY;

            $(this).css({ "left" : x, "top" : y });
        }

        return false;
    });

    $(".draggable").mousedown(function(e) {
        pos_x = e.pageX;
        pos_y = e.pageY;
        is_draggable = true;
        return false;
    });

    $(".draggable").mouseup(function() {
        is_draggable = false;
        return false;
    });

    $(".draggable").dblclick(function() {
        window.close();
    });
});
</script>

<?php
include_once(G5_PATH.'/tail.sub.php');