<?php

namespace Oro\Bundle\UserProBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

use Oro\Bundle\UserProBundle\Provider\TFAConfigProvider;

class TwoFactorTokenFactory implements DecoratedTokenFactoryInterface
{
    /** @var TFAConfigProvider */
    protected $configProvider;

    /**
     * @param TFAConfigProvider $configProvider
     */
    public function __construct(TFAConfigProvider $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function create(TokenInterface $decoratedToken)
    {
        return new TwoFactorToken(
            $decoratedToken,
            $this->configProvider->getCodeExpiryDate()
        );
    }
}
