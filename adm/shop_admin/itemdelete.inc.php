<?php
// itemlistdelete.php 에서 include 하는 파일

if (!defined('_GNUBOARD_')) exit;
if (!defined('_ITEM_DELETE_')) exit; // 개별 페이지 접근 불가

if (!function_exists("itemdelete")) {

    // 상품삭제
    // 메세지출력후 주문개별내역페이지로 이동
    function itemdelete($it_id)
    {
        global $g5, $is_admin;

        $sql = " select it_explan, it_mobile_explan, it_img1, it_img2, it_img3, it_img4, it_img5, it_img6, it_img7, it_img8, it_img9, it_img10
                    from {$g5['g5_shop_item_table']} where it_id = '$it_id' ";
        $it = sql_fetch($sql);

        // 상품 이미지 삭제
        $dir_list = array();
        for($i=1; $i<=10; $i++) {
            $file = G5_DATA_PATH.'/item/'.clean_relative_paths($it['it_img'.$i]);
            if(is_file($file) && $it['it_img'.$i]) {
                @unlink($file);
                $dir = dirname($file);
                delete_item_thumbnail($dir, basename($file));

                if(!in_array($dir, $dir_list))
                    $dir_list[] = $dir;
            }
        }

        // 이미지디렉토리 삭제
        for($i=0; $i<count($dir_list); $i++) {
            if(is_dir($dir_list[$i]))
                rmdir($dir_list[$i]);
        }

        // 상, 하단 이미지 삭제
        @unlink(G5_DATA_PATH."/item/$it_id"."_h");
        @unlink(G5_DATA_PATH."/item/$it_id"."_t");

        // 장바구니 삭제
        $sql = " delete from {$g5['g5_shop_cart_table']} where it_id = '$it_id' and ct_status = '쇼핑' ";
        sql_query($sql);

        // 이벤트삭제
        $sql = " delete from {$g5['g5_shop_event_item_table']} where it_id = '$it_id' ";
        sql_query($sql);

        // 사용후기삭제
        $sql = " delete from {$g5['g5_shop_item_use_table']} where it_id = '$it_id' ";
        sql_query($sql);

        // 상품문의삭제
        $sql = " delete from {$g5['g5_shop_item_qa_table']} where it_id = '$it_id' ";
        sql_query($sql);

        // 관련상품삭제
        $sql = " delete from {$g5['g5_shop_item_relation_table']} where it_id = '$it_id' or it_id2 = '$it_id' ";
        sql_query($sql);

        // 옵션삭제
        sql_query(" delete from {$g5['g5_shop_item_option_table']} where it_id = '$it_id' ");


        //------------------------------------------------------------------------
        // HTML 내용에서 에디터에 올라간 이미지의 경로를 얻어 삭제함
        //------------------------------------------------------------------------
        $imgs = get_editor_image($it['it_explan'], false);
        $count_imgs = (isset($imgs[1]) && is_array($imgs[1])) ? count($imgs[1]) : 0;

        for($i=0;$i<$count_imgs;$i++) {
            $p = parse_url($imgs[1][$i]);
            if(strpos($p['path'], "/data/editor/") === false)
                continue;
            if(strpos($p['path'], "/data/") != 0)
                $data_path = preg_replace("/^\/.*\/data/", "/data", $p['path']);
            else
                $data_path = $p['path'];

            $destfile = G5_PATH.clean_relative_paths($data_path);

            if(is_file($destfile) && preg_match('/(\.(gif|jpe?g|png))$/i', $destfile))
                @unlink($destfile);
        }

        $imgs = get_editor_image($it['it_mobile_explan'], false);
        $count_imgs = (isset($imgs[1]) && is_array($imgs[1])) ? count($imgs[1]) : 0;

        for($i=0;$i<$count_imgs;$i++) {
            $p = parse_url($imgs[1][$i]);
            if(strpos($p['path'], "/data/editor/") === false)
                continue;
            if(strpos($p['path'], "/data/") != 0)
                $data_path = preg_replace("/^\/.*\/data/", "/data", $p['path']);
            else
                $data_path = $p['path'];

            $destfile = G5_PATH.clean_relative_paths($data_path);

            if(is_file($destfile) && preg_match('/(\.(gif|jpe?g|png))$/i', $destfile))
                @unlink($destfile);
        }
        //------------------------------------------------------------------------


        // 상품 삭제
        $sql = " delete from {$g5['g5_shop_item_table']} where it_id = '$it_id' ";
        sql_query($sql);
    }
}

run_event('shop_admin_delete_item_file', $it_id);

itemdelete($it_id);