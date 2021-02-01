<?php
namespace app;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class WsmResourceOwner implements ResourceOwnerInterface
{
    /**
     * @var array
     */
    protected $response;

    public function __construct(array $response = [])
    {
        $this->response = $response;
    }
    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getValueByKey($this->response, 'user_id');
    }

    /**
     * @inheritdoc
     */
    public function toArray()
    {
        return $this->response;
    }
}