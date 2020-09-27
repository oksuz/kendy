<?php

namespace Application\Util;

class TextUtil
{
    public static function trim(&$data)
    {
        return !empty($data) ? trim($data) : null;
    }

    public static function implodeArrayWithKeys(array $array)
    {
        $return = "";
        array_walk($array, function ($value, $key) use (&$return){
            $return .= (is_array($value)) ?
                sprintf("[%s:%s]", $key, TextUtil::implodeArrayWithKeys($value)) :
                sprintf("[%s:%s]", $key, $value);
        });

        return $return;
    }
}