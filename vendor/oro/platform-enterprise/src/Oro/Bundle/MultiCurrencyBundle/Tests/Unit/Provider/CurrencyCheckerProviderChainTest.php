<?php

namespace Oro\Bundle\MultiCurrencyBundle\Tests\Unit\Provider;

use Symfony\Bundle\FrameworkBundle\Tests\Templating\Helper\Fixtures\StubTranslator;

use Oro\Bundle\CurrencyBundle\Provider\RepositoryCurrencyCheckerProviderInterface;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\MultiCurrencyBundle\Provider\CurrencyCheckerProviderChain;

class CurrencyCheckerProviderChainTest extends \PHPUnit_Framework_TestCase implements
    RepositoryCurrencyCheckerProviderInterface
{
    /**
     * @var CurrencyCheckerProviderChain
     */
    private $chainProvider;

    protected function setUp()
    {
        $this->chainProvider = new CurrencyCheckerProviderChain(new StubTranslator());
        $this->chainProvider->addProvider($this);
    }

    public function testGetEntityLabelsWithMissedCurrenciesFoundNothing()
    {
        $this->assertEmpty(
            $this->chainProvider->getEntityLabelsWithMissedCurrencies(['USD', 'EUR'])
        );
    }

    public function testGetEntityLabelsWithMissedCurrenciesFoundEntity()
    {
        $wrongEntities = $this->chainProvider->getEntityLabelsWithMissedCurrencies(
            ['USD', 'EUR', 'GBP']
        );
        $this->assertEquals('[trans]TestEntity[/trans]', reset($wrongEntities));
    }

    /**
     * {@inheritdoc}
     */
    public function hasRecordsWithRemovingCurrencies(
        array $removingCurrencies,
        Organization $organization = null
    ) {
        // We pretend that GBP is used by TestEntity
        return in_array('GBP', $removingCurrencies, true);
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityLabel()
    {
        return 'TestEntity';
    }
}
