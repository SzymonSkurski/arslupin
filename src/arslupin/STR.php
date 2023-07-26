<?php

declare(strict_types = 1);

namespace src\arslupin;


use src\arslupin\exception\BadTokenException;
use src\arslupin\exception\EmailFormatException;
use src\arslupin\exception\EmailMaxLengthException;
use src\arslupin\exception\PasswordLenException;

class STR
{
    public const SYSTEM_TOKEN_LEN = 12;
    public const EMAIL_MAX_LEN = 60;

    /**
     * print string in New Line
     * @param string $str
     */
    public static function printNL($str): void
    {
        print_r("\r\n{$str}");
    }

    public static function camelCase(string $str, $space = ' ', array $trim = [' ', '_', '-']): string
    {
        $strCamel = '';
        $strParts = explode($space, $str);
        foreach ($strParts as $key => $part) {
            $part = str_replace($trim, '', $part);
            $strCamel .= $key > 0 ? ucfirst($part) : $part;
        }
        return $strCamel;
    }

    public static function deCamelCase($camelCaseStr, $glue = ' ')
    {
        return preg_replace_callback('/[A-Z]/', function ($matches) use ($glue) {
            return $glue . strtolower($matches[0]);
        }, $camelCaseStr);
    }

    public static function snakeCase(string $str): string
    {
        return str_replace(' ', '_', trim($str));
    }

