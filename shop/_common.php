<?
include_once('../common.php');

if (!defined('G4_USE_SHOP') || !G4_USE_SHOP)
    die('<p>현재 쇼핑몰 사용 안함으로 설정되어 있습니다.</p><p>'.G4_EXTEND_DIR.'/shop.extend.php 에 define(\'G4_USE_SHOP\', true); 로 설정해 주세요.</p>');
?>