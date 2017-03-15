<?php

namespace Entrepids\Bundle\BraintreeBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Oro\Bundle\ConfigBundle\DependencyInjection\SettingsBuilder;
use Oro\Bundle\PaymentBundle\Method\PaymentMethodInterface;
use Oro\Bundle\CurrencyBundle\DependencyInjection\Configuration as CurrencyConfiguration;
use Oro\Bundle\PaymentBundle\DependencyInjection\Configuration as PaymentConfiguration;
/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
	
	// Seccion de Detalles
	const BRAINTREE_ENABLED_KEY = 'braintree_enabled';
	const BRAINTREE_LABEL_KEY = 'braintree_label';
	const BRAINTREE_SHORT_LABEL_KEY = 'braintree_short_label';
	const BRAINTREE_SORT_ORDER_KEY = 'braintree_sort_order';
	const BRAINTREE_ALLOWED_COUNTRIES_KEY = 'braintree_allowed_countries';
	const BRAINTREE_SELECTED_COUNTRIES_KEY = 'braintree_selected_countries';
	const BRAINTREE_ALLOWED_CURRENCIES = 'braintree_allowed_currencies';
	const BRAINTREE_PRO_ALLOWED_CC_TYPES_KEY = 'braintree_allowed_cc_types';
	// Seccion de Braintree Account Details
	const BRAINTREE_ENVIRONMENT_TYPES = 'braintree_environment_types';
	const BRAINTREE_SANDBOX_MERCH_ID = 'braintree_sandbox_merch_id';
	const BRAINTREE_SANDBOX_ACCOUNT_ID = 'braintree_sandbox_merch_account_id';
	const BRAINTREE_SANDBOX_PUBLIC_KEY = 'braintree_sandbox_merch_public_key';
	const BRAINTREE_SANDBOX_PRIVATE_KEY = 'braintree_sandbox_merch_private_key';
	// Seccion de Credit Card
	const BRAINTREE_CREDIT_CARD_ENABLED = 'braintree_credit_card_enabled';
	const BRAINTREE_CREDIT_CARD_TITLE = 'braintree_credit_card_title';
	// Seccion de Capture
	const BRAINTREE_CAPTURE_PAYMENT_ACTION = 'braintree_capture_payment_action';
	const BRAINTREE_CAPTURE_CAPTURE_ACTION = 'braintree_capture_capture_action';
	const BRAINTREE_CAPTURE_NEW_ORDER_STATUS = 'braintree_capture_new_order_status';
	// Seccion de features
	const BRAINTREE_FEATURES_ENABLED_VAULT_SAVED_CARDS = 'braintree_features_enabled_vault_saved_cards';
	const BRAINTREE_FEATURES_CVV_VERIFICATION = 'braintree_features_cvv_verification';
	const BRAINTREE_FEATURES_DISPLAY_CARD_TYPES = 'braintree_features_display_card_types';


	const CARD_VISA = 'visa';
	const CARD_MASTERCARD = 'mastercard';
	const CARD_DISCOVER = 'discover';
	const CARD_AMERICAN_EXPRESS = 'american_express';	
	
	const SANDBOX = 'SandBox';
	const PRODUCTION = 'Production';
	
	const CAPTURE_ACTION_INVOICE = 'Invoice';
	const CAPTURE_ACTION_SHIPMENT = 'Shipment';
	const CAPTURE_ACTION_AUTHORIZED = 'Authorized';
	
	const NEWORDER_ACTION_PROCESSING = 'Processing';
	const NEWORDER_ACTION_SUSPECTEDFRAUD = 'Suspected Fraud';
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('braintree');

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.
        SettingsBuilder::append(
        		$rootNode,
        		[
        				self::BRAINTREE_ENABLED_KEY => [
        						'type' => 'boolean',
        						'value' => false,
        				],
        				self::BRAINTREE_LABEL_KEY => [
        						'type' => 'text',
        						'value' => 'Braintree',
        				],
        				self::BRAINTREE_SHORT_LABEL_KEY => [
        						'type' => 'text',
        						'value' => 'Braintree',
        				],
        				self::BRAINTREE_SORT_ORDER_KEY => [
        						'type' => 'string',
        						'value' => 60,
        				],
        				self::BRAINTREE_ALLOWED_COUNTRIES_KEY => [
        						'type' => 'text',
        						'value' => PaymentConfiguration::ALLOWED_COUNTRIES_ALL,
        				],
        				self::BRAINTREE_SELECTED_COUNTRIES_KEY => [
        						'type' => 'array',
        						'value' => [],
        				],
        				self::BRAINTREE_ALLOWED_CURRENCIES => [
        						'type' => 'array',
        						'value' => CurrencyConfiguration::$defaultCurrencies,
        				],
        				self::BRAINTREE_PRO_ALLOWED_CC_TYPES_KEY => [
        						'type' => 'array',
        						'value' => [self::CARD_VISA, self::CARD_MASTERCARD]
        				],
        				self::BRAINTREE_ENVIRONMENT_TYPES => [
        						'type' => 'text',
        						'value' => self::SANDBOX
        				],
        				self::BRAINTREE_SANDBOX_MERCH_ID => [
        						'type' => 'text',
        						'value' => '',
        				],     
        				self::BRAINTREE_SANDBOX_ACCOUNT_ID => [
        						'type' => 'text',
        						'value' => '',
        				],
        				self::BRAINTREE_SANDBOX_PUBLIC_KEY => [
        						'type' => 'text',
        						'value' => '',
        				],
        				self::BRAINTREE_SANDBOX_PRIVATE_KEY => [
        						'type' => 'text',
        						'value' => '',
        				],     
        				self::BRAINTREE_CREDIT_CARD_ENABLED => [
        						'type' => 'boolean',
        						'value' => true,
        				],   
        				self::BRAINTREE_CREDIT_CARD_TITLE => [
        						'type' => 'text',
        						'value' => '',
        				],  
        				self::BRAINTREE_CAPTURE_PAYMENT_ACTION => [
        						'type' => 'text',
        						'value' => PaymentMethodInterface::AUTHORIZE
        				],
        				self::BRAINTREE_CAPTURE_CAPTURE_ACTION => [
        						'type' => 'text',
        						'value' => self::CAPTURE_ACTION_INVOICE
        				],
        				self::BRAINTREE_CAPTURE_NEW_ORDER_STATUS => [
        						'type' => 'text',
        						'value' => self::NEWORDER_ACTION_PROCESSING
        				],  
        				self::BRAINTREE_FEATURES_ENABLED_VAULT_SAVED_CARDS => [
      						'type' => 'boolean',
        						'value' => true,
        				],
        				self::BRAINTREE_FEATURES_CVV_VERIFICATION => [
      						'type' => 'boolean',
        						'value' => true,
        				],
        				self::BRAINTREE_FEATURES_DISPLAY_CARD_TYPES => [
      						'type' => 'boolean',
        						'value' => true,
        				],
     				
        		]
        );
        return $treeBuilder;
    }
}
