<?php

namespace malkusch\phpmock;

/**
 * Common tests for mocks.
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @license http://www.wtfpl.net/txt/copying/ WTFPL
 * @see Mock
 */
abstract class AbstractMockTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Disable all mocks.
     */
    abstract protected function disableMocks();
    
    /**
     * Builds an enabled function mock.
     *
     * @param string   $namespace    The namespace.
     * @param string   $functionName The function name.
     * @param callable $function     The function mock.
     */
    abstract protected function mockFunction($namespace, $functionName, callable $function);

    protected function tearDown()
    {
        parent::tearDown();

        $this->disableMocks();
    }

    /**
     * Tests mocking a function without parameters.
     *
     * @test
     */
    public function testMockFunctionWithoutParameters()
    {
        $this->mockFunction(__NAMESPACE__, "getmyuid", function () {
            return 1234;
        });
        $this->assertEquals(1234, getmyuid());
    }

    /**
     * Tests mocking a previously mocked function again.
     *
     * @test
     * @depends testMockFunctionWithoutParameters
     */
    public function testRedefine()
    {
        $this->mockFunction(__NAMESPACE__, "getmyuid", function () {
            return 5;
        });
        $this->assertEquals(5, getmyuid());
    }

    /**
     * Tests mocking a function without parameters.
     *
     * @test
     */
    public function testMockFunctionWithParameters()
    {
        $this->mockFunction(__NAMESPACE__, "rand", function ($min, $max) {
            return $max;
        });
        $this->assertEquals(1234, rand(1, 1234));
    }
    
    /**
     * Tests mocking of an undefined function.
     *
     * @test
     */
    public function testUndefinedFunction()
    {
        $this->assertFalse(function_exists("testUndefinedFunction"));
        $this->mockFunction(__NAMESPACE__, "testUndefinedFunction", function ($arg) {
            return $arg + 1;
        });
        $result = testUndefinedFunction(1);
        $this->assertEquals(2, $result);
    }
    
    /**
     * Tests failing enabling an already enabled mock.
     *
     * @expectedException malkusch\phpmock\MockEnabledException
     * @test
     */
    public function testFailEnable()
    {
        $name = "testFailEnable";
        $this->mockFunction(__NAMESPACE__, $name, "sqrt");
        $this->mockFunction(__NAMESPACE__, $name, "sqrt");
    }
    
    /**
     * Tests passing by value.
     *
     * @test
     */
    public function testPassingByValue()
    {
        $this->mockFunction(__NAMESPACE__, "testPassingByValue", function ($a) {
            return $a + 1;
        });
        
        // Tests passing directly the value.
        $this->assertEquals(3, testPassingByValue(2));
    }
    
    /**
     * Test passing by reference.
     *
     * @test
     */
    public function testPassingByReference()
    {
        $this->mockFunction(__NAMESPACE__, "exec", function ($a, &$b, &$c) {
            $a   = "notExpected";
            $b[] = "test1";
            $b[] = "test2";
            $c = "test";
        });

        $noReference = "expected";
        $b = [];
        $c = "";

        exec($noReference, $b, $c);
        $this->assertEquals(["test1", "test2"], $b);
        $this->assertEquals("test", $c);
        $this->assertEquals("test", $c);
        $this->assertEquals("expected", $noReference);
    }
    
    /**
     * Tests that the mock preserves the default argument
     *
     * @test
     */
    public function testPreserveArgumentDefaultValue()
    {
        $functionName = $this->buildPrivateFunctionName("testPreserveArgumentDefaultValue");
        
        eval("
            function $functionName(\$b = \"default\") {
                return \$b;
            }
        ");
        
        $this->mockFunction(
            __NAMESPACE__,
            $functionName,
            function ($arg = "expected") {
                return $arg;
            }
        );
        
        $fqfn   = __NAMESPACE__ . "\\$functionName";
        $result = $fqfn();
        $this->assertEquals("expected", $result);
    }
    
    /**
     * Tests that the disabled mock uses the default argument of the original function.
     *
     * @test
     * @depends testPreserveArgumentDefaultValue
     */
    public function testResetToDefaultArgumentOfOriginalFunction()
    {
        $functionName = $this->buildPrivateFunctionName("testPreserveArgumentDefaultValue");
        $result       = $functionName();
        $this->assertEquals("default", $result);
    }
    
    /**
     * Tests some methods which use the varname "...".
     *
     * @test
     */
    public function testCVariadic()
    {
        $this->mockFunction(__NAMESPACE__, "min", "max");
        
        $this->assertEquals(2, min(2, 1));
        $this->assertEquals(2, min([2, 1]));
    }

    /**
     * Tests some methods which use the varname "..." after a mock was defined.
     *
     * @test
     * @depends testCVariadic
     */
    public function testCVariadicReset()
    {
        $this->assertEquals(1, min(2, 1));
        $this->assertEquals(1, min([2, 1]));
    }
    
    /**
     * Setup a mock for testDisable().
     *
     * @test
     */
    public function testDisableSetup()
    {
        $this->mockFunction(__NAMESPACE__, "rand", function () {
            return 1234;
        });
        $this->mockFunction(__NAMESPACE__, "mt_rand", function () {
            return 1234;
        });
    }

    /**
     * Tests disable().
     *
     * @test
     * @depends testDisableSetup
     */
    public function testDisable()
    {
        $this->assertNotEquals(1234, rand());
        $this->assertNotEquals(1234, mt_rand());
    }

    /**
     * Tests defining the mock.
     *
     * @test
     */
    public function testDefine()
    {
        $functionName = $this->buildPrivateFunctionName("testDefine");
        $fqfn         = __NAMESPACE__."\\$functionName";
        $this->assertFalse(function_exists($fqfn));
        $this->mockFunction(__NAMESPACE__, $functionName, "sqrt");
        $this->assertTrue(function_exists($fqfn));
    }

    /**
     * Builds a function name which is has a postfix for the current class.
     *
     * @param string $name The function name.
     *
     * @return string The function name.
     */
    private function buildPrivateFunctionName($name)
    {
        return $name . str_replace("\\", "_", get_class($this));
    }
}
