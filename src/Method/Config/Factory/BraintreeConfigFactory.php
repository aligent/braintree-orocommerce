<?php
/**
 * @category  Aligent
 * @package   BraintreeBundle
 * @author    Adam Hall <adam.hall@aligent.com.au>
 * @copyright 2020 Aligent Consulting.
 * @license
 * @link      http://www.aligent.com.au/
 */

namespace Aligent\BraintreeBundle\Method\Config\Factory;

use Aligent\BraintreeBundle\Entity\BraintreeIntegrationSettings;
use Aligent\BraintreeBundle\Method\Config\BraintreeConfig;
use Aligent\BraintreeBundle\Method\Config\BraintreeConfigInterface;
use Doctrine\Common\Collections\Collection;
use Oro\Bundle\IntegrationBundle\Generator\IntegrationIdentifierGeneratorInterface;
use Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue;
use Oro\Bundle\LocaleBundle\Helper\LocalizationHelper;
use Oro\Bundle\PaymentBundle\Method\Config\ParameterBag\AbstractParameterBagPaymentConfig;
use Oro\Bundle\SecurityBundle\Encoder\SymmetricCrypterInterface;

class BraintreeConfigFactory implements BraintreeConfigFactoryInterface
{
    protected LocalizationHelper $localizationHelper;
    protected IntegrationIdentifierGeneratorInterface $identifierGenerator;
    protected SymmetricCrypterInterface $encoder;

    public function __construct(
        LocalizationHelper $localizationHelper,
        IntegrationIdentifierGeneratorInterface $identifierGenerator,
        SymmetricCrypterInterface $encoder,
    ) {
        $this->localizationHelper = $localizationHelper;
        $this->identifierGenerator = $identifierGenerator;
        $this->encoder = $encoder;
    }

    public function create(BraintreeIntegrationSettings $settings): BraintreeConfigInterface
    {
        $params = $settings->getSettingsBag();
        $channel = $settings->getChannel();

        return new BraintreeConfig(
            array_merge(
                $params->all(),
                [
                    BraintreeConfigInterface::PUBLIC_KEY_KEY => $this->encoder->decryptData($settings->getPublicKey()),
                    BraintreeConfigInterface::PRIVATE_KEY_KEY
                        => $this->encoder->decryptData($settings->getPrivateKey()),
                    BraintreeConfigInterface::LABEL_KEY => $this->getLocalizedValue($settings->getLabels()),
                    BraintreeConfigInterface::SHORT_LABEL_KEY => $this->getLocalizedValue($settings->getShortLabels()),
                    AbstractParameterBagPaymentConfig::FIELD_ADMIN_LABEL => $channel->getName(),
                    AbstractParameterBagPaymentConfig::FIELD_PAYMENT_METHOD_IDENTIFIER
                        => $this->identifierGenerator->generateIdentifier($channel),
                    BraintreeConfigInterface::PAYMENT_METHODS_CONFIG_KEY => $settings->getPaymentMethodSettings()
                ]
            )
        );
    }

    /**
     * @param Collection<int,LocalizedFallbackValue> $values
     * @return string
     */
    private function getLocalizedValue(Collection $values): string
    {
        return (string) $this->localizationHelper->getLocalizedValue($values);
    }
}
