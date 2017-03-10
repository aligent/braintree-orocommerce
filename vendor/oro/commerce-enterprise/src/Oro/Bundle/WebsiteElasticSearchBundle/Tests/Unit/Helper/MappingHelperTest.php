<?php

namespace Oro\Bundle\WebsiteElasticSearchBundle\Tests\Unit\Helper;

use Oro\Bundle\WebsiteElasticSearchBundle\Helper\PlaceholderHelper;
use Oro\Bundle\WebsiteSearchBundle\Placeholder\AbstractPlaceholder;
use Oro\Bundle\WebsiteSearchBundle\Placeholder\PlaceholderRegistry;

class PlaceholderHelperTest extends \PHPUnit_Framework_TestCase
{
    /** @var PlaceholderRegistry|\PHPUnit_Framework_MockObject_MockObject */
    private $placeholderRegistry;

    /** @var PlaceholderHelper */
    private $helper;

    protected function setUp()
    {
        $this->placeholderRegistry = $this->getMockBuilder(PlaceholderRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->helper = new PlaceholderHelper($this->placeholderRegistry);
    }

    /**
     * @return array
     */
    public function aliasMatchDataProvider()
    {
        $placeholder = $this->getMockBuilder(AbstractPlaceholder::class)->getMock();

        $placeholder
            ->expects($this->any())
            ->method('getPlaceholder')
            ->willReturn('WEBSITE_ID');

        $placeholder
            ->expects($this->any())
            ->method('getDefaultValue')
            ->willReturn('[0-9]+');

        return [
            'alias matches pattern' => [
                'alias' => 'oro_test_WEBSITE_ID',
                'aliasValue' => 'oro_test_11',
                'placeholders' => [$placeholder],
                'expectedResult' => true
            ],
            'alias not matches placeholder pattern' => [
                'alias' => 'oro_test_WEBSITE_ID',
                'aliasValue' => 'oro_test_aa',
                'placeholders' => [$placeholder],
                'expectedResult' => false
            ],
        ];
    }

    /**
     * @dataProvider aliasMatchDataProvider
     * @param string $alias
     * @param string $aliasValue
     * @param array $placholders
     * @param bool $expectedResult
     */
    public function testIsAliasMatch($alias, $aliasValue, array $placholders, $expectedResult)
    {
        $this->placeholderRegistry
            ->expects($this->once())
            ->method('getPlaceholders')
            ->willReturn($placholders);

        $this->assertEquals($expectedResult, $this->helper->isAliasMatch($alias, $aliasValue));
    }
}
