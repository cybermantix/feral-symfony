<?php


namespace Nodez\Inline\Utility\Filter\Comparator;


interface ScalarTestInterface
{
    /**
     * Run a test against a single scalar
     * @param $actual
     * @return bool
     */
    public function testScalar($actual): bool;
}
