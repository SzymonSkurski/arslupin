<?php

namespace test;

use PHPUnit\Framework\TestCase;
use src\arslupin\STR;

class STRTest extends TestCase
{
    public function testGetArrayKeyFromStringNode() : void
    {
        $have = 'lvl1:lvl2';
        $expected = '[lvl1][lvl2]';
        $this->assertSame($expected, STR::getArrayKeyFromStringNode($have));

        $have = 'lvl1';
        $expected = '[lvl1]';
        $this->assertSame($expected, STR::getArrayKeyFromStringNode($have));
    }

    /**
     * @test
     */
    public function cutRight(): void
    {
        $str = '.php.test.php.php';
        $cut = '.php';
        $expected = '.php.test.php';
        $this->assertSame($expected, STR::cutRight($str, $cut));

        $str = '.php.test.php.php.';
        $expected = $str; //nothing changed
        $this->assertSame($expected, STR::cutRight($str, $cut));
    }

    /**
     * @test
     */
    public function cutLeft(): void
    {
        $str = '.php.test.php.php';
        $cut = '.php';
        $expected = '.test.php.php';
        $this->assertSame($expected, STR::cutLeft($str, $cut));

        $str = 'php.test.php.php';
        $expected = $str;
        $this->assertSame($expected, STR::cutLeft($str, $cut));
    }

    /**
     * @test
     */
    public function testGetOccurrencePos(): void
    {
        $str = 'this world in not my home world';
        $needle = 'world';
        $expect = [5, 26];
        $this->assertSame($expect, STR::getOccurrencePos($str, $needle));
    }

    public function testCutLastOccurrence(): void
    {
        $str = 'this world is not my home world!';
        $needle = 'world';
        $expect = 'this world is not my home !';
        $this->assertSame($expect, STR::cutLastOccurrence($str, $needle));

        $r = 'planet';
        $expect = 'this world is not my home planet!';
        $this->assertSame($expect, STR::cutLastOccurrence($str, $needle, $r));
    }

    public function testCutFirstOccurrence(): void
    {
        $str = 'this world is not my home world!';
        $needle = 'world ';
        $expect = 'this is not my home world!';
        $this->assertSame($expect, STR::cutFirstOccurrence($str, $needle));

        $needle = 'world';
        $r = 'planet';
        $expect = 'this planet is not my home world!';
        $this->assertSame($expect, STR::cutFirstOccurrence($str, $needle, $r));
    }

    /**
     * @test
     */
    public function testGetFileNameFromPath(): void
    {
        //linux slashes
        $path = '/ST/arslupin/php/vendor/phpunit/phpunit/phpunit.php';
        $expected = 'phpunit.php';
        $this->assertSame($expected, STR::getFileNameFromPath($path));
        //windows backslashes
        $path = '\\arslupin\\php\\vendor\\phpunit\\phpunit\\phpunit.php';
        $this->assertSame($expected, STR::getFileNameFromPath($path));
    }

    /**
     * @test
     */
    public function testEndsWith(): void
    {
        $str = 'some test string';
        $needle = 'string';
        $this->assertTrue(STR::endsWith($str, $needle));

        $needle = 'test';
        $this->assertFalse(STR::endsWith($str, $needle));

        $str = '{user:test, pass:testPass, params:[1,2,{val:test, code:3}]}';
        $needle = '}';
        $this->assertTrue(STR::endsWith($str, $needle));
    }

    public function tesStartsWith(): void
    {
        $str = 'some test string';
        $needle = 'some';
        $this->assertTrue(STR::startsWith($str, $needle));

        $needle = 'test';
        $this->assertFalse(STR::startsWith($str, $needle));
    }

    public function testDotsEmail(): void
    {
        $str = 'nuts';
        $expect = $str;
        $this->assertSame($expect, STR::dotsEmail($str));

        $str = 'validEmail@test.com';
        $expect = 'v........l@test.com';
        $this->assertSame($expect, STR::dotsEmail($str));

        $str = 'as@test.com';
        $expect = 'as@test.com';
        $this->assertSame($expect, STR::dotsEmail($str));
    }
}
