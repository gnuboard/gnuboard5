<?php
include_once('./_common.php');

$g5['title'] = '카카오톡 메세지 입력';
include_once(G5_PATH.'/head.sub.php');

$skin = G5_MSHOP_SKIN_PATH.'/kakaolinkform.skin.php';

if(is_file($skin))
    include_once($skin);
else
    echo '<div class="empty_list">'.str_replace(G5_PATH.'/', '', $skin).' 파일이 존재하지 않습니다.</div>';

include_once(G5_PATH.'/tail.sub.php');
?>