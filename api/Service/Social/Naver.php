<?php

namespace API\Service\Social;


use Hybridauth\Adapter\OAuth2;
use Hybridauth\Exception\UnexpectedApiResponseException;
use Hybridauth\Data;
use Hybridauth\User;


class Naver extends OAuth2
{
    // phpcs:ignore
    protected $scope;


    protected $apiBaseUrl = 'https://openapi.naver.com/v1/nid/me';


    protected $authorizeUrl = 'https://nid.naver.com/oauth2.0/authorize';


    protected $accessTokenUrl = 'https://nid.naver.com/oauth2.0/token';


    protected $apiDocumentation = 'https://developers.naver.com/docs/login/api';

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
     * @return User\Profile
     * @throws UnexpectedApiResponseException
     * @throws \Hybridauth\Exception\HttpClientFailureException
     * @throws \Hybridauth\Exception\HttpRequestFailedException
     * @throws \Hybridauth\Exception\InvalidAccessTokenException
     */
    public function getUserProfile()
    {
        $response = $this->apiRequest($this->apiBaseUrl);
        $data = new Data\Collection($response->response);

        if (!$data->exists('id')) {
            throw new UnexpectedApiResponseException('Provider API returned an unexpected response.');
        }

        $user_profile = new User\Profile();
        $user_profile->identifier = $data->get('id');
        $user_profile->firstName = $data->get('name');
        $user_profile->displayName = $data->get('nickname');
        $user_profile->photoURL = $data->get('profile_image');
        $user_profile->gender = $data->get('gender');
        $user_profile->email = $data->get('email');

        $user_profile->emailVerified = $data->get('email_verified') ? $user_profile->email : '';

        if ($this->config->get('photo_size')) {
            $user_profile->photoURL .= '?sz=' . $this->config->get('photo_size');
        }

        return $user_profile;
    }

}
