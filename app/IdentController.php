<?php
namespace app;

class IdentController extends AbstractController
{

    public function __construct($request)
    {
        parent::__construct($request);
    }

    public function init()
    {
        $private = [];
        $result = shell_exec('/sbin/ifconfig -a | grep "^\s*inet[6]*\s" | grep -v "[^\w]127\.0\.0\.1[^\w]" | grep -v "[^\w]::1[^\w]" | sort');
        //$result = shell_exec("/sbin/ifconfig -a |grep \"inet \"|grep -v 127.0.0.1");
        $out = explode("\n", trim($result));
        foreach ($out as $value) {
            $parts = explode(' ', preg_replace('/\s+/', ' ', str_replace('addr:', '', trim($value))));
            $ip = [];
            for ($i = 0; $i < count($parts) - 1; $i++) {
                if (in_array($parts[$i], ['inet', 'inet6'])) {
                    $parts2 = explode('/', $parts[$i + 1]);

                    if (count($parts2) == 2) {
                        $ip = $parts2[0];
                    } else {
                        $ip = $parts[$i + 1];
                    }
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                        $host[] = $ip;
                    }
                }
            }
        }

        $APPLIANCE_ID = getenv('APPLIANCE_ID');
        $TOKEN = getenv('TOKEN');

        If ($APPLIANCE_ID == 'nop' || $APPLIANCE_ID == '') {
            $ID = $this->getID();
            if ($this->updateenv('APPLIANCE_ID', $ID) === FALSE) {
                return array('type' => 'error', 'text' => 'Unable to write the environment file');
            }
            $APPLIANCE_ID = $ID;
        }

        If ($TOKEN == 'nop' || $TOKEN == '') {
            return array('type' => 'error', 'text' => 'No TOKEN', 'data' => $APPLIANCE_ID, 'url' => $host);
        } else {
            return array('type' => 'success', 'text' => 'Identified', 'data' => $APPLIANCE_ID, 'url' => $host);
        }
    }

    public function set()
    {
        $token = trim($this->request['token']);

        if ($this->updateenv('TOKEN', $token) === FALSE) {
            return array('type' => 'error', 'text' => 'Unable to write the environment file');
        }

        return array('type' => 'success', 'text' => 'Identified');
    }

    protected function updateenv($env, $value)
    {
        $file = '.env';
        $path = __DIR__ . DIRECTORY_SEPARATOR . '..';
        $filePath = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $file;

        if (!is_readable($filePath) || !is_file($filePath)) {
            return false;
        }

        $lines = file($filePath);

        foreach ($lines as $key => $line) {
            if (stristr($line, $env) !== FALSE) {
                $lines[$key] = $env . ' = ' . $value . PHP_EOL;
            }
        }

        return file_put_contents($filePath, $lines);
    }

    protected function getID()
    {
        mt_srand((double) microtime() * 10000); //optional for php 4.2.0 and up.
        $uuid = strtoupper(md5(uniqid(rand(), true)));
        return $uuid;
    }
}
