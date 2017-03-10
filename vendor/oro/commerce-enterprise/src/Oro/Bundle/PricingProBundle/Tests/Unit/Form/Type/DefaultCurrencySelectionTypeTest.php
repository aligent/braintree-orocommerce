<?php

namespace Oro\Bundle\PricingProBundle\Tests\Unit\Form\Type;

use Oro\Bundle\CurrencyBundle\Provider\CurrencyProviderInterface;
use Oro\Bundle\CurrencyBundle\Utils\CurrencyNameHelper;
use Oro\Bundle\LocaleBundle\Model\LocaleSettings;
use Oro\Bundle\PricingProBundle\Form\Type\DefaultCurrencySelectionType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\FormIntegrationTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\Translator;

class DefaultCurrencySelectionTypeTest extends FormIntegrationTestCase
{
    /**
     * @var CurrencyProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $currencyProvider;

    /**
     * @var LocaleSettings
     */
    protected $localeSettings;

    /**
     * @var Translator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $translator;

    /**
     * @var RequestStack|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestStack;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Oro\Bundle\CurrencyBundle\Utils\CurrencyNameHelper
     */
    protected $currencyNameHelper;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->translator = $this->getMockBuilder(Translator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->localeSettings = $this->getMockBuilder(LocaleSettings::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->currencyProvider = $this->getMockBuilder(CurrencyProviderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->currencyNameHelper = $this
            ->getMockBuilder(CurrencyNameHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestStack = $this->createMock(RequestStack::class);

        parent::setUp();
    }

    /**
     * @return array
     */
    protected function getExtensions()
    {
        return [
            new PreloadedExtension(
                [
                    DefaultCurrencySelectionType::NAME => new DefaultCurrencySelectionType(
                        $this->currencyProvider,
                        $this->localeSettings,
                        $this->translator,
                        $this->requestStack,
                        $this->currencyNameHelper
                    ),
                ],
                []
            ),
        ];
    }

    /**
     * @dataProvider submitFormDataProvider
     * @param array $defaultCurrency
     * @param array $enableCurrencies
     * @param string $submittedValue
     * @param bool $isValid
     */
    public function testSubmitForm(array $defaultCurrency, array $enableCurrencies, $submittedValue, $isValid)
    {
        $this->currencyProvider->expects($this->once())
            ->method('getCurrencyList')
            ->willReturn(['USD', 'CAD', 'EUR']);

        $currentRequest = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();

        $currentRequest->expects($this->once())
            ->method('get')
            ->with('pricing')
            ->willReturn([
                'oro_pricing_pro___default_currency' => $defaultCurrency,
                'oro_pricing_pro___enabled_currencies' => $enableCurrencies
            ]);

        $this->requestStack->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn($currentRequest);

        $form = $this->factory->create('oro_pricing_pro_default_currency_selection');

        /** @var FormInterface|\PHPUnit_Framework_MockObject_MockObject $parentForm */
        $rootForm = $this->createMock(FormInterface::class);
        $rootForm->expects($this->once())
            ->method('getRoot')
            ->willReturn($rootForm);
        $rootForm->expects($this->once())
            ->method('getName')
            ->willReturn('pricing');
        $rootForm->expects($this->once())
            ->method('has')
            ->with('oro_pricing_pro___enabled_currencies')
            ->willReturn(true);

        $form->setParent($rootForm);

        $form->submit($submittedValue);
        $this->assertSame($isValid, $form->isValid());
    }

    /**
     * @return array
     */
    public function submitFormDataProvider()
    {
        return [
            'valid without default' => [
                'defaultCurrency' => [
                    'value' => 'USD'
                ],
                'enableCurrencies' => [
                    'value' => ['USD', 'CAD', 'EUR']
                ],
                'submittedValue' => 'USD',
                'isValid' => true
            ],
            'invalid without default' => [
                'defaultCurrency' => [
                    'value' => 'EUR'
                ],
                'enableCurrencies' => [
                    'value' => ['CAD']
                ],
                'submittedValue' => 'EUR',
                'isValid' => false
            ],
            'valid with defaultCurrency default' => [
                'defaultCurrency' => [
                    'use_parent_scope_value' => true,
                ],
                'enableCurrencies' => [
                    'value' => ['CAD', 'USD']
                ],
                'submittedValue' => '',
                'isValid' => true
            ],
            'invalid with defaultCurrency default' => [
                'defaultCurrency' => [
                    'use_parent_scope_value' => true,
                ],
                'enableCurrencies' => [
                    'value' => ['CAD', 'EUR']
                ],
                'submittedValue' => '',
                'isValid' => false
            ],
            'valid with enableCurrencies default' => [
                'defaultCurrency' => [
                    'value' => 'USD'
                ],
                'enableCurrencies' => [
                    'use_parent_scope_value' => true,
                    'value' => []
                ],
                'submittedValue' => 'USD',
                'isValid' => true
            ],
            'valid with default' => [
                'defaultCurrency' => [
                    'use_parent_scope_value' => true,
                ],
                'enableCurrencies' => [
                    'use_parent_scope_value' => true,
                ],
                'submittedValue' => '',
                'isValid' => true
            ]
        ];
    }

    public function testGetName()
    {
        $formType = new DefaultCurrencySelectionType(
            $this->currencyProvider,
            $this->localeSettings,
            $this->translator,
            $this->requestStack,
            $this->currencyNameHelper
        );
        $this->assertEquals(DefaultCurrencySelectionType::NAME, $formType->getName());
    }
}
