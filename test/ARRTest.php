<?php

namespace test;

use Exception;
use PHPUnit\Framework\TestCase;
use src\arslupin\ARR;
use src\arslupin\HeapAlgorithm;

class ARRTest extends TestCase
{
    public function testJavascriptObjectToArray(): void
    {
        $obj = '{user:test, pass:testPass, params:[1,2,{val:test, code:3}], nestedObj:{nested_str_val:str, nested_int_val:777}}';
        $expected = '{"user":"test","pass":"testPass","params":["1","2",{"val":"test","code":"3"}],"nestedObj":{"nested_str_val":"str","nested_int_val":"777"}}';
        $res = ARR::javascriptJSONObjectToArray($obj);
        $this->assertNotEmpty($res);
        $this->assertSame(json_decode($expected, true, 512, 32), $res);
    }

    public function testIsJsObject(): void
    {
        $obj = '{user:test, pass:testPass}';
        $this->assertTrue(ARR::isJsObject($obj));
    }

    public function testIsJsArray(): void
    {
        $obj = '[user:test, pass:testPass]';
        $this->assertTrue(ARR::isJsArray($obj));
    }

    /**
     * @test
     */
    public function arrayGetValByChain(): void
    {
        $arr = ['lvl_1' => ['lvl_2' => ['lvl_3' => 'val']]];
        $expected = 'val';
        $val = ARR::getByChain($arr, 'lvl_1:lvl_2:lvl_3');
        $this->assertSame($expected, $val);
        $val = ARR::getByChain($arr, 'lvl_1:lvl_2:lvl_4');
        $expected = null;
        $this->assertSame($expected, $val);
    }

    /**
     * @test
     */
    public function testSetByChain(): void
    {
        $testCases = [
            // arr, chain, set, expected
            [[], 'lvl_1:lvl_2:lvl_3', 'val', ['lvl_1' => ['lvl_2' => ['lvl_3' => 'val']]]],
            [
                ['lvl_1' => [
                'lvl_1_1' => ['val_1'],
                'lvl_1_2' => ['val_2']
                ]],
                'lvl_1:lvl_1_2:lvl_1_2_1',
                'set_val',
                ['lvl_1' => [
                    'lvl_1_1' => ['val_1'],
                    'lvl_1_2' => [
                        'val_2',
                        'lvl_1_2_1' => 'set_val'
                    ],
                ]]
            ],
        ];
        foreach ($testCases as $case) {
            [$arr, $chain, $set, $exp] = $case;
            ARR::setByChain($arr, $chain, $set);
            $this->assertSame($set, ARR::getByChain($arr, $chain));
            $this->assertSame($exp, $arr);
        }
    }

    public function testGetArrayRandomValues(): void
    {
        $testCases = [
            // input, amount
            [['a', 'b', 'c'], 2, 2],
            [['a', 'b', 'c'], 4, 3], // cannot draw more than pool size
        ];
        foreach ($testCases as $case) {
            [$arr, $amount, $expAmount] = $case;
            $results = ARR::getArrayRandomValues($arr, $amount);
            $this->assertCount((int) $expAmount, $results);
            foreach ($results as $result) {
                $this->assertContains($result, $arr);
            }
        }
    }

    public function testCountRecurrent(): void
    {
        $arr = [0,0, [0,1]];
        $expected = 3; //occurrences of 0
        $this->assertSame($expected, ARR::countOccurrencesRecurrent($arr, 0));

        $expected = 2; //occurrences of key 0
        $this->assertSame($expected, ARR::countOccurrencesRecurrent($arr, 0, true));
    }

    /**
     * @test
     */
    public function testQuickSort(): void
    {
        $testCases = [
            // input arr, expected, asc
            [[7, 1, 3, 4, 5, 6, 2], [1, 2, 3, 4, 5, 6, 7], true],
            [[7, 1, 3, 4, 5, 6, 2], [7, 6, 5, 4, 3, 2, 1], false],
            [['c', 'a', 'e', 'b', 'f', 'd'], ['a', 'b', 'c', 'd', 'e', 'f'], true],
            [['c', 'a', 'e', 'b', 'f', 'd'], ['f', 'e', 'd', 'c', 'b', 'a'], false],
        ];

        foreach ($testCases as $case) {
            [$arr, $exp, $asc] = $case;
            $this->assertSame($exp, ARR::quickSort($arr, $asc));
        }
    }

