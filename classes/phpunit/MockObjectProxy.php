<?php

namespace malkusch\phpmock\phpunit;

use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Proxy for PHPUnit's PHPUnit_Framework_MockObject_MockObject.
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @license http://www.wtfpl.net/txt/copying/ WTFPL
 * @internal
 */
class MockObjectProxy implements MockObject
{
    
    /**
     * The delegation method name.
     */
    const METHOD = "delegate";

    /**
     * @var MockObject $mockObject The mock object.
     */
    private $mockObject;
    
    /**
     * Inject the subject.
     *
     * @param MockObject $mockObject   The subject.
     */
    public function __construct(MockObject $mockObject)
    {
        $this->mockObject = $mockObject;
    }
    
    /**
     * @SuppressWarnings(PHPMD)
     */
    // @codingStandardsIgnoreStart
    public function __phpunit_getInvocationMocker()
    {
        // @codingStandardsIgnoreEnd
        return $this->mockObject->__phpunit_getInvocationMocker();
    }

    /**
     * @SuppressWarnings(PHPMD)
     */
    // @codingStandardsIgnoreStart
    public function __phpunit_setOriginalObject($originalObject)
    {
        // @codingStandardsIgnoreEnd
        return $this->mockObject->__phpunit_setOriginalObject($originalObject);
    }

    /**
     * @SuppressWarnings(PHPMD)
     */
    // @codingStandardsIgnoreStart
    public function __phpunit_verify()
    {
        // @codingStandardsIgnoreEnd
        return $this->mockObject->__phpunit_verify();
    }

    public function expects(\PHPUnit_Framework_MockObject_Matcher_Invocation $matcher)
    {
        return $this->mockObject->expects($matcher)->method(self::METHOD);
    }
    
    /**
     * This method is not part of the contract but was found in
     * PHPUnit's mocked_class.tpl.dist.
     *
     * @SuppressWarnings(PHPMD)
     */
    // @codingStandardsIgnoreStart
    public function __phpunit_hasMatchers()
    {
        // @codingStandardsIgnoreEnd
        return $this->mockObject->__phpunit_hasMatchers();
    }
}