    public static function isStringContain(string $string, array $chars = []): bool
    {
        foreach ($chars as $char) {
            if (strpos($string, $char) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $email
     * @throws EmailFormatException
     */
    public static function checkEmailFormat(string $email): void
    {
        if (!self::isValidEmail($email)) {
            throw new EmailFormatException();
        }
    }

    public static function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * hash string for security reason use
     * @param string $string
     * @return string
     * @author SimonS
     */
    public static function hashString(string $string): string
    {
        return password_hash($string, PASSWORD_BCRYPT);
    }

    /**
     * @param array $array
     * @return string
     */
    public static function stringifyForGetRequest(array $array = []): string
    {
        $res = '';
        foreach ($array as $field => $value) {
            $res .= $field . '=' . $value . '&';
        }
        return rtrim($res, '&');
    }

    static function getArrayKeyFromStringNode(string $node): string
    {
        $key = strpos($node, ':') !== false ? explode(':', $node) : $node;
        return (is_array($key)) ? '[' . implode('][', $key) . ']' : '[' . $key . ']';
    }

    public static function withLeadingChars(string $string, int $length, string $char = ' '): string
    {
        $strLength = strlen($string);
        if ($strLength >= $length) {
            return $string;
        }
        $spaces = implode('', array_fill(0, $length - $strLength, $char));
        return $spaces . $string;
    }

    public static function getFileNameFromPath(string $path): string
    {
        $exSlash = explode('/', $path);
        $exBackSlash = explode('\\', $path);
        return count($exSlash) > count($exBackSlash) ? ARR::getLast($exSlash) : ARR::getLast($exBackSlash);
    }

    public static function getLastExplode(string $string, $del = ' '): string
    {
        if (empty($string)) {
            return '';
        }
        $parts = explode($del, $string);
        return $parts[count($parts) - 1] ?? '';
    }

    public static function makeGetterFromString(string $str): string
    {
        return 'get' . ucfirst($str);
    }

    /**
     * @param string $email
     * @param int|null $max
     * @return void
     * @throws EmailFormatException
     * @throws EmailMaxLengthException
     */
    public static function emailFormatChecker(string $email, ?int $max = null): void
    {
        if ($max === null) {
            $max = self::EMAIL_MAX_LEN;
        }
        self::checkEmailFormat($email);

        if (strlen($email) > $max) {
            throw new EmailMaxLengthException((string) $max);
        }
    }

    /**
     * @param int $length How many characters do we want?
     * @param string $keySpace A string of all possible characters to select from
     * @return string
     * @author Thinker
     * @function random_str         Generate a random string, using a cryptographically secure
     *                              pseudo random number generator (random_int)
     *                              For PHP 7, random_int is a PHP core function
     *                              For PHP 5.x, depends on https://github.com/paragonie/random_compat
     */
    public static function random_str(int $length, $keySpace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'): string
    {
        $str = '';
        $max = mb_strlen($keySpace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            try {
                $randKey = random_int(0, $max);
            } catch (\Exception $e) {
                $randKey = $i;
            }
            $str .= $keySpace[$randKey];
        }
        return $str;
    }

    public static function getSystemTokenLength(): int
    {
        return self::SYSTEM_TOKEN_LEN;
    }

    public static function generateSystemToken(): string
    {
        return self::random_str(self::getSystemTokenLength());
    }

    /**
     * @param string $token
     * @param string $sysToken
     * @param string $msg
     * @return void
     * @throws BadTokenException
     */
    public static function checkSystemToken(string $token, string $sysToken, string $msg = ''): void
    {
        self::checkSystemTokeLen($sysToken);
        if ($token === $sysToken) {
            return;
        }
        throw new BadTokenException();
    }

    /**
     * @param string $token
     * @throws BadTokenException
     */
    public static function checkSystemTokeLen(string $token): void
    {
        if (!self::isValidTokenLength($token)) {
            throw new BadTokenException();
        }
    }

    public static function isValidTokenLength(string $token): bool
    {
        return strlen($token) === self::getSystemTokenLength();
    }

    public static function replaceFirstOnly($needle, $replace, $str)
    {
        $pattern = '/' . preg_quote($needle, '/') . '/';

        return preg_replace($pattern, $replace, $str, 1);
    }

    public static function stringChainUnsetLast(string $chain, $glue = ':'): string
    {
        $parts = explode($glue, $chain);
        unset($parts[count($parts) - 1]);
        return implode($glue, $parts);
    }

    public static function toString($value): string
    {
        return (is_array($value) || is_object($value)) ? json_encode($value, 32) : (string) $value;
    }

    public static function endsWith(string $str, string $needle): bool
    {
//        var_dump($str, $needle);
//        return strlen($str) - strlen($needle) === strpos($str, $needle);

        if(!($length = strlen( $needle ))) {
            return true;
        }
        return substr( $str, -$length ) === $needle;
    }

    public static function startsWith(string $str, string $needle): bool
    {
        return strpos($str, $needle) === 0;
    }

    public static function cutRight(string $str, string $cut, $r = ''): string
    {
        $pattern = "/{$cut}$/";
        return preg_replace($pattern, $r, $str);
    }

    public static function getOccurrencePos(string $string, string $needle): array
    {
        $lastPos = 0;
        $positions = [];

        while (($lastPos = strpos($string, $needle, $lastPos))!== false) {
            $positions[] = $lastPos;
            $lastPos = $lastPos + strlen($needle);
        }
        return $positions;
    }

    public static function cutLastOccurrence(string $str, string $cut, $r = ''): string
    {
        return !empty($pos = self::getOccurrencePos($str, $cut))
            ? substr_replace($str, $r, $pos[array_key_last($pos)], strlen($cut))
            : $str;
    }

    public static function cutLeft(string $str, string $cut, $r = ''): string
    {
        $pattern = "/^{$cut}/";
        return preg_replace($pattern, $r, $str);
    }

    public static function cutFirstOccurrence(string $str, string $cut, $r = ''): string
    {
        return self::cutOccurrence($str, $cut, $r, 1);
    }

    public static function cutOccurrence(string $str, string $cut, $r = '', int $occurrence = 1): string
    {
        $occurrence = max(0, $occurrence - 1);
        if (empty($pos = self::getOccurrencePos($str, $cut)) || (($start = ($pos[$occurrence] ?? null)) === null)) {
            return $str;
        }
        return substr_replace($str, $r, (int) $start, strlen($cut));
    }

    public static function getLiteralBoolVal(string $str, bool $default = false): bool
    {
        if ($str === 'false') {
            return false;
        }
        if ($str === 'true') {
            return true;
        }
        return $default;
    }

    /**
     * @param string $password
     * @param int $max
     * @param $min
     * @return void
     * @throws PasswordLenException
     */
    public static function checkPasswordString(string $password, int $max, $min): void
    {
        $len = strlen($password);
        if ($len > $max) {
            throw new PasswordLenException('password max chars ' . $max);
        }
        if ($len < $min) {
            throw new PasswordLenException('password at least ' . $min . ' chars');
        }
    }

    public static function boolToString(bool $bool): string
    {
        return $bool ? 'true' : 'false';
    }

    public static function unQuote($val)
    {
        if (is_array($val)) {
            return json_decode(self::unQuote(json_encode($val, 32)), true, 512, 32);
        }
        if (is_object($val)) {
            return json_decode(self::unQuote(json_encode($val, 128)), false, 512, 128);
        }
        if (is_numeric($val) || is_bool($val)) {
            return $val;
        }
        return str_replace(["\'", "'"], "`", (string) $val);
    }

    public static function grave($val): string
    {
        return '`' . $val . '`';
    }

    public static function contain(string $string, string $needle): bool
    {
        return strpos($string, $needle) !== false;
    }

    public static function equalCaseless(string $str_1, string $str_2): bool
    {
        return strtolower($str_1) === strtolower($str_2);
    }

    public static function lastChar(string $str): string
    {
        $key = strlen($str) - 1;
        return (string) ($str[$key] ?? '');
    }

    public static function getWithoutPaths(string $trace): string
    {
        $pattern = '/\\\\{1,10}/m';
        $repl = '/';
        $trace = preg_replace($pattern, $repl, $trace);
        $pattern = '/(\D+:)?[A-z0-9]*+\//m';
        $repl = '';
        return preg_replace($pattern, $repl, $trace);
    }
    /**
     * @param int $number
     * @param int $length
     * @return string
     * @author SimonS
     */
    public static function withLeadingZeros(int $number, int $length): string
    {
        return NUM::withLeadingZeros($number, $length);
    }

    public static function dotsEmail(string $email, string $dot = '.'): string
    {
        if (!self::isValidEmail($email)) {
            return $email;
        }
        $l = strlen(explode('@', $email)[0] ?? '');
        for ($c = 1; $c < $l - 1; ++$c) {
            $email[$c] = $dot;
        }
        return $email;
    }
}