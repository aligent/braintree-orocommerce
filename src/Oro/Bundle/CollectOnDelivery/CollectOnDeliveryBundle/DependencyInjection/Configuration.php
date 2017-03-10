<?php

namespace Oro\Bundle\CollectOnDelivery\CollectOnDeliveryBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

use Oro\Bundle\ConfigBundle\DependencyInjection\SettingsBuilder;
use Oro\Bundle\CurrencyBundle\DependencyInjection\Configuration as CurrencyConfiguration;
use Oro\Bundle\PaymentBundle\DependencyInjection\Configuration as PaymentConfiguration;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
	
	const COLLECT_ON_DELIVERY_ENABLED_KEY = 'collect_on_delivery_enabled';
	const COLLECT_ON_DELIVERY_LABEL_KEY = 'collect_on_delivery_label';
	const COLLECT_ON_DELIVERY_SHORT_LABEL_KEY = 'collect_on_delivery_short_label';
	const COLLECT_ON_DELIVERY_SORT_ORDER_KEY = 'collect_on_delivery_sort_order';
	const COLLECT_ON_DELIVERY_ALLOWED_COUNTRIES_KEY = 'collect_on_delivery_allowed_countries';
	const COLLECT_ON_DELIVERY_SELECTED_COUNTRIES_KEY = 'collect_on_delivery_selected_countries';
	const COLLECT_ON_DELIVERY_ALLOWED_CURRENCIES = 'collect_on_delivery_allowed_currencies';
	const COLLECT_ON_DELIVERY_PRO_ALLOWED_CC_TYPES_KEY = 'collect_on_delivery_allowed_cc_types';
	
	const CARD_VISA = 'visa';
	const CARD_MASTERCARD = 'mastercard';
	const CARD_DISCOVER = 'discover';
	const CARD_AMERICAN_EXPRESS = 'american_express';	
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('collect_on_delivery');

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.
        
        SettingsBuilder::append(
        		$rootNode,
        		[
        				self::COLLECT_ON_DELIVERY_ENABLED_KEY => [
        						'type' => 'boolean',
        						'value' => false,
        				],
        				self::COLLECT_ON_DELIVERY_LABEL_KEY => [
        						'type' => 'text',
        						'value' => 'Collect On Delivery',
        				],
        				self::COLLECT_ON_DELIVERY_SHORT_LABEL_KEY => [
        						'type' => 'text',
        						'value' => 'Collect On Delivery',
        				],
        				self::COLLECT_ON_DELIVERY_SORT_ORDER_KEY => [
        						'type' => 'string',
        						'value' => 60,
        				],
        				self::COLLECT_ON_DELIVERY_ALLOWED_COUNTRIES_KEY => [
        						'type' => 'text',
        						'value' => PaymentConfiguration::ALLOWED_COUNTRIES_ALL,
        				],
        				self::COLLECT_ON_DELIVERY_SELECTED_COUNTRIES_KEY => [
        						'type' => 'array',
        						'value' => [],
        				],
        				self::COLLECT_ON_DELIVERY_ALLOWED_CURRENCIES => [
        						'type' => 'array',
        						'value' => CurrencyConfiguration::$defaultCurrencies,
        				],
        				self::COLLECT_ON_DELIVERY_PRO_ALLOWED_CC_TYPES_KEY => [
        						'type' => 'array',
        						'value' => [self::CARD_VISA, self::CARD_MASTERCARD]
        				],        				
        		]
        );
        

        return $treeBuilder;
    }
}
