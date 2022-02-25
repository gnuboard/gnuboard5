<?php
    header('Content-Type: text/html; charset=UTF-8');
    $g5_path['path'] = '..';
    include_once ('../config.php');
    include_once('../lib/json.lib.php');
    include_once('../lib/common.lib.php');    // 공통 라이브러리
    include_once("../lib/aws/aws-autoloader.php");

    use Aws\S3\S3Client;
    use Aws\Exception\AwsException;
    use Aws\S3\Exception\S3Exception;
    use Aws\Credentials\Credentials;

    try {
        $access_key = $_REQUEST['access_key'];
        $secret_key = $_REQUEST['secret_key'];
        $bucket_name = $_REQUEST['bucket_name'];

        if(isset($access_key) == false || $access_key == '') throw new Exception("access key를 입력해주세요");
        if(isset($secret_key) == false || $secret_key == '') throw new Exception("secret key를 입력해주세요");
        if(isset($bucket_name) == false || $bucket_name == '') throw new Exception("bucket 이름을 입력해주세요");

        $data = array();

        try {
            $credentials = new Credentials($access_key, $secret_key);
            $options = [
                'region'            => 'ap-northeast-2',
                'version'           => 'latest',
                'credentials'       => $credentials,
            ];

            $s3_client = new S3Client($options);
            $buckets = $s3_client->listBuckets();

            $check = false;
            foreach($buckets['Buckets'] as $key => $var) {
                if($var['Name'] == $bucket_name) {
                    $check = true;
                    break;
                }
            }

            if($check == false) throw new Exception("해당 bucket이 존재하지 않습니다.");

            $error = 0;
            $message = "검증되었습니다.";
        } catch (Exception $ae) {
            $error = 1;
            $buckets = array();
            if(empty($ae->getMessage())) {
                $message = $ae->getMessage();
            } else {
                $message = "검증에 실패했습니다. 올바른 key 값인지 확인해주세요.";
            }
            $message = $ae->getMessage();
        }

        if($error == 1) throw new Exception($message);
        
        $data['error'] = 0;
        $data['message'] = $message;
    } catch (Exception $e) {
        $data = array();
        $data['error'] = 1;
        $data['code'] = $e->getCode();
        $data['message'] = $e->getMessage();
    }

    die(json_encode($data));

?>