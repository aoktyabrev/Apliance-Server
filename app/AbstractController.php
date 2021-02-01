<?php

namespace app;

use Wsm\DBManagers\MySQLManager;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

abstract class AbstractController {

    protected $request;
    private $authorizationProvider;

    public function __construct($request)
    {
        $this->request = $request;
        $this->authorizationProvider = new WsmAuthProvider();
    }

    public function authorize(){

        if(!isset($this->request['login']) || !isset($this->request['password'])){
            return array('type' => 'error', 'text' => 'Please provide login and password');
        }

        if($this->request['login'] != getenv('ADMIN_LOGIN') || $this->request['password'] != getenv('ADMIN_PASSWORD')){
            return array('type' => 'error', 'text' => 'Invalid login or password');
        }

        return array('type' => 'success', 'text' => 'Authorized');

        try {
            // Try to get an access token using the resource owner password credentials grant.
            $accessToken = $this->authorizationProvider->getAccessToken('password', [
                'username' => $this->request['login'],
                'password' => $this->request['password']
            ]);

            return array('type' => 'success', 'token' => $accessToken);

        } catch (IdentityProviderException $e) {
            // Failed to get the access token
            return array('type' => 'error', 'text' => $e->getMessage());
        }
    }
}