<?php

namespace API\Service\Social;


use Hybridauth\Adapter\OAuth2;
use Hybridauth\Exception\UnexpectedApiResponseException;
use Hybridauth\Data;
use Hybridauth\User;


class Kakao extends OAuth2
{
    // phpcs:ignore
    protected $scope = 'account_email, profile_image';


    protected $apiBaseUrl = 'https://kapi.kakao.com/v2/user/me';


    protected $authorizeUrl = 'https://kauth.kakao.com/oauth/authorize';


    protected $accessTokenUrl = 'https://kauth.kakao.com/oauth/token';


    protected $apiDocumentation = 'https://developers.kakao.com/docs/latest/ko/kakaologin/common';

    protected function initialize()
    {
        parent::initialize();

        $this->AuthorizeUrlParameters += [
            'access_type' => 'offline'
        ];

        if ($this->isRefreshTokenAvailable()) {
            $this->tokenRefreshParameters += [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret
            ];
        }
    }


    /**
     * {@inheritdoc}
     * See: https://developers.kakao.com/docs/latest/ko/kakaologin/rest-api#req-user-info
     * @return User\Profile
     * @throws UnexpectedApiResponseException
     * @throws \Hybridauth\Exception\HttpClientFailureException
     * @throws \Collection\Exception\HttpRequestFailedException
     * @throws \Hybridauth\Exception\InvalidAccessTokenException
     */
    public function getUserProfile()
    {
        $response = $this->apiRequest($this->apiBaseUrl);

        $data = new Data\Collection($response);

        if (!$data->exists('id')) {
            throw new UnexpectedApiResponseException('Provider API returned an unexpected response.');
        }

        $userProfile = new User\Profile();
        $userProfile->identifier = $data->get('id');
        $userProfile->firstName = $data->get('nickname');
        $userProfile->displayName = $data->get('nickname');
        $userProfile->photoURL = $data->get('thumbnail_image_url');
        $userProfile->gender = $data->get('gender');
        $userProfile->email = $data->get('account_email');
        $userProfile->emailVerified = $data->get('is_email_verified') ? $userProfile->email : '';

        return $userProfile;
    }

}
