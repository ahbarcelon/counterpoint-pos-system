<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class App extends BaseConfig
{
    public string $baseURL = 'http://localhost:8080/';
    public array $allowedHostnames = [];
    public string $indexPage = '';
    public string $uriProtocol = 'REQUEST_URI';
    public string $permittedURIChars = 'a-z 0-9~%.:_\\-';
    public string $defaultLocale = 'en';
    public bool $negotiateLocale = false;
    public array $supportedLocales = ['en'];
    public string $appTimezone = 'Asia/Manila';
    public string $charset = 'UTF-8';
    public bool $forceGlobalSecureRequests = false;
    public array $proxyIPs = [];
    public bool $CSPEnabled = false;

    public function __construct()
    {
        $baseURL = env('app.baseURL', null) ?: getenv('APP_BASE_URL') ?: $this->baseURL;
        $this->baseURL = rtrim((string) $baseURL, '/') . '/';
        $this->appTimezone = (string) (env('app.appTimezone', null) ?: getenv('APP_TIMEZONE') ?: $this->appTimezone);
        $secure = env('app.forceGlobalSecureRequests', null);
        $this->forceGlobalSecureRequests = filter_var($secure ?? (getenv('APP_FORCE_HTTPS') ?: false), FILTER_VALIDATE_BOOL);
    }
}
