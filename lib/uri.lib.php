<?php
if (!defined('_GNUBOARD_')) exit;

include_once(dirname(__FILE__) .'/URI/uri.class.php');
include_once(dirname(__FILE__) .'/URI/obj.class.php');

// 짧은 주소 형식으로 만들어서 가져온다.
function get_pretty_url($folder, $no='', $query_string='', $action='')
{
    global $g5, $config;

    $boards = get_board_names();
    $segments = array();
    $url = $add_query = '';

	// use shortten url
	if($config['cf_bbs_rewrite']) {
        
        $segments[0] = G5_URL;

        if( $folder === 'content' && $no ){     // 내용관리
            
            $segments[1] = $folder;

            if( $config['cf_bbs_rewrite'] > 1 ){

                $get_content = get_content_db( $no , true);
                $segments[2] = $get_content['co_seo_title'] ? urlencode($get_content['co_seo_title']).'/' : urlencode($no);

            } else {
                $segments[2] = urlencode($no);
            }

        } else if(in_array($folder, $boards)) {     // 게시판

			$segments[1] = $folder;

			if($no) {

                if( $config['cf_bbs_rewrite'] > 1 ){

                    $get_write = get_write( $g5['write_prefix'].$folder, $no , true);
                    
                    $segments[2] = $get_write['wr_seo_title'] ? urlencode($get_write['wr_seo_title']).'/' : urlencode($no);

                } else {
                    $segments[2] = urlencode($no);
                }

			} else if($action) {
                $segments[2] = urlencode($action);
            }

		} else {
            $segments[1] = $folder;
			if($no) {
				$no_array = explode("=", $no);
				$no_value = end($no_array);
                $segments[2] = urlencode($no_value);
			}
		}

        if($query_string) {
            // If the first character of the query string is '&', replace it with '?'.
            if(substr($query_string, 0, 1) == '&') {
                $add_query = preg_replace("/\&amp;/", "?", $query_string, 1);
            } else {
                $add_query = '?'. $query_string;
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

        $segments[0] = $url;
	}

	return implode('/', $segments).$add_query;
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

function generate_seo_title($string, $wordLimit=G5_SEO_TITEL_WORD_CUT){
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

    if( function_exists('mb_convert_encoding') ){
        $string = mb_convert_encoding($string, 'UTF-8', 'UTF-8');
    }

    foreach ($trans as $key => $val){
        $string = preg_replace('#'.$key.'#iu', $val, $string);
    }

    $string = strtolower($string);

    return trim(trim($string, $separator));
}

function exist_seo_url($type, $seo_title, $write_table, $sql_id=0){
    global $g5;

    $exists_title = '';
    $sql_id = preg_replace('/[^a-z0-9_]/i', '', $sql_id);

    if( $type === 'bbs' ){
        $sql = "select wr_seo_title FROM {$write_table} WHERE wr_seo_title = '".sql_real_escape_string($seo_title)."' AND wr_id <> '$sql_id' limit 1";

        $row = sql_fetch($sql);
        
        echo $sql;

        $exists_title = $row['wr_seo_title'];

    } else if ( $type === 'content' ){

        $sql = "select co_seo_title FROM {$write_table} WHERE co_seo_title = '".sql_real_escape_string($seo_title)."' AND co_id <> '$sql_id' limit 1";
        $row = sql_fetch($sql);

        $exists_title = $row['co_seo_title'];

    } else {
        return $seo_title;
    }

    if ($exists_title)
        return 'is_exists';
    else
        return '';
}

function exist_seo_title_recursive($type, $seo_title, $write_table, $sql_id=0){
    static $count = 0;

    $seo_title_add = ($count > 0) ? utf8_strcut($seo_title, 255 - ($count+1), '')."-$count" : $seo_title;

    if( ! exist_seo_url($type, $seo_title_add, $write_table, $sql_id) ){
        return $seo_title_add;
    }
    
    $count++;

    if( $count > 253 ){
        return $seo_title_add;
    }

    return exist_seo_title_recursive($type, $seo_title, $write_table, $sql_id);
}

?>