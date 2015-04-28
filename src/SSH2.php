<?php
namespace ponbiki\FTW;

class SSH2
{
    protected $server =  'blah.com';
    protected $port   =  22;
    protected $ssh_user = 'blah';
    protected $ssh_pass = 'blah';
    public $con;
    
    public function __construct()
    {
        if (!$this->con = ssh2_connect($this->server, $this->port)) {
            throw new \Exception('Failed to establish connection');
        } else {
            if (!ssh2_auth_password($this->con, $this->ssh_user, $this->ssh_pass)) {
                throw new \Exception('Failed to authenticate');
            }
        }
    }
    
    public function exec($command)
    {
        if (!$stream = ssh2_exec($this->con, $command)) {
            throw new \Exception('Unable to execute command');
        } else {
            stream_set_blocking($stream, true);
            $data = '';
            while ($buf = fread($stream, 4096)) {
                $data .= $buf;
            }
            fclose($stream);
            return $data;
        }
    }
    
    public function disconnect()
    {
        $this->con = null;
    }
    
    public function __destruct()
    {
        $this->disconnect;
    }
}

$test = new SSH2();
