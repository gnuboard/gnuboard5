<?php
define('_INDEX_', true);
include_once('./_common.php');
include_once($g4['path'].'/lib/latest.lib.php');

if (G4_IS_MOBILE) {
    include_once($g4['path'].'/mobile.index.php');
} else {
    include_once($g4['path'].'/main.php');
}
?>
