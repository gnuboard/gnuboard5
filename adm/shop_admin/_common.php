<?
define('G4_IS_ADMIN', true);
include_once ('../../common.php');

if (!defined('G4_USE_SHOP') || !G4_USE_SHOP)
    die('<p>쇼핑몰 설치 후 이용해 주십시오.</p>');

include_once(G4_ADMIN_PATH.'/admin.lib.php');
?>
