<?php

namespace ponbiki\FTW;

class DataBase
{
    private $settings = [
        'db_host' => '12.34.56.78',
        'db_port' => '3306',
        'db_name' => 'blah',
        'db_user' => 'blah',
        'db_pass' => 'blah',
        'db_charset' => 'utf8'        
    ];
    
    protected $pdo;
    
    protected function construct()
    {
        if (!$this->pdo = new PDO(
            sprintf(
                'mysql:host=%s;dbname=%s;port=%s;charset=%s',
                $this->settings['db_host'],
                $this->settings['db_name'],
                $this->settings['db_port'],
                $this->settings['db_charset']),
            $this->settings['db_user'],
            $this->settings['db_pass'])
        ) {
            throw new \Exception('Database connection problem');
        }
    }
    
    
}
