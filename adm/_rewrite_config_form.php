<?php
if (!defined('_GNUBOARD_')) exit;

$is_apache = (stripos($_SERVER['SERVER_SOFTWARE'], 'apache') !== false);

$is_nginx = (stripos($_SERVER['SERVER_SOFTWARE'], 'nginx') !== false);

$is_iis = !$is_apache && (stripos($_SERVER['SERVER_SOFTWARE'], 'microsoft-iis') !== false);

?>
<section id="anc_cf_url">
    <h2 class="h2_frm">짧은 주소 설정</h2>
    <?php echo $pg_anchor ?>
    <div class="local_desc02 local_desc">
        <p>
            게시판과 컨텐츠 페이지에 짧은 URL 을 사용합니다.
        </p>
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
        <div class="is_apache">
            <pre>
                # nginx configuration

                location /g54/ {
                  if (!-e $request_filename){
                    rewrite ^/g54/rss/([0-9a-zA-Z_]+)$ /g54/bbs/rss.php?bo_table=$1 break;
                    rewrite ^/g54/([0-9a-zA-Z_]+)$ /g54/bbs/board.php?bo_table=$1&rewrite=1 break;
                    rewrite ^/g54/([0-9a-zA-Z_]+)/([^/]+)/$ /g54/bbs/board.php?bo_table=$1&wr_seo_title=$2&rewrite=1 break;
                    rewrite ^/g54/([0-9a-zA-Z_]+)/write$ /g54/bbs/write.php?bo_table=$1&rewrite=1 break;
                    rewrite ^/g54/([0-9a-zA-Z_]+)/p([0-9]+)$ /g54/bbs/board.php?bo_table=$1&page=$2 break;
                    rewrite ^/g54/([0-9a-zA-Z_]+)/([0-9]+)$ /g54/bbs/board.php?bo_table=$1&wr_id=$2&rewrite=1 break;
                  }
                }
            </pre>
        </div>

        <div class="is_nginx">

            <pre>
                # nginx configuration

                location /g54/ {
                  if (!-e $request_filename){
                    rewrite ^/g54/rss/([0-9a-zA-Z_]+)$ /g54/bbs/rss.php?bo_table=$1 break;
                    rewrite ^/g54/([0-9a-zA-Z_]+)$ /g54/bbs/board.php?bo_table=$1&rewrite=1 break;
                    rewrite ^/g54/([0-9a-zA-Z_]+)/([^/]+)/$ /g54/bbs/board.php?bo_table=$1&wr_seo_title=$2&rewrite=1 break;
                    rewrite ^/g54/([0-9a-zA-Z_]+)/write$ /g54/bbs/write.php?bo_table=$1&rewrite=1 break;
                    rewrite ^/g54/([0-9a-zA-Z_]+)/p([0-9]+)$ /g54/bbs/board.php?bo_table=$1&page=$2 break;
                    rewrite ^/g54/([0-9a-zA-Z_]+)/([0-9]+)$ /g54/bbs/board.php?bo_table=$1&wr_id=$2&rewrite=1 break;
                  }
                }
            </pre>
        </div>

    </div>
</section>