<?php
include_once('./_common.php');

/**
 * @file syndi_echo.php
 * @author sol (ngleader@gmail.com)
 * @brief Print Syndication Data XML
 */

header('Content-Type: text/html; charset=UTF-8');
header('Pragma: no-cache');

if(version_compare(PHP_VERSION, '5.3.0') >= 0)
{
	date_default_timezone_set(@date_default_timezone_get());		
}

error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);


$syndi_path = dirname(__FILE__);

// include class
include $syndi_path . '/libs/SyndicationHandler.class.php';
include $syndi_path . '/libs/SyndicationObject.class.php';
include $syndi_path . '/libs/SyndicationSite.class.php';
include $syndi_path . '/libs/SyndicationChannel.class.php';
include $syndi_path . '/libs/SyndicationArticle.class.php';
include $syndi_path . '/libs/SyndicationDeleted.class.php';

// config & custom func for site
include $syndi_path . '/config/site.config.php';
include $syndi_path . '/func/site.func.php';

$oSyndicationHandler = &SyndicationHandler::getInstance();
$oSyndicationHandler->setArgument();

echo $oSyndicationHandler->getXML();
?>
