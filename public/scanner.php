<?php
require(__DIR__ . '/../vendor/autoload.php');

$dotenv = new Dotenv\Dotenv(__DIR__ . '/../');
$dotenv->load();

// Launch an ARP-Scan on the local subnet
// Must be run as root

if (isset($_SERVER['argv'][1])) {
    $options = $_SERVER['argv'][1];
} else {
    $options = '--localnet';
}

if ($options == '--localnet') {
    shell_exec('curl -X DELETE http://' . getenv('COUCHDB_HOST') . '/' . getenv('SCANNED_SERVERS_DATABASE'));
}
shell_exec('curl -X PUT http://' . getenv('COUCHDB_HOST') . '/' . getenv('SCANNED_SERVERS_DATABASE'));

$arp_scan_raw = shell_exec('sudo arp-scan ' . $options);
// Get lines as an array
$arp_scan = explode("\n", $arp_scan_raw);
// Will contain matching fields in the regexp
$matches = [];
// Will contain all found interfaces in a mac-indexed array
//$found_interfaces = [];
// Scan results

$i = 0;
foreach ($arp_scan as $scan) {

    if ($i >= 100) {
        echo $i;
        exit;
    }
    $matches = []; // reset
    // Parse output lines
//    if (preg_match('/^([0-9\.]+)[[:space:]]+([0-9a-f:]+)[[:space:]]+(.+)$/', $scan, $matches) !== 1) {
    if (preg_match('/\b(?:(?:2(?:[0-4][0-9]|5[0-5])|[0-1]?[0-9]?[0-9])\.){3}(?:(?:2([0-4][0-9]|5[0-5])|[0-1]?[0-9]?[0-9]))\b/', $scan, $matches) !== 1) {
        // Ignore lines that don't contain results
        continue;
    }
    $ip = $matches[0];
    $str = explode("\t", $scan);
    $mac = $str[1];
    $desc = $str[2];
    @shell_exec('curl -X PUT http://' . getenv('COUCHDB_HOST') . '/' . getenv('SCANNED_SERVERS_DATABASE') . "/{$mac} -d '{\"ip\":\"{$ip}\", \"description\":\"{$desc}\"}'");
    $i++;
}

echo $i;
exit;
