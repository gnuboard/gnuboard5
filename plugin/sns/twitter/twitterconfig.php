<?php
/**
 * @file
 * A single location to store configuration.
 */

define('CONSUMER_KEY', $config['cf_twitter_key']);
define('CONSUMER_SECRET', $config['cf_twitter_secret']);
define('OAUTH_CALLBACK', G5_SNS_URL.'/twitter/callback.php');
//define('OAUTH_CALLBACK', '');
