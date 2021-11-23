<?php

namespace App\Helpers;

use App\Exceptions\ConfigFileNotFoundException;

class Config
{
    /**
     * Get content of config file
     *
     * @param  string $fileName
     * @throws ConfigFileNotFoundException
     * @return array
     */
    public static function getContentsFile(string $fileName): array
    {
        $filePath = realpath(__DIR__ . '/../configs/' . $fileName . '.php');
        
        if (! $filePath) {
            throw new ConfigFileNotFoundException(
                sprintf('Config File ["%s"] does not exist.', $fileName)
            );
        }

        return require $filePath;
    }

    /**
     * Get content of config file with key
     *
     * @param  string      $fileName
     * @param  string|null $key
     * @throws ConfigFileNotFoundException
     * @return string|array|null Return null when key does not exists in config file
     */
    public static function get(string $fileName, string $key = null): string|array|null
    {
        $config = self::getContentsFile($fileName);

        if (is_null($key)) return $config;

        return $config[$key] ?? null;
    }
}