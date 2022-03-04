<?php
if (!defined('_GNUBOARD_')) exit;

include_once(G5_LIB_PATH.'/aws/aws-autoloader.php');    // aws autoloader 추가

use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use Aws\Credentials\Credentials;

class S3 {
    private $_s3Client = false;

    private $_accessKey;
    private $_secretKey;
    private $_bucketName = '';

    private $_region = 'ap-northeast-2';
    private $_version = 'latest';

    private $_credentials;

    private $_path;

    public function __construct($accessKey, $secretKey, $bucketName) {
        $this->_accessKey = $accessKey;
        $this->_secretKey = $secretKey;
        $this->_bucketName = $bucketName;

        $this->setCredentials($this->_accessKey, $this->_secretKey);
        $this->setPath("s3://".$bucketName);
        $this->setUrl("https://".$this->_bucketName.'.s3.'.$this->_region.'.amazonaws.com');

        $this->setS3();
    }

    public function getPath() {
        return $this->_path;
    }

    public function setPath($path) {
        $this->_path = $path;
    }

    public function getUrl() {
        return $this->_url;
    }

    public function setUrl($url) {
        $this->_url = $url;
    }

    public function setCredentials($accessKey, $secretKey) {
        $this->_credentials = new Credentials($accessKey, $secretKey);
    }

    private function setS3() {
        if(empty($this->_region)) return false;
        if(empty($this->_version)) return false;
        if(empty($this->_credentials)) return false;

        if(empty($this->_s3Client)) {
            $options = array(
                'region'        => $this->_region,
                'version'       => $this->_version,
                'credentials'   => $this->_credentials,
            );

            $this->_s3Client = new S3Client($options);
            $this->_s3Client->registerStreamWrapper();
        }
        
        return $this->_s3Client;
    }


    public function makeDir($dirName = '') {
        if($dirname == '') return false;
        if($this->_s3Client == false) return false;

        $path = $this->getPath();
        mkdir($path.'/'.$dirname);
    }

    public function uploadFile($tmp_file, $dest_file) {
        if(empty($tmp_file)) return false;
        if($this->_s3Client == false) return false;

        $error_code = move_uploaded_file($tmp_file, $dest_file) or $_FILES['bf_file']['error'][$i];
        chmod($dest_file, G5_FILE_PERMISSION);

        return $error_code;
    }
}