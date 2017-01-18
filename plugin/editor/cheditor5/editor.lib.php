<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

function editor_html($id, $content, $is_dhtml_editor=true)
{
    global $config, $w, $board;
    global $editor_width, $editor_height;
    static $js = true;

    if( $is_dhtml_editor && $content && !$w && (isset($board['bo_insert_content']) && !empty($board['bo_insert_content']) ) ){       //글쓰기 기본 내용 처리
        if( preg_match('/\r|\n/', $content) && $content === strip_tags($content, '<a><strong><b>') ) {  //textarea로 작성되고, html 내용이 없다면
            $content = nl2br($content);
        }
    }

    $width  = isset($editor_width)  ? $editor_width  : "100%";
    $height = isset($editor_height) ? $editor_height : "250px";
    if (defined(G5_PUNYCODE))
        $editor_url = G5_PUNYCODE.'/'.G5_EDITOR_DIR.'/'.$config['cf_editor'];
    else
        $editor_url = G5_EDITOR_URL.'/'.$config['cf_editor'];

    $html = "";

    if ($is_dhtml_editor) {
        if ($js) {
            $html .= "<script src=\"{$editor_url}/cheditor.js\"></script>";
        }
        $html .= "<script>\n";
        $html .= "var ed_nonce = \"".ft_nonce_create('cheditor')."\";\n";
        $html .= "var ed_{$id} = new cheditor('ed_{$id}');\n";
        $html .= "ed_{$id}.config.editorWidth = \"{$width}\";\n";             
        $html .= "ed_{$id}.config.editorHeight = \"{$height}\";\n";           
        $html .= "ed_{$id}.config.imgReSize = false;\n";                    
        $html .= "ed_{$id}.config.fullHTMLSource = false;\n";               
        $html .= "ed_{$id}.config.editorPath = \"{$editor_url}\";\n"; 
        $html .= "ed_{$id}.inputForm = \"tx_{$id}\";\n";
        $html .= "</script>\n";                                             
        $html .= "<span class=\"sound_only\">웹에디터 시작</span>";
        $html .= "<textarea name=\"{$id}\" id=\"tx_{$id}\" style=\"display:none;\">{$content}</textarea>\n";
        $html .= "\n<span class=\"sound_only\">웹 에디터 끝</span>";
        $html .= "<script>ed_{$id}.run();</script>\n";
    } else {
        $html .= "<textarea id=\"$id\" name=\"$id\" style=\"width:{$width};height:{$height};\" maxlength=\"65536\">$content</textarea>\n";
    }
    return $html;
}


// textarea 로 값을 넘긴다. javascript 반드시 필요
function get_editor_js($id, $is_dhtml_editor=true)
{
    if ($is_dhtml_editor) {
        return "document.getElementById('tx_{$id}').value = ed_{$id}.outputBodyHTML();\n";
    } else {
        return "var {$id}_editor = document.getElementById('{$id}');\n";
    }
}


//  textarea 의 값이 비어 있는지 검사
function chk_editor_js($id, $is_dhtml_editor=true)
{
    if ($is_dhtml_editor) {
        return "if (document.getElementById('tx_{$id}') && jQuery.inArray(ed_{$id}.outputBodyHTML().toLowerCase().replace(/^\s*|\s*$/g, ''), ['&nbsp;','<p>&nbsp;</p>','<p><br></p>','<div><br></div>','<p></p>','<br>','']) != -1) { alert(\"내용을 입력해 주십시오.\"); ed_{$id}.returnFalse(); return false; }\n";
    } else {
        return "if (!{$id}_editor.value) { alert(\"내용을 입력해 주십시오.\"); {$id}_editor.focus(); return false; }\n";
    }
}

/*
https://github.com/timostamm/NonceUtil-PHP
*/

if (!defined('FT_NONCE_UNIQUE_KEY'))
    define( 'FT_NONCE_UNIQUE_KEY' , sha1($_SERVER['SERVER_SOFTWARE'].G5_MYSQL_USER.session_id().G5_TABLE_PREFIX) );

if (!defined('FT_NONCE_SESSION_KEY'))
    define( 'FT_NONCE_SESSION_KEY' , substr(md5(FT_NONCE_UNIQUE_KEY), 5) );

if (!defined('FT_NONCE_DURATION'))
    define( 'FT_NONCE_DURATION' , 60 * 30  ); // 300 makes link or form good for 5 minutes from time of generation,  300은 5분간 유효, 60 * 60 은 1시간

if (!defined('FT_NONCE_KEY'))
    define( 'FT_NONCE_KEY' , '_nonce' );

// This method creates a key / value pair for a url string
if(!function_exists('ft_nonce_create_query_string')){
    function ft_nonce_create_query_string( $action = '' , $user = '' ){
        return FT_NONCE_KEY."=".ft_nonce_create( $action , $user );
    }
}

if(!function_exists('ft_get_secret_key')){
    function ft_get_secret_key($secret){
        return md5(FT_NONCE_UNIQUE_KEY.$secret);
    }
}

// This method creates an nonce. It should be called by one of the previous two functions.
if(!function_exists('ft_nonce_create')){
    function ft_nonce_create( $action = '',$user='', $timeoutSeconds=FT_NONCE_DURATION ){

        $secret = ft_get_secret_key($action.$user);

		$salt = ft_nonce_generate_hash();
		$time = time();
		$maxTime = $time + $timeoutSeconds;
		$nonce = $salt . "|" . $maxTime . "|" . sha1( $salt . $secret . $maxTime );

        set_session('nonce_'.FT_NONCE_SESSION_KEY, $nonce);

		return $nonce;

    }
}

// This method validates an nonce
if(!function_exists('ft_nonce_is_valid')){
    function ft_nonce_is_valid( $nonce, $action = '', $user='' ){

        $secret = ft_get_secret_key($action.$user);

		if (is_string($nonce) == false) {
			return false;
		}
		$a = explode('|', $nonce);
		if (count($a) != 3) {
			return false;
		}
		$salt = $a[0];
		$maxTime = intval($a[1]);
		$hash = $a[2];
		$back = sha1( $salt . $secret . $maxTime );
		if ($back != $hash) {
			return false;
		}
		if (time() > $maxTime) {
			return false;
		}
		return true;
    }
}

// This method generates the nonce timestamp
if(!function_exists('ft_nonce_generate_hash')){
    function ft_nonce_generate_hash(){
		$length = 10;
		$chars='1234567890qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM';
		$ll = strlen($chars)-1;
		$o = '';
		while (strlen($o) < $length) {
			$o .= $chars[ rand(0, $ll) ];
		}
		return $o;
    }
}
?>