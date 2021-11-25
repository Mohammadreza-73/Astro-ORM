<?php

namespace App\Traits;

use App\Helpers\Config;

trait HasConfig
{
    public function getConfig(): array
    {
        return Config::get('database', 'pdo_testing');
    }
}