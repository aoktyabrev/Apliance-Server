<?php
namespace app;

use Wsm\DBManagers\MySQLManager;

class UserController extends AbstractController
{

    public function __construct($request)
    {
        parent::__construct($request);
    }
}
