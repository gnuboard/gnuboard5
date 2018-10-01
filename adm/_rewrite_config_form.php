<?php
if (!defined('_GNUBOARD_')) exit;

$is_apache = (stripos($_SERVER['SERVER_SOFTWARE'], 'apache') !== false);

$is_nginx = (stripos($_SERVER['SERVER_SOFTWARE'], 'nginx') !== false);

$is_iis = !$is_apache && (stripos($_SERVER['SERVER_SOFTWARE'], 'microsoft-iis') !== false);

$is_write_file = false;

if ( $is_nginx ){
    $is_write_file = false;
} else if ( $is_apache && ((!file_exists(G5_PATH.'/.htaccess') && is_writable($home_path)) || is_writable(G5_PATH.'/.htaccess')) ){
    $is_write_file = true;
}

$get_path_url = parse_url( G5_URL );

$base_path = isset($get_path_url['path']) ? $get_path_url['path'].'/' : '/';

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.G5_JS_URL.'/remodal/remodal.css">', 11);
add_stylesheet('<link rel="stylesheet" href="'.G5_JS_URL.'/remodal/remodal-default-theme.css">', 12);
add_javascript('<script src="'.G5_JS_URL.'/remodal/remodal.js"></script>', 10);
?>
<section id="anc_cf_url">
    <h2 class="h2_frm">짧은 주소 설정</h2>
    <?php echo $pg_anchor ?>
    <div class="local_desc02 local_desc">
        <p>
            게시판과 컨텐츠 페이지에 짧은 URL 을 사용합니다.
        </p>
    </div>

    <div>
        <?php if ( $is_apache ){ ?>
            <button type="button" data-remodal-target="modal_apache" class="btn btn_03">Apache 설정 코드 보기</button>
        <?php } else if ( $is_nginx ) { ?>
            <button type="button" data-remodal-target="modal_nginx" class="btn btn_03">Nginx 설정 코드 보기</button>
        <?php } ?>
    </div>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>짧은주소 설정</caption>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <?php
            $short_url_arrs = array(
            '0'=>array('label'=>'사용안함', 'url'=>G5_URL.'/board.php?bo_table=free&wr_id=123'),
            '1'=>array('label'=>'숫자', 'url'=>G5_URL.'/free/123'),
            '2'=>array('label'=>'글 이름', 'url'=>G5_URL.'/free/안녕하세요/'),
            );
            foreach($short_url_arrs as $k=>$v){
                $checked = ((int) $config['cf_bbs_rewrite'] === (int) $k) ? 'checked' : '';
        ?>
            <tr>
                <td><input name="cf_bbs_rewrite" id="cf_bbs_rewrite_<?php echo $k; ?>" type="radio" value="<?php echo $k; ?>" <?php echo $checked;?> ><label for="cf_bbs_rewrite_<?php echo $k; ?>"><?php echo $v['label']; ?></label></td>
                <td><?php echo $v['url']; ?></td>
            </tr>
        <?php }     //end foreach ?>
        </tbody>
        </table>
    </div>

    <div class="server_rewrite_info">
        <div class="is_rewrite remodal" data-remodal-id="modal_apache" role="dialog" aria-labelledby="modalApache" aria-describedby="modal1Desc">

        <button type="button" class="connect-close" data-remodal-action="close">
            <i class="fa fa-close"></i>
            <span class="txt">닫기</span>
        </button>

        <h4 class="copy_title">.htaccess 파일에 적용할 코드입니다.
        <?php if( ! $is_write_file ) { ?> 
        <br>아래 코드를 복사하여 .htaccess 파일에 붙여넣기 하여 주세요.
        <?php } ?>
        </h4>
            <textarea readonly="readonly" rows="10"><?php echo get_mod_rewrite_rules(true); ?></textarea>
        </div>

        <div class="is_rewrite remodal" data-remodal-id="modal_nginx" role="dialog" aria-labelledby="modalNginx" aria-describedby="modal2Desc">

<h4 class="copy_title">아래 코드를 복사하여 nginx 설정 파일에 적용해 주세요.</h4>
<textarea readonly="readonly" rows="10">
if (!-e $request_filename){
    rewrite ^<?php echo $base_path; ?>content/([0-9a-zA-Z_]+)$ <?php echo $base_path; ?>bbs/content.php?co_id=$1&rewrite=1 break;
    rewrite ^<?php echo $base_path; ?>content/([^/]+)/$ <?php echo $base_path; ?>bbs/content.php?co_seo_title=$1&rewrite=1 break;
    rewrite ^<?php echo $base_path; ?>rss/([0-9a-zA-Z_]+)$ <?php echo $base_path; ?>bbs/rss.php?bo_table=$1 break;
    rewrite ^<?php echo $base_path; ?>([0-9a-zA-Z_]+)$ <?php echo $base_path; ?>bbs/board.php?bo_table=$1&rewrite=1 break;
    rewrite ^<?php echo $base_path; ?>([0-9a-zA-Z_]+)/([^/]+)/$ <?php echo $base_path; ?>bbs/board.php?bo_table=$1&wr_seo_title=$2&rewrite=1 break;
    rewrite ^<?php echo $base_path; ?>([0-9a-zA-Z_]+)/write$ <?php echo $base_path; ?>bbs/write.php?bo_table=$1&rewrite=1 break;
    rewrite ^<?php echo $base_path; ?>([0-9a-zA-Z_]+)/p([0-9]+)$ <?php echo $base_path; ?>bbs/board.php?bo_table=$1&page=$2 break;
    rewrite ^<?php echo $base_path; ?>([0-9a-zA-Z_]+)/([0-9]+)$ <?php echo $base_path; ?>bbs/board.php?bo_table=$1&wr_id=$2&rewrite=1 break;
}
</textarea>
        </div>

    </div>
</section>