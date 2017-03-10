<?php

namespace Oro\Bundle\ElasticSearchBundle\Tests\Unit\RequestBuilder;

use Oro\Bundle\ElasticSearchBundle\RequestBuilder\RequestBuilderInterface;
use Oro\Bundle\ElasticSearchBundle\RequestBuilder\RequestBuilderRegistry;

class RequestBuilderRegistryTest extends \PHPUnit_Framework_TestCase
{
    /** @var RequestBuilderRegistry */
    protected $registry;

    protected function setUp()
    {
        $this->registry = new RequestBuilderRegistry();
    }

    public function testGetRequestBuilder()
    {
        $paymentMethods = $this->registry->getRequestBuilders();
        $this->assertInternalType('array', $paymentMethods);
        $this->assertEmpty($paymentMethods);
    }

    public function testAddRequestBuilder()
    {
        $requestBuilder = $this->createMock(RequestBuilderInterface::class);
        $this->registry->addRequestBuilder($requestBuilder);
        $this->assertContains($requestBuilder, $this->registry->getRequestBuilders());
    }
}
