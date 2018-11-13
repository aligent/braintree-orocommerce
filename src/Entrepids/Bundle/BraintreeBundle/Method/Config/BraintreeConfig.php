<?php
namespace Entrepids\Bundle\BraintreeBundle\Method\Config;

use Entrepids\Bundle\BraintreeBundle\Method\Config\BraintreeForm\BraintreeFormInterface;
use Entrepids\Bundle\BraintreeBundle\Method\EntrepidsBraintreeMethod;
use Oro\Bundle\PaymentBundle\Method\Config\ParameterBag\AbstractParameterBagPaymentConfig;

// sacar la BraintreeFormInterface
class BraintreeConfig extends AbstractParameterBagPaymentConfig implements
    BraintreeConfigInterface
{

    const PAYMENT_ACTION_KEY = 'payment_action';

    // not found in paypal
    const ENVIRONMENT_TYPE = 'environment_type';

    const TYPE = 'entrepids_braintree';

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
    public function getAllowedCreditCards()
    {
        return (array) $this->get(self::ALLOWED_CREDIT_CARD_TYPES_KEY);
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedEnvironmentTypes()
    {
        return (string) $this->get(self::ENVIRONMENT_TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function getBoxMerchId()
    {
        return (string) $this->get(self::MERCH_ID_KEY);
    }

    /**
     * {@inheritdoc}
     */
    public function getBoxMerchAccountId()
    {
        return (string) $this->get(self::MERCH_ACCOUNT_ID_KEY);
    }

    /**
     * {@inheritdoc}
     */
    public function getBoxPublicKey()
    {
        return (string) $this->get(self::PUBLIC_KEY_KEY);
    }

    /**
     * {@inheritdoc}
     */
    public function getBoxPrivateKey()
    {
        return (string) $this->get(self::PRIVATE_KEY_KEY);
    }

    /**
     * {@inheritdoc}
     */
    public function getPurchaseAction()
    {
        return (string) $this->get(self::PAYMENT_ACTION_KEY);
    }

    /**
     * {@inheritdoc}
     */
    public function isEnableSaveForLater()
    {
        return (string) $this->get(self::SAVE_FOR_LATER_KEY);
    }

    /**
     * {@inheritdoc}
     */
    public function isZeroAmountAuthorizationEnabled()
    {
        return (bool) $this->get(self::ZERO_AMOUNT_AUTHORIZATION_KEY);
    }
}
