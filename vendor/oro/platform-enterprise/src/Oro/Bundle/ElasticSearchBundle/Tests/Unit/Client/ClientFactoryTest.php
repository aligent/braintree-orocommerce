<?php

namespace Oro\Bundle\ElasticSearchBundle\Tests\Unit\Client;

use Elasticsearch\ClientBuilder;

use Oro\Bundle\ElasticSearchBundle\Client\ClientFactory;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class ClientFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ClientFactory
     */
    private $factory;

    /**
     * @var ClientBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $builder;

    /**
     * @var PropertyAccessor
     */
    private $accessor;

    public function setUp()
    {
        $this->builder = $this->createMock('Elasticsearch\ClientBuilder');
        $this->accessor = $this->createMock('Symfony\Component\PropertyAccess\PropertyAccessor');
        $this->factory = new ClientFactory($this->builder, $this->accessor);
    }

    public function tearDown()
    {
        unset($this->factory, $this->builder, $this->accessor);
    }

    public function testCreate()
    {
        $configuration = [
            'hosts' => ['1.2.3.4:5678'],
        ];

        $this->accessorNeverExpectedToBeCalled();

        $this->builder
            ->expects($this->once())
            ->method('setHosts')
            ->with(['1.2.3.4:5678']);

        $client = $this->getMockBuilder('Elasticsearch\Client')
            ->disableOriginalConstructor()
            ->getMock();

        $this->builder
            ->expects($this->once())
            ->method('build')
            ->willReturn($client);

        $this->assertEquals($client, $this->factory->create($configuration));
    }
    
    protected function accessorNeverExpectedToBeCalled()
    {
        $this->accessor
            ->expects($this->never())
            ->method('isWritable');

        $this->accessor
            ->expects($this->never())
            ->method('setValue');
    }

    public function testCreateWithAllOptions()
    {
        $configuration = [
            'hosts' => ['1.2.3.4:5678'],
            'sslVerification' => '/path/to/cacert.pem',
            'sslCert' => ['/path/to/cert', 'certPassword'],
            'sslKey' => ['/path/to/key', 'keyPassword'],
        ];

        $this->accessorNeverExpectedToBeCalled();

        $this->builder
            ->expects($this->once())
            ->method('setHosts')
            ->with(['1.2.3.4:5678']);

        $this->builder
            ->expects($this->once())
            ->method('setSSLVerification')
            ->with('/path/to/cacert.pem');

        $this->builder
            ->expects($this->once())
            ->method('setSSLCert')
            ->with('/path/to/cert', 'certPassword');

        $this->builder
            ->expects($this->once())
            ->method('setSSLKey')
            ->with('/path/to/key', 'keyPassword');

        $client = $this->getMockBuilder('Elasticsearch\Client')
            ->disableOriginalConstructor()
            ->getMock();

        $this->builder
            ->expects($this->once())
            ->method('build')
            ->willReturn($client);

        $this->assertEquals($client, $this->factory->create($configuration));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Hosts configuration option is required
     */
    public function testCreateWhenHostsOptionIsAbsent()
    {
        $configuration = [
        ];

        $this->accessorNeverExpectedToBeCalled();

        $this->builder
            ->expects($this->never())
            ->method('build');

        $this->factory->create($configuration);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Option sslKey has to be array of two elements
     */
    public function testCreateWhenInvalidSslKeyIsGiven()
    {
        $configuration = [
            'hosts' => ['127.0.0.1'],
            'sslKey' => 'somevalue'
        ];

        $this->accessorNeverExpectedToBeCalled();

        $this->builder
            ->expects($this->never())
            ->method('build');

        $this->factory->create($configuration);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Option sslCert has to be array of two elements
     */
    public function testCreateWhenInvalidSslCertIsGiven()
    {
        $configuration = [
            'hosts' => ['127.0.0.1'],
            'sslCert' => 'somevalue'
        ];

        $this->accessorNeverExpectedToBeCalled();

        $this->builder
            ->expects($this->never())
            ->method('build');

        $this->factory->create($configuration);
    }

    public function testCreateWithExistingOption()
    {
        $configuration = [
            'hosts' => ['127.0.0.1'],
            'someExistingOption' => 'someExistingOptionValue'
        ];

        $this->accessor
            ->expects($this->once())
            ->method('isWritable')
            ->with($this->builder, 'someExistingOption')
            ->willReturn(true);

        $this->accessor
            ->expects($this->once())
            ->method('setValue')
            ->with($this->builder, 'someExistingOption', 'someExistingOptionValue');

        $this->builder
            ->expects($this->once())
            ->method('build');

        $this->factory->create($configuration);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unsupported option badOption with value badOptionValue
     */
    public function testCreateWithNotExistingOption()
    {
        $configuration = [
            'hosts' => ['127.0.0.1'],
            'badOption' => 'badOptionValue'
        ];

        $this->accessor
            ->expects($this->once())
            ->method('isWritable')
            ->with($this->builder, 'badOption')
            ->willReturn(false);

        $this->accessor
            ->expects($this->never())
            ->method('setValue');

        $this->builder
            ->expects($this->never())
            ->method('build');

        $this->factory->create($configuration);
    }
}
