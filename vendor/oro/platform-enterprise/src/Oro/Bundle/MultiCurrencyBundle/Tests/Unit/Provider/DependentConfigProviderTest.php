<?php

namespace Oro\Bundle\MultiCurrencyBundle\Tests\Unit\Provider;

use Oro\Bundle\MultiCurrencyBundle\Provider\ConfigDependencyInterface;
use Oro\Bundle\MultiCurrencyBundle\Provider\DependentConfigProvider;
use Oro\Bundle\MultiCurrencyBundle\Tests\Unit\Stub\StubConfigDependency;

class DependentConfigProviderTest extends \PHPUnit_Framework_TestCase
{
    /** @var DependentConfigProvider */
    private $provider;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->provider = new DependentConfigProvider();
    }

    public function testAddDependencySuccess()
    {
        $this->provider->addDependency(new StubConfigDependency([]));
    }

    public function testValidateDependencySuccess()
    {
        $this->provider->addDependency(new StubConfigDependency(['USD']));

        $this->assertTrue(
            $this->provider->isDependenciesValid(['USD']),
            'Everything is fine so we should not fail validation'
        );
    }

    public function testValidateDependencyFail()
    {
        $this->provider->addDependency(new StubConfigDependency(['EUR']));

        $this->assertFalse(
            $this->provider->isDependenciesValid(['USD']),
            'One dependency is failed validation should fail but it is not'
        );
    }
}
