<?php

namespace App\Traits;

use App\Helpers\Config;

trait HasConfig
{
    public function getConfigs(string $fileName, ?string $key = null): array
    {
        return Config::get($fileName, $key);
    }
}