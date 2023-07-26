<?php

declare(strict_types=1);

namespace src\arslupin;

use src\arslupin\exception\JsonException;

class JSON
{
    public const MAX_DEPTH = 10; //max depth of un JSON array
    /**
     * Does json_decode on any arrays' field which is proper JSON string
     * @param array $array multidimensional array
     * @param bool $assoc json_decode assoc, default:true
     * @param int $options json_decode option, default:32
     * @param int $depth current depth
     * @return array
     */
    public static function tableUnJSON(array &$array, bool $assoc = true, int $options = 32, int $depth = 0): array
    {
        foreach($array as $key => $val) {
            if ($depth === self::MAX_DEPTH) {
                continue;
            }
            if (is_array($val)) {
                $array[$key] = self::tableUnJSON($val, $assoc, $options, $depth + 1);
                continue;
            }
            if ($val === 'NULL' || $val === '{}') {
                $array[$key] = '{}';
                continue;
            }
            if (!JSON::isJSONString($val)) {
                $array[$key] = $val;
                continue;
            }
            $val = json_decode($val, $assoc, 512, $options);
            $array[$key] = self::tableUnJSON($val, $assoc, $depth + 1);
        }
        return $array;
    }

    /**
     * will return true if given param is valid JSON string
     * @param $str
     * @return bool
     */
    public static function isJSONString($str): bool
    {
        return (is_string($str) //have to be string
            && !is_numeric($str) //cannot be numeric
            && ((strpos($str, '{') === 0 && strpos($str, '}') !== false) //json object {}
                || (strpos($str, '[') === 0 && strpos($str, ']') !== false)) //json array []
            && is_array(json_decode($str, true)) //decodeAble
            && (json_last_error() === JSON_ERROR_NONE)); //decoded without errors
    }

    /**
     * @param $string
     * @throws JsonException
     */
    public static function checkIsJSONString($string): void
    {
        if (!self::isJSONString($string)) {
            throw new JsonException('invalid json');
        }
    }

    /**
     * @param string $string
     * @return array
     * @throws JsonException
     */
    public static function decodeJSON(string $string) : array
    {
        self::checkIsJSONString($string);
        $jsonArr = json_decode($string, true, 512, 32) ?? [];
        return self::tableUnJSON($jsonArr);
    }

    /**
     * will decode val into assoc array
     * not valid JSON, empty strings, integers etc. will output empty array []
     * ensures that whatever passes, it always returns array
     * @param $val
     * @param array $default
     * @return array
     */
    public static function decodeAssoc($val, array $default = []): array
    {
        if (is_numeric($val)) {
            return [];
        }
        if (is_object($val)) {
            $val = json_encode($val, 32);
        }
        $res = $val ?? [];
        return is_array($res) ? $res : json_decode((string) $res, true, 512, 32) ?? $default;
    }

    public static function decodeObj($val): object
    {
        if (is_object($val)) {
            return $val; //no reason
        }
        $empty = '{}';
        if (is_numeric($val)) {
            $val = $empty;
        }
        if (is_array($val)) {
            return Arr::forceObject($val);
        }
        if (is_string($val) && !JSON::isJSONString($val)) {
            $val = $empty;
        }
        $res = $val ?? $empty;
        $decoded = json_decode((string) $res, false, 512, 128 | 32);
        return is_array($decoded) ? ARR::forceObject($decoded) : $decoded;
    }
}