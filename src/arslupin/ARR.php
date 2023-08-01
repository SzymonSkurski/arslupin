<?php

declare(strict_types=1);

namespace src\arslupin;

use Exception;
use src\arslupin\exception\ArrayInvalidKeyException;
use src\arslupin\exception\ArrayMissingKeysException;
use function is_array;

class ARR
{
    public const DEFAULT_SEPARATOR = ':';

    public static function swapValues(array &$arr, $k0, $k1, bool $castInt = true): void
    {
        $val0 = $castInt ? (int) ($arr[$k0] ?? 0) : $arr[$k0] ?? null;
        $val1 = $castInt ? (int) ($arr[$k1] ?? 0) : $arr[$k1] ?? null;
        $arr[$k0] = $val1;
        $arr[$k1] = $val0;
    }

    public static function countOccurrencesRecurrent(array $arr, $needle, bool $keys = false): int
    {
        $c = 0;
        foreach ($arr as $key => $val) {
            if (is_array($val)) {
                $c += self::countOccurrencesRecurrent($val, $needle, $keys);
                continue;
            }
            if ($keys) { //search for match keys
                $c += (int) ($key == $needle);
            } else { //search for match values
                $c += (int) ($val == $needle);
            }
        }
        return $c;
    }
    /**
     * @param array $array
     * @param $key
     * @param int $sortType
     * @return array
     * @author SimonS
     */
    public static function sortBySubKey(array $array, $key, $sortType = SORT_DESC): array
    {
        $keys = array_column($array, $key);
        array_multisort($keys, $sortType, $array);
        return $array;
    }

    public static function sortByTwoSubKeys(array $array, $primaryKey, $secondaryKey, $sortPrimary = SORT_DESC, $sortSecondary = SORT_DESC) : array
    {
        $array1 = array_column($array, $primaryKey);
        array_multisort
        (
            $array1, $sortPrimary,
            array_column($array, $secondaryKey), $sortSecondary,
            $array
        );
        return $array;
    }

    /**
     * @param array $array
     * @param $find
     * @param $replace
     * @return array
     * @author SimonS
     */
    public static function replaceValue(array $array, $find, $replace): array
    {
        if (empty($array)) {
            return [];
        }
        foreach ($array as $key => $value) {
            if ($value !== $find) {
                continue;
            }
            $array[$key] = $replace;
        }
        return $array;
    }

    /**
     * @param array $array
     * @param $oldName
     * @param $newName
     * @return array
     * @author SimonS
     */
    public static function renameKey(array $array, $oldName, $newName): array
    {
        if (!isset($array[$oldName])) {
            return $array;
        }
        $array[$newName] = $array[$oldName];
        unset($array[$oldName]);
        return $array;
    }

    /**
     * @param array $arr1
     * @param array $arr2
     * @return array
     * @author SimonS
     */
    public static function mergeSum(array $arr1, array $arr2): array
    {
        $merged = array_merge_recursive($arr1, $arr2);
        $keys = array_keys($merged);
        foreach ($keys as $key) {
            if (!is_array($merged[$key])) {
                continue;
            }
            $merged[$key] = array_sum($merged[$key]);
        }
        return $merged;
    }

    /**
     * will return array contains keys from both array
     * if key occurred in both arrays values will be summarized
     * numeric values (1+1)
     * string values (str1.$del.str2)
     * array values will be recurrent mergeSum
     * [a:2,b:'abc',c:'x'] + [a:'1',b:'def'] will return [a:3,b:'abcdef',c:'x']
     * @param array $arr1
     * @param array $arr2
     * @param string $del char connecting (int the middle) strings from both arrays
     * @return array
     */
    public static function mergeSumValues(array $arr1, array $arr2, $del = ''): array
    {
        $res = [];
        $keys = self::getArraysKeysUnique($arr1, $arr2);
        foreach ($keys as $key) {
            $val1 = $arr1[$key] ?? 0;
            $val2 = $arr2[$key] ?? 0;
            if (is_array($val2) && is_array($val1)) {
                $res[$key] = self::mergeSumValues($val1, $val2);
                continue;
            }
            if (is_array($val1)) {
                $val1 = json_encode($val1);
            }
            if (is_array($val2)) {
                $val2 = json_encode($val2);
            }
            $res[$key] = (is_numeric($val1) && is_numeric($val2)) ? $val1 + $val2
                : (string) $val1 . $del . $val2;
        }
        return $res;
    }

