<?php

namespace API\Service;

use API\Database\Db;
use Google\Auth\Credentials\ServiceAccountCredentials;
use GuzzleHttp\Exception\GuzzleException;

class AlarmService
{

    private $google_service_credentials;
    private string $project_id;

    private array $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];

    public function __construct()
    {
        $this->project_id = $_ENV['FIREBASE_PROJECT_ID'] ?? '';
        $this->google_service_credentials = new ServiceAccountCredentials($this->scopes, $_ENV['FIREBASE_KEY_PATH'] ?? G5_DATA_PATH . '/fcm.json');
    }

    /**
     * @return string|false
     */
    public function getAuthToken()
    {
        if (!$this->google_service_credentials) {
            return false;
        }

        $fetch_result = $this->google_service_credentials->fetchAuthToken();
        error_log('fcm auth token: ' . $fetch_result['access_token']);
        return $fetch_result['access_token'] ?? false;
    }

    /**
     * @param array $data
     * @return bool
     */
    public function sendMessage($data)
    {
        $auth_token = $this->getAuthToken();
        if (!$auth_token) {
            return false;
        }

        $url = "https://fcm.googleapis.com/v1/projects/{$this->project_id}/messages:send";
        $headers = [
            'Authorization' => 'Bearer ' . $auth_token,
            'Content-Type' => 'application/json;',
        ];
        $client = new \GuzzleHttp\Client();
        try {
            $client->request('POST', $url, [
                'headers' => $headers,
                'json' => $data,
            ]);
        } catch (GuzzleException $e) {
            error_log('fcm send fail: ' . $e->getMessage() . ' data' . json_encode($data));
            return false;
        }

        return true;
    }


    /**
     *
     * @param string $target topic, token, condition
     * @param string $title
     * @param string $body
     * @param ?string $image
     * @return array
     */
    public function createMessage($target_data, $title, $body, $image = null)
    {
        $notification = [
            'title' => $title,
            'body' => $body
        ];

        if ($image) {
            $notification['image'] = $image;
        }

        $data = [
            'message' => [
                'notification' => $notification
            ]
        ];
        
        list($target, $target_value) = ($target_data);
        switch ($target) {
            case 'token':
                $data['message']['token'] = $target_value;
                break;
            case 'topic':
                $data['message']['topic'] = $target_value;
                break;
            case 'condition':
                $data['message']['condition'] = $target_value;
                break;
            default:
                throw new \InvalidArgumentException("Invalid type: " . print_r($target_data, true));
        }

        return $data;
    }


    /**
     * @param string $mb_id
     * @param string $token
     * @param string $platform
     * @param string $ip
     * @return void
     */
    public function registerFcmToken($mb_id, $token, $platform, $ip)
    {
        $fcm_token_table = $GLOBALS['g5']['fcm_token'] ?? G5_TABLE_PREFIX . 'fcm_token';
        $query = "INSERT INTO $fcm_token_table SET
            mb_id = ?,
            ft_token = ?, 
            ft_platform = ?, 
            ft_expired_at = DATE_ADD(NOW(), INTERVAL 270 DAY), 
            ft_last_access_at = NOW(), 
            ft_ip = ? ON DUPLICATE KEY UPDATE ft_last_access_at = NOW(), ft_ip = ?";
        Db::getInstance()->run($query, [$mb_id, $token, $platform, $ip, $ip]);
    }

    /**
     * 접속 안한지 270 일 후 만료되는 토큰 삭제 하므로 270일을 업데이트
     * @see https://firebase.google.com/docs/cloud-messaging/manage-tokens
     * @return void
     */
    public function updateExpiresFcmToken($token)
    {
        $fcm_token_table = $GLOBALS['g5']['fcm_token'];
        $query = "UPDATE $fcm_token_table SET ft_expired_at = DATE_ADD(NOW(), INTERVAL 270 DAY) WHERE ft_token = ?";
        Db::getInstance()->run($query, [$token]);
    }

    /**
     * 접속 안한지 270 일 후 만료되는 토큰 삭제
     * @see https://firebase.google.com/docs/cloud-messaging/manage-tokens
     * @return void
     */
    public function deleteExpriesFcmToken()
    {
        $fcm_token_table = $GLOBALS['g5']['fcm_token'];
        $query = "DELETE FROM $fcm_token_table WHERE ft_expired_at < NOW()";
        Db::getInstance()->run($query);
    }

    public function deleteFcmToken($token)
    {
        $fcm_token_table = $GLOBALS['g5']['fcm_token'];
        Db::getInstance()->deleteById($fcm_token_table, 'ft_token', $token);
    }
}