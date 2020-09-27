<?php

namespace Application\Util;

class HttpUtil
{
    const CURL_TIMEOUT_THIRTY_SEC = 30;
    const CURL_TIMEOUT_ONE_MIN = 60;
    const CURL_TIMEOUT_TWO_MIN = 120;

    protected static $DEFAULT_CURL_CONFIG = [
        \CURLOPT_RETURNTRANSFER => true,
        \CURLOPT_HEADER => true,
        \CURLOPT_CONNECTTIMEOUT => self::CURL_TIMEOUT_ONE_MIN,
        \CURLOPT_USERAGENT => "HttpClient",
        \CURLOPT_FOLLOWLOCATION => true,
    ];

    protected static $defaultCurlHandler = null;

    /**
     * @param string $url
     * @param array $config must containing CURLOPT_* => value pairs
     * @param array $post key value pairs
     * @param array $headers key value pairs
     * @return array when succeed, false when fail
     */
    public static function makeRequest(
        $url,
        array $config = [],
        array $post = [],
        array $headers = []
    ) {
        $ch = (null !== self::$defaultCurlHandler) ? self::$defaultCurlHandler : curl_init($url);

        if (!empty($config)) {
            curl_setopt_array($ch, $config);
        }

        $configKeyDiff = array_diff_key(self::$DEFAULT_CURL_CONFIG, $config);
        if (!empty($configKeyDiff)) {
            foreach ($configKeyDiff as $option => $value) {
                curl_setopt($ch, $option, $value);
            }
        }

        if (!empty($post)) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
        }

        if (!empty($headers)) {
            $curlStyleHeaders = [];
            foreach ($headers as $header => $value) {
                $curlStyleHeaders[] = sprintf("%s:%s", $header, $value);
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $curlStyleHeaders);
        }

        $exec = curl_exec($ch);
        $info = curl_getinfo($ch);

        if (false !== $exec) {
            $headers = substr($exec, 0, $info["header_size"]);
            $source = substr($exec, $info["header_size"]);
            return [
                "headers" => self::parseHeaders($headers),
                "source" => $source,
                "status" => $info["http_code"],
                "curl_handler" => $ch,
            ];
        }

        return false;
    }

    protected static function parseHeaders($rawHeaders)
    {
        // @FIXME: parse raw header data in here
        return $rawHeaders;
    }


    protected static function setCurlHandler($handler)
    {
        self::$defaultCurlHandler = $handler;
    }

    public static function reset()
    {
        self::$defaultCurlHandler = null;
    }

}