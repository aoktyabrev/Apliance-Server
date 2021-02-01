<?php

namespace app;

use Wsm\DBManagers\MySQLManager;
use Wsm\DataObjects\CredentialsGroup;
use Wsm\DataObjects\ServerTicketCredentialsGroup;

class GroupCredentialsController extends AbstractController {

    private $credentialsGroup;

    public function __construct($request)
    {
        parent::__construct($request);
        $this->credentialsGroup = new CredentialsGroup(new MySQLManager());
    }

    public function getCredentialsGroupList()
    {
        return $this->credentialsGroup->getManager()->all();
    }

    public function getCredentialsGroupServerTickets()
    {
        $serverTicketCredentialsGroupObject = new ServerTicketCredentialsGroup(new MySQLManager());
        $serverTicketCredentialsGroup = $serverTicketCredentialsGroupObject->getBy('credentials_group_id', $this->request['_id']);
        return count($serverTicketCredentialsGroup);
    }

    public function detachCredentialsGroup()
    {
        if(!isset($this->request['_id'])){
            return array('type' => 'error', 'text' => 'No credentials group _id value provided');
        }

        try {
            $credentialsGroupObject = new CredentialsGroup(new MySQLManager());

            if(!$credentialsGroupObject->delete($this->request['_id'])){
                return array('type' => 'error', 'text' => 'Not able to detach credentials group');
            }

            //TODO: check if any servers use this credentials group!
        }catch (\Throwable $e){
            //this may be should be moved to endpoint.php
            return array('type' => 'error', 'text' => $e->getMessage());
        }
        return array('type' => 'message', 'text' => 'Credentials group successfully detached');
    }
}