    /**
     * get unique keys from both arrays
     * arr1 = [a:1, b:1]
     * arr2 = [b:2, c:2]
     * will return [a,b,c]
     * @param array $arr1
     * @param array $arr2
     * @return array
     */
    public static function getArraysKeysUnique(array $arr1, array $arr2): array
    {
        return array_unique(array_merge(array_keys($arr1), array_keys($arr2)));
    }

    public static function getFirstKeyMatchSubKeyAndValue(array $array, string $subKey, int $subValue): int
    {
        foreach ($array as $key => $value) {
            if ((int) ($value[$subKey] ?? -1) === $subValue) {
                return (int) $key;
            }
        }
        return -1;
    }

    public static function getFirst(array $arr)
    {
        return $arr[self::getFirstKey($arr)] ?? null;
    }

    public static function getFirstKey(array $arr)
    {
        if (empty($arr)) {
            return null;
        }
        reset($arr);
        return key($arr);
    }

    public static function getLastKey(array $arr) {
        return !empty($arr) ? key(array_slice($arr, -1, 1, true)) : null;
    }

    public static function getLast(array $arr) {
        return $arr[self::getLastKey($arr)] ?? null;
    }

    public static function getOddValues(array $array, bool $preserveKeys = true) : array
    {
        $odd = [];
        foreach ($array as $key => $value) {
            if ((int) $value % 2 !== 0) {
                $preserveKeys
                    ? $odd[$key] = $value
                    : $odd[] = $value;
            }
        }
        return $odd;
    }

    public static function getEvenValues(array $array, bool $preserveKeys = true) : array
    {
        $even = [];
        foreach ($array as $key => $value) {
            if ((int) $value % 2 === 0) {
                $preserveKeys
                    ? $even[$key] = $value
                    : $even[] = $key;
            }
        }
        return $even;
    }

    /**
     * merge recursively two associate arrays
     * @param $arr1
     * @param $arr2
     * @return array
     */
    public static function mergeAssoc($arr1, $arr2): array
    {
        $arrKeys = self::getArraysKeysUnique($arr1, $arr2);
        $res = [];
        foreach($arrKeys as $key) {
            $val1 = $arr1[$key] ?? null;
            $val2 = $arr2[$key] ?? null;
            if (is_array($val1) && is_array($val2)) {
                $res[$key] = self::mergeAssoc($val1, $val2);
                continue;
            }
            $res[$key] = $val2 !== null ? $val2 : $val1;
        }
        return $res;
    }

    /**
     * merge recursively two arrays
     * second array has priority
     * @param array $arr1
     * @param array $arr2
     * @param array $chain
     * @return array
     */
    public static function mergeDeep(array $arr1, array $arr2, array $chain = []): array
    {
        if ($arr1 === $arr2) {
            return $arr2;
        }
        if (self::isArrayPlain($arr1) && self::isArrayPlain($arr2)) {
            return array_merge($arr1, $arr2);
        }
        $res = [];
        //set keys from array 2 not existed in array 1
        foreach (array_diff(array_keys($arr2), array_keys($arr1)) as $key) {
            $c = $chain;
            $c[] = $key;
            $sc = join(self::DEFAULT_SEPARATOR, $c);
            self::setByChain($res, $sc, self::getByChain($arr2, $sc));
        }
        foreach ($arr1 as $key => $v1) {
            $c = $chain;
            $c[] = $key;
            $str_chain = join(self::DEFAULT_SEPARATOR, $c);
            if (!array_key_exists($key, $arr2) || ($v2 = $arr2[$key]) === $v1) {
                self::setByChain($res, $str_chain, $v1);
                continue;
            }
            if (is_array($v1) && is_array($v2)) {
                if (self::isArrayPlain($v1) && self::isArrayPlain($v2)) {
                    self::setByChain($res, $str_chain, array_merge($v1, $v2));
                    continue;
                }
                self::setByChain($res, $str_chain, self::mergeDeep($v1, $v2, $chain));
                continue;
            }
            self::setByChain($res, $str_chain, $v2); //arr 2 override arr 1
        }
        return $res;
    }

