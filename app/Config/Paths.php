<?php

namespace Config;

class Paths
{
    public string $systemDirectory;
    public string $appDirectory;
    public string $writableDirectory;
    public string $testsDirectory;
    public string $viewDirectory;
    public string $envDirectory;

    public function __construct()
    {
        $root = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR;
        $this->systemDirectory = $root . 'vendor/codeigniter4/framework/system';
        $this->appDirectory = $root . 'app';
        $this->writableDirectory = $root . 'writable';
        $this->testsDirectory = $root . 'tests';
        $this->viewDirectory = $this->appDirectory . DIRECTORY_SEPARATOR . 'Views';
        $this->envDirectory = $root;
    }
}
