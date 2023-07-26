<?php

declare(strict_types=1);

namespace src\arslupin;


class NUM
{
    public static function SqrtN($number, int $n = 2): float
    {
        if (!is_numeric($number)) {
            return 0.0;
        }
        return (float) ($number ** (1 / $n));
    }

    public static function isOdd($value): bool
    {
        return is_numeric($value) ? ($value % 2 !== 0)
            : false;
    }

    public static function isEven($value): bool
    {
        return is_numeric($value) ? ($value % 2 !== 0)
            : false;
    }

    public static function percent($value, $percent)
    {
        return is_numeric($value) ? $value * (max(1, (int) $percent) / 100) : 0;
    }

    public static function percentage(int $value, $total, bool $ceil = true): float
    {
        if (!is_numeric($value) || !is_numeric($total) || !$total) {
            return 0.0;
        }
        $percentage = $value / $total * 100;
        return $ceil ? ceil($percentage) : $percentage;
    }

    public static function matchErrMargin($value, $demand, int $errMargin = 5): bool
    {
        return (!is_numeric($value) || !is_numeric($demand)) ? false
            : in_array($value, range($demand - $errMargin, $demand + $errMargin));
    }

    public static function convertMSToS(float $ms): float
    {
        return (float) ($ms * (10 ** -6));
    }

    public static function withLeadingZeros(int $number, int $length, string $zero = '0'): string
    {
        $stringed = (string) $number;
        $strLength = strlen($stringed);
        if ($strLength >= $length) {
            return (string) $number;
        }
        $zeros = implode('', array_fill(0, $length - $strLength, $zero));
        return $zeros . $stringed;
    }

