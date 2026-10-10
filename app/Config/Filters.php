<?php

namespace Config;

use App\Filters\AuthFilter;
use CodeIgniter\Config\Filters as BaseFilters;
use CodeIgniter\Filters\CSRF;
use CodeIgniter\Filters\DebugToolbar;
use CodeIgniter\Filters\Honeypot;
use CodeIgniter\Filters\ForceHTTPS;
use CodeIgniter\Filters\PageCache;
use CodeIgniter\Filters\PerformanceMetrics;
use CodeIgniter\Filters\InvalidChars;
use CodeIgniter\Filters\SecureHeaders;
use CodeIgniter\Filters\Cors;

class Filters extends BaseFilters
{
    public array $aliases = [
        'csrf' => CSRF::class,
        'toolbar' => DebugToolbar::class,
        'honeypot' => Honeypot::class,
        'forcehttps' => ForceHTTPS::class,
        'pagecache' => PageCache::class,
        'performance' => PerformanceMetrics::class,
        'invalidchars' => InvalidChars::class,
        'secureheaders' => SecureHeaders::class,
        'cors' => Cors::class,
        'auth' => AuthFilter::class,
    ];

    public array $globals = [
        'before' => ['csrf'],
        'after' => [],
    ];

    public array $methods = [];
    public array $filters = [];
}
