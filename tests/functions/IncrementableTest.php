<?php

namespace malkusch\phpmock\functions;

/**
 * Tests Incrementable and all its implementations.
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @license http://www.wtfpl.net/txt/copying/ WTFPL
 * @see Incrementable
 */
class IncrementableTest extends \PHPUnit_Framework_TestCase
{
    
    /**
     * Tests increment().
     *
     * @param mixed $expected               The expected value.
     * @param mixed $increment              The amount of increase.
     * @param Incrementable $incrementable  The tested Incrementable.
     * @param callable $getValue            The lambda for getting the value.
     *
     * @test
     * @dataProvider provideTestIncrement
     */
    public function testIncrement(
        $expected,
        $increment,
        Incrementable $incrementable,
        callable $getValue
    ) {
        $incrementable->increment($increment);
        $this->assertEquals($expected, $getValue($incrementable));
    }
    
    /**
     * Test cases for testIncrement().
     *
     * @return array Test cases.
     */
    public function provideTestIncrement()
    {
        $getFixedValue = function (FixedValueFunction $function) {
            return $function->getValue();
        };
        $getMicrotime = function (FixedMicrotimeFunction $function) {
            return $function->getMicrotime(true);
        };
        return array(
            array(1, 1, new FixedValueFunction(0), $getFixedValue),
            array(2, 1, new FixedValueFunction(1), $getFixedValue),
            array(-1, -1, new FixedValueFunction(0), $getFixedValue),
            
            array(1, 1, new FixedMicrotimeFunction(0), $getMicrotime),
            array(-1, -1, new FixedMicrotimeFunction(0), $getMicrotime),
            array(2, 1, new FixedMicrotimeFunction(1), $getMicrotime),
            
            array(
                1.00000001,
                0.00000001,
                new FixedMicrotimeFunction(1),
                $getMicrotime
            ),
            array(
                1.00000009,
                0.00000009,
                new FixedMicrotimeFunction(1),
                $getMicrotime
            ),
        );
    }
}