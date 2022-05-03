<?php
    include_once ("./_common.php");

    try {
        $version = isset($_POST['version']) ? $_POST['version'] : null;
        if($version == null) throw new Exception("버전을 입력해주세요");

        $result = $g5['update']->getVersionModifyContent($version);
        if($result == false) throw new Exception("정보를 찾을 수 없습니다.");

        preg_match_all('/(?:(?:https?|ftp):)?\/\/[a-z0-9+&@#\/%?=~_|!:,.;]*[a-z0-9+&@#\/%=~_|]/i', $result, $match);
        
        $content_url = $match[0];
        foreach($content_url as $key => $var) {
            $result = str_replace($var, "@".$key."@", $result);
        }

        $txt = "<p class=\"content_title\">".$version." 버전 수정</p>";
        $txt .= "<p style=\"white-space:pre-line; line-height:2;\">";
        foreach($content_url as $key => $var) {
            $result = str_replace('@'.$key.'@', '<a class="a_style" href="'.$var.'" target="_blank">변경코드확인</a>', $result);
        }
        $txt .= htmlspecialchars_decode($result, ENT_HTML5);
        $txt .= "</p><br>";

        $data = array();
        $data['error']      = 0;
        $data['item']       = $txt;
    } catch (Exception $e) {
        $data = array();
        $data['code']    = $e->getCode();
        $data['message'] = $e->getMessage();
    }

    die(json_encode($data));
?>