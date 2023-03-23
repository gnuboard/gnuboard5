<?php
if (!defined('_GNUBOARD_')) exit;

function shop_type_url($type, $add_param=''){
    global $config;

    if( $config['cf_bbs_rewrite'] ){
        return get_pretty_url('shop', 'type-'.$type, $add_param);
    }
	
	$add_params = $add_param ? '&'.$add_param : '';
    return G5_SHOP_URL.'/listtype.php?type='.urlencode($type).$add_params;
}

function shop_item_url($it_id, $add_param=''){
    global $config;

    if( $config['cf_bbs_rewrite'] ){
        return get_pretty_url('shop', $it_id, $add_param);
    }
	
	$add_params = $add_param ? '&'.$add_param : '';
    return G5_SHOP_URL.'/item.php?it_id='.urlencode($it_id).$add_params;
}

function shop_category_url($ca_id, $add_param=''){
    global $config;

    if( $config['cf_bbs_rewrite'] ){
        return get_pretty_url('shop', 'list-'.$ca_id, $add_param);
    }
	
	$add_params = $add_param ? '&'.$add_param : '';
    return G5_SHOP_URL.'/list.php?ca_id='.urlencode($ca_id).$add_params;
}

function add_pretty_shop_url($url, $folder, $no='', $query_string='', $action=''){
    global $g5, $config;

    if( $folder !== 'shop' ){
        return $url;
    }

    $segments = array();
    $url = $add_query = '';
    
    if( $config['cf_bbs_rewrite'] ){
        $segments[0] = G5_URL;
        $segments[1] = urlencode($folder);

        if( $config['cf_bbs_rewrite'] > 1 && ! preg_match('/^(list|type)\-([^\/]+)/i', $no) ){
            $item = get_shop_item($no, true);
            $segments[2] = (isset($item['it_seo_title']) && $item['it_seo_title']) ? urlencode($item['it_seo_title']).'/' : urlencode($no);
        } else {
            $segments[2] = urlencode($no);
        }

        if($query_string) {
            // If the first character of the query string is '&', replace it with '?'.
            if(substr($query_string, 0, 1) == '&') {
                $add_query = preg_replace("/\&amp;/", "?", $query_string, 1);
            } else {
                $add_query = '?'. $query_string;
            }
        }
    } else {
        
        if( preg_match('/^list\-([^\/]+)/i', $no) ){
            $url = G5_SHOP_URL. '/list.php?ca_id='.urlencode($no);
        } else if( preg_match('/^type\-([^\/]+)/i', $no) ){
            $url = G5_SHOP_URL. '/listtype.php?type='.urlencode($no);
        } else {
            $url = G5_SHOP_URL. '/item.php?it_id='.urlencode($no);
        }

        if($query_string) {
            $url .= ($no ? '?' : '&amp;'). $query_string;
        }

        $segments[0] = $url;
    }

    return implode('/', $segments).$add_query;
}

function shop_short_url_clean($string_url, $url, $page_name, $array_page_names){
	
	global $config, $g5;
	
	if( $config['cf_bbs_rewrite'] && stripos($string_url, G5_SHOP_URL) !== false && in_array($page_name, array('item', 'list', 'listtype')) ){
		
		parse_str($url['query'], $vars);
		
		$allow_param_keys = array('it_id'=>'', 'ca_id'=>'', 'type'=>'');

        $s = array('shop_dir'=>G5_SHOP_DIR);

        foreach( $allow_param_keys as $key=>$v ){
            if( !isset($vars[$key]) || empty($vars[$key]) ) continue;
			
			$key_value = $vars[$key];

			if( $key === 'ca_id' ){
				$key_value = 'list-'.$vars[$key];
			} else if ( $key === 'type' ){
				$key_value = 'type-'.$vars[$key];
			}

            $s[$key] = $key_value;
        }

        if( $config['cf_bbs_rewrite'] > 1 && $page_name === 'item' && (isset($s['it_id']) && $s['it_id']) ){
            $get_item = get_shop_item($s['it_id'], true);
            
            if( $get_item['it_seo_title'] ){
                unset($s['it_id']);
                $s['it_seo_title'] = urlencode($get_item['it_seo_title']).'/';
            }
        }

        $fragment = isset($url['fragment']) ? '#'.$url['fragment'] : '';

        $host = G5_URL;

        if( isset($url['host']) ){

            $array_file_paths = run_replace('url_clean_page_paths', array('/'.G5_SHOP_DIR.'/item.php', '/'.G5_SHOP_DIR.'/list.php', '/'.G5_SHOP_DIR.'/listtype.php'));

            $str_path = isset($url['path']) ? $url['path'] : '';
            $http = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on') ? 'https://' : 'http://';
            $port = (isset($url['port']) && ($url['port']!==80 || $url['port']!==443)) ? ':'.$url['port'] : '';
            $host = $http.$url['host'].$port.str_replace($array_file_paths, '', $str_path);
        }

        $add_param = '';

        if( $result = array_diff_key($vars, $allow_param_keys ) ){
            $add_param = '?'.http_build_query($result,'','&amp;');
        }

        if( isset($add_qry) ){
            $add_param .= $add_param ? '&amp;'.$add_qry : '?'.$add_qry;
        }

        $return_url = '';
        foreach($s as $k => $v) { $return_url .= '/'.$v; }

        return $host.$return_url.$add_param.$fragment;
	}

	return $string_url;
}

