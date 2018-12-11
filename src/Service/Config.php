<?php

namespace Service\Config;

class ConfigService {
    private static $server_root;

    public function __construct() {
        self::$server_root = join(DIRECTORY_SEPARATOR,
            [
                $_SERVER['DOCUMENT_ROOT'],
                'scoreboard',
                'src',
                'Config',
            ]
        );
    }

    public static function get_conf_path() {
        return self::$server_root;
    }

    public static function get_db_config() {
        $path = join(DIRECTORY_SEPARATOR, [self::get_conf_path(), 'db.cnf']);

        $content = file_get_contents($path);

        return self::parse_config($content);
    }

    private static function parse_config($config_string) {
        $lines = explode(PHP_EOL, $config_string);
        $config = [];
        foreach ($lines as $line) {
            $line_parts = explode('=', $line);
            if ($line_parts[0]) {
                if (isset($line_parts[1])) {
                    $config[$line_parts[0]] = $line_parts[1];
                } else {
                    $config[$line_parts[0]] = '';
                }
            }
        }

        return $config;
    }
}
