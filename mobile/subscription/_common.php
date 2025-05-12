<?php
include_once('../../common.php');

// 쇼핑몰 설정을 그대로 따른다.
include_once G5_MSHOP_PATH.'/_common.php';

if (!(defined('G5_USE_SUBSCRIPTION') && G5_USE_SUBSCRIPTION)) {
    exit('<p>정기결제 프로그램을 설치 후 이용해 주십시오.</p>');
}