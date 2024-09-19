<?php

use dokuwiki\Extension\Plugin;

class helper_plugin_structacl extends Plugin
{
    public const STRUCTACL_SEPCHAR = '.';
    /**
     * Convert config lines "schema.field name" into an array
     *
     * @param string $confValue
     * @return array
     */
    public function getConfiguration($confValue)
    {
        $lines = explode(PHP_EOL, $confValue);
        $config = [];

        foreach ($lines as $line) {
            // ignore comments, empty and invalid lines
            $line = preg_replace('/#.*$/', '', $line);
            $line = trim($line);
            if ($line === '' || strpos($line, self::STRUCTACL_SEPCHAR) === false) continue;

            [$schema, $field] = explode(self::STRUCTACL_SEPCHAR, $line, 2);
            $config[$schema] ??= [];
            $config[$schema][] = $field;
        }

        return $config;
    }
}
