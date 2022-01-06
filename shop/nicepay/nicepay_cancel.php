<?php
include_once('./_common.php');
include_once(G5_SHOP_PATH.'/settle_nicepay.inc.php');

$nicepay->m_ActionType      = "CL0";
$nicepay->m_ssl             = 'true';
$nicepay->m_price           = $_REQUEST['Amt'];
$nicepay->m_NetCancelAmt    = $_REQUEST['Amt'];