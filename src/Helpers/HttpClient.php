<?php

namespace App\Helpers;

use GuzzleHttp\Client;
use App\Traits\HasConfig;

class HttpClient extends Client
{
    use HasConfig;
    
    private $httpClient;

    public function __construct()
    {
        $config = $this->getConfigs('app');

        parent::__construct($config);
    }
}