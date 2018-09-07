<?php
if (!defined('_GNUBOARD_')) exit;

include_once(dirname(__FILE__) .'/URI/uri.class.php');

// 짧은 주소 형식으로 만들어서 가져온다.
function get_pretty_url($folder, $no='', $query_string='', $action='')
{
    global $g5, $config;

    static $boards = array();

    if( ! $boards ){
        $sql = " select bo_table from {$g5['board_table']} ";
        $result = sql_query($sql);

        while ($row = sql_fetch_array($result)) {
            $boards[] = $row['bo_table'];
        }
    }

	// use shortten url
	if($config['cf_bbs_rewrite']) {
		if(in_array($folder, $boards)) {
			$url = G5_URL. '/'. $folder;
			if($no) {
				$url .= '/'. $no;
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
?>