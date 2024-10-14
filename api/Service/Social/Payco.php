<?php

namespace API\Service\Social;

use Exception;
use Hybridauth\Adapter\OAuth2;
use Hybridauth\Exception\UnexpectedApiResponseException;
use Hybridauth\Data;
use Hybridauth\User;


class Payco extends OAuth2
{

    protected $apiBaseUrl = 'https://apis-payco.krp.toastoven.net/payco/friends/find_member_v2.json';
    protected $authorizeUrl = 'https://id.payco.com/oauth2.0/authorize';
    protected $accessTokenUrl = 'https://id.payco.com/oauth2.0/token';


    /**
     * @see https://developers.payco.com/guide/development/apply/web
     */
    protected $apiDocumentation = 'https://developers.payco.com/guide/development/login';

    /**
     * {@inheritdoc}
     * @return void
     */
    protected function initialize()
    {
        parent::initialize();

        $this->AuthorizeUrlParameters += [
            'serviceProviderCode' => 'FRIENDS',
            'userLocale' => 'ko_KR',
        ];


        if ($this->isRefreshTokenAvailable()) {
            $this->tokenRefreshParameters += [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret
            ];
        }
    }


    /**
     * @return User\Profile
     * @throws UnexpectedApiResponseException
     * @throws \Hybridauth\Exception\HttpClientFailureException
     * @throws \Hybridauth\Exception\HttpRequestFailedException
     * @throws \Hybridauth\Exception\InvalidAccessTokenException
     */
    public function getUserProfile()
    {
        $headers = [
            'client_id' => $this->clientId,
            'access_token' => $this->getStoredData('access_token'),
            'Content-Type' => 'application/json'
        ];

        $body = [
            'client_id' => $this->clientId,
            'access_token' => $this->getStoredData('access_token'),
            'MemberProfile' => 'idNo,id,name,email,mobile,genderCode,birthdayMMdd,ageGroup',
        ];

        $response = $this->apiRequest($this->apiBaseUrl, "POST", $body, $headers);
        if (isset($response->error_code)) {
            throw new Exception($response->message);
        }

        $data = new Data\Collection($response->data->member);


        if (!$data->get('idNo')) {
            throw new UnexpectedApiResponseException('payco 응답이 실패했습니다. paycoresultMessage' . $response->header->resultMessage);
        }

        $user_profile = new User\Profile();
        $user_profile->identifier = $data->get('idNo');
        $user_profile->firstName = $data->get('name');
        $user_profile->displayName = $data->get('name');
        $user_profile->photoURL = $data->get(''); // 페이코는 프로필이 없다.
        $user_profile->gender = $data->get('genderCode');
        $user_profile->email = $data->get('email');

        $user_profile->emailVerified = $data->get('email_verified') ? $user_profile->email : '';

        return $user_profile;
    }


}