<?php

namespace Config;

use CodeIgniter\Database\Config;

class Database extends Config
{
    public string $filesPath = APPPATH . 'Database' . DIRECTORY_SEPARATOR;
    public string $defaultGroup = 'default';

    public array $default = [
        'DSN'      => '',
        'hostname' => 'localhost',
        'username' => 'root',
        'password' => '',
        'database' => 'pos_system',
        'DBDriver' => 'MySQLi',
        'DBPrefix' => '',
        'pConnect' => false,
        'DBDebug'  => true,
        'charset'  => 'utf8mb4',
        'DBCollat' => 'utf8mb4_unicode_ci',
        'swapPre'  => '',
        'encrypt'  => false,
        'compress' => false,
        'strictOn' => false,
        'failover' => [],
        'port'     => 3306,
        'numberNative' => false,
        'foundRows' => false,
        'dateFormat' => [
            'date' => 'Y-m-d',
            'datetime' => 'Y-m-d H:i:s',
            'time' => 'H:i:s',
        ],
    ];

    public array $tests = [
        'DSN' => '',
        'hostname' => '127.0.0.1',
        'username' => 'root',
        'password' => '',
        'database' => 'pos_system_test',
        'DBDriver' => 'MySQLi',
        'DBPrefix' => '',
        'pConnect' => false,
        'DBDebug' => true,
        'charset' => 'utf8mb4',
        'DBCollat' => 'utf8mb4_unicode_ci',
        'swapPre' => '',
        'encrypt' => false,
        'compress' => false,
        'strictOn' => false,
        'failover' => [],
        'port' => 3306,
        'numberNative' => false,
        'foundRows' => false,
        'dateFormat' => ['date' => 'Y-m-d', 'datetime' => 'Y-m-d H:i:s', 'time' => 'H:i:s'],
    ];

    public function __construct()
    {
        parent::__construct();
        $this->default['hostname'] = (string) $this->value('database.default.hostname', 'DB_HOST', $this->default['hostname']);
        $this->default['username'] = (string) $this->value('database.default.username', 'DB_USER', $this->default['username']);
        $this->default['password'] = (string) $this->value('database.default.password', 'DB_PASSWORD', $this->default['password']);
        $this->default['database'] = (string) $this->value('database.default.database', 'DB_DATABASE', $this->default['database']);
        $this->default['DBDriver'] = (string) $this->value('database.default.DBDriver', 'DB_DRIVER', $this->default['DBDriver']);
        $this->default['port'] = (int) $this->value('database.default.port', 'DB_PORT', $this->default['port']);
        $this->default['DBDebug'] = ENVIRONMENT !== 'production';
        if (ENVIRONMENT === 'testing') {
            $this->defaultGroup = 'tests';
            $this->tests['hostname'] = getenv('DB_TEST_HOST') ?: '127.0.0.1';
            $this->tests['username'] = getenv('DB_TEST_USER') ?: 'root';
            $this->tests['password'] = getenv('DB_TEST_PASSWORD') ?: '';
            $this->tests['database'] = getenv('DB_TEST_DATABASE') ?: 'pos_system_test';
            $this->tests['port'] = (int) (getenv('DB_TEST_PORT') ?: 3306);
        }
    }

    private function value(string $envKey, string $fallbackKey, mixed $default): mixed
    {
        $value = env($envKey, null);
        return ($value === null || $value === '') ? (getenv($fallbackKey) ?: $default) : $value;
    }
}
