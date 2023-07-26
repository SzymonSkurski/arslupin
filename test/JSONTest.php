<?php /** @noinspection PhpDeprecationInspection */

namespace test;

use PHPUnit\Framework\TestCase;
use src\arslupin\JSON;

class JSONTest extends TestCase
{
    public function testEncode(): void
    {
        $variable = null;
        $this->assertTrue(is_object(JSON::decodeObj($variable)));

        $variable = 'string';
        $this->assertTrue(is_object(JSON::decodeObj($variable)));

        $variable = 0; //int
        $this->assertTrue(is_object(JSON::decodeObj($variable)));

        $variable = [];
        $this->assertTrue(is_object(JSON::decodeObj($variable)));

        $variable = ['a' => 'a'];
        $obj = JSON::decodeObj($variable);
        $err = null;
        try {
            $this->assertSame('a', $obj->a);
        } catch (\Throwable $e) {
            $err = $e;
        }
        $this->assertNull($err);

        $variable = '{"a":"a"}';
        $obj = JSON::decodeObj($variable);
        $err = null;
        try {
            $this->assertSame('a', $obj->a);
        } catch (\Throwable $e) {
            $err = $e;
        }
        $this->assertNull($err);

        $variable = '["a","b"]';
        $this->assertTrue(is_object(JSON::decodeObj($variable)));
        $f = '0';
        $this->assertSame('a', JSON::decodeObj($variable)->$f);
    }

    public function testDecode(): void
    {
        $variable = null;
        $expected = [];
        $this->assertSame($expected, JSON::decodeAssoc($variable));

        $variable = 'string';
        $this->assertSame($expected, JSON::decodeAssoc($variable));

        $variable = 0; //int
        $this->assertSame($expected, JSON::decodeAssoc($variable));

        $variable = [];
        $expected = $variable;
        $this->assertSame($expected, JSON::decodeAssoc($variable));

        $variable = ['a' => 'a'];
        $expected = $variable;
        $this->assertSame($expected, JSON::decodeAssoc($variable));

        $variable = '{"a":"a"}';
        $this->assertSame($expected, JSON::decodeAssoc($variable));

        $variable = '["a","b"]';
        $expected = ['a', 'b'];
        $this->assertSame($expected, JSON::decodeAssoc($variable));
    }

    public function testIsJSONString(): void
    {
        $valid = [
            '{}',
            '[]',
            '[1,2,3]',
            '["a", "b", "c"]',
            '{"a":"b", "b":[1,2]}',
        ];
        foreach ($valid as $s) {
            $this->assertTrue(JSON::isJSONString($s));
        }
        $inValid = [
            '[1,2,3}',
            'abc',
            '{]',
            1,
            'a',
            [],
            json_decode('{}', false),
            '[a, b, c]',
        ];
        foreach ($inValid as $s) {
            $this->assertFalse(JSON::isJSONString($s));
        }
    }
}