    public function testGetLastKey(): void
    {
        $this->assertSame(null, ARR::getLastKey([]));
        $this->assertSame(3, ARR::getLastKey(['a', 'b', 'c', 'd']));
        $this->assertSame('d', ARR::getLastKey(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4]));
    }

    public function testGetFirstKey(): void
    {
        $this->assertSame(null, ARR::getFirstKey([]));
        $this->assertSame(0, ARR::getFirstKey(['a', 'b', 'c', 'd']));
        $this->assertSame('a', ARR::getFirstKey(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4]));
    }

    /**
     * @test
     */
    public function testSortBySubKey(): void
    {
        $a = [
            [
                'id' => 4767,
                'first_name' => 'Peter',
                'last_name' => 'Griffin',
            ],
            [
                'id' => 5698,
                'first_name' => 'Ben',
                'last_name' => 'Smith',
            ],
            [
                'id' => 3809,
                'first_name' => 'Joe',
                'last_name' => 'Doe',
            ]
        ];
        $sorted = ARR::sortBySubKey($a, 'first_name'); //sort desc
        $first_names = array_column($sorted, 'first_name');
        $this->assertSame(['Peter', 'Joe', 'Ben'], $first_names);

        $sorted = ARR::sortBySubKey($a, 'first_name', SORT_ASC); //sort asc
        $first_names = array_column($sorted, 'first_name');
        $this->assertSame(['Ben', 'Joe', 'Peter'], $first_names);
    }

    /**
     * @test
     */
    public function testSearchKeyDeep(): void
    {
        $arr = [
            'key_1' => [
                'key_1_1' => 'val_1_1',
                'key_1_2' => 'val_1_2',
            ],
            'key_2' => [
                'key_2_1' => 'val_2_1',
                'key_2_2' => [
                    'key_2_2_1' => 'val_2_2_1',
                    'key_2_2_2' => 'val_2_2_2',
                ],
            ],
        ];

        $exc = null;
        try {
            $this->assertEmpty(ARR::searchKeyDeep($arr, 'nuts'));
        } catch (Exception $e) {
            $exc = $e;
        }
        $this->assertNull($exc);
        $expected = [
            'chain' => 'key_2:key_2_2:key_2_2_2',
            'value' => 'val_2_2_2',
        ];

        try {
            $this->assertSame($expected, ARR::searchKeyDeep($arr, 'key_2_2_2'));
        } catch (Exception $e) {
            $exc = $e;
        }
        $this->assertNull($exc);
    }

    public function testUnsetByChain(): void
    {
        $arr = [];
        $arr['l1']['l2']['l3'] = 'val';
        $arr_c = $arr;
        $c = 'l1:l2:l3';
        ARR::unsetByChain($arr, $c);
        unset($arr_c['l1']['l2']['l3']);
        $this->assertSame($arr_c, $arr);
    }

    public function testMergeDeep(): void
    {
        $arr1 = [
            'l1' => [
                'l2_1' => ['v1'],
                'l2_3' => 'v4',
                'l2_4' => ['v6', 'v7'],
                'l2_5' => [
                    'l3_1' => 'a',
                    'l3_2' => null,
                ],
            ],
            'l1_2' => null,
        ];
        $arr2 = [
            'l1' => [
                'l2_1' => ['v2'],
                'l2_2' => 'v3',
                'l2_3' => 'v5',
                'l2_5' => ['l3_1' => 'b'],
            ],
            'l1_1' => null,
        ];
        $expected = '{"l1_1":null,"l1":{"l2_2":"v3","l2_1":["v1","v2"],"l2_3":"v5","l2_4":["v6","v7"],"l2_5":{"l3_1":"b","l3_2":null}},"l1_2":null}';
        $res = ARR::mergeDeep($arr1, $arr2);
//        print_r(json_encode($res, 128));
        $this->assertSame($expected, json_encode($res));
    }

    public function testPermutations(): void
    {
        $arr = [1,2,3,4];
        $expected = [
            [1,2,3,4], //1
            [2,1,3,4], //2
            [3,1,2,4], //3
            [1,3,2,4], //4
            [2,3,1,4], //5
            [3,2,1,4],
            [4,2,1,3],
            [2,4,1,3],
            [1,4,2,3],
            [4,1,2,3], //10
            [2,1,4,3],
            [1,2,4,3],
            [1,3,4,2],
            [3,1,4,2],
            [4,1,3,2],
            [1,4,3,2],
            [3,4,1,2],
            [4,3,1,2],
            [4,3,2,1],
            [3,4,2,1], //20
            [2,4,3,1],
            [4,2,3,1],
            [3,2,4,1],
            [2,3,4,1], //24
        ];
        $heap = new HeapAlgorithm($arr);
        $permutations = $heap->get();
        $this->assertTrue(array_unique($permutations, SORT_REGULAR) === $permutations);
        $this->assertSame($expected, $heap->get());
        //get only specified steps
        $heap = new HeapAlgorithm($arr, 3);
        $expected = [[1,2,3,4],[2,1,3,4],[3,1,2,4]];
        $this->assertSame($expected, $heap->get());

        $expected = [
            [2,3,1,4],
            [3,2,1,4],
            [4,2,1,3],
        ];
        $heap = new HeapAlgorithm($arr, 3, 5);
        $this->assertSame($expected, $heap->get());

        $heap = new HeapAlgorithm($arr, 1, 20);
        $expected = [[3,4,2,1]];
        $this->assertSame($expected, $heap->get());

        $heap = new HeapAlgorithm($arr, 0, 20);
        $expected = [
            [3,4,2,1], //20
            [2,4,3,1],
            [4,2,3,1],
            [3,2,4,1],
            [2,3,4,1],
        ];
        $this->assertSame($expected, $heap->get());
    }
}
