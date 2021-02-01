<?php
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE);

require(__DIR__ . '/../../vendor/autoload.php');
require(__DIR__ . '/../../vendor/wsm/api-module/src/Wsm/API/Services/RequestService.php');
require(__DIR__ . '/../../app/AbstractController.php');
require(__DIR__ . '/../../app/WsmAuthProvider.php');
require(__DIR__ . '/../../app/ApiController.php');

//HOME is required for AWS store credentials to work.
putenv('HOME=' . __DIR__ . '/../../');

$dotenv = new Dotenv\Dotenv(__DIR__ . '/../../');
$dotenv->load();

$requestService = new Wsm\API\Services\RequestService(null);

if (isset($input)) {
    echo json_encode($requestService->process($input));
}

?>