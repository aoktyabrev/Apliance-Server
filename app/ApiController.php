<?php
namespace app;

class ApiController extends AbstractController
{

    private $applianceId;
    private $token;
    private $endpoint;

    const API_VERSION = "/apiv2/";

    public function __construct($request)
    {
        parent::__construct($request);
        $this->applianceId = getenv('APPLIANCE_ID');
        $this->token = getenv('TOKEN');
        $this->endpoint = getenv('API_ENDPOINT');
    }

    //------------core main function--------------------------
    public function addServer($serverTicket, $serverTicketHash)
    {
        $data = [
            'client' => [
                'token' => $this->token,
            ],
            'data' => [
                "action" => "appliance_server_add",
                "fields" => [
                    'appliance_id' => $this->applianceId,
                    'serverTicket' => $serverTicket,
                    'serverTicketHash' => $serverTicketHash,
                ],
            ],
        ];

        try {
            $ret = $this->sendPackage($data);
            if (!isset($ret['data']['message']) || $ret['data']['message'] != 'SUCCESS') {
                $err = isset($ret['data']['message']) ? $ret['data']['message'] : 'API in not responding.';
                throw new \Exception($err);
            }
        } catch (\Throwable $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function removeServer($serverTicketHash)
    {
        $data = [
            'client' => [
                'token' => $this->token,
            ],
            'data' => [
                "action" => "appliance_server_remove",
                "fields" => [
                    'appliance_id' => $this->applianceId,
                    'serverTicketHash' => $serverTicketHash,
                ],
            ],
        ];

        try {
            $ret = $this->sendPackage($data);
            if (!isset($ret['data']['message']) || $ret['data']['message'] != 'SUCCESS') {
                $err = isset($ret['data']['message']) ? $ret['data']['message'] : 'API in not responding.';
                throw new \Exception($err);
            }
        } catch (\Throwable $e) {
            throw new \Exception($e->getMessage());
        }
    }

    //------------proxy for agent function---------------------
    public function addAgentServer($fields)
    {
        $fields['appliance_id'] = $this->applianceId;

        $data = [
            'client' => [
                'token' => $this->token,
            ],
            'data' => [
                "action" => "server_add",
                "fields" => $fields,
            ],
        ];

        try {
            $ret = $this->sendPackage($data);
            if (!isset($ret['data']['message']) || $ret['data']['message'] != 'SUCCESS') {
                $err = isset($ret['data']['message']) ? $ret['data']['message'] : 'API in not responding.';
                throw new \Exception($err);
            }
//-------------------save server on appliance DB------------------------
            $messageBody = array(
                'serverTicket' => array(
                    'fingerprint' => $fields['fingerprint'],
                    'ip' => isset($fields['ip']) ? $fields['ip'] : $fields['host'],
                    'port' => $fields['port'],
                    'os' => isset($fields['os']) ? strtolower($fields['os']) : strtolower($fields['type']),
                    'connection_type' => isset($fields['connection_type']) ? $fields['connection_type'] : $fields['connect_type'],
                    'auth_method' => isset($fields['auth_method']) ? $fields['auth_method'] : $fields['access_type']
                ),
                'token' => $this->token,
                'name' => isset($fields['name']) ? $fields['name'] : ''
            );

            $mySQLManager = new \Wsm\DBManagers\MySQLManager();
            $serverTicketObject = new \Wsm\DataObjects\ServerTicket($mySQLManager);
            $serverTicketObject->fill($messageBody['serverTicket']);
            $serverTicketHash = $serverTicketHash ? $serverTicketHash : \Wsm\DataObjects\ServerTicket::hash($serverTicketObject->toArray());
            $serverTicketObject->setId($serverTicketHash);
            $serverTicketObject->put();
//--------------------------------------------------------------------------
            return true;
        } catch (\Throwable $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function getAgentCommand($fields)
    {
        $fields['appliance_id'] = $this->applianceId;

        $data = [
            'client' => [
                'token' => $this->token,
            ],
            'data' => [
                "action" => "get_command",
                "fields" => $fields,
            ],
        ];

        try {
            $ret = $this->sendPackage($data);
            if (isset($ret['data']['message_id'])) {
                switch ($ret['data']['message_id']) {
                    case 21:
                        return null; //no command available
                        break;
                    case 22:
                        return $ret['data']; //an available command exists
                        break;
                    case 23:
                        throw new \Exception($ret['data']['message']); //Exception
                        break;
                }
            }
            throw new \Exception('API in not responding.');
        } catch (\Throwable $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function sendResponse($fields)
    {
        $fields['appliance_id'] = $this->applianceId;
        $fields['action'] = "set_command_response";

        $data = [
            'client' => [
                'token' => $this->token,
            ],
        ];

        $data['data'] = $fields;

        try {
            $ret = $this->sendPackage($data);
            if (isset($ret['data']['message_id'])) {
                switch ($ret['data']['message_id']) {
                    case 31:
                        return $ret['data']; //Command response has been accepted
                        break;
                    case 32:
                        throw new \Exception($ret['data']['message']); //Exception
                        break;
                    case 33:
                        throw new \Exception($ret['data']['message']); //Exception
                        break;
                    case 23:
                        throw new \Exception($ret['data']['message']); //Exception
                        break;
                }
            }
            throw new \Exception('API in not responding.');
        } catch (\Throwable $e) {
            throw new \Exception($e->getMessage());
        }
    }

    //-------------proxy for connector function------------------
    public function getMessageVerification()
    {
        $data = [
            'client' => [
                'token' => $this->token,
            ],
            'data' => [
                "action" => "get_message_verification",
                "fields" => [
                    'appliance_id' => $this->applianceId,
                ],
            ],
        ];

        try {
            $ret = $this->sendPackage($data);
            if (isset($ret['data']['message_id']) && $ret['data']['message_id'] == 41) {
                return $ret['data']['messageObject'];
            } else {
                return false;
            }
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function setMessageVerification($messageResponse)
    {
        $data = [
            'client' => [
                'token' => $this->token,
            ],
            'data' => [
                "action" => "set_message_verification",
                "fields" => [
                    'appliance_id' => $this->applianceId,
                    'messageResponse' => $messageResponse,
                ],
            ],
        ];

        try {
            $ret = $this->sendPackage($data);
            if (isset($ret['data']['message_id']) && $ret['data']['message_id'] == 51) {
                return true;
            } else {
                return false;
            }
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function getMessageCommand()
    {
        $data = [
            'client' => [
                'token' => $this->token,
            ],
            'data' => [
                "action" => "get_message_command",
                "fields" => [
                    'appliance_id' => $this->applianceId,
                ],
            ],
        ];

        try {
            $ret = $this->sendPackage($data);
            if (isset($ret['data']['message_id']) && $ret['data']['message_id'] == 61) {
                return $ret['data']['messageObject'];
            } else {
                return false;
            }
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function setMessageCommand($messageResponse)
    {
        $data = [
            'client' => [
                'token' => $this->token,
            ],
            'data' => [
                "action" => "set_message_command",
                "fields" => [
                    'appliance_id' => $this->applianceId,
                    'messageResponse' => $messageResponse,
                ],
            ],
        ];

        try {
            $ret = $this->sendPackage($data);
            if (isset($ret['data']['message_id']) && $ret['data']['message_id'] == 71) {
                return true;
            } else {
                return false;
            }
        } catch (\Throwable $e) {
            return false;
        }
    }

    //------------------util-------------------------------------
    private function sendPackage($data)
    {
        $ch = curl_init();
        //curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_URL, $this->endpoint . self::API_VERSION);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        $result_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return json_decode($result, true);
    }
}
