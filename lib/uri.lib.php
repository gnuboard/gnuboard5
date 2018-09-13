<?php
if (!defined('_GNUBOARD_')) exit;

include_once(dirname(__FILE__) .'/URI/uri.class.php');
include_once(dirname(__FILE__) .'/URI/obj.class.php');

// 짧은 주소 형식으로 만들어서 가져온다.
function get_pretty_url($folder, $no='', $query_string='', $action='')
{
    global $g5, $config;

    $boards = get_board_names();

	// use shortten url
	if($config['cf_bbs_rewrite']) {
		if(in_array($folder, $boards)) {

			$url = G5_URL. '/'. $folder;
			if($no) {

                if( $config['cf_bbs_rewrite'] > 1 ){

                    $get_write = get_write( $g5['write_prefix'].$folder, $no , true);

                    if( $get_write['wr_seo_title'] ){
                        $url .= '/'. urlencode($get_write['wr_seo_title']).'/';
                    } else {
                        $url .= '/'. $no;
                    }

                } else {
                    $url .= '/'. $no;
                }

			} else if($action) {
                $url .= '/'. $action;
            }

		} else {
            $url = G5_URL. '/'.$folder;
			if($no) {
				$no_array = explode("=", $no);
				$no_value = end($no_array);
				$url .= '/'. $no_value;
			}
		}
        if($query_string) {
            // If the first character of the query string is '&', replace it with '?'.
            if(substr($query_string, 0, 1) == '&') {
                $url .= preg_replace("/\&amp;/", "?", $query_string, 1);
            } else {
                $url .= '?'. $query_string;
            }
        }
	} else { // don't use shortten url
		if(in_array($folder, $boards)) {
			$url = G5_BBS_URL. '/board.php?bo_table='. $folder;
			if($no) {
				$url .= '&amp;wr_id='. $no;
			}
			if($query_string) {
				$url .= '&amp;'. $query_string;
			}
		} else {
			$url = G5_BBS_URL. '/'.$folder.'.php';
            if($no) {
				$url .= '?'. $no;
			}
            if($query_string) {
                $url .= ($no ? '?' : '&amp;'). $query_string;
			}
		}
	}

	return $url;
}

function short_url_clean($url, $add_qry=''){
    if( class_exists('G5_URI') ){
        return G5_URI::getInstance()->url_clean($url, $add_qry);
    }

    return $url;
}

function correct_goto_url($url){
    return $url.'/';
}

function generate_seo_url($string, $wordLimit = 0){
    $separator = '-';
    
    if($wordLimit != 0){
        $wordArr = explode(' ', $string);
        $string = implode(' ', array_slice($wordArr, 0, $wordLimit));
    }

    $quoteSeparator = preg_quote($separator, '#');

    $trans = array(
        '&.+?;'                    => '',
        '[^\w\d _-]'            => '',
        '\s+'                    => $separator,
        '('.$quoteSeparator.')+'=> $separator
    );

    $string = strip_tags($string);
    foreach ($trans as $key => $val){
        $string = preg_replace('#'.$key.'#iu', $val, $string);
    }

    $string = strtolower($string);

    return trim(trim($string, $separator));
}

function exist_seo_url($type, $seo_title, $write_table, $sql_id=0){
    global $g5;

    $exists_title = '';
    if( $type === 'bbs' ){
        $sql = "select wr_seo_title FROM {$write_table} WHERE wr_seo_title = '".sql_real_escape_string($seo_title)."' AND wr_id <> '$sql_id' limit 1";
        $row = sql_fetch($sql);

        $exists_title = $row['wr_seo_title'];

    } else {
        return $seo_title;
    }

    if ($exists_title)
        return 'is_exists';
    else
        return '';
}

function exist_seo_url_recursive($type, $seo_title, $write_table, $sql_id=0){
    static $count = 0;

    $seo_title_add = ($count > 0) ? utf8_strcut($seo_title, 255 - ($count+1), '')."-$count" : $seo_title;

    if( ! exist_seo_url($type, $seo_title_add, $write_table, $sql_id) ){
        return $seo_title_add;
    }
    
    $count++;

    if( $count > 253 ){
        return $seo_title_add;
    }

    return exist_seo_url_recursive($type, $seo_title, $write_table, $sql_id);
}

?>