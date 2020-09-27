<?php

namespace Application\Builder;

class ConfigBuilder
{
    const DIST_FILE_NAME = "Configuration.php.dist";
    const CONFIG_FILE_NAME = "Configuration.php";
    const CONFIG_DIRECTORY = "Config";

    protected static $DIST_FILE;
    protected static $CONFIG_FILE;

    public static function initialize()
    {
        self::$DIST_FILE = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . self::CONFIG_DIRECTORY . DIRECTORY_SEPARATOR . self::DIST_FILE_NAME;
        self::$CONFIG_FILE = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . self::CONFIG_DIRECTORY . DIRECTORY_SEPARATOR . self::CONFIG_FILE_NAME;
    }

    public static function buildConfig()
    {
        self::initialize();

        $preConf = self::readConfig();
        if (empty($preConf)) {
            throw new \Exception(sprintf("%s is not readable or its empty" , self::$DIST_FILE));
        }

        $lines = [];
        foreach ($preConf as $key => $value) {
            $lines[$key] = self::readLine($key, $value);
        }

        self::generateConfigurationFile($lines);
    }

    protected static function readConfig()
    {
        return parse_ini_file(self::$DIST_FILE);
    }

    protected static function readLine($envVariable, $defaultValue = null)
    {
        $line = (empty($defaultValue)) ?
            readline(sprintf("%s:", $envVariable)) :
            readline(sprintf("%s (%s):", $envVariable, $defaultValue));

        $line = trim($line);

        return empty($line) ? $defaultValue : $line;
    }

    protected static function generateConfigurationFile($values)
    {
        $code = self::line("<?php");
        $code .= self::line("namespace Application\\Config;");
        $code .= self::line();
        $code .= self::line("class Configuration");
        $code .= self::line("{");
        foreach ($values as $key => $value) {
            $code .= self::line(sprintf('const %s = "%s";', $key, $value), 1);
        }

        $basePath = dirname(__FILE__)
            . DIRECTORY_SEPARATOR
            . ".."
            . DIRECTORY_SEPARATOR;

        $code .= self::line(sprintf('const %s = "%s";', "APP_PATH", realpath($basePath)), 1);

        $code .= self::line("}");

        file_put_contents(self::$CONFIG_FILE, $code);

        chmod(self::$CONFIG_FILE, 0664);
    }

    private static function line($code = "", $tab = 0)
    {
        return str_repeat("\x20", $tab * 4) . $code . PHP_EOL;
    }
}