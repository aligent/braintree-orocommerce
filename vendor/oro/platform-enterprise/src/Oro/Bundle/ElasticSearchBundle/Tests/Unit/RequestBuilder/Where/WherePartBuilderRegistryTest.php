<?php

namespace Oro\Bundle\ElasticSearchBundle\Tests\Unit\RequestBuilder\Where;

use Oro\Bundle\ElasticSearchBundle\RequestBuilder\Where\WherePartBuilderRegistry;
use Oro\Bundle\ElasticSearchBundle\RequestBuilder\Where\WherePartBuilderInterface;

class WherePartBuilderRegistryTest extends \PHPUnit_Framework_TestCase
{
    /** @var WherePartBuilderRegistry */
    protected $registry;

    protected function setUp()
    {
        $this->registry = new WherePartBuilderRegistry();
    }

    public function testGetRequestBuilder()
    {
        $paymentMethods = $this->registry->getPartBuilders();
        $this->assertInternalType('array', $paymentMethods);
        $this->assertEmpty($paymentMethods);
    }

    public function testAddRequestBuilder()
    {
        $requestBuilder = $this->createMock(WherePartBuilderInterface::class);
        $this->registry->addWherePartBuilder($requestBuilder);
        $this->assertContains($requestBuilder, $this->registry->getPartBuilders());
    }
}
