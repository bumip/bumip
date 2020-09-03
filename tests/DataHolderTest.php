<?php

use PHPUnit\Framework\TestCase;

final class DataHolderTest extends TestCase
{
    public function testCanBeIterated(): void
    {
        $data = new \Bumip\Core\DataHolder([1, 2, 3, 4]);
        $items = [];
        foreach ($data as $v) {
            $items[] = $v;
        }
        $this->assertCount(4, $items);
    }
}
