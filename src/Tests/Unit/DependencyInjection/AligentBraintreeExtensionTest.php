<?php
/**
 * @category  Aligent
 * @package
 * @author    Chris Rossi <chris.rossi@aligent.com.au>
 * @copyright 2022 Aligent Consulting.
 * @license
 * @link      http://www.aligent.com.au/
 */

namespace Aligent\BraintreeBundle\Tests\Unit\DependencyInjection;

use Aligent\BraintreeBundle\Braintree\PaymentMethod\Settings\Builder\ApplePayConfigurationBuilder;
use Aligent\BraintreeBundle\Braintree\PaymentMethod\Settings\Builder\ChainConfigurationBuilder;
use Aligent\BraintreeBundle\Braintree\PaymentMethod\Settings\Builder\GooglePayConfigurationBuilder;
use Aligent\BraintreeBundle\Braintree\PaymentMethod\Settings\Builder\PayPalConfigurationBuilder;
use Aligent\BraintreeBundle\Braintree\PaymentMethod\Settings\Builder\PayPalCreditConfigurationBuilder;
use Aligent\BraintreeBundle\Braintree\PaymentMethod\Settings\Builder\VenmoConfigurationBuilder;
use Aligent\BraintreeBundle\DependencyInjection\AligentBraintreeExtension;
use Aligent\BraintreeBundle\EventListener\AdvancedFraudEventListener;
use Aligent\BraintreeBundle\EventListener\PurchaseActionEventListener;
use Aligent\BraintreeBundle\Form\Type\BraintreeIntegrationSettingsType;
use Aligent\BraintreeBundle\Form\Type\CreditCardSettingsType;
use Aligent\BraintreeBundle\Form\Type\PaymentMethodSettingsType;
use Aligent\BraintreeBundle\Form\Type\PayPalCreditSettingsType;
use Aligent\BraintreeBundle\Form\Type\PayPalSettingsType;
use Aligent\BraintreeBundle\Integration\BraintreeChannelType;
use Aligent\BraintreeBundle\Integration\BraintreeTransport;
use Aligent\BraintreeBundle\Method\Action\AbstractBraintreeAction;
use Aligent\BraintreeBundle\Method\Action\Provider\BraintreeActionProvider;
use Aligent\BraintreeBundle\Method\Action\PurchaseAction;
use Aligent\BraintreeBundle\Method\Config\Factory\BraintreeConfigFactory;
use Aligent\BraintreeBundle\Method\Config\Provider\BraintreeConfigProvider;
use Aligent\BraintreeBundle\Method\Factory\BraintreeMethodFactory;
use Aligent\BraintreeBundle\Method\Provider\BraintreeMethodProvider;
use Aligent\BraintreeBundle\Method\View\Factory\BraintreeViewFactory;
use Aligent\BraintreeBundle\Method\View\Provider\BraintreeViewProvider;
use Oro\Bundle\TestFrameworkBundle\Test\DependencyInjection\ExtensionTestCase;

class AligentBraintreeExtensionTest extends ExtensionTestCase
{
    public function testLoad(): void
    {
        $this->loadExtension(new AligentBraintreeExtension());

        // Services
        $expectedDefinitions = [
            'aligent_braintree.generator.braintree_config_identifier',
            BraintreeConfigFactory::class,
            BraintreeConfigProvider::class,
            BraintreeViewFactory::class,
            BraintreeViewProvider::class,
            BraintreeActionProvider::class,
            BraintreeMethodFactory::class,
            BraintreeMethodProvider::class,
            ChainConfigurationBuilder::class,
            ApplePayConfigurationBuilder::class,
            GooglePayConfigurationBuilder::class,
            PayPalCreditConfigurationBuilder::class,
            PayPalConfigurationBuilder::class,
            VenmoConfigurationBuilder::class,
            BraintreeChannelType::class,
            BraintreeTransport::class,
            BraintreeIntegrationSettingsType::class,
            PaymentMethodSettingsType::class,
            CreditCardSettingsType::class,
            PayPalSettingsType::class,
            PayPalCreditSettingsType::class,
            PurchaseActionEventListener::class,
            AdvancedFraudEventListener::class,
            AbstractBraintreeAction::class,
            PurchaseAction::class,

        ];
        $this->assertDefinitionsLoaded($expectedDefinitions);

        $expectedExtensionConfigs = ['aligent_braintree'];
        $this->assertExtensionConfigsLoaded($expectedExtensionConfigs);
    }
}
