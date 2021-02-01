<?php

namespace app;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Repositories\AccessTokenRepository;
use Psr\Http\Message\ResponseInterface;

class WsmAuthProvider extends AbstractProvider {

    const ACCESS_TOKEN_RESOURCE_OWNER_ID = null;

    public function getBaseAuthorizationUrl()
    {
        return 'http://54.191.170.79/endpoint.php?action=authorize';
    }

    public function getBaseAccessTokenUrl(array $params)
    {
        return 'http://54.191.170.79/endpoint.php?action=token';
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {

    }

    public function getAccessToken($grant, array $options = array())
    {
        $resourceOwner = new WsmResourceOwner();
        return AccessTokenRepository::getNewToken($resourceOwner, []);
    }

    protected function getDefaultScopes()
    {
        return [];
    }

    protected function checkResponse(ResponseInterface $response, $data)
    {

    }

    protected function createResourceOwner(array $response, AccessToken $token)
    {

    }
}