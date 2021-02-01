<?php
parse_str(implode('&', array_slice($argv, 1)), $_GET);

if (!isset($_GET['type'])) {
    return false;
}

require(__DIR__ . '/vendor/autoload.php');
require(__DIR__ . '/app/AbstractController.php');
require(__DIR__ . '/app/WsmAuthProvider.php');
require(__DIR__ . '/app/ApiController.php');

//HOME is required for AWS store credentials to work.
putenv('HOME=' . __DIR__);

const TYPE_VERIFICATION = 'verification';
const TYPE_COMMAND = 'command';

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

$APPLIANCE_ID = getenv('APPLIANCE_ID');
$TOKEN = getenv('TOKEN');

if (($APPLIANCE_ID == 'nop' || $APPLIANCE_ID == '') || ($TOKEN == 'nop' || $TOKEN == '')) {
    /*
     * not ident.
     * skipping the queues processing.
     */
} else {

    $queuesProcessing = new QueuesProcessing();

    if ($_GET['type'] == TYPE_VERIFICATION) {
        $queuesProcessing->actionProcessVerificationMessages();
    } elseif ($_GET['type'] == TYPE_COMMAND) {
        $queuesProcessing->actionProcessCommandMessages();
    }
}