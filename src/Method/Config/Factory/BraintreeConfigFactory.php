<?php
/**
 * Created by PhpStorm.
 * User: adamhall
 * Date: 3/3/19
 * Time: 3:10 AM
 */

namespace Aligent\BraintreeBundle\Method\Config\Factory;


use Aligent\BraintreeBundle\Entity\BraintreeIntegrationSettings;
use Aligent\BraintreeBundle\Method\Config\BraintreeConfig;
use Aligent\BraintreeBundle\Method\Config\BraintreeConfigInterface;
use Doctrine\Common\Collections\Collection;
use Oro\Bundle\IntegrationBundle\Generator\IntegrationIdentifierGeneratorInterface;
use Oro\Bundle\LocaleBundle\Helper\LocalizationHelper;
use Oro\Bundle\SecurityBundle\Encoder\SymmetricCrypterInterface;

class BraintreeConfigFactory implements BraintreeConfigFactoryInterface
{
    /**
     * @var LocalizationHelper
     */
    protected $localizationHelper;

    /**
     * @var IntegrationIdentifierGeneratorInterface
     */
    protected $identifierGenerator;

    /**
     * @var SymmetricCrypterInterface
     */
    protected $encoder;

    /**
     * @param LocalizationHelper $localizationHelper
     * @param IntegrationIdentifierGeneratorInterface $identifierGenerator
     * @param SymmetricCrypterInterface $encoder
     */
    public function __construct(
        LocalizationHelper $localizationHelper,
        IntegrationIdentifierGeneratorInterface $identifierGenerator,
        SymmetricCrypterInterface $encoder
    ) {
        $this->localizationHelper = $localizationHelper;
        $this->identifierGenerator = $identifierGenerator;
        $this->encoder = $encoder;
    }

    /**
     * @param BraintreeIntegrationSettings $settings
     * @return BraintreeConfigInterface
     */
    public function create(BraintreeIntegrationSettings $settings)
    {
        $params = $settings->getSettingsBag();
        $channel = $settings->getChannel();

        return new BraintreeConfig(
            array_merge(
                $params->all(),
                [
                    BraintreeConfig::PUBLIC_KEY_KEY => $this->encoder->decryptData($settings->getPublicKey()),
                    BraintreeConfig::PRIVATE_KEY_KEY => $this->encoder->decryptData($settings->getPrivateKey()),
                    BraintreeConfig::LABEL_KEY => $this->getLocalizedValue($settings->getLabels()),
                    BraintreeConfig::SHORT_LABEL_KEY => $this->getLocalizedValue($settings->getShortLabels()),
                    BraintreeConfig::FIELD_ADMIN_LABEL => $channel->getName(),
                    BraintreeConfig::FIELD_PAYMENT_METHOD_IDENTIFIER => $this->identifierGenerator->generateIdentifier($channel),
                    BraintreeConfig::PAYMENT_METHODS_CONFIG_KEY => $settings->getPaymentMethodSettings()
                ]
            )
        );
    }

    /**
     * @param Collection $values
     *
     * @return string
     */
    private function getLocalizedValue(Collection $values)
    {
        return (string) $this->localizationHelper->getLocalizedValue($values);
    }
}