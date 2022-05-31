<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

$is_use_apache = (stripos($_SERVER['SERVER_SOFTWARE'], 'apache') !== false);

$is_use_nginx = (stripos($_SERVER['SERVER_SOFTWARE'], 'nginx') !== false);

$is_use_iis = !$is_use_apache && (stripos($_SERVER['SERVER_SOFTWARE'], 'microsoft-iis') !== false);

$is_write_file = false;
$is_apache_need_rules = false;
$is_apache_rewrite = false;

if (!($is_use_apache || $is_use_nginx || $is_use_iis)) {    // 셋다 아니면 다 출력시킨다.
    $is_use_apache = true;
    $is_use_nginx = true;
}

if ($is_use_nginx) {
    $is_write_file = false;
}

if ($is_use_apache) {
    $is_write_file = (is_writable(G5_PATH) || (file_exists(G5_PATH . '/.htaccess') && is_writable(G5_PATH . '/.htaccess'))) ? true : false;
    $is_apache_need_rules = check_need_rewrite_rules();
    $is_apache_rewrite = function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules());
}

$get_path_url = parse_url(G5_URL);

$base_path = isset($get_path_url['path']) ? $get_path_url['path'] . '/' : '/';

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="' . G5_JS_URL . '/remodal/remodal.css">', 11);
add_stylesheet('<link rel="stylesheet" href="' . G5_JS_URL . '/remodal/remodal-default-theme.css">', 12);
add_javascript('<script src="' . G5_JS_URL . '/remodal/remodal.js"></script>', 10);
?>
<section id="anc_cf_url">
    <h2 class="h2_frm">짧은 주소 설정</h2>
    <?php echo $pg_anchor ?>
    <div class="local_desc02 local_desc">
        <p>
            게시판과 컨텐츠 페이지에 짧은 URL 을 사용합니다. <a href="https://sir.kr/manual/g5/286" class="btn btn_03" target="_blank" style="margin-left:10px">설정 관련 메뉴얼 보기</a>
            <?php if ($is_use_apache && !$is_use_nginx) { ?>
                <?php if (!$is_apache_rewrite) { ?>
                    <br><strong>Apache 서버인 경우 rewrite_module 이 비활성화 되어 있으면 짧은 주소를 사용할수 없습니다.</strong>
                <?php } elseif (!$is_write_file && $is_apache_need_rules) {   // apache인 경우 ?>
                    <br><strong>짧은 주소 사용시 아래 Apache 설정 코드를 참고하여 설정해 주세요.</strong>
                <?php } ?>
            <?php } ?>
        </p>
    </div>

    <div class="server_config_views">
        <?php if ($is_use_apache) { ?>
            <button type="button" data-remodal-target="modal_apache" class="btn btn_03">Apache 설정 코드 보기</button>
        <?php } ?>
        <?php if ($is_use_nginx) { ?>
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
                    '0' => array('label' => '사용안함', 'url' => G5_URL . '/board.php?bo_table=free&wr_id=123'),
                    '1' => array('label' => '숫자', 'url' => G5_URL . '/free/123'),
                    '2' => array('label' => '글 이름', 'url' => G5_URL . '/free/안녕하세요/'),
                );
                foreach ($short_url_arrs as $k => $v) {
                    $checked = ((int) $config['cf_bbs_rewrite'] === (int) $k) ? 'checked' : '';
                ?>
                    <tr>
                        <td><input name="cf_bbs_rewrite" id="cf_bbs_rewrite_<?php echo $k; ?>" type="radio" value="<?php echo $k; ?>" <?php echo $checked; ?>><label for="cf_bbs_rewrite_<?php echo $k; ?>" class="rules_label"><?php echo $v['label']; ?></label></td>
                        <td><?php echo $v['url']; ?></td>
                    </tr>
                <?php } //end foreach ?>
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
                <?php if (!$is_apache_rewrite) { ?>
                    <br><span class="info-warning">Apache 서버인 경우 rewrite_module 이 비활성화 되어 있으면 짧은 주소를 사용할수 없습니다.</span>
                <?php } elseif (!$is_write_file && $is_apache_need_rules) { ?>
                    <br><span class="info-warning">자동으로 .htaccess 파일을 수정 할수 있는 권한이 없습니다.<br>.htaccess 파일이 없다면 생성 후에, 아래 코드가 없으면 코드를 복사하여 붙여넣기 해 주세요.</span>
                <?php } elseif (!$is_apache_need_rules) { ?>
                    <br><span class="info-success">정상적으로 적용된 상태입니다.</span>
                <?php } ?>
            </h4>
            <textarea readonly="readonly" rows="10"><?php echo get_mod_rewrite_rules(true); ?></textarea>
        </div>

        <div class="is_rewrite remodal" data-remodal-id="modal_nginx" role="dialog" aria-labelledby="modalNginx" aria-describedby="modal2Desc">

            <button type="button" class="connect-close" data-remodal-action="close">
                <i class="fa fa-close"></i>
                <span class="txt">닫기</span>
            </button>
            <h4 class="copy_title">아래 코드를 복사하여 nginx 설정 파일에 적용해 주세요.</h4>
            <textarea readonly="readonly" rows="10"><?php echo get_nginx_conf_rules(true); ?></textarea>
        </div>

    </div>
</section>