    public static function getSetVal($val1, $val2)
    {
        if ($val1 === $val2) {
            return $val1;
        }
        if (!$val1 && $val2) {
            return $val2;
        }
        if (!$val2 && $val1) {
            return $val1;
        }
        if ($val1 === null && $val2 !== null) {
            return $val2;
        }
        if ($val2 === null && $val1 !== null) {
            return $val1;
        }
        return null;
    }

    public static function toObject(array $arr)
    {
        $encoded = json_encode($arr, 16);
        return json_decode($encoded, false, 512, 16);
    }

    public static function javascriptJSONObjectToArray(string $js_object): array
    {
        $res = [];
        if (!self::isJsObject($js_object) && !self::isJsArray($js_object)) {
            return $res; //it is not json
        }
        $js_object = str_replace([', ', ' ,'], ',', $js_object); //get rid off spaces
        $pregAfter = function (string $char, string &$str) {
            $pattern = '/['.$char.']{1}[\d\w]/i';
            $valve = 0;
            $max = strlen($str);
            $matches = [];
            while (preg_match($pattern, $str, $matches, PREG_OFFSET_CAPTURE)) {
                if (empty($matches) || $valve > $max) {
                    break;
                }
                $str = substr_replace($str, str_replace($char, $char.'"', $matches[0][0]), $matches[0][1], 2);
                $valve++;
            }
        };
        $pregBefore = function (string $char, string &$str) {
            $pattern = '/[\d\w]['.$char.']{1}/i';
            $valve = 0;
            $max = strlen($str);
            $matches = [];
            while (preg_match($pattern, $str, $matches, PREG_OFFSET_CAPTURE)) {
                if (empty($matches) || $valve > $max) {
                    break;
                }
                $str = substr_replace($str, str_replace($char, '"'.$char, $matches[0][0]), $matches[0][1], 2);
                $valve++;
            }
        };

        //replace {\w into {"\w
        $pregAfter('{', $js_object);
        $pregAfter('[', $js_object);
        $pregBefore('}', $js_object);
        $pregBefore(']', $js_object);
        $pregBefore(',', $js_object);
        $pregAfter(',', $js_object);
        $pregBefore(':', $js_object);
        $pregAfter(':', $js_object);

        return JSON::decodeAssoc($js_object);
    }

    public static function isJsArray(string $js): bool
    {
        return STR::startsWith($js, '[') && STR::endsWith($js, ']');
    }

    public static function isJsObject(string $js): bool
    {
        return STR::startsWith($js, '{') && STR::endsWith($js, '}');
    }

    public static function toSimpleString(array $arr): string
    {
        return str_replace(['"', '{', '}'], '', json_encode($arr, 16));
    }

    /**
     * @param array $array
     * @param $needle
     * @return bool
     */
    public static function inArrayCaseInsensitive(array $array, $needle): bool
    {
        foreach ($array as $value) {
            if ($needle === $value) {
                return true;
            }
            if ((is_integer($needle) || is_bool($needle)) && $value === $needle) {
                return true;
            }
            if (is_string($needle) && is_string($value) && strtolower($needle) === strtolower($value)) {
                return true;
            }
        }
        return false;
    }