function add_shop_nginx_conf_rules($rules, $get_path_url, $base_path, $return_string=false){

    $add_rules = array();

    $add_rules[] = "rewrite ^{$base_path}shop/list-([0-9a-z]+)$ {$base_path}".G5_SHOP_DIR."/list.php?ca_id=$1&rewrite=1 break;";
    $add_rules[] = "rewrite ^{$base_path}shop/type-([0-9a-z]+)$ {$base_path}".G5_SHOP_DIR."/listtype.php?type=$1&rewrite=1 break;";
    $add_rules[] = "rewrite ^{$base_path}shop/([0-9a-zA-Z_\-]+)$ {$base_path}".G5_SHOP_DIR."/item.php?it_id=$1&rewrite=1 break;";
    $add_rules[] = "rewrite ^{$base_path}shop/([^/]+)/$ {$base_path}".G5_SHOP_DIR."/item.php?it_seo_title=$1&rewrite=1 break;";

    return implode("\n", $add_rules).$rules;

}

function add_shop_mod_rewrite_rules($rules, $get_path_url, $base_path, $return_string=false){

    $add_rules = array();
    
    $add_rules[] = 'RewriteRule ^shop/list-([0-9a-z]+)$  '.G5_SHOP_DIR.'/list.php?ca_id=$1&rewrite=1  [QSA,L]';
    $add_rules[] = 'RewriteRule ^shop/type-([0-9a-z]+)$  '.G5_SHOP_DIR.'/listtype.php?type=$1&rewrite=1  [QSA,L]';
    $add_rules[] = 'RewriteRule ^shop/([0-9a-zA-Z_\-]+)$  '.G5_SHOP_DIR.'/item.php?it_id=$1&rewrite=1  [QSA,L]';
    $add_rules[] = 'RewriteRule ^shop/([^/]+)/$  '.G5_SHOP_DIR.'/item.php?it_seo_title=$1&rewrite=1  [QSA,L]';

    return implode("\n", $add_rules).$rules;

}

function add_shop_admin_dbupgrade($is_check){
    global $g5;

    // 내용 관리 짧은 주소
    $sql = " SHOW COLUMNS FROM `{$g5['g5_shop_item_table']}` LIKE 'it_seo_title' ";
    $row = sql_fetch($sql);

    if( !$row ){
        sql_query("ALTER TABLE `{$g5['g5_shop_item_table']}`
                    ADD `it_seo_title` varchar(200) NOT NULL DEFAULT '' AFTER `it_name`,
                    ADD INDEX `it_seo_title` (`it_seo_title`);
        ", false);

        $is_check = true;
    }

    return $is_check;

}

function shop_exist_check_seo_title($seo_title, $type, $shop_item_table, $it_id){
    
    $sql = "select it_seo_title FROM {$shop_item_table} WHERE it_seo_title = '".sql_real_escape_string($seo_title)."' AND it_id <> '$it_id' limit 1";
    $row = sql_fetch($sql, false);

    if( isset($row['it_seo_title']) && $row['it_seo_title'] ){
        return 'is_exists';
    }

    return '';
}

function shop_seo_title_update($it_id, $is_edit=false){
    global $g5;

	$shop_item_cache = $is_edit ? false : true;
    $item = get_shop_item($it_id, $shop_item_cache);

    if( (! $item['it_seo_title'] || $is_edit) && $item['it_name'] ){
        $it_seo_title = exist_seo_title_recursive('shop', generate_seo_title($item['it_name']), $g5['g5_shop_item_table'], $item['it_id']);

        if( isset($item['it_seo_title']) && $it_seo_title !== $item['it_seo_title'] ){
            $sql = " update `{$g5['g5_shop_item_table']}` set it_seo_title = '{$it_seo_title}' where it_id = '{$item['it_id']}' ";
            sql_query($sql);
        }
    }
}