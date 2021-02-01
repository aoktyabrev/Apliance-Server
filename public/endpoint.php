<?php
require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../app/AbstractController.php');
require(__DIR__ . '/../app/ServerController.php');
require(__DIR__ . '/../app/GroupCredentialsController.php');
require(__DIR__ . '/../app/UserController.php');
require(__DIR__ . '/../app/ApiController.php');
require(__DIR__ . '/../app/IdentController.php');
require(__DIR__ . '/../app/WsmAuthProvider.php');
require(__DIR__ . '/../app/WsmResourceOwner.php');

putenv('HOME=' . __DIR__ . '/../');

$dotenv = new Dotenv\Dotenv(__DIR__ . '/../');
$dotenv->load();

$userController = new \app\UserController($_POST);
$response = array();

if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'authorize':
            $response = $userController->authorize();
            break;

        case 'resource':
            $response = $userController->getToken();
            break;
    }
}

$identController = new \app\IdentController($_POST);

if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'ident_init':
            $response = $identController->init();
            break;
        case 'ident_set':
            $response = $identController->set();
            break;
    }
}

$serverController = new \app\ServerController($_POST);
$credentialsGroupController = new \app\GroupCredentialsController($_POST);

switch ($_GET['action']) {
    case 'list':
        $response = $serverController->getServerList();
        break;

    case 'add':
        $response = $serverController->addServer();
        break;

    case 'remove':
        $response = $serverController->removeServer();
        break;

    case 'scanNetwork':
        $response = $serverController->scanLocalNetwork();
        break;

    case 'scanAllNetwork':
        $response = $serverController->scanAllNetwork();
        break;

    case 'addNetwork':
        $response = $serverController->addNetwork();
        break;

    case 'delNetwork':
        $response = $serverController->delNetwork();
        break;

    case 'getScannedServers':
        $response = $serverController->getScannedServers();
        break;

    case 'getSubnets':
        $response = $serverController->getSubnets();
        break;

    case 'credentialsGroupList':
        $response = $credentialsGroupController->getCredentialsGroupList();
        break;

    case 'detachCredentialsGroup':
        $response = $credentialsGroupController->detachCredentialsGroup();
        break;

    case 'credentialsGroupServerTickets':
        $response = $credentialsGroupController->getCredentialsGroupServerTickets();
        break;
}

echo json_encode($response);

exit;
