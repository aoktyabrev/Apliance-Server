<?php
namespace app;

use Wsm\DBManagers\MySQLManager;
use Wsm\DBManagers\CouchDBManager;
use Wsm\DataObjects\ServerTicket;
use Wsm\DataObjects\CredentialsGroup;
use Wsm\DataObjects\ServerTicketCredentialsGroup;
use Wsm\DataObjects\Credentials;
use Wsm\DataObjects\ScannedServer;
use Wsm\DataObjects\Networks;

//use Wsm\API\Managers\SQSManager;

class ServerController extends AbstractController
{

    private $serverTicketDataObject;
    private $apiController;

    public function __construct($request)
    {
        parent::__construct($request);
        $this->serverTicketDataObject = new ServerTicket(new MySQLManager());
        $this->serverTicketDataObject->setTable(getenv('SERVER_TICKET_TABLE'));
        $this->apiController = new \app\ApiController($request);
    }

    public function addServer()
    {
        try {
            $this->serverTicketDataObject->fill($this->request['serverTicket']);

            if ($this->serverTicketDataObject->checkIfServerExists()) {
                return array('type' => 'error', 'text' => 'Server already exists');
            }

            $this->serverTicketDataObject->setFingerprint();
            $serverTicket = $this->serverTicketDataObject->toArray();
            $serverTicketHash = ServerTicket::hash($serverTicket);
            $this->serverTicketDataObject->setId($serverTicketHash);

            //credentials group used
            if (isset($this->request['credentialsGroup']['group']) && $serverTicket['auth_method'] == ServerTicket::AUTH_METHOD_CREDENTIALS_GROUP) {
                $serverTicketCredentialsGroup = new ServerTicketCredentialsGroup(new MySQLManager());
                $serverTicketCredentialsGroup->fill(array('server_ticket_hash' => $serverTicketHash, 'credentials_group_id' => $this->request['credentialsGroup']['group']));
                $serverTicketCredentialsGroup->put();
                //new credentials
            } else {
                $credentials = new Credentials(new CouchDBManager());
                $this->request['credentials']['_id'] = $serverTicketHash;
                $credentials->fill($this->request['credentials']);
                $credentials->put();
            }

            //new credentials group added
            if (isset($this->request['credentialsGroup']['makeGroup']) && isset($this->request['credentialsGroup']['groupName']) && $serverTicket['auth_method'] != ServerTicket::AUTH_METHOD_CREDENTIALS_GROUP) {
                $credentialsGroup = new CredentialsGroup(new MySQLManager());
                $nameCheck = $credentialsGroup->getBy('name', $this->request['credentialsGroup']['groupName']);
                if ($nameCheck) {
                    return array('type' => 'error', 'text' => 'Group with the same name already exists');
                }
                $credentialsGroup->fill(array('name' => $this->request['credentialsGroup']['groupName'], 'credentials_id' => $serverTicketHash));
                $credentialsGroup->put();
            }

            $this->serverTicketDataObject->put();

            $error = $this->serverTicketDataObject->getManager()->getError();
            if ($error) {
                return array('type' => 'error', 'text' => $error);
            }

            $this->apiController->addServer($serverTicket, $serverTicketHash);
        } catch (\Throwable $e) {
            return array('type' => 'error', 'text' => $e->getMessage());
        }

        return array('type' => 'message', 'text' => 'Server successfully added');
    }

    public function removeServer()
    {
        if (!isset($this->request['_id'])) {
            return array('type' => 'error', 'text' => 'No server ticket _id value provided');
        }

        try {
            $serverTicket = $this->serverTicketDataObject->get($this->request['_id']);

            if ($serverTicket['connection_type'] == ServerTicket::CONNECTION_TYPE_SERVER && $serverTicket['auth_method'] == ServerTicket::AUTH_METHOD_CREDENTIALS_GROUP) {
                $serverTicketCredentialsGroupObject = new ServerTicketCredentialsGroup(new MySQLManager());
                $serverTicketCredentialsGroup = $serverTicketCredentialsGroupObject->getBy('server_ticket_hash', $this->request['_id']);
                if ($serverTicketCredentialsGroup) {
                    $serverTicketCredentialsGroupObject->delete($serverTicketCredentialsGroup['_id']);
                }
            }

            if ($serverTicket['connection_type'] == ServerTicket::CONNECTION_TYPE_SERVER && $serverTicket['auth_method'] != ServerTicket::AUTH_METHOD_CREDENTIALS_GROUP) {
                $credentialsGroupObject = new CredentialsGroup(new MySQLManager());
                $credentialsGroup = $credentialsGroupObject->getBy('credentials_id', $this->request['_id']);

                if ($credentialsGroup) {
                    return array('type' => 'error', 'text' => "This server is main credentials for group {$credentialsGroup['name']}! Detach group first, please.");
                }

                $credentials = new Credentials(new CouchDBManager());
                if (!$credentials->delete($this->request['_id'])) {
                    return array('type' => 'error', 'text' => 'Not able to remove credentials');
                }
            }

            if (!$this->serverTicketDataObject->delete($this->request['_id'])) {
                return array('type' => 'error', 'text' => 'Not able to remove server');
            }

            $this->apiController->removeServer($this->request['_id']);
        } catch (\Throwable $e) {
            //this may be should be moved to endpoint.php
            return array('type' => 'error', 'text' => $e->getMessage());
        }
        return array('type' => 'message', 'text' => 'Server successfully removed');
    }