    public static function isContainString(array $array, string $needle): bool
    {
        foreach ($array as $value) {
            if (is_array($value)) {
                self::isContainString($value, $needle);
                continue;
            }
            if (strpos((string) $value, $needle) !== false) {
                return true;
            }
        }
        return false;
    }

    public static function getAssocArrayFirstFreeNumericKey(array $arr): int
    {
        if (empty($arr)) {
            return 1;
        }
        $keys = array_keys($arr);
        sort($keys);
        $prev = 1;
        $key = 0;
        foreach ($keys as $key) {
            if ((int) $key - 1 !== $prev) {
                return $key;
            }
            $prev = $key;
        }
        return $key + 1;
    }

    public static function getAssocArrayNextFreeNumericKey(array $arr): int
    {
        return self::getAssocArrayHighestNumericKey($arr) + 1;
    }

    public static function getAssocArrayHighestNumericKey(array $arr): int
    {
        $keys = array_keys($arr);
        sort($keys);
        $highest = 0;
        foreach ($keys as $key) {
            if (!is_numeric($key) || (int) $key < $highest) {
                continue;
            }
            $highest = $key;
        }
        return $highest;
    }

    /**
     * [0,1,2,3] = plain
     * [a,b,c] = plain
     * [0:0,a:1] != plain
     * [1:1, 2:2] != plain
     * @param array $arr
     * @return bool
     */
    public static function isArrayPlain(array $arr): bool
    {
        if (empty($arr)) {
            return true;
        }
        $keys = array_keys($arr);
        sort($keys);
        $count = count($keys);
        $firstKey = ($keys[0] ?? null);
        $lastKey = $keys[$count - 1] ?? null;
        return (is_numeric($firstKey)
            && (int) $firstKey === 0
            && is_numeric($lastKey)
            && (int) $lastKey === $count - 1
        );
    }

    /**
     * check if array starts from key 0 and has all keys in sequence
     * empty array is also sequential
     * @param array $arr
     * @return bool
     */
    public static function isSequential(array $arr): bool
    {
        if (empty($arr)) {
            return true;
        }
        if (!self::isArrayPlain($arr)) {
            return false;
        }
        $prev = -1;
        $keys = array_keys($arr);
        sort($keys);
        foreach (array_keys($arr) as $key) {
            if ($key !== $prev + 1) {
                return false; //is not sequential
            }
            $prev = $key;
        }
        return true;
    }

    /**
     * get random values form given array like draw from pool, each value could be drawn only once
     * for [1,2,3] with amount:3 result could be: [1,2,3] | [1,3,2] | [3,1,3] etc.
     * because results are draw from pool max amount = pool size
     * @param array $drawPool
     * @param $amount
     * @return array
     */
    public static function getArrayRandomValues(array $drawPool, $amount): array
    {
        if ($amount >= count($drawPool)) {
            return $drawPool; //no reason to rend return all u got
        }
        $draws = range(1, $amount);
        $drawn = [];
        /** @noinspection PhpUnusedLocalVariableInspection */
        foreach ($draws as $draw) {
            if (empty($drawPool)) {
                break;
            }
            $rand = array_rand($drawPool);
            $drawn[] = $drawPool[$rand];
            unset($drawPool[$rand]);
        }
        return $drawn;
    }

    public static function arrayToString(array $arr): string
    {
        $res = '';
        foreach ($arr as $key => $value) {
            $res .= "{$key}:{$value}, ";
        }
        $res = rtrim($res, ', ');
        return $res;
    }

