<?php
namespace Entrepids\Bundle\BraintreeBundle\Method\Config;

use Entrepids\Bundle\BraintreeBundle\Method\BraintreeMethod;
use Oro\Bundle\PaymentBundle\Method\Config\AbstractPaymentConfig;
use Oro\Bundle\PaymentBundle\Method\Config\CountryAwarePaymentConfigTrait;
use Oro\Bundle\PaymentBundle\Method\Config\ParameterBag\AbstractParameterBagPaymentConfig;

class BraintreeConfig extends AbstractParameterBagPaymentConfig implements BraintreeConfigInterface
{

    const LABEL_KEY = 'label';

    const SHORT_LABEL_KEY = 'short_label';

    const ADMIN_LABEL_KEY = 'admin_label';

    const PAYMENT_METHOD_IDENTIFIER_KEY = 'payment_method_identifier';

    const PAYMENT_ACTION_KEY = 'payment_action';

    const ENVIRONMENT_TYPE = "environment_type";

    const TYPE = 'braintree';

    const MERCH_ID_KEY = "merch_id";

    const MERCH_ACCOUNT_ID_KEY = "merch_account_id";

    const PUBLIC_KEY_KEY = "public_key";

    const PRIVATE_KEY_KEY = "private_key";

    const SAVE_FOR_LATER_KEY = "save_for_later";

    const ZERO_AMOUNT_AUTHORIZATION_KEY = 'zero_amount_authorization';

    const AUTHORIZATION_FOR_REQUIRED_AMOUNT_KEY = 'authorization_for_required_amount';

    const ALLOWED_CREDIT_CARD_TYPES_KEY = 'allowed_credit_card_types';

    const PAYMETHOD_NONCE = 'payment_method_nonce';

    const CLIENT_TOKEN = 'braintree_client_token';

    const CREDIT_CARD_VALUE = 'credit_card_value';

    const CREDIT_CARD_SELECTED = 'credit_cards_saved';

    const CREDIT_CARD_FIRST_VALUE = 'credit_card_first_value';

    /**
     * {@inheritDoc}
     */
    public function __construct(array $parameters)
    {
        parent::__construct($parameters);
    }

    /**
     * {@inheritdoc}
     */
    protected function getPaymentExtensionAlias()
    {
        return BraintreeMethod::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return (string) $this->get(self::CREDIT_CARD_ENABLED_KEY);
    }

    /**
     *
     * @ERROR!!!
     *
     */
    public function getAllowedCreditCards()
    {
        return (array) $this->get(self::ALLOWED_CREDIT_CARD_TYPES_KEY);
    }

    /**
     *
     * @ERROR!!!
     *
     */
    public function getAllowedEnvironmentTypes()
    {
        return (string) $this->get(self::ENVIRONMENT_TYPE);
    }

    /**
     *
     * @ERROR!!!
     *
     */
    public function getSandBoxMerchId()
    {
        return (string) $this->get(self::MERCH_ID_KEY);
    }

    /**
     *
     * @ERROR!!!
     *
     */
    public function getSandBoxMerchAccountId()
    {
        return (string) $this->get(self::MERCH_ACCOUNT_ID_KEY);
    }

    /**
     *
     * @ERROR!!!
     *
     */
    public function getSandBoxPublickKey()
    {
        return (string) $this->get(self::PUBLIC_KEY_KEY);
    }

    /**
     *
     * @ERROR!!!
     *
     */
    public function getSandBoxPrivateKey()
    {
        return (string) $this->get(self::PRIVATE_KEY_KEY);
    }

    /**
     *
     * @ERROR!!!
     *
     */
    public function getPurchaseAction()
    {
        return (string) $this->get(self::PAYMENT_ACTION_KEY);
    }

    /**
     *
     * @ERROR!!!
     *
     */
    public function isEnableSaveForLater()
    {
        return (string) $this->get(self::SAVE_FOR_LATER_KEY);
    }

    /**
     *
     * @ERROR!!!
     *
     */
    public function isZeroAmountAuthorizationEnabled()
    {
        return (bool) $this->get(self::ZERO_AMOUNT_AUTHORIZATION_KEY);
    }

    /**
     *
     * @ERROR!!!
     *
     */
    public function getPaymentMethodNonce()
    {
        return (string) $this->get(self::PAYMETHOD_NONCE);
    }

    /**
     *
     * @ERROR!!!
     *
     */
    public function getBraintreeClientToken()
    {
        return (string) $this->get(self::CLIENT_TOKEN);
    }

    /**
     *
     * @ERROR!!!
     *
     */
    public function getCreditCardValue()
    {
        return (string) $this->get(self::CREDIT_CARD_VALUE);
    }

    /**
     *
     * @ERROR!!!
     *
     */
    public function getCreditCardFirstValue()
    {
        return (string) $this->get(self::CREDIT_CARD_FIRST_VALUE);
    }

    /**
     *
     * @ERROR!!!
     *
     */
    public function getCreditCardsSaved()
    {
        return (string) $this->get(self::CREDIT_CARD_SELECTED);
    }
}
