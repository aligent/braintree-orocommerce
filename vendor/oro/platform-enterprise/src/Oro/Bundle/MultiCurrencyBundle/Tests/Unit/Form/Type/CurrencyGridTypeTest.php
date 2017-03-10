<?php
namespace Oro\Bundle\CurrencyBundle\Tests\Unit\Form\Type;

use Genemu\Bundle\FormBundle\Tests\Form\Type\TypeTestCase;

use Oro\Bundle\MultiCurrencyBundle\Form\Type\CurrencyGridType;

class CurrencyGridTypeTest extends TypeTestCase
{
    /**
     * @var CurrencyGridType
     */
    protected $type;

    /** @var  \PHPUnit_Framework_MockObject_MockObject|\Oro\Bundle\ConfigBundle\Config\ConfigManager */
    protected $configManager;

    /** @var  \PHPUnit_Framework_MockObject_MockObject|\Oro\Bundle\CurrencyBundle\Utils\CurrencyNameHelper */
    protected $currencyNameHelper;

    /**
     * Setup test env
     */
    public function setUp()
    {
        parent::setUp();
        $this->configManager = $this
            ->getMockBuilder('Oro\Bundle\ConfigBundle\Config\ConfigManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->currencyNameHelper = $this
            ->getMockBuilder('Oro\Bundle\CurrencyBundle\Utils\CurrencyNameHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->type = new CurrencyGridType($this->configManager, $this->currencyNameHelper);
    }

    protected function createRequestMock()
    {
        return $this->createMock('Symfony\Component\HttpFoundation\Request');
    }

    public function testBuildForm()
    {
        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $builder->expects($this->exactly(1))
            ->method('addViewTransformer')
            ->will($this->returnSelf());

        $builder->expects($this->any())
            ->method('addEventSubscriber')
            ->with($this->isInstanceOf('Symfony\Component\EventDispatcher\EventSubscriberInterface'))
            ->will($this->returnSelf());

        $this->type->buildForm($builder, array());
    }

    public function testSetDefaultOptions()
    {
        $resolver = $this->getMockBuilder('Symfony\Component\OptionsResolver\OptionsResolver')
            ->disableOriginalConstructor()
            ->getMock();
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with($this->isType('array'));

        $this->type->configureOptions($resolver);
    }

    /**
     * @dataProvider currenciesConfigProvider
     *
     * @param bool                                                $restrict
     * @param array                                               $currencyChoices
     * @param \PHPUnit_Framework_MockObject_Stub_ConsecutiveCalls $currencyNamesConsecutiveCallsStub
     * @param array                                               $currencyCollection
     */
    public function testBuildView(
        $restrict,
        $currencyNamesConsecutiveCallsStub,
        $currencyChoices,
        array $currencyCollection
    ) {
        $this->configManager
            ->method('get')
            ->with('oro_multi_currency.allowed_currencies')
            ->willReturn($currencyChoices);

        $this->currencyNameHelper
            ->method('getCurrencyName')
            ->will($currencyNamesConsecutiveCallsStub);

        $this->currencyNameHelper
            ->method('getCurrencyFilteredList')
            ->willReturn(
                [
                    'USD' => 'US Dollar',
                    'GBP' => 'British Pound',
                    'EUR' => 'Euro',
                    'UAH' => 'Ukrainian Hryvnia'
                ]
            );
        $form = $this->factory->create(
            new CurrencyGridType($this->configManager, $this->currencyNameHelper),
            $currencyChoices,
            ['restrict' => $restrict]
        );

        $view = $form->createView();

        $this->assertArrayHasKey('currencyCollection', $view->vars);
        $this->assertEquals($currencyCollection, $view->vars['currencyCollection']);
    }

    public function currenciesConfigProvider()
    {
        return [
            'currencies restrict with organization' => [
                true,
                $this->onConsecutiveCalls(
                    'USD_NAME',
                    'USD_SYMBOL',
                    'EUR_NAME',
                    'EUR_SYMBOL'
                ),
                [0 => 'USD', 1 => 'EUR'],
                [
                    'USD' => [
                        'code' => 'USD',
                        'name' => 'USD_NAME',
                        'symbol' => 'USD_SYMBOL'
                    ],
                    'EUR' => [
                        'code' => 'EUR',
                        'name' => 'EUR_NAME',
                        'symbol' => 'EUR_SYMBOL'
                    ]
                ]
            ],
            'currencies in system config' => [
                false,
                $this->onConsecutiveCalls(
                    'USD_NAME',
                    'USD_SYMBOL',
                    'GBP_NAME',
                    'GBP_SYMBOL',
                    'EUR_NAME',
                    'EUR_SYMBOL',
                    'UAH_NAME',
                    'UAH_SYMBOL'
                ),
                [0 => 'USD', 1 => 'EUR'],
                [
                    'USD' => [
                        'code' => 'USD',
                        'name' => 'USD_NAME',
                        'symbol' => 'USD_SYMBOL'
                    ],
                    'EUR' => [
                        'code' => 'EUR',
                        'name' => 'EUR_NAME',
                        'symbol' => 'EUR_SYMBOL'
                    ],
                    'GBP' => [
                        'code' => 'GBP',
                        'name' => 'GBP_NAME',
                        'symbol' => 'GBP_SYMBOL'
                    ],
                    'UAH' => [
                        'code' => 'UAH',
                        'name' => 'UAH_NAME',
                        'symbol' => 'UAH_SYMBOL'
                    ],

                ]
            ]
        ];
    }

    public function testGetName()
    {
        $this->assertEquals('oro_currency_grid', $this->type->getName());
    }
}
