<?php

declare(strict_types=1);

namespace src\arslupin;

class HeapAlgorithm
{

    private array $B = [];
    private int $steps = 0; //generate only specified amount of steps
    private int $offset = 0; //start generate from specified step
    private int $c = 0;

    public static function getInstance($A): array {
        $instance = new HeapAlgorithm($A);
        return $instance->get();
    }

    /**
     * HeapAlgorithm constructor.
     * @param array $A
     * @param int $steps 0 - get all steps
     * @param int $offset 0 - no offset
     */
    public function __construct(array $A, int $steps = 0, int $offset = 0) {
        $n = count($A);
        $this->steps = $steps;
        $this->offset = $offset;
        self::Heap($n,$A);
    }

    private static function swap(&$x, &$y): void {
        [$x,$y] = [$y,$x];
    }

    private function Heap($n, &$A): array {
        if (($this->steps && $this->c >= $this->steps + max(0, $this->offset - 1))) {
            return $A;
        }
        if ($n === 1) {
            $this->c++; //count every full step
            if ($this->c < $this->offset) {
                return $A;
            }
            $this->B[] = $A;
            return $A;
        }
        if ($n <= 0) {
            $this->B[] = [];
            return $A;
        }
        for ($i = 0; $i < $n - 1; ++$i) {
            $this->Heap($n - 1, $A);
            ($n % 2) === 0
                ? $this->swap($A[$i],$A[$n - 1])
                : $this->swap($A[0],$A[$n - 1]);
        }
        $this->Heap($n - 1,$A);
        return $A;
    }

    public function get(): array {
        return $this->B;
    }

    public function getCount(): int {
        return $this->c;
    }
}