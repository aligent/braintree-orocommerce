<?php

namespace Oro\Bundle\MultiCurrencyBundle\Tests\Unit\Validator;

abstract class AbstractValidator extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Symfony\Component\Validator\Context\ExecutionContext
     */
    protected $context;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Symfony\Component\Validator\Violation\ConstraintViolationBuilder
     */
    protected $constraintViolation;

    protected function setUp()
    {
        $this->prepareConstraintViolationAndContext();
    }

    protected function prepareConstraintViolationAndContext()
    {
        $this->context = $this
            ->getMockBuilder('Symfony\Component\Validator\Context\ExecutionContext')
            ->disableOriginalConstructor()
            ->setMethods(['buildViolation'])
            ->getMock();

        $this->constraintViolation = $this
            ->getMockBuilder('Symfony\Component\Validator\Violation\ConstraintViolationBuilder')
            ->disableOriginalConstructor()
            ->setMethods(['setTranslationDomain', 'addViolation'])
            ->getMock();

        $this->constraintViolation
            ->expects($this->any())
            ->method('setTranslationDomain')
            ->with('messages')
            ->will($this->returnSelf());

        $this->constraintViolation
            ->expects($this->any())
            ->method('addViolation')
            ->withAnyParameters()
            ->will($this->returnSelf());
    }
}