    public static function getByChain(array &$arr, string $chain, string $s = self::DEFAULT_SEPARATOR) {
        $links = explode($s, $chain);
        $key = $links[0];
        if (count($links) === 1) {
            return $arr[$key] ?? null;
        }
        if (!array_key_exists($key, $arr) || !is_array($arr[$key])) {
            return null;
        }
        unset($links[0]);
        return self::getByChain($arr[$key], join($s, $links), $s);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public static function setByChain(array &$arr, string $chain, $set, string $s = self::DEFAULT_SEPARATOR)
    {
        $links = explode($s, $chain);
        $key = $links[0];
        if (count($links) === 1) {
            $arr[$key] = $set;
            return;
        }
        if (!array_key_exists($key, $arr) || !isset($arr[$key])) {
            $arr[$key] = [];
        }
        unset($links[0]);
        self::setByChain($arr[$key], join($s, $links), $set, $s);
    }

    /**
     * @param array $arr
     * @param string $chain
     * @param string $s
     * @return void
     */
    public static function unsetByChain(array &$arr, string $chain, string $s = self::DEFAULT_SEPARATOR): void
    {
        $links = explode($s, $chain);
        $key = $links[0];
        if (count($links) === 1) {
            unset($arr[$key]);

            return;
        }
        if (!array_key_exists($key, $arr) || !is_array($arr[$key])) {
            return;
        }
        unset($links[0]);
        self::unsetByChain($arr[$key], join($s, $links), $s);
    }

    public static function trimKeys(array $arr): array
    {
        $res = [];
        foreach ($arr as $k => $v) {
            $res[trim($k)] = $v;
        }
        return $res;
    }

    public static function unQuoteArr(array &$arr): void
    {
        foreach ($arr as $k => $v) {
            if (is_string($v)) {
                unset($arr[$k]);
                $arr[STR::unQuote(trim($k))] = STR::unQuote($v);
            }
        }
    }

    public static function trim(array $arr): array
    {
        $res = [];
        foreach ($arr as $k => $v) {
            $k = trim($k);
            if (is_array($v)) {
                $res[$k] = self::trim($v);
                continue;
            }
            $res[$k] = trim($v ?? '');
        }
        return $res;
    }

    public static function forceObject(array $arr): object
    {
        if (empty($arr)) {
            return json_decode('{}', false, 512, 128);
        }
        if (self::isArrayPlain($arr)) {
            $arr['_'] = null;
        }
        return json_decode(json_encode($arr, 128), false, 512, 128 | 32); //force array to object
    }

    /**
     * will return array without excluded keys
     * @param array $array
     * @param array $excludeKeys
     * @return array
     */
    public static function excludeKeys(array $array, array $excludeKeys): array
    {
        return array_diff_key($array, array_flip($excludeKeys));
    }

    /**
     * will return array contains only allowed keys
     * @param array $arr
     * @param array $allowed
     * @return array
     */
    public static function includeKeys(array $arr, array $allowed): array
    {
        return array_filter(
            $arr,
            function ($key) use ($allowed) {
                return in_array($key, $allowed);
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * @param array $demandKeys
     * @param array $arr
     * @param string $message
     * @throws ArrayMissingKeysException
     */
    public static function checkHasAllDemandKeys(array $demandKeys, array $arr, string $message = 'missing keys: '): void
    {
        $missing = [];
        foreach ($demandKeys as $demandKey) {
            if (!array_key_exists($demandKey, $arr)) {
                $missing[] = $demandKeys;
            }
        }
        if (!empty($missing)) {
            throw new ArrayMissingKeysException($message . implode(', ', $missing));
        }
    }

    /**
     * push all items, one by one, into array
     * @param array $arr
     * @param array $push
     * @return array
     */
    public static function pushAll(array $arr, array $push): array
    {
        foreach ($push as $item) {
            $arr[] = $item;
        }
        return $arr;
    }

    /**
     * @param $arr
     * @param bool $asc set false to sort DESC
     * @return array
     */
    public static function quickSort($arr, bool $asc = true)
    {
        if (count($arr) <= 1){
            return $arr; //empty or 1 element array no bother
        }

        $pivot = $arr[0];
        $smaller = [];
        $equal = [];
        $greater = [];

        //all values smaller than pivot value push into smaller array
        foreach ($arr as $x) {
            if (($asc ? $x < $pivot : $x >$pivot)) {
                $smaller[] = $x;
            }
        }

        //all values equal to pivot value push into equal array
        foreach ($arr as $x) {
            if ($x == $pivot) {
                $equal[] = $x;
            }
        }

        //all values greater than pivot value push into smaller array
        foreach ($arr as $x) {
            if (($asc ? $x > $pivot : $x < $pivot)) {
                $greater[] = $x;
            }
        }
        //merge smaller equal greater
        return array_merge(self::quickSort($smaller, $asc), $equal, self::quickSort($greater, $asc));
    }

    /**
     * @param array $arr
     * @return int
     * @throws ArrayInvalidKeyException
     */
    public static function maxKeyValueInt(array $arr): int
    {
        if (!self::isNumeric($arr, true)) {
            throw new ArrayInvalidKeyException('array keys has to be numeric');
        }
        if (empty($arr)) {
            return 0;
        }
        $keys = array_keys($arr);
        return (int) max($keys);
    }

    public static function maxKeyValueStr(array $arr): string
    {
        return (string) max(array_keys($arr));
    }

    /**
     * check is all array values or keys are numeric
     * @param array $arr
     * @param bool $keys true will check array keys
     * @return bool
     */
    public static function isNumeric(array $arr, bool $keys = false): bool
    {
        $check = $keys ? array_keys($arr) : $arr;
        foreach ($check as $value) {
            if (!is_numeric($value)) {
                return false; //at least one is not numeric
            }
        }
        return true;
    }

    public static function getSlice(array &$a, int $pieces, int $offset = 0, bool $cut = false): array
    {
        if (!$pieces || $offset > count($a)) {
            return [];
        }
        $slice = [];
        $c = 0;
        $p = 0;
        foreach ($a as $key => $value) {
            if ($p >= $pieces) {
                break;
            }
            if ($c > $offset) {
                $slice[$key] = $value;
                $p++;
                if ($cut) {
                    unset($a[$key]);
                }
            }
            $c++;
        }
        return $slice;
    }

    public static function bracketValues(array $params): string
    {
        $brackets = '(';
        foreach ($params as $param) {
            if ($param === null || (is_string($param) && strtolower($param) === 'null')) {
                $brackets .= 'NULL,';
                continue;
            }
            if (is_numeric($param)) {
                $brackets .= $param . ',';
                continue;
            }
            $param = (is_array($param) || is_object($param)) ?
                json_encode($param, 32)
                : (string) $param;
            $param = '\'' . str_replace('\'', '`', $param) . '\'';
            $brackets .= $param . ',';
        }
        $brackets = rtrim($brackets, ',');
        $brackets .= ')';
        return $brackets;
    }

    /**
     * search for match key in array to given depth
     * @param array $arr
     * @param string $needleKey
     * @param array $chain
     * @param int $depth
     * @return array [chain, value] or [] if not found
     * @throws Exception
     */
    public static function searchKeyDeep(array $arr, string $needleKey, array $chain = [], int $depth = 255): array {
        if (count($chain) >= $depth) {
            throw new Exception('max depth reached');
        }
        $cChain = $chain;
        foreach ($arr as $key => $val) {
            if ((string) $key === $needleKey) {
                $chain[] = $key;
                return [
                    'chain' => join(':', $chain),
                    'value' => $val
                ];
            }
            if (is_array($val))  {
                $chain[] = $key;
                if (!empty($res = self::searchKeyDeep($val, $needleKey, $chain))) {
                    return $res;
                }
            }
            $chain = $cChain; //reset chain
        }
        return []; //not found
    }

    public static function toChars(&$arr): void
    {
        array_walk_recursive($arr, fn($v) => chr((int) $v));
    }

    public static function forceArray($v): array
    {
        return is_array($v) ? $v : [];
    }
}