    public function getServerList()
    {
        $servers = $this->serverTicketDataObject->all();
        return $servers;
    }

    public function scanLocalNetwork()
    {
        $scannedServersCount = exec('php ' . getenv('BASE_DIR') . '/public/scanner.php --localnet 2>&1');
        if ((int) $scannedServersCount > 0) {
            return array('type' => 'message', 'text' => 'Server scan finished. ' . $scannedServersCount . ' servers found.');
        }

        return array('type' => 'error', 'text' => 'Scanning unsuccessful');
    }

    public function scanAllNetwork()
    {
        $networks = new Networks(new CouchDBManager());
        $data = $networks->all();
        if ($data->total_rows != 0) {
            $rows = $data->rows;
            $scannedServersCount_ = 0;
            foreach ($rows as $row) {
                $doc = $row->doc;
                $subnet = $doc->subnet;
                $scannedServersCount = exec('php ' . getenv('BASE_DIR') . '/public/scanner.php ' . $subnet . ' 2>&1');
                $scannedServersCount_ = $scannedServersCount_ + $scannedServersCount;
            }
            if ((int) $scannedServersCount_ > 0) {
                return array('type' => 'message', 'text' => 'Server scan finished. ' . $scannedServersCount_ . ' servers found.');
            }
        }

        return array('type' => 'error', 'text' => 'Scanning unsuccessful');
    }

    public function addNetwork()
    {
        try {
            $subnet = $this->request['subnet'];

            if ($subnet == '') {
                return array('type' => 'error', 'text' => 'Enter valid subnet value!');
            }

            $cidr = explode('/', trim($subnet));

            $subnet_ip = $cidr[0];
            $subnet_mask = long2ip(-1 << (32 - (int) $cidr[1]));
            $subnet_ip_range[0] = long2ip((ip2long($cidr[0])) & ((-1 << (32 - (int) $cidr[1]))));
            $subnet_ip_range[1] = long2ip((ip2long($cidr[0])) + pow(2, (32 - (int) $cidr[1])) - 1);


            if (!filter_var($subnet_ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                return array('type' => 'error', 'text' => 'Enter valid subnet value!');
            }

            $networks = new Networks(new CouchDBManager());
            $networks->fill(['subnet' => trim($subnet), 'description' => "Range " . $subnet_ip_range[0] . " - " . $subnet_ip_range[1]]);
            $networks->put();
        } catch (\Throwable $e) {
            return array('type' => 'error', 'text' => $e->getMessage());
        }

        return array('type' => 'message', 'text' => 'Subnet successfully added');
    }

    public function delNetwork()
    {
        try {
            $subnet = $this->request['subnet'];

            if ($subnet == '') {
                return array('type' => 'error', 'text' => 'Enter valid subnet value!');
            }

            $networks = new Networks(new CouchDBManager());
            $rows = (array) $networks->all();
            foreach ($rows['rows'] as $items) {
                if ($items->doc->subnet == $subnet) {
                    $networks->delete($items->id);
                }
            }
        } catch (\Throwable $e) {
            return array('type' => 'error', 'text' => $e->getMessage());
        }
        return array('type' => 'message', 'text' => 'Subnet successfully deleted');
    }

    public function getScannedServers()
    {
        $scannedServer = new ScannedServer(new CouchDBManager());
        return $scannedServer->all();
    }

    public function getSubnets()
    {
        $networks = new Networks(new CouchDBManager());
        return $networks->all();
    }
}