    public static function convertToWord(int $num): string
    {
        $num = (int) $num;
        $words = array();
        $list1 = array('', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven',
            'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen'
        );
        $list2 = array('', 'ten', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety', 'hundred');
        $list3 = array('', 'thousand', 'million', 'billion', 'trillion', 'quadrillion', 'quintillion', 'sextillion', 'septillion',
            'octillion', 'nonillion', 'decillion', 'undecillion', 'duodecillion', 'tredecillion', 'quattuordecillion',
            'quindecillion', 'sexdecillion', 'septendecillion', 'octodecillion', 'novemdecillion', 'vigintillion'
        );
        $num_length = strlen((string) $num);
        $levels = (int) (($num_length + 2) / 3);
        $max_length = $levels * 3;
        $num = substr('00' . $num, -$max_length);
        $num_levels = str_split($num, 3);
        for ($i = 0; $i < count($num_levels); ++$i) {
            $levels--;
            $hundreds = (int) ($num_levels[$i] / 100);
            $hundreds = ($hundreds ? ' ' . $list1[$hundreds] . ' hundred' . ' ' : '');
            $tens = (int) ($num_levels[$i] % 100);
            $singles = '';
            if ( $tens < 20 ) {
                $tens = ($tens ? ' ' . $list1[$tens] . ' ' : '' );
            } else {
                $tens = (int)($tens / 10);
                $tens = ' ' . $list2[$tens] . ' ';
                $singles = (int) ($num_levels[$i] % 10);
                $singles = ' ' . $list1[$singles] . ' ';
            }
            $words[] = $hundreds . $tens . $singles . ( ( $levels && ( int ) ( $num_levels[$i] ) ) ? ' ' . $list3[$levels] . ' ' : '' );
        } //end for loop
        return implode(' ', $words);
    }

    public static function convertToOrdinals(int $num): string
    {
        $exceptions = [
            1 => 'first',
            2 => 'second',
            3 => 'third'
        ];
        if (($ordinal = $exceptions[$num] ?? '')) {
            return $ordinal;
        }
        $literal = trim(self::convertToWord($num));
        return $num > 10 && $num % 10 === 0 ? STR::cutRight($literal, 'y', 'ie') . 'th'
            : STR::cutRight($literal, 'e', '') . 'th';
    }

    public static function fitRangeInt(int $num, int $min = 0, int $max = 255): int
    {
        $rMax = max($min, $max); //relative max
        $rMin = min($min, $max); //relative min
        return max($rMin, min($rMax, $num)); //keep range 0, 255
    }

    public static function fitRangeFloat(float $num, float $min = 0, float $max = 255): float
    {
        $rMax = max($min, $max); //relative max
        $rMin = min($min, $max); //relative min
        return (float) max($rMin, min($rMax, $num)); //keep range 0, 255
    }

    public static function factorial(float $n): float
    {
        $n = max(0, $n);
        if ($n === 0) {
            return (float) 1;
        }
        return $n * self::factorial($n - 1);
    }

    public static function getRomanNumeral(int $number) : string
    {
        $romanNumerals =
            [
                'M' => 1000,
                'CM' => 900,
                'D' => 500,
                'CD' => 400,
                'C' => 100,
                'XC' => 90,
                'L' => 50,
                'XL' => 40,
                'X' => 10,
                'IX' => 9,
                'V' => 5,
                'IV' => 4,
                'I' => 1
            ];
        $romanValue = '';
        while ($number > 0) {
            foreach ($romanNumerals as $roman => $arabic) {
                if ($number >= $arabic) {
                    $number -= $arabic;
                    $romanValue .= $roman;
                    break;
                }
            }
        }
        return $romanValue;
    }

    /**
     * 10 will return 10
     * 11 | 12 | 13 will return 10
     * 14 | 15 | 16 will return 15
     * 17 | 18 | 19 will return 20
     * 20 will return 20
     * 99 will return 100
     * @param int $num
     * @return int
     */
    public static function roundToFive(int $num): int
    {
        if (!$num) {
            return $num; //no bother
        }
        $strN = (string) $num;
        if (($lastN = (int) $strN[($lastKey = strlen($strN) - 1)]) === 5 || $lastN === 0) {
            return $num;
        }
        switch ($lastN) {
            case in_array($lastN, [1,2,3], true) : $strN[$lastKey] = 0; break;
            case in_array($lastN, [4,5,6], true) : $strN[$lastKey] = 5; break;
            case in_array($lastN, [7,8,9], true) : $num += 10; $strN = (string) $num; $lastKey = strlen($strN) - 1; $strN[$lastKey] = 0; break;
        }
        return (int) $strN;
    }

    public static function approximatelyInt(int $num, int $exp, $r = 1): bool
    {
        return $num === $exp || ($num >= $exp - $r && $num <= $exp + $r);
    }

    public static function getFibonacciStepByValue(int $value): int
    {
        if (!$value || $value === 1) {
            return $value;
        }
        $max = $value ** 2;
        for ($n = 1; $n <= $max; $n ++) {
//            print_r("\r\n" . $n .':' . self::fibonacci($n));
            if (self::fibonacci($n) >= $value) {
                return $n;
            }
        }
        return $value;
    }

    public static function fibonacci(int $n): int
    {
        return $n < 1 ? 0
            : ($n <= 2 ? 1
                : self::fibonacci($n - 1) + self::fibonacci($n - 2));
    }

    /**
     * @param $n
     * @param int $round
     * @param bool $progressive
     * @return int
     * @author SimonS
     */
    public static function roundUpToAnyInt(int $n, $round = 5, $progressive = false) : int
    {
        $nLen = (int) strlen((string) $n);
        if ($progressive) {
            $round = max(5, 10 ** ($nLen - 1));
        }
        return (int) round(($n + $round / 2) / $round) * $round;
    }

    public static function getUniformlyIncreasingNumbersString(int $steps = 20): array
    {
        $res[] = 0;
        for ($n = 1; $n <= $steps; $n ++) {
            $c = 0;
            while ($c < $n) {
                $res[] = $n;
                $c++;
            }
        }
        return $res;
    }

    public static function getFibonacciStepValue(int $step = 0): int
    {
        $step = max(0, $step);
        return (int) (self::getFibonacciArray($step)[$step] ?? 0);
    }

    public static function getFibonacciArray(int $steps = 100): array
    {
        $res[0] = 0;
        for ($n = 1; $n <= $steps; $n ++) {
            $res[$n] = ($res[$n - 2] ?? 1) + ($res[$n - 1] ?? 1);
        }
        return $res;
    }

    /**
     * @param int $number
     * @return int
     * @author SimonS
     */
    public static function getCeilRounded(int $number) : int
    {
        $length = strlen((string) $number);
        if ($length < 1) {
            return $number;
        }
        $slotsToZero = (int) floor($length / 2);
        $roundedNumber = (int) round($number, - $slotsToZero, 1);
        $numberBase = substr((string) $roundedNumber, 0, $length - $slotsToZero);
        $zerosQty = $length - strlen($numberBase);
        $zeros = array_fill(0, $zerosQty, 0);
        foreach ($zeros as $zero) {
            $numberBase .= $zero;
        }
        return (int) $numberBase;
    }

    public static function bigint(string $value, bool $unsigned = false) {
        $value = trim($value);
        $minus = null;
        if ($unsigned || ($minus = ($value[0] ?? '') === '-' ? '-' : null)) {
            $value = STR::cutFirstOccurrence($value, '-');
        }
        if (ctype_digit($value)) {
            return $minus . $value;
        }
        $value = preg_replace("/[^0-9](.*)$/", '', $value);
        if (ctype_digit($value)) {
            return $minus . $value;
        }
        return '0';
    }
}