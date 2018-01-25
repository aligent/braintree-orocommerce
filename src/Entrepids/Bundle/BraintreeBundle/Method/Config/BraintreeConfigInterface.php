<?php
namespace Entrepids\Bundle\BraintreeBundle\Method\Config;

use Oro\Bundle\PaymentBundle\Method\Config\PaymentConfigInterface;

interface BraintreeConfigInterface extends PaymentConfigInterface
{

    /**
     *
     * @return array
     */
    public function getAllowedCreditCards();

    /**
     *
     * @return string
     */
    public function getAllowedEnvironmentTypes();
    
    /**
     *
     * @return string
     */
    public function getBoxMerchId();

    /**
     *
     * @return string
     */
    public function getBoxMerchAccountId();

    /**
     *
     * @return string
     */
    public function getBoxPublickKey();

    /**
     *
     * @return string
     */
    public function getBoxPrivateKey();

    /**
     *
     * @return string
     */
    public function getPurchaseAction();

    /**
     *
     * @return bool
     */
    public function isEnableSaveForLater();

    /**
     *
     * @return bool
     */
    public function isZeroAmountAuthorizationEnabled();
}
