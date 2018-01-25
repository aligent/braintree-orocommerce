<?php
namespace Entrepids\Bundle\BraintreeBundle\Method\Config\Factory;

use Doctrine\Common\Collections\Collection;
use Oro\Bundle\IntegrationBundle\Generator\IntegrationIdentifierGeneratorInterface;
use Oro\Bundle\LocaleBundle\Helper\LocalizationHelper;
use Entrepids\Bundle\BraintreeBundle\Entity\BraintreeSettings;
use Entrepids\Bundle\BraintreeBundle\Method\Config\BraintreeConfig;
use Oro\Bundle\SecurityBundle\Encoder\SymmetricCrypterInterface;

class BraintreeConfigFactory implements BraintreeConfigFactoryInterface
{

    /**
     * @var SymmetricCrypterInterface
     */
    private $encoder;
    
    /**
     *
     * @var LocalizationHelper
     */
    private $localizationHelper;

    /**
     *
     * @var IntegrationIdentifierGeneratorInterface
     */
    private $identifierGenerator;

    /**
     *
     * @param SymmetricCrypterInterface $encoder
     * @param LocalizationHelper $localizationHelper
     * @param IntegrationIdentifierGeneratorInterface $identifierGenerator
     */
    public function __construct(
        SymmetricCrypterInterface $encoder,
        LocalizationHelper $localizationHelper,
        IntegrationIdentifierGeneratorInterface $identifierGenerator
    ) {
        $this->encoder = $encoder;
        $this->localizationHelper = $localizationHelper;
        $this->identifierGenerator = $identifierGenerator;
    }

    /**
     * {@inheritDoc}
     */
    public function create(BraintreeSettings $settings)
    {
        $params = [];
        $channel = $settings->getChannel();
        
        $params[BraintreeConfig::LABEL_KEY] = $this->getLocalizedValue($settings->getBraintreeLabel());
        $params[BraintreeConfig::SHORT_LABEL_KEY] = $this->getLocalizedValue($settings->getBraintreeShortLabel());
        $params[BraintreeConfig::ADMIN_LABEL_KEY] = $channel->getName();
        $params[BraintreeConfig::PAYMENT_METHOD_IDENTIFIER_KEY] =
            $this->identifierGenerator->generateIdentifier($channel);
        $params[BraintreeConfig::ALLOWED_CREDIT_CARD_TYPES_KEY] = $settings->getAllowedCreditCardTypes();
        $params[BraintreeConfig::PAYMENT_ACTION_KEY] = $settings->getBraintreePaymentAction();
        $params[BraintreeConfig::ENVIRONMENT_TYPE] = $settings->getBraintreeEnvironmentType();
        $params[BraintreeConfig::MERCH_ID_KEY] = $settings->getBraintreeMerchId();
        $params[BraintreeConfig::MERCH_ACCOUNT_ID_KEY] = $settings->getBraintreeMerchAccountId();
        $params[BraintreeConfig::PUBLIC_KEY_KEY] = $this->getDecryptedValue($settings->getBraintreeMerchPublicKey());
        $params[BraintreeConfig::PRIVATE_KEY_KEY] = $this->getDecryptedValue($settings->getBraintreeMerchPrivateKey());
        $params[BraintreeConfig::SAVE_FOR_LATER_KEY] = $settings->getSaveForLater();
        $params[BraintreeConfig::ZERO_AMOUNT_AUTHORIZATION_KEY] = $settings->getZeroAmountAuthorization();
        $params[BraintreeConfig::AUTHORIZATION_FOR_REQUIRED_AMOUNT_KEY] =
            $settings->getAuthorizationForRequiredAmount();
        $params[BraintreeConfig::ALLOWED_CREDIT_CARD_TYPES_KEY] = $settings->getAllowedCreditCardTypes();
        return new BraintreeConfig($params);
    }

    /**
     * @param string $value
     * @return string
     */
    protected function getDecryptedValue($value)
    {
        return (string)$this->encoder->decryptData($value);
    }
    
    
    /**
     *
     * @param Collection $values
     *
     * @return string
     */
    private function getLocalizedValue(Collection $values)
    {
        return (string) $this->localizationHelper->getLocalizedValue($values);
    }
}
