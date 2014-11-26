<?php

namespace malkusch\phpmock;

/**
 * Base functionality for all mocking classes.
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @license WTFPL
 */
abstract class AbstractMock
{
    
    /**
     * @var callback[] defined callbacks for the mocks.
     */
    private static $definedCallbacks = array();
    
    /**
     * @var string namespace for the mock function.
     */
    private $namespace;
    
    /**
     * @param string $namespace namespace for the mock function.
     */
    public function __construct($namespace)
    {
        $this->namespace = $namespace;
    }
    
    /**
     * Enables this mock.
     */
    public function enable()
    {
        $this->defineMockFunction();
        self::$definedCallbacks[$this->getCanonicalFunctionName()]
                = array($this, "mockFunction");
    }

    /**
     * Disable this mock.
     */
    public function disable()
    {
        unset(self::$definedCallbacks[$this->getCanonicalFunctionName()]);
    }
    
    /**
     * Returns the function name which will be mocked
     */
    abstract protected function getFunctionName();
    
    /**
     * Mocks the php function.
     */
    abstract public function mockFunction();
    
    /**
     * Returns the defined callback for a mocked function.
     * 
     * This method is called from the function mock.
     * 
     * @param string $canonicalFunctionName The canonical function name.
     * @return callback The call back.
     */
    public static function getCallback($canonicalFunctionName)
    {
        if (!isset(self::$definedCallbacks[$canonicalFunctionName])) {
            return null;
            
        }
        return self::$definedCallbacks[$canonicalFunctionName];
    }
    
    /**
     * Returns the function name with its namespace.
     * 
     * @return String The function name with its namespace.
     */
    private function getCanonicalFunctionName()
    {
        return "$this->namespace\\{$this->getFunctionName()}";
    }

    /**
     * Defines the mocked function in the given namespace.
     */
    private function defineMockFunction()
    {
        $canonicalFunctionName = $this->getCanonicalFunctionName();
        if (function_exists($canonicalFunctionName)) {
            return;
            
        }
        
        $definition = "
            namespace $this->namespace {
                
                function {$this->getFunctionName()}()
                {
                    \$callback = \malkusch\phpmock\AbstractMock::getCallback('$canonicalFunctionName');
                    if (empty(\$callback)) {
                        \$callback = '{$this->getFunctionName()}';
                        
                    }
                    return call_user_func_array(\$callback ,func_get_args());
                }
                
            }";
                
        eval($definition);
    }
}