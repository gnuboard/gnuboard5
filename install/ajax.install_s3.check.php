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
        // $access_key = "AKIAQOTJE4FTM4NVCK4C";
        $secret_key = $_REQUEST['secret_key'];
        // $secret_key = "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";
        $bucket_name = $_REQUEST['bucket_name'];

        if(isset($access_key) == false || $access_key == '') throw new Exception("access key를 입력해주세요");
        if(isset($secret_key) == false || $secret_key == '') throw new Exception("secret key를 입력해주세요");
        // if(isset($bucket_name) == false || $bucket_name == '') throw new Exception("bucket 이름을 입력해주세요");

        $data = array();

        $credentials = new Credentials($access_key, $secret_key);
        try {
            $options = [
                'region'            => 'ap-northeast-2',
                'version'           => 'latest',
                'credentials'       => $credentials,
            ];

            $s3Client = new S3Client($options);
            $buckets = $s3Client->listBuckets();

            $error = 0;
            $message = "검증되었습니다.";
        } catch (S3Exception $ae) {
            $error = 1;
            $buckets = array();
            $message = "검증에 실패했습니다. 올바른 key 값인지 확인해주세요.";
        }

        if($error == 1) throw new Exception($message);
        
        $data['error'] = 0;
        $data['item'] = $buckets;
        
    } catch (Exception $e) {
        $data = array();
        $data['error'] = 1;
        $data['code'] = $e->getCode();
        $data['message'] = $e->getMessage();
    }

    die(json_encode($data));

